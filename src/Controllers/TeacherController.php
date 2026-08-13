<?php

require_once __DIR__ . '/../Models/Teacher.php';
require_once __DIR__ . '/../Models/ClassSubjectTeacher.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class TeacherController
{
    private Teacher $teacherModel;
    private ClassSubjectTeacher $assignmentModel;

    public function __construct()
    {
        $this->teacherModel = new Teacher();
        $this->assignmentModel = new ClassSubjectTeacher();
    }

    public function dashboard(): void
    {
        AuthMiddleware::requireRole('teacher');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Bridge from the logged-in user to their teacher profile
        $teacher = $this->teacherModel->findByUserId($_SESSION['user_id']);

        if (!$teacher) {
            // Safety net: a 'teacher' role user with no matching teacher profile
            // shouldn't normally happen, but we handle it gracefully rather than crash
            echo "Teacher profile not found. Please contact the administrator.";
            exit;
        }

        $assignments = $this->assignmentModel->forTeacher($teacher['id']);

        require __DIR__ . '/../../views/teacher/dashboard.php';
    }
}