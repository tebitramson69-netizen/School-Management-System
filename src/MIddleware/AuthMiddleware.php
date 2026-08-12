<?php

class AuthMiddleware
{
    public static function requireRole(string|array $allowedRoles): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        $allowedRoles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];

        if (!in_array($_SESSION['role'], $allowedRoles, true)) {
            http_response_code(403);
            echo "Access denied. You don't have permission to view this page.";
            exit;
        }

        if (!empty($_SESSION['must_change_password'])) {
            header('Location: ' . BASE_URL . '/index.php?action=change_password_form');
            exit;
        }
    }
}