<?php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Teacher.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class AdminController
{
    private User $userModel;
    private Teacher $teacherModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->teacherModel = new Teacher();
    }

    public function dashboard(): void
    {
        AuthMiddleware::requireRole('admin');
        require __DIR__ . '/../../views/admin/dashboard.php';
    }

    public function showCreateTeacherForm(): void
    {
        AuthMiddleware::requireRole('admin');
        require __DIR__ . '/../../views/admin/create_teacher.php';
    }

    public function createTeacher(): void
    {
        AuthMiddleware::requireRole('admin');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic validation
        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }

        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if ($this->userModel->emailExists($email)) {
            $errors[] = 'This email is already registered.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = ['email' => $email, 'full_name' => $fullName, 'phone' => $phone];
            header('Location: ' . BASE_URL . '/index.php?action=create_teacher_form');
            exit;
        }

        // Create the user record, then the teacher profile linked to it
        $userId = $this->userModel->create($email, $password, 'teacher');
        $this->teacherModel->create($userId, $fullName, $phone);

        $_SESSION['success_message'] = "Teacher account created successfully for {$fullName}.";
        header('Location: ' . BASE_URL . '/index.php?action=admin_dashboard');
        exit;
    }
}