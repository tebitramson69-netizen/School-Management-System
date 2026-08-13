<?php

require_once __DIR__ . '/../Models/Teacher.php';
require_once __DIR__ . '/../Models/ClassSubjectTeacher.php';
require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/Attendance.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class TeacherController
{
    private Teacher $teacherModel;
    private ClassSubjectTeacher $assignmentModel;
    private Student $studentModel;
    private Attendance $attendanceModel;

    public function __construct()
    {
        $this->teacherModel = new Teacher();
        $this->assignmentModel = new ClassSubjectTeacher();
        $this->studentModel = new Student();
        $this->attendanceModel = new Attendance();
    }

    public function dashboard(): void
    {
        AuthMiddleware::requireRole('teacher');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $teacher = $this->teacherModel->findByUserId($_SESSION['user_id']);

        if (!$teacher) {
            echo "Teacher profile not found. Please contact the administrator.";
            exit;
        }

        $assignments = $this->assignmentModel->forTeacher($teacher['id']);

        require __DIR__ . '/../../views/teacher/dashboard.php';
    }

    public function showMarkAttendanceForm(): void
    {
        AuthMiddleware::requireRole('teacher');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $teacher = $this->teacherModel->findByUserId($_SESSION['user_id']);
        $assignmentId = (int) ($_GET['assignment_id'] ?? 0);

        $assignment = $this->assignmentModel->find($assignmentId);

        // Security check: confirm this assignment actually belongs to this teacher,
        // so nobody can mark another teacher's subject period by editing the URL
        if (!$assignment || (int) $assignment['teacher_id'] !== $teacher['id']) {
            http_response_code(403);
            echo "Access denied. This is not your assignment.";
            exit;
        }

        $date = date('Y-m-d');
        $students = $this->studentModel->allByClass($assignment['class_id']);
        $existingAttendance = $this->attendanceModel->getForAssignmentDate($assignmentId, $date);

        require __DIR__ . '/../../views/teacher/mark_attendance.php';
    }

    public function markAttendance(): void
    {
        AuthMiddleware::requireRole('teacher');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $teacher = $this->teacherModel->findByUserId($_SESSION['user_id']);
        $assignmentId = (int) ($_POST['assignment_id'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $statuses = $_POST['status'] ?? [];

        $assignment = $this->assignmentModel->find($assignmentId);

        if (!$assignment || (int) $assignment['teacher_id'] !== $teacher['id']) {
            http_response_code(403);
            echo "Access denied. This is not your assignment.";
            exit;
        }

        $this->attendanceModel->markBulk($assignmentId, $date, $statuses, $teacher['id']);

        $_SESSION['success_message'] = "Attendance saved for {$assignment['subject_name']} ({$assignment['class_name']}) on " . date('F j, Y', strtotime($date)) . ".";
        header('Location: ' . BASE_URL . '/index.php?action=teacher_dashboard');
        exit;
    }
}