<?php

/**
 * =========================================================
 * SCHOOL MANAGEMENT SYSTEM
 * AuthMiddleware.php
 *
 * Handles:
 * - Authentication checks
 * - Role-based authorization
 * - Forced password-change protection
 *
 * PHP 8+
 * =========================================================
 */

class AuthMiddleware
{
    /**
     * Require the current user to be authenticated and
     * have one of the specified roles.
     *
     * @param string|array $allowedRoles
     * @return void
     */
    public static function requireRole(string|array $allowedRoles): void
    {
        /*
         * -------------------------------------------------
         * 1. Start session if necessary
         * -------------------------------------------------
         */
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        /*
         * -------------------------------------------------
         * 2. Authentication check
         * -------------------------------------------------
         */
        if (
            empty($_SESSION['user_id']) ||
            empty($_SESSION['role'])
        ) {
            self::redirectToLogin();
        }

        /*
         * -------------------------------------------------
         * 3. Normalize allowed roles
         * -------------------------------------------------
         */
        $allowedRoles = is_array($allowedRoles)
            ? $allowedRoles
            : [$allowedRoles];

        /*
         * Remove empty values just in case.
         */
        $allowedRoles = array_values(
            array_filter(
                $allowedRoles,
                static fn ($role) => is_string($role) && trim($role) !== ''
            )
        );

        /*
         * -------------------------------------------------
         * 4. Validate current user's role
         * -------------------------------------------------
         */
        $currentRole = (string) $_SESSION['role'];

        if (!in_array($currentRole, $allowedRoles, true)) {
            self::denyAccess();
        }

        /*
         * -------------------------------------------------
         * 5. Forced password change
         *
         * Users who have been marked to change their
         * password must be redirected before accessing
         * protected application pages.
         *
         * EXCEPTION:
         * The password-change page itself must remain
         * accessible, otherwise a redirect loop occurs.
         * -------------------------------------------------
         */
        if (
            !empty($_SESSION['must_change_password']) &&
            self::currentAction() !== 'change_password_form' &&
            self::currentAction() !== 'change_password'
        ) {
            self::redirectToPasswordChange();
        }
    }


    /**
     * Redirect unauthenticated users to login.
     */
    private static function redirectToLogin(): void
    {
        header(
            'Location: ' .
            BASE_URL .
            '/index.php?action=login'
        );

        exit;
    }


    /**
     * Redirect users who must change their password.
     */
    private static function redirectToPasswordChange(): void
    {
        header(
            'Location: ' .
            BASE_URL .
            '/index.php?action=change_password_form'
        );

        exit;
    }


    /**
     * Stop unauthorized users with HTTP 403.
     */
    private static function denyAccess(): void
    {
        http_response_code(403);

        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Access Denied - School Management System</title>';
        echo '</head>';

        echo '<body>';

        echo '<main style="
            max-width: 700px;
            margin: 80px auto;
            padding: 30px;
            text-align: center;
            font-family: Arial, sans-serif;
        ">';

        echo '<h1>403</h1>';
        echo '<h2>Access Denied</h2>';

        echo '<p>
            You do not have permission to access this page.
        </p>';

        echo '<p>
            <a href="' .
            htmlspecialchars(
                BASE_URL . '/index.php?action=login',
                ENT_QUOTES,
                'UTF-8'
            ) .
            '">
                Return to Login
            </a>
        </p>';

        echo '</main>';

        echo '</body>';
        echo '</html>';

        exit;
    }


    /**
     * Get the current requested action.
     *
     * Used to prevent password-change redirect loops.
     */
    private static function currentAction(): string
    {
        return trim(
            (string) ($_GET['action'] ?? '')
        );
    }
}