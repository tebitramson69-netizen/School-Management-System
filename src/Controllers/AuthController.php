<?php

require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Display login page.
     */
    public function showLoginForm(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If the user is already authenticated, don't show login again.
        if (isset($_SESSION['user_id'], $_SESSION['role'])) {
            $this->redirectToDashboard($_SESSION['role']);
        }

        require __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Authenticate user.
     */
    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Only accept POST requests.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=login'
            );
            exit;
        }

        if (!Security::checkLoginRateLimit()) {
            $_SESSION['login_error'] =
                'Too many login attempts. Please wait 5 minutes before trying again.';

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=login'
            );
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        /*
         * Basic validation
         */
        if ($email === '' || $password === '') {
            $_SESSION['login_error'] =
                'Please enter both email and password.';

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=login'
            );
            exit;
        }

        /*
         * Validate email format.
         */
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] =
                'Please enter a valid email address.';

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=login'
            );
            exit;
        }

        /*
         * Find account.
         */
        $user = $this->userModel->findByEmail($email);

        /*
         * Do not reveal whether the email exists.
         */
        if (
            !$user ||
            !isset($user['password_hash']) ||
            !$this->userModel->verifyPassword(
                $password,
                $user['password_hash']
            )
        ) {
            Security::recordLoginAttempt();

            $_SESSION['login_error'] =
                'Invalid email or password.';

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=login'
            );
            exit;
        }

        /*
         * Check account status.
         */
        if (
            isset($user['is_active']) &&
            !(bool) $user['is_active']
        ) {
            $_SESSION['login_error'] =
                'This account has been deactivated. Contact the administrator.';

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=login'
            );
            exit;
        }

        /*
         * Regenerate session ID after successful authentication.
         *
         * This protects against session fixation attacks.
         */
        session_regenerate_id(true);

        /*
         * Store only the information required by
         * the application.
         */
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        /*
         * Some older records may not contain the field.
         * Treat missing value as false for compatibility.
         */
        $_SESSION['must_change_password'] =
            isset($user['must_change_password'])
                ? (bool) $user['must_change_password']
                : false;

        Security::clearLoginAttempts();

        unset($_SESSION['login_error']);

        /*
         * Force newly-created accounts to change
         * their temporary password.
         */
        if ($_SESSION['must_change_password']) {
            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=change_password_form'
            );
            exit;
        }

        /*
         * Send user to the correct dashboard.
         */
        $this->redirectToDashboard($user['role']);
    }

    /**
     * Display change-password page.
     */
    public function showChangePasswordForm(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['role'])
        ) {
            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=login'
            );
            exit;
        }

        require __DIR__ . '/../../views/auth/change_password.php';
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['role'])
        ) {
            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=login'
            );
            exit;
        }

        /*
         * Only accept POST requests.
         */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=change_password_form'
            );
            exit;
        }

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if ($newPassword === '') {
            $errors[] = 'Please enter a new password.';
        } else {
            $errors = array_merge($errors, Security::validatePasswordStrength($newPassword));
        }

        if ($confirmPassword === '') {
            $errors[] = 'Please confirm your new password.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        /*
         * If validation fails, return to form.
         */
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=change_password_form'
            );
            exit;
        }

        /*
         * Update password.
         */
        $updated = $this->userModel->updatePassword(
            (int) $_SESSION['user_id'],
            $newPassword
        );

        /*
         * If the model reports failure, don't pretend
         * the password was changed.
         */
        if ($updated === false) {
            $_SESSION['form_errors'] = [
                'Unable to update your password. Please try again.'
            ];

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=change_password_form'
            );
            exit;
        }

        /*
         * Password successfully changed.
         */
        $_SESSION['must_change_password'] = false;

        unset($_SESSION['form_errors']);

        /*
         * Send user back to their role dashboard.
         */
        $this->redirectToDashboard($_SESSION['role']);
    }

    /**
     * Redirect authenticated users according to their role.
     */
    private function redirectToDashboard(string $role): void
    {
        $destinations = [
            'admin' =>
                BASE_URL . '/index.php?action=admin_dashboard',

            'teacher' =>
                BASE_URL . '/index.php?action=teacher_dashboard',

            'student' =>
                BASE_URL . '/index.php?action=student_dashboard',

            'parent' =>
                BASE_URL . '/index.php?action=parent_dashboard',
        ];

        /*
         * Unknown/invalid role should never be allowed
         * to continue into the application.
         */
        if (!isset($destinations[$role])) {
            $this->logout();

            return;
        }

        header('Location: ' . $destinations[$role]);
        exit;
    }

    /**
     * Log the current user out.
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        /*
         * Clear all session data.
         */
        $_SESSION = [];

        /*
         * Remove the session cookie where applicable.
         */
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        /*
         * Destroy server-side session.
         */
        session_destroy();

        /*
         * Return to login.
         */
        header(
            'Location: ' .
            BASE_URL .
            '/index.php?action=login'
        );
        exit;
    }
}