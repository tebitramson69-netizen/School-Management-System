<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Controllers/TeacherController.php';
require_once __DIR__ . '/../src/Controllers/StudentController.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

$action = $_GET['action'] ?? 'login';

$authController = new AuthController();
$adminController = new AdminController();
$teacherController = new TeacherController();
$studentController = new StudentController();

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

    case 'change_password_form':
        $authController->showChangePasswordForm();
        break;

    case 'change_password':
        $authController->changePassword();
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

    case 'create_student_form':
        $adminController->showCreateStudentForm();
        break;

    case 'create_student':
        $adminController->createStudent();
        break;

    case 'create_parent_form':
        $adminController->showCreateParentForm();
        break;

    case 'create_parent':
        $adminController->createParent();
        break;

    case 'assign_teacher_form':
        $adminController->showAssignTeacherForm();
        break;

    case 'assign_teacher':
        $adminController->assignTeacher();
        break;

    case 'view_teachers':
        $adminController->viewTeacherAssignments();
        break;

    case 'view_classes':
        $adminController->viewClasses();
        break;

    case 'view_class_list':
        $adminController->viewClassList();
        break;

    case 'teacher_dashboard':
        $teacherController->dashboard();
        break;

    case 'mark_attendance_form':
        $teacherController->showMarkAttendanceForm();
        break;

    case 'mark_attendance':
        $teacherController->markAttendance();
        break;

    case 'enter_scores_form':
        $teacherController->showEnterScoresForm();
        break;

    case 'enter_scores':
        $teacherController->enterScores();
        break;

    case 'student_dashboard':
        $studentController->dashboard();
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