<?php

require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLoginForm(): void
    {
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter both email and password.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $_SESSION['login_error'] = 'Invalid email or password.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        if (!$user['is_active']) {
            $_SESSION['login_error'] = 'This account has been deactivated. Contact the administrator.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        // Credentials valid — store minimal identifying info in the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['must_change_password'] = (bool) $user['must_change_password'];

        if ($_SESSION['must_change_password']) {
            header('Location: ' . BASE_URL . '/index.php?action=change_password_form');
            exit;
        }

        $this->redirectToDashboard($user['role']);
    }

    public function showChangePasswordForm(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        require __DIR__ . '/../../views/auth/change_password.php';
    }

    public function changePassword(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            header('Location: ' . BASE_URL . '/index.php?action=change_password_form');
            exit;
        }

        $this->userModel->updatePassword($_SESSION['user_id'], $newPassword);
        $_SESSION['must_change_password'] = false;

        $this->redirectToDashboard($_SESSION['role']);
    }

    private function redirectToDashboard(string $role): void
    {
        $destinations = [
            'admin' => BASE_URL . '/index.php?action=admin_dashboard',
            'teacher' => BASE_URL . '/index.php?action=teacher_dashboard',
            'student' => BASE_URL . '/index.php?action=student_dashboard',
            'parent' => BASE_URL . '/index.php?action=parent_dashboard',
        ];

        header('Location: ' . $destinations[$role]);
        exit;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        header('Location: ' . BASE_URL . '/index.php?action=login');
        exit;
    }
}