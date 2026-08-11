<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

$action = $_GET['action'] ?? 'login';

$authController = new AuthController();
$adminController = new AdminController();

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
        $adminController->dashboard();
        break;

    case 'create_teacher_form':
        $adminController->showCreateTeacherForm();
        break;

    case 'create_teacher':
        $adminController->createTeacher();
        break;

    case 'teacher_dashboard':
        AuthMiddleware::requireRole('teacher');
        echo "Teacher dashboard coming soon.";
        break;

    case 'student_dashboard':
        AuthMiddleware::requireRole('student');
        echo "Student dashboard coming soon.";
        break;

    case 'parent_dashboard':
        AuthMiddleware::requireRole('parent');
        echo "Parent dashboard coming soon.";
        break;

    default:
        http_response_code(404);
        echo "Page not found.";
        break;
}