<?php

require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/Attendance.php';
require_once __DIR__ . '/../Models/Score.php';
require_once __DIR__ . '/../Models/Announcement.php';
require_once __DIR__ . '/../Models/GradeScale.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class StudentController
{
    private Student $studentModel;
    private Attendance $attendanceModel;
    private Score $scoreModel;
    private Announcement $announcementModel;
    private GradeScale $gradeScaleModel;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->attendanceModel = new Attendance();
        $this->scoreModel = new Score();
        $this->announcementModel = new Announcement();
        $this->gradeScaleModel = new GradeScale();
    }

    public function dashboard(): void
    {
        AuthMiddleware::requireRole('student');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Bridge from the logged-in user to their own student profile
        $student = $this->studentModel->findByUserId($_SESSION['user_id']);

        if (!$student) {
            echo "Student profile not found. Please contact the administrator.";
            exit;
        }

        $attendance = $this->attendanceModel->forStudent($student['id']);
        $scores = $this->scoreModel->forStudent($student['id']);
        $classId = $this->studentModel->getCurrentClassId($student['id']);
        $announcements = $this->announcementModel->forDashboard($classId);

        // Build the report card: attach a letter grade to each subject average
        $reportCard = $this->scoreModel->reportCardForStudent($student['id']);
        $subjectAverages = [];
        foreach ($reportCard as &$row) {
            $grade = $this->gradeScaleModel->forScore((float) $row['average_score']);
            $row['letter'] = $grade['letter'] ?? '—';
            $row['remark'] = $grade['remark'] ?? '—';
            $subjectAverages[] = (float) $row['average_score'];
        }
        unset($row); // break the reference from the foreach loop above

        $overallAverage = !empty($subjectAverages) ? array_sum($subjectAverages) / count($subjectAverages) : null;
        $overallGrade = $overallAverage !== null ? $this->gradeScaleModel->forScore($overallAverage) : null;

        require __DIR__ . '/../../views/student/dashboard.php';
    }
}