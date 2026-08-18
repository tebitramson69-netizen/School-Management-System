<?php

require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/Attendance.php';
require_once __DIR__ . '/../Models/Score.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class StudentController
{
    private Student $studentModel;
    private Attendance $attendanceModel;
    private Score $scoreModel;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->attendanceModel = new Attendance();
        $this->scoreModel = new Score();
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

        require __DIR__ . '/../../views/student/dashboard.php';
    }
}