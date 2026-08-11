<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

$action = $_GET['action'] ?? 'login';

$authController = new AuthController();

switch ($action) {
    case 'login':
        $authController->showLoginForm();
        break;

    case 'login_submit':
        $authController->login();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'admin_dashboard':
        echo "Admin dashboard coming soon.";
        break;

    case 'teacher_dashboard':
        echo "Teacher dashboard coming soon.";
        break;

    case 'student_dashboard':
        echo "Student dashboard coming soon.";
        break;

    case 'parent_dashboard':
        echo "Parent dashboard coming soon.";
        break;

    default:
        http_response_code(404);
        echo "Page not found.";
        break;
}