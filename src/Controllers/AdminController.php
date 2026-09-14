<?php

/**
 * =========================================================
 * SCHOOL MANAGEMENT SYSTEM
 * AdminController.php
 *
 * Handles administrator operations:
 *
 * - Admin dashboard
 * - Teacher creation
 * - Student creation
 * - Parent creation
 * - Teacher/class/subject assignments
 * - Class lists
 * - Announcements
 *
 * PHP 8+
 * XAMPP / Apache
 * =========================================================
 */

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Teacher.php';
require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/ClassModel.php';
require_once __DIR__ . '/../Models/ParentModel.php';
require_once __DIR__ . '/../Models/Subject.php';
require_once __DIR__ . '/../Models/ClassSubjectTeacher.php';
require_once __DIR__ . '/../Models/SubjectCoefficient.php';
require_once __DIR__ . '/../Models/Announcement.php';

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Core/School.php';
require_once __DIR__ . '/../Core/Security.php';
require_once __DIR__ . '/../Models/AcademicYear.php';


class AdminController
{
    private User $userModel;
    private Teacher $teacherModel;
    private Student $studentModel;
    private ClassModel $classModel;
    private ParentModel $parentModel;
    private Subject $subjectModel;
    private ClassSubjectTeacher $assignmentModel;
    private SubjectCoefficient $coefficientModel;
    private Announcement $announcementModel;
    private AcademicYear $academicYearModel;


    /**
     * -----------------------------------------------------
     * Constructor
     * -----------------------------------------------------
     */
    public function __construct()
    {
        $this->userModel = new User();
        $this->teacherModel = new Teacher();
        $this->studentModel = new Student();
        $this->classModel = new ClassModel();
        $this->parentModel = new ParentModel();
        $this->subjectModel = new Subject();
        $this->assignmentModel = new ClassSubjectTeacher();
        $this->coefficientModel = new SubjectCoefficient();
        $this->announcementModel = new Announcement();
        $this->academicYearModel = new AcademicYear();
    }


    /**
     * -----------------------------------------------------
     * ADMIN DASHBOARD
     * -----------------------------------------------------
     */
   public function dashboard(): void
{
    AuthMiddleware::requireRole('admin');

    /*
     * -----------------------------------------------------
     * DASHBOARD STATISTICS
     * -----------------------------------------------------
     *
     * Gather the current school statistics from the
     * existing models before loading the dashboard view.
     */

    $studentStatistics =
        $this->studentModel->getDashboardStatistics();

    $dashboardStats = [
        'students' =>
            $studentStatistics['total_students'] ?? 0,

        'teachers' =>
            $this->teacherModel->getTotalCount(),

        'classes' =>
            $this->classModel->getTotalCount(),

        'gce_candidates' =>
            $studentStatistics['gce_candidates'] ?? 0,
    ];


    /*
     * -----------------------------------------------------
     * LOAD DASHBOARD
     * -----------------------------------------------------
     */

    require __DIR__ . '/../../views/admin/dashboard.php';
}


    /**
     * -----------------------------------------------------
     * CREATE TEACHER
     * -----------------------------------------------------
     */

    public function showCreateTeacherForm(): void
    {
        AuthMiddleware::requireRole('admin');

        require __DIR__ . '/../../views/admin/create_teacher.php';
    }


    public function createTeacher(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $username = trim($_POST['username'] ?? '');
        $email = $username . SCHOOL_EMAIL_DOMAIN;

        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];


        /*
         * Username validation
         */
        if (
            empty($username) ||
            !preg_match('/^[a-zA-Z0-9._-]+$/', $username)
        ) {
            $errors[] =
                'Username is required and can only contain letters, numbers, dots, underscores, and hyphens.';
        }


