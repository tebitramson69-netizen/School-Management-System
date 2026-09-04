<?php

require_once __DIR__ . '/../Models/ParentModel.php';
require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/Attendance.php';
require_once __DIR__ . '/../Models/Score.php';
require_once __DIR__ . '/../Models/Announcement.php';
require_once __DIR__ . '/../Models/GradeScale.php';
require_once __DIR__ . '/../Models/Result.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Core/School.php';
class ParentController
{
    private ParentModel $parentModel;
    private Student $studentModel;
    private Attendance $attendanceModel;
    private Score $scoreModel;
    private Announcement $announcementModel;
    private GradeScale $gradeScaleModel;
    private Result $resultModel;

    public function __construct()
    {
        $this->parentModel = new ParentModel();
        $this->studentModel = new Student();
        $this->attendanceModel = new Attendance();
        $this->scoreModel = new Score();
        $this->announcementModel = new Announcement();
        $this->gradeScaleModel = new GradeScale();
        $this->resultModel = new Result();
    }

    public function dashboard(): void
    {
        AuthMiddleware::requireRole('parent');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $parent = $this->parentModel->findByUserId($_SESSION['user_id']);

        if (!$parent) {
            echo "Parent profile not found. Please contact the administrator.";
            exit;
        }

        $children = $this->parentModel->getChildren($parent['id']);

        $school = School::settings();

        require __DIR__ . '/../../views/parent/dashboard.php';
    }

    public function childDetail(): void
    {
        AuthMiddleware::requireRole('parent');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $parent = $this->parentModel->findByUserId($_SESSION['user_id']);
        $studentId = (int) ($_GET['student_id'] ?? 0);

        // Security check: confirm this parent is actually linked to this child,
        // so nobody can view another family's child by editing the URL
        if (!$this->parentModel->isLinkedToChild($parent['id'], $studentId)) {
            http_response_code(403);
            echo "Access denied. This is not your child.";
            exit;
        }

        $student = $this->studentModel->find($studentId);
        $attendance = $this->attendanceModel->forStudent($studentId);
        $scores = $this->scoreModel->forStudent($studentId);
        $classId = $this->studentModel->getCurrentClassId($studentId);
        $announcements = $this->announcementModel->forDashboard($classId);

        $latestTermId = $this->scoreModel->latestTermIdForStudent($studentId);

        $reportCard = [];
        $overallAverage = null;
        $overallGrade = null;

        if ($latestTermId !== null) {
            $reportCard = $this->scoreModel->subjectAveragesForStudentTerm($studentId, $latestTermId);
            foreach ($reportCard as &$row) {
                $grade = $this->gradeScaleModel->forScore((float) $row['average_score']);
                $row['letter'] = $grade['letter'] ?? '—';
                $row['remark'] = $grade['remark'] ?? '—';
            }
            unset($row);

            $overallAverage = $this->scoreModel->overallAverageForStudentTerm(
                $studentId,
                $latestTermId,
                (int) ($classId ?? 0)
            );
            $overallGrade = $overallAverage !== null
                ? $this->gradeScaleModel->forScore($overallAverage)
                : null;
        }

        // Class position (ranking) for the latest term with scores
        $classPosition = null;
        $classSize = null;

        $enrollment = $this->studentModel->getCurrentEnrollment($studentId);

        if ($latestTermId !== null && $enrollment !== null) {
            $classResults = $this->resultModel->getClassResults(
                $enrollment['class_id'],
                $enrollment['academic_year_id'],
                $latestTermId
            );

            $classSize = 0;
            foreach ($classResults as $classResult) {
                if ((int) $classResult['student_id'] === $studentId) {
                    $classPosition = $classResult['position'];
                }
                if ($classResult['overall_average'] !== null) {
                    $classSize++;
                }
            }
        }

        $school = School::settings();

        require __DIR__ . '/../../views/parent/child_detail.php';
    }
}