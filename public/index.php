<?php

/**
 * =========================================================
 * SCHOOL MANAGEMENT SYSTEM
 * public/index.php
 *
 * Front Controller / Application Router
 *
 * PHP 8+
 * XAMPP / Apache
 * No framework required
 * =========================================================
 */


/*
 * ---------------------------------------------------------
 * 1. Load application configuration
 * ---------------------------------------------------------
 */
require_once __DIR__ . '/../config/database.php';


/*
 * ---------------------------------------------------------
 * 2. Load middleware and security
 * ---------------------------------------------------------
 */
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Core/Security.php';


/*
 * ---------------------------------------------------------
 * 3. Start session with hardened settings
 * ---------------------------------------------------------
 */
Security::configureSession();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
 * ---------------------------------------------------------
 * 4. Determine requested action
 *
 * Default route:
 *
 *     /index.php
 *
 * becomes:
 *
 *     login
 * ---------------------------------------------------------
 */
$action = trim($_GET['action'] ?? 'login');


/*
 * Prevent an empty action from producing an unnecessary
 * 404 response.
 */
if ($action === '') {
    $action = 'login';
}


/*
 * ---------------------------------------------------------
 * 5. CSRF validation for POST requests
 * ---------------------------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::requireValidCsrf();
}


/*
 * ---------------------------------------------------------
 * 6. Application routes
 *
 * Controllers are intentionally created INSIDE their
 * respective routes.
 *
 * This prevents the application from loading every
 * controller and every model when the user only wants
 * to display the login page.
 * ---------------------------------------------------------
 */
switch ($action) {

    /*
     * =====================================================
     * AUTHENTICATION
     * =====================================================
     */

    case 'login':

        require_once __DIR__ .
            '/../src/Controllers/AuthController.php';

        $authController = new AuthController();

        $authController->showLoginForm();

        break;


    case 'login_submit':

        require_once __DIR__ .
            '/../src/Controllers/AuthController.php';

        $authController = new AuthController();

        $authController->login();

        break;


    case 'logout':

        require_once __DIR__ .
            '/../src/Controllers/AuthController.php';

        $authController = new AuthController();

        $authController->logout();

        break;


    case 'change_password_form':

        require_once __DIR__ .
            '/../src/Controllers/AuthController.php';

        $authController = new AuthController();

        $authController->showChangePasswordForm();

        break;


    case 'change_password':

        require_once __DIR__ .
            '/../src/Controllers/AuthController.php';

        $authController = new AuthController();

        $authController->changePassword();

        break;


    /*
     * =====================================================
     * ADMIN
     * =====================================================
     */

    case 'admin_dashboard':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->dashboard();

        break;


    /*
     * -----------------------------------------------------
     * ACADEMIC YEAR MANAGEMENT
     * -----------------------------------------------------
     */

    case 'academic_years':

        require_once __DIR__ .
            '/../src/Controllers/AcademicYearController.php';

        $academicYearController =
            new AcademicYearController();

        $academicYearController->index();

        break;


    case 'create_academic_year':

        require_once __DIR__ .
            '/../src/Controllers/AcademicYearController.php';

        $academicYearController =
            new AcademicYearController();

        $academicYearController->create();

        break;


    case 'activate_academic_year':

        require_once __DIR__ .
            '/../src/Controllers/AcademicYearController.php';

        $academicYearController =
            new AcademicYearController();

        $academicYearController->activate();

        break;

            /*
     * -----------------------------------------------------
     * CURRENT TERM / SEQUENCE MANAGEMENT
     * -----------------------------------------------------
     */

    case 'set_current_term':

        require_once __DIR__ .
            '/../src/Controllers/AcademicYearController.php';

        $academicYearController =
            new AcademicYearController();

        $academicYearController->setCurrentTerm();

        break;


    /*
     * -----------------------------------------------------
     * Teacher management
     * -----------------------------------------------------
     */

    case 'create_teacher_form':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->showCreateTeacherForm();

        break;


    case 'create_teacher':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->createTeacher();

        break;


    /*
     * -----------------------------------------------------
     * Student management
     * -----------------------------------------------------
     */

    case 'create_student_form':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->showCreateStudentForm();

        break;


    case 'create_student':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->createStudent();

        break;


    /*
     * -----------------------------------------------------
     * Parent management
     * -----------------------------------------------------
     */

    case 'create_parent_form':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->showCreateParentForm();

        break;


    case 'create_parent':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->createParent();

        break;


    /*
     * -----------------------------------------------------
     * Teacher assignment
     * -----------------------------------------------------
     */

    case 'assign_teacher_form':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->showAssignTeacherForm();

        break;


    case 'assign_teacher':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->assignTeacher();

        break;


    /*
     * -----------------------------------------------------
     * Teacher/class viewing
     * -----------------------------------------------------
     */

    case 'view_teachers':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->viewTeacherAssignments();

        break;


    case 'view_classes':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->viewClasses();

        break;


    case 'view_class_list':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->viewClassList();

        break;


    /*
     * -----------------------------------------------------
     * Announcements
     * -----------------------------------------------------
     */

    case 'post_announcement_form':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->showPostAnnouncementForm();

        break;


    case 'post_announcement':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->postAnnouncement();

        break;


    case 'view_announcements':

        require_once __DIR__ .
            '/../src/Controllers/AdminController.php';

        $adminController = new AdminController();

        $adminController->viewAnnouncements();

        break;


    /*
     * =====================================================
     * TEACHER
     * =====================================================
     */

    case 'teacher_dashboard':

        require_once __DIR__ .
            '/../src/Controllers/TeacherController.php';

        $teacherController = new TeacherController();

        $teacherController->dashboard();

        break;


    case 'mark_attendance_form':

        require_once __DIR__ .
            '/../src/Controllers/TeacherController.php';

        $teacherController = new TeacherController();

        $teacherController->showMarkAttendanceForm();

        break;


    case 'mark_attendance':

        require_once __DIR__ .
            '/../src/Controllers/TeacherController.php';

        $teacherController = new TeacherController();

        $teacherController->markAttendance();

        break;


    case 'enter_scores_form':

        require_once __DIR__ .
            '/../src/Controllers/TeacherController.php';

        $teacherController = new TeacherController();

        $teacherController->showEnterScoresForm();

        break;


    case 'enter_scores':

        require_once __DIR__ .
            '/../src/Controllers/TeacherController.php';

        $teacherController = new TeacherController();

        $teacherController->enterScores();

        break;


    /*
     * =====================================================
     * STUDENT
     * =====================================================
     */

    case 'student_information':

        require_once __DIR__ .
            '/../src/Controllers/StudentController.php';

        $studentController = new StudentController();

        $studentController->information();

        break;


    case 'student_dashboard':

        /*
         * The monolithic dashboard is being split into separate
         * pages. Student Information is the first page, so the old
         * dashboard URL now redirects there — existing bookmarks
         * keep working. dashboard() remains in the controller until
         * the remaining pages are split out.
         */

        header(
            'Location: ' .
            BASE_URL .
            '/index.php?action=student_information',
            true,
            301
        );

        exit;


    /*
     * =====================================================
     * PARENT
     * =====================================================
     */

    case 'parent_dashboard':

        require_once __DIR__ .
            '/../src/Controllers/ParentController.php';

        $parentController = new ParentController();

        $parentController->dashboard();

        break;


    case 'parent_child_detail':

        require_once __DIR__ .
            '/../src/Controllers/ParentController.php';

        $parentController = new ParentController();

        $parentController->childDetail();

        break;


    /*
     * =====================================================
     * UNKNOWN ROUTE
     * =====================================================
     */

    default:

        http_response_code(404);

        echo '<!DOCTYPE html>';

        echo '<html lang="en">';

        echo '<head>';

        echo '<meta charset="UTF-8">';

        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

        echo '<title>Page Not Found - School Management System</title>';

        echo '</head>';

        echo '<body>';

        echo '<main style="
            max-width: 700px;
            margin: 80px auto;
            padding: 30px;
            text-align: center;
            font-family: Arial, sans-serif;
        ">';

        echo '<h1>404</h1>';

        echo '<h2>Page Not Found</h2>';

        echo '<p>
            The page or action you requested does not exist.
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

        break;
}