        /*
         * Full name validation
         */
        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        }


        /*
         * Password validation
         */
        $passwordErrors =
            Security::validatePasswordStrength($password);

        if (!empty($passwordErrors)) {
            $errors = array_merge($errors, $passwordErrors);
        }


        /*
         * Username/email uniqueness
         */
        if (
            $username !== '' &&
            $this->userModel->emailExists($email)
        ) {
            $errors[] = 'This username is already taken.';
        }


        /*
         * Return validation errors to form
         */
        if (!empty($errors)) {

            $_SESSION['form_errors'] = $errors;

            $_SESSION['old_input'] = [
                'username' => $username,
                'full_name' => $fullName,
                'phone' => $phone
            ];

            self::redirect(
                'create_teacher_form'
            );
        }


        /*
         * Create authentication account
         */
        $userId = $this->userModel->create(
            $email,
            $password,
            'teacher'
        );


        /*
         * Create teacher profile
         */
        $this->teacherModel->create(
            $userId,
            $fullName,
            $phone
        );


        $_SESSION['success_message'] =
            "Teacher account created successfully for {$fullName}.";


        self::redirect('admin_dashboard');
    }


    /**
     * -----------------------------------------------------
     * CREATE STUDENT
     * -----------------------------------------------------
     */

    public function showCreateStudentForm(): void
    {
        AuthMiddleware::requireRole('admin');

        $classes = $this->classModel->all();

        require __DIR__ . '/../../views/admin/create_student.php';
    }


    public function createStudent(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $username = trim($_POST['username'] ?? '');
        $email = $username . SCHOOL_EMAIL_DOMAIN;

        $fullName = trim($_POST['full_name'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
      

        $classId = (int) (
            $_POST['class_id'] ?? 0
        );

        $password = $_POST['password'] ?? '';

        $errors = [];


        /*
         * Username
         */
        if (
            empty($username) ||
            !preg_match('/^[a-zA-Z0-9._-]+$/', $username)
        ) {
            $errors[] =
                'Username is required and can only contain letters, numbers, dots, underscores, and hyphens.';
        }


        /*
         * Full name
         */
        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        }




        /*
         * Date of birth
         */
        if ($dob === '') {
            $errors[] = 'Date of birth is required.';
        }


        /*
         * Gender
         */
        if (!in_array($gender, ['M', 'F'], true)) {
            $errors[] = 'Please select a gender.';
        }


        /*
         * Class
         */
        if ($classId <= 0) {
            $errors[] = 'Please select a class.';
        }


        /*
         * Password
         */
        $passwordErrors =
            Security::validatePasswordStrength($password);

        if (!empty($passwordErrors)) {
            $errors = array_merge($errors, $passwordErrors);
        }


        /*
         * Username uniqueness
         */
        if (
            $username !== '' &&
            $this->userModel->emailExists($email)
        ) {
            $errors[] = 'This username is already taken.';
        }


        /*
         * Return errors
         */
        if (!empty($errors)) {

            $_SESSION['form_errors'] = $errors;

            $_SESSION['old_input'] = [
                'username' => $username,
                'full_name' => $fullName,
                
                'dob' => $dob,
                'gender' => $gender,
                'class_id' => $classId
            ];

            self::redirect(
                'create_student_form'
            );
        }
/*
 * ---------------------------------------------------------
 * CURRENT ACADEMIC YEAR
 * ---------------------------------------------------------
 *
 * A student cannot be registered without an active
 * academic year because enrollment is tied to the
 * academic year.
 */
$currentAcademicYear =
    $this->academicYearModel->getCurrent();

if (!$currentAcademicYear) {

    $_SESSION['form_errors'] = [
        'No active academic year has been configured. Please ask the administrator to activate an academic year before registering students.'
    ];

    $_SESSION['old_input'] = [
        'username' => $username,
        'full_name' => $fullName,
       
        'dob' => $dob,
        'gender' => $gender,
        'class_id' => $classId
    ];

    self::redirect(
        'create_student_form'
    );
}

$currentAcademicYearId =
    (int) $currentAcademicYear['id'];


/*
 * ---------------------------------------------------------
 * CREATE STUDENT TRANSACTION
 * ---------------------------------------------------------
 *
 * User account, student profile and enrollment must either
 * all succeed or all fail.
 */
$db = Database::getConnection();

try {

    /*
     * Start transaction.
     */
    $db->beginTransaction();


    /*
     * -----------------------------------------------------
     * 1. CREATE USER ACCOUNT
     * -----------------------------------------------------
     */
    $userId =
        $this->userModel->create(
            $email,
            $password,
            'student'
        );


    /*
     * -----------------------------------------------------
     * 2. CREATE STUDENT PROFILE
     * -----------------------------------------------------
     */
    $studentId =
        $this->studentModel->create(
            $userId,
            $fullName,
            $dob,
            $gender,
            
        );


    /*
     * -----------------------------------------------------
     * 3. ENROLL STUDENT
     * -----------------------------------------------------
     */
    $this->studentModel->enroll(
        $studentId,
        $classId,
        $currentAcademicYearId
    );


    /*
     * -----------------------------------------------------
     * 4. COMMIT
     * -----------------------------------------------------
     */
    $db->commit();


    $_SESSION['success_message'] =
        "Student account created successfully for {$fullName}.";

    self::redirect(
        'admin_dashboard'
    );


} catch (Throwable $e) {

    /*
     * -----------------------------------------------------
     * ROLLBACK
     * -----------------------------------------------------
     *
     * If any operation failed, remove everything created
     * during this registration attempt.
     */
    if ($db->inTransaction()) {
        $db->rollBack();
    }


    /*
     * Keep technical database details away from the user.
     * We can log them properly later.
     */
    $_SESSION['form_errors'] = [
        'Student registration could not be completed. Please try again.'
    ];


    $_SESSION['old_input'] = [
        'username' => $username,
        'full_name' => $fullName,
        'dob' => $dob,
        'gender' => $gender,
        'class_id' => $classId
    ];


    self::redirect(
        'create_student_form'
    );
}
}

    /**
     * -----------------------------------------------------
     * CREATE PARENT
     * -----------------------------------------------------
     */

    public function showCreateParentForm(): void
    {
        AuthMiddleware::requireRole('admin');

               $students = $this->studentModel->allEnrolledInCurrentYear();

        require __DIR__ . '/../../views/admin/create_parent.php';
    }


    public function createParent(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $username = trim($_POST['username'] ?? '');
        $email = $username . SCHOOL_EMAIL_DOMAIN;

        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        $studentIds = $_POST['student_ids'] ?? [];


        /*
         * Ensure student IDs are always treated as an array.
         */
        if (!is_array($studentIds)) {
            $studentIds = [];
        }


        /*
         * Normalize student IDs.
         */
        $studentIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $studentIds
                    ),
                    static fn ($id) => $id > 0
                )
            )
        );


        $errors = [];


        /*
         * Username
         */
        if (
            empty($username) ||
            !preg_match('/^[a-zA-Z0-9._-]+$/', $username)
        ) {
            $errors[] =
                'Username is required and can only contain letters, numbers, dots, underscores, and hyphens.';
        }


        /*
         * Full name
         */
        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        }


        /*
         * Password
         */
        $passwordErrors =
            Security::validatePasswordStrength($password);

        if (!empty($passwordErrors)) {
            $errors = array_merge($errors, $passwordErrors);
        }


        /*
         * Child selection
         */
        if (empty($studentIds)) {
            $errors[] =
                'Please select at least one child.';
        }


        /*
         * Username uniqueness
         */
        if (
            $username !== '' &&
            $this->userModel->emailExists($email)
        ) {
            $errors[] = 'This username is already taken.';
        }


        /*
         * Return errors
         */
        if (!empty($errors)) {

            $_SESSION['form_errors'] = $errors;

            $_SESSION['old_input'] = [
                'username' => $username,
                'full_name' => $fullName,
                'phone' => $phone,
                'student_ids' => $studentIds
            ];

            self::redirect(
                'create_parent_form'
            );
        }


        /*
         * Create user account
         */
        $userId = $this->userModel->create(
            $email,
            $password,
            'parent'
        );


        /*
         * Create parent profile
         */
        $parentId = $this->parentModel->create(
            $userId,
            $fullName,
            $phone
        );


        /*
         * Link children
         */
        foreach ($studentIds as $studentId) {

            $this->parentModel->linkChild(
                $parentId,
                $studentId
            );
        }


        $_SESSION['success_message'] =
            "Parent account created successfully for {$fullName}.";


        self::redirect('admin_dashboard');
    }


    /**
     * -----------------------------------------------------
     * TEACHER ASSIGNMENT
     * -----------------------------------------------------
     */

    public function showAssignTeacherForm(): void
    {
        AuthMiddleware::requireRole('admin');

        $classes = $this->classModel->all();
        $subjects = $this->subjectModel->all();
        $teachers = $this->teacherModel->all();

        require __DIR__ . '/../../views/admin/assign_teacher.php';
    }


    public function assignTeacher(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $classId = (int) (
            $_POST['class_id'] ?? 0
        );

        $subjectId = (int) (
            $_POST['subject_id'] ?? 0
        );

        $teacherId = (int) (
            $_POST['teacher_id'] ?? 0
        );

        $errors = [];


        if ($classId <= 0) {
            $errors[] = 'Please select a class.';
        }

        if ($subjectId <= 0) {
            $errors[] = 'Please select a subject.';
        }

        if ($teacherId <= 0) {
            $errors[] = 'Please select a teacher.';
        }


        /*
         * Prevent duplicate class/subject assignment.
         */
        if (
            $classId > 0 &&
            $subjectId > 0 &&
            $this->assignmentModel
                ->existsForClassSubject(
                    $classId,
                    $subjectId
                )
        ) {
            $errors[] =
                'This class already has a teacher assigned for this subject.';
        }


        if (!empty($errors)) {

            $_SESSION['form_errors'] = $errors;

            self::redirect(
                'assign_teacher_form'
            );
        }


        /*
         * Create assignment.
         */
        $this->assignmentModel->assign(
            $classId,
            $subjectId,
            $teacherId
        );


        $_SESSION['success_message'] =
            'Teacher assigned successfully.';


        self::redirect('admin_dashboard');
    }


    /**
     * -----------------------------------------------------
     * SUBJECT COEFFICIENTS — CONFIGURATION FORM
     * -----------------------------------------------------
     *
     * Per-class subject coefficients for GCE weighting.
     *
     * When no class is selected the view shows a class picker.
     * When a class is selected we list the subjects actually
     * taught in that class (from class_subject_teacher) and
     * pre-fill each coefficient with the stored value, or 1
     * where none has been configured yet.
     */
    public function showClassCoefficientsForm(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $classes = $this->classModel->all();

        $classId = (int) (
            $_GET['class_id'] ?? 0
        );

        $selectedClass = null;
        $subjects = [];

        if ($classId > 0) {

            $selectedClass =
                $this->classModel->find($classId);

            if ($selectedClass) {

                /*
                 * Only subjects genuinely taught in this class,
                 * merged with any coefficient already stored.
                 */
                $taughtSubjects =
                    $this->assignmentModel
                        ->subjectsForClass($classId);

                $existingCoefficients =
                    $this->coefficientModel
                        ->getForClass($classId);

                foreach ($taughtSubjects as $subject) {

                    $subjectId = (int) $subject['id'];

                    $subject['coefficient'] =
                        $existingCoefficients[$subjectId] ?? 1;

                    $subjects[] = $subject;
                }
            }
        }

        require __DIR__ .
            '/../../views/admin/class_coefficients.php';
    }


    /**
     * -----------------------------------------------------
     * SUBJECT COEFFICIENTS — SAVE
     * -----------------------------------------------------
     *
     * Persists the submitted per-class coefficients via the
     * transactional SubjectCoefficient::saveBulk(). Every
     * coefficient must be a positive integer, matching the
     * table's CHECK (coefficient > 0).
     */
    public function saveClassCoefficients(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $classId = (int) (
            $_POST['class_id'] ?? 0
        );

        $submitted = $_POST['coefficients'] ?? [];

        $errors = [];


        if ($classId <= 0) {
            $errors[] = 'Please select a class.';
        }


        if (!is_array($submitted) || empty($submitted)) {
            $errors[] = 'No coefficients were submitted.';
        }


        /*
         * Validate every coefficient: positive integer only.
         * Reject blanks, non-numeric, decimals, zero, negatives.
         */
        $coefficients = [];

        if (is_array($submitted)) {

            foreach ($submitted as $subjectId => $rawValue) {

                $subjectId = (int) $subjectId;

                if ($subjectId <= 0) {
                    continue;
                }

                $rawValue = trim((string) $rawValue);

                if ($rawValue === '' || !ctype_digit($rawValue)) {
                    $errors[] =
                        'Each coefficient must be a whole number of 1 or more.';
                    break;
                }

                $value = (int) $rawValue;

                if ($value <= 0) {
                    $errors[] =
                        'Each coefficient must be 1 or more.';
                    break;
                }

                $coefficients[$subjectId] = $value;
            }
        }


        if (!empty($errors)) {

            $_SESSION['form_errors'] = $errors;

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=class_coefficients_form&class_id=' .
                $classId
            );

            exit;
        }


        try {

            $this->coefficientModel->saveBulk(
                $classId,
                $coefficients
            );

            $_SESSION['success_message'] =
                'Subject coefficients saved successfully.';

        } catch (Throwable $e) {

            $_SESSION['form_errors'] = [
                'Coefficients could not be saved. Please try again.'
            ];
        }


        self::redirect(
            'class_coefficients_form&class_id=' . $classId
        );
    }


    /**
     * -----------------------------------------------------
     * VIEW TEACHER ASSIGNMENTS
     * -----------------------------------------------------
     */

    public function viewTeacherAssignments(): void
    {
        AuthMiddleware::requireRole('admin');

        $assignments =
            $this->assignmentModel->all();


        /*
         * Group assignments by teacher.
         */
        $byTeacher = [];


        foreach ($assignments as $assignment) {

            $teacherName =
                $assignment['teacher_name']
                ?? 'Unknown Teacher';

            $byTeacher[$teacherName][] =
                $assignment;
        }


        ksort($byTeacher);


        require __DIR__ .
            '/../../views/admin/teacher_assignments.php';
    }


    /**
     * -----------------------------------------------------
     * VIEW CLASSES
     * -----------------------------------------------------
     */

    public function viewClasses(): void
    {
        AuthMiddleware::requireRole('admin');

        $classes =
            $this->classModel->all();

        require __DIR__ .
            '/../../views/admin/classes.php';
    }


    /**
     * -----------------------------------------------------
     * VIEW CLASS LIST
     * -----------------------------------------------------
     */

    public function viewClassList(): void
    {
        AuthMiddleware::requireRole('admin');

        $classId = (int) (
            $_GET['class_id'] ?? 0
        );


        if ($classId <= 0) {
            self::notFound('Class not found.');
        }


        $class =
            $this->classModel->find($classId);


        if (!$class) {
            self::notFound('Class not found.');
        }


        $students =
            $this->studentModel->allByClass(
                $classId
            );


        require __DIR__ .
            '/../../views/admin/class_list.php';
    }


    /**
     * -----------------------------------------------------
     * ANNOUNCEMENTS
     * -----------------------------------------------------
     */

    public function showPostAnnouncementForm(): void
    {
        AuthMiddleware::requireRole('admin');

        $classes =
            $this->classModel->all();

        require __DIR__ .
            '/../../views/admin/post_announcement.php';
    }


    public function postAnnouncement(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $title = trim(
            $_POST['title'] ?? ''
        );

        $body = trim(
            $_POST['body'] ?? ''
        );

        $classId = (int) (
            $_POST['class_id'] ?? 0
        );

        /*
         * 0 means school-wide announcement.
         */
        $classId =
            $classId > 0
                ? $classId
                : null;


        $errors = [];


        if ($title === '') {
            $errors[] =
                'Title is required.';
        }


        if ($body === '') {
            $errors[] =
                'Announcement body is required.';
        }


        if (!empty($errors)) {

            $_SESSION['form_errors'] =
                $errors;

            $_SESSION['old_input'] = [
                'title' => $title,
                'body' => $body,
                'class_id' => $classId
            ];

            self::redirect(
                'post_announcement_form'
            );
        }


        /*
         * Make sure the authenticated user ID exists
         * before creating the announcement.
         */
        $postedBy =
            (int) ($_SESSION['user_id'] ?? 0);


        if ($postedBy <= 0) {
            self::redirectToLogin();
        }


        $this->announcementModel->create(
            $postedBy,
            $classId,
            $title,
            $body
        );


        $_SESSION['success_message'] =
            'Announcement posted successfully.';


        self::redirect('admin_dashboard');
    }


    /**
     * -----------------------------------------------------
     * VIEW ANNOUNCEMENTS
     * -----------------------------------------------------
     */

    public function viewAnnouncements(): void
    {
        AuthMiddleware::requireRole('admin');

        $announcements =
            $this->announcementModel->all();

        require __DIR__ .
            '/../../views/admin/announcements.php';
    }


    /**
     * =====================================================
     * PRIVATE HELPERS
     * =====================================================
     */


    /**
     * Start session safely.
     */
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    /**
     * Redirect to an application action.
     */
    private static function redirect(
        string $action
    ): void {

        header(
            'Location: ' .
            BASE_URL .
            '/index.php?action=' .
            rawurlencode($action)
        );

        exit;
    }


    /**
     * Redirect to login.
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
     * Render a basic 404 response.
     */
    private static function notFound(
        string $message = 'Page not found.'
    ): void {

        http_response_code(404);

        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Not Found - School Management System</title>';
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

        echo '<h2>' .
            htmlspecialchars(
                $message,
                ENT_QUOTES,
                'UTF-8'
            ) .
            '</h2>';

        echo '<p>
            <a href="' .
            htmlspecialchars(
                BASE_URL . '/index.php?action=admin_dashboard',
                ENT_QUOTES,
                'UTF-8'
            ) .
            '">
                Return to Dashboard
            </a>
        </p>';

        echo '</main>';

        echo '</body>';
        echo '</html>';

        exit;
    }
}