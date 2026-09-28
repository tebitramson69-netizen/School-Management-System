<?php

class Security
{
    public static function configureSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        if (self::isHttps()) {
            ini_set('session.cookie_secure', '1');
        }
    }

    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validateCsrfToken(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $submittedToken = $_POST['csrf_token'] ?? '';

        if ($sessionToken === '' || $submittedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $submittedToken);
    }

    public static function requireValidCsrf(): void
    {
        if (!self::validateCsrfToken()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            http_response_code(403);

            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if ($referer !== '' && str_contains($referer, BASE_URL)) {
                $_SESSION['form_errors'] = ['Invalid or expired form submission. Please try again.'];
                header('Location: ' . $referer);
            } else {
                $_SESSION['login_error'] = 'Invalid or expired form submission. Please try again.';
                header('Location: ' . BASE_URL . '/index.php?action=login');
            }
            exit;
        }
    }

    public static function validatePasswordStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        return $errors;
    }

    public static function checkLoginRateLimit(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $attempts = $_SESSION['login_attempts'] ?? 0;
        $lastAttempt = $_SESSION['login_last_attempt'] ?? 0;
        $lockoutDuration = 300; // 5 minutes
        $maxAttempts = 5;

        if ($attempts >= $maxAttempts && (time() - $lastAttempt) < $lockoutDuration) {
            return false;
        }

        if ((time() - $lastAttempt) >= $lockoutDuration) {
            $_SESSION['login_attempts'] = 0;
        }

        return true;
    }

    public static function recordLoginAttempt(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['login_last_attempt'] = time();
    }

    public static function clearLoginAttempts(): void
    {
        unset($_SESSION['login_attempts'], $_SESSION['login_last_attempt']);
    }

    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}
