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
require_once __DIR__ . '/../Models/ClassSubject.php';
require_once __DIR__ . '/../Models/SubjectCoefficient.php';
require_once __DIR__ . '/../Models/Announcement.php';

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Core/School.php';
require_once __DIR__ . '/../Core/Security.php';
require_once __DIR__ . '/../Models/AcademicYear.php';
require_once __DIR__ . '/../Models/Term.php';
require_once __DIR__ . '/../Models/Score.php';
require_once __DIR__ . '/../Models/Result.php';
require_once __DIR__ . '/../Models/GradeScale.php';


class AdminController
{
    private User $userModel;
    private Teacher $teacherModel;
    private Student $studentModel;
    private ClassModel $classModel;
    private ParentModel $parentModel;
    private Subject $subjectModel;
    private ClassSubjectTeacher $assignmentModel;
    private ClassSubject $classSubjectModel;
    private SubjectCoefficient $coefficientModel;
    private Announcement $announcementModel;
    private AcademicYear $academicYearModel;
    private Term $termModel;
    private Score $scoreModel;
    private Result $resultModel;
    private GradeScale $gradeScaleModel;


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
        $this->classSubjectModel = new ClassSubject();
        $this->coefficientModel = new SubjectCoefficient();
        $this->announcementModel = new Announcement();
        $this->academicYearModel = new AcademicYear();
        $this->termModel = new Term();
        $this->scoreModel = new Score();
        $this->resultModel = new Result();
        $this->gradeScaleModel = new GradeScale();
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

        self::startSession();

        $classes = $this->classModel->all();
        $teachers = $this->teacherModel->all();

        /*
         * Teacher assignment is constrained to the subjects a class
         * actually offers (class_subjects). The view uses this map
         * to filter the subject dropdown by the chosen class.
         */
        $classSubjectsMap = $this->classSubjectModel->mapByClass();

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
         * The subject must be one the class actually offers
         * (class_subjects). This mirrors the constraint the form's
         * dependent dropdown enforces, and blocks a tampered POST.
         */
        if (
            $classId > 0 &&
            $subjectId > 0 &&
            !in_array(
                $subjectId,
                $this->classSubjectModel->getSubjectIdsForClass($classId),
                true
            )
        ) {
            $errors[] =
                'That subject is not offered by the selected class. Set it on the Class Subjects screen first.';
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

            $_SESSION['old_input'] = [
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId
            ];

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
                 * The subjects this class OFFERS (class_subjects),
                 * merged with any coefficient already stored. This
                 * is the authoritative subject list, independent of
                 * whether a teacher has been assigned.
                 */
                $offeredSubjects =
                    $this->classSubjectModel
                        ->getForClass($classId);

                $existingCoefficients =
                    $this->coefficientModel
                        ->getForClass($classId);

                foreach ($offeredSubjects as $subject) {

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

            /*
             * Keep coefficients consistent with the class's offered
             * subjects: drop any stale coefficient rows for subjects
             * no longer offered (e.g. left over from old data).
             */
            $this->coefficientModel->pruneForClass(
                $classId,
                array_keys($coefficients)
            );

            $_SESSION['success_message'] =
                'Subject coefficients saved successfully.';

        } catch (Throwable $e) {

            $_SESSION['form_errors'] = [
                'Coefficients could not be saved. Please try again.'
            ];
        }


        header(
            'Location: ' .
            BASE_URL .
            '/index.php?action=class_coefficients_form&class_id=' .
            $classId
        );

        exit;
    }


    /**
     * -----------------------------------------------------
     * CLASS SUBJECTS — CONFIGURATION FORM
     * -----------------------------------------------------
     *
     * Defines which subjects a class offers. When no class is
     * selected the view shows a class picker. When a class is
     * selected we show every subject as a checkbox, pre-checking
     * the class's saved set — or, if none is saved yet, the
     * (level + stream) defaults for the admin to review and save.
     */
    public function showClassSubjectsForm(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $classes = $this->classModel->all();

        $allSubjects = $this->subjectModel->all();

        $classId = (int) ($_GET['class_id'] ?? 0);

        $selectedClass = null;
        $checkedIds = [];
        $usingDefaults = false;

        if ($classId > 0) {

            $selectedClass = $this->classModel->find($classId);

            if ($selectedClass) {

                $checkedIds =
                    $this->classSubjectModel
                        ->getSubjectIdsForClass($classId);

                /*
                 * Nothing saved yet: pre-select the stream defaults
                 * (not persisted until the admin clicks Save).
                 */
                if (empty($checkedIds)) {

                    $checkedIds = $this->defaultSubjectIdsForClass(
                        $selectedClass,
                        $allSubjects
                    );

                    $usingDefaults = !empty($checkedIds);
                }
            }
        }

        require __DIR__ .
            '/../../views/admin/class_subjects.php';
    }


    /**
     * -----------------------------------------------------
     * CLASS SUBJECTS — SAVE
     * -----------------------------------------------------
     */
    public function saveClassSubjects(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $classId = (int) ($_POST['class_id'] ?? 0);

        $submitted = $_POST['subjects'] ?? [];

        if ($classId <= 0 || !$this->classModel->find($classId)) {

            $_SESSION['form_errors'] = [
                'Please select a valid class.'
            ];

            self::redirect('class_subjects_form');
        }

        $subjectIds = is_array($submitted)
            ? array_map('intval', $submitted)
            : [];

        try {

            $this->classSubjectModel->saveForClass(
                $classId,
                $subjectIds
            );

            $_SESSION['success_message'] =
                'Class subjects saved successfully.';

        } catch (Throwable $e) {

            $_SESSION['form_errors'] = [
                'Class subjects could not be saved. Please try again.'
            ];
        }

        header(
            'Location: ' .
            BASE_URL .
            '/index.php?action=class_subjects_form&class_id=' .
            $classId
        );

        exit;
    }


    /**
     * -----------------------------------------------------
     * CLASS SUBJECTS — RESOLVE STREAM DEFAULTS TO IDS
     * -----------------------------------------------------
     *
     * Maps the (level + option) default subject CODES to the ids
     * present in this install's subjects table. Codes with no
     * matching subject are simply skipped.
     */
    private function defaultSubjectIdsForClass(
        array $class,
        array $allSubjects
    ): array {

        $codes = ClassSubject::defaultSubjectCodes(
            $class['level'] ?? null,
            $class['class_option'] ?? null
        );

        if (empty($codes)) {
            return [];
        }

        $idByCode = [];

        foreach ($allSubjects as $subject) {
            $idByCode[$subject['code']] = (int) $subject['id'];
        }

        $ids = [];

        foreach ($codes as $code) {
            if (isset($idByCode[$code])) {
                $ids[] = $idByCode[$code];
            }
        }

        return $ids;
    }


    /**
     * -----------------------------------------------------
     * REPORT CARDS — CLASS + TERM PICKER / STUDENT LIST
     * -----------------------------------------------------
     */
    public function showReportCards(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $classes = $this->classModel->all();

        /*
         * Distinct term names for the current academic year, each
         * with a representative term_id (the Score/Result methods
         * group a term by name, so any of its sequence rows works).
         */
        $terms = [];

        foreach ($this->termModel->all() as $t) {
            $name = $t['name'];
            if (!isset($terms[$name])) {
                $terms[$name] = [
                    'term_id' => (int) $t['id'],
                    'name' => $name,
                ];
            }
        }

        $terms = array_values($terms);

        $classId = (int) ($_GET['class_id'] ?? 0);
        $termId = (int) ($_GET['term_id'] ?? 0);

        $selectedClass = null;
        $selectedTerm = null;
        $students = [];

        if ($classId > 0 && $termId > 0) {

            $selectedClass = $this->classModel->find($classId);
            $selectedTerm = $this->termModel->find($termId);

            if ($selectedClass && $selectedTerm) {
                $students = $this->studentModel->allByClass($classId);
            }
        }

        require __DIR__ . '/../../views/admin/report_cards.php';
    }


    /**
     * -----------------------------------------------------
     * REPORT CARD — ONE STUDENT'S PRINTABLE BULLETIN
     * -----------------------------------------------------
     *
     * Coefficient-weighted, per term (Sequence 1 + Sequence 2 +
     * term average). Uses the same averaging engine as the student
     * portal and the class ranking, so the figures agree.
     */
    public function showReportCard(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $studentId = (int) ($_GET['student_id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);
        $termId = (int) ($_GET['term_id'] ?? 0);

        $student = $this->studentModel->find($studentId);
        $class = $this->classModel->find($classId);
        $term = $this->termModel->find($termId);

        if (!$student || !$class || !$term) {

            $_SESSION['form_errors'] = [
                'Could not build that report card (missing student, class or term).'
            ];

            self::redirect('report_cards');
        }

        $school = School::settings();
        $academicYear = $this->academicYearModel->getCurrent();
        $academicYearId = (int) ($academicYear['id'] ?? 0);


        /*
         * Subject rows: per-subject Seq1/Seq2/average, plus the
         * per-class coefficient, grade/remark and average x coef.
         */
        $seqRows = $this->scoreModel->sequenceMarksForStudentTerm(
            $studentId,
            $termId
        );

        $coefficients = $this->coefficientModel->getForClass($classId);

        $rows = [];
        $totalCoefficient = 0;
        $totalWeighted = 0.0;

        foreach ($seqRows as $r) {

            $subjectId = (int) $r['subject_id'];

            $average = $r['average_score'] !== null
                ? (float) $r['average_score']
                : null;

            $coefficient = $coefficients[$subjectId] ?? 1;

            $grade = $average !== null
                ? $this->gradeScaleModel->forScore($average)
                : null;

            $weighted = $average !== null
                ? round($average * $coefficient, 2)
                : null;

            if ($average !== null) {
                $totalCoefficient += $coefficient;
                $totalWeighted += $average * $coefficient;
            }

            $rows[] = [
                'subject_name' => $r['subject_name'],
                'subject_code' => $r['subject_code'],
                'seq1' => $r['seq1'],
                'seq2' => $r['seq2'],
                'average' => $average,
                'coefficient' => $coefficient,
                'weighted' => $weighted,
                'letter' => $grade['letter'] ?? '—',
                'remark' => $grade['remark'] ?? '—',
            ];
        }

        $overallAverage = $totalCoefficient > 0
            ? round($totalWeighted / $totalCoefficient, 2)
            : null;

        $overallGrade = $overallAverage !== null
            ? $this->gradeScaleModel->forScore($overallAverage)
            : null;


        /*
         * Class position + class average, from the shared ranking
         * engine (coefficient-weighted).
         */
        $classPosition = null;
        $classSize = null;
        $classAverage = null;

        if ($academicYearId > 0) {

            $classResults = $this->resultModel->getClassResults(
                $classId,
                $academicYearId,
                $termId
            );

            $sum = 0.0;
            $withResults = 0;

            foreach ($classResults as $cr) {

                if ((int) $cr['student_id'] === $studentId) {
                    $classPosition = $cr['position'];
                }

                if ($cr['overall_average'] !== null) {
                    $withResults++;
                    $sum += (float) $cr['overall_average'];
                }
            }

            $classSize = $withResults;
            $classAverage = $withResults > 0
                ? round($sum / $withResults, 2)
                : null;
        }


        /*
         * Ordinal position label (1st, 2nd, 3rd...).
         */
        $positionLabel = null;

        if ($classPosition !== null) {
            $n = (int) $classPosition;
            $suffix = 'th';
            if (!in_array($n % 100, [11, 12, 13], true)) {
                $suffix = match ($n % 10) {
                    1 => 'st',
                    2 => 'nd',
                    3 => 'rd',
                    default => 'th',
                };
            }
            $positionLabel = $n . $suffix;
        }


        $passMark = (float) ($school['pass_mark'] ?? 10);

        $passed = $overallAverage !== null
            ? $overallAverage >= $passMark
            : null;

        require __DIR__ . '/../../views/admin/report_card.php';
    }


    /**
     * -----------------------------------------------------
     * SUBJECTS — LIST
     * -----------------------------------------------------
     */
    public function viewSubjects(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $subjects = $this->subjectModel->all();

        require __DIR__ . '/../../views/admin/subjects.php';
    }


    /**
     * -----------------------------------------------------
     * SUBJECTS — CREATE FORM
     * -----------------------------------------------------
     */
    public function showCreateSubjectForm(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        require __DIR__ . '/../../views/admin/create_subject.php';
    }


    /**
     * -----------------------------------------------------
     * SUBJECTS — CREATE
     * -----------------------------------------------------
     */
    public function createSubject(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));

        $errors = $this->validateSubjectInput($name, $code, null);

        if (!empty($errors)) {

            $_SESSION['form_errors'] = $errors;

            $_SESSION['old_input'] = [
                'name' => $name,
                'code' => $code
            ];

            self::redirect('create_subject_form');
        }

        $this->subjectModel->create($name, $code);

        $_SESSION['success_message'] =
            "Subject \"{$name}\" created successfully.";

        self::redirect('view_subjects');
    }


    /**
     * -----------------------------------------------------
     * SUBJECTS — EDIT FORM
     * -----------------------------------------------------
     */
    public function showEditSubjectForm(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $subjectId = (int) ($_GET['subject_id'] ?? 0);

        $subject = $this->subjectModel->find($subjectId);

        if (!$subject) {

            $_SESSION['form_errors'] = [
                'The requested subject could not be found.'
            ];

            self::redirect('view_subjects');
        }

        require __DIR__ . '/../../views/admin/edit_subject.php';
    }


    /**
     * -----------------------------------------------------
     * SUBJECTS — UPDATE
     * -----------------------------------------------------
     */
    public function updateSubject(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $subjectId = (int) ($_POST['subject_id'] ?? 0);

        $existing = $this->subjectModel->find($subjectId);

        if (!$existing) {

            $_SESSION['form_errors'] = [
                'The requested subject could not be found.'
            ];

            self::redirect('view_subjects');
        }

        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));

        $errors = $this->validateSubjectInput($name, $code, $subjectId);

        if (!empty($errors)) {

            $_SESSION['form_errors'] = $errors;

            $_SESSION['old_input'] = [
                'name' => $name,
                'code' => $code
            ];

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=edit_subject_form&subject_id=' .
                $subjectId
            );

            exit;
        }

        $this->subjectModel->update($subjectId, $name, $code);

        $_SESSION['success_message'] =
            "Subject \"{$name}\" updated successfully.";

        self::redirect('view_subjects');
    }


    /**
     * -----------------------------------------------------
     * SUBJECTS — DELETE
     * -----------------------------------------------------
     *
     * Blocked when the subject is referenced by scores,
     * teacher assignments or coefficients, because those
     * foreign keys cascade on delete and would destroy
     * recorded marks.
     */
    public function deleteSubject(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();

        $subjectId = (int) ($_POST['subject_id'] ?? 0);

        $subject = $this->subjectModel->find($subjectId);

        if (!$subject) {

            $_SESSION['form_errors'] = [
                'The requested subject could not be found.'
            ];

            self::redirect('view_subjects');
        }

        if ($this->subjectModel->isInUse($subjectId)) {

            $_SESSION['form_errors'] = [
                "\"{$subject['name']}\" is in use (scores, teacher assignments or coefficients) and cannot be deleted."
            ];

            self::redirect('view_subjects');
        }

        $this->subjectModel->delete($subjectId);

        $_SESSION['success_message'] =
            "Subject \"{$subject['name']}\" deleted successfully.";

        self::redirect('view_subjects');
    }


    /**
     * -----------------------------------------------------
     * SUBJECTS — SHARED VALIDATION
     * -----------------------------------------------------
     *
     * $excludeId is the subject being edited (so its own code
     * does not clash with itself); null when creating.
     */
    private function validateSubjectInput(
        string $name,
        string $code,
        ?int $excludeId
    ): array {

        $errors = [];

        if ($name === '') {
            $errors[] = 'Subject name is required.';
        } elseif (mb_strlen($name) > 100) {
            $errors[] = 'Subject name must be 100 characters or fewer.';
        }

        if ($code === '') {
            $errors[] = 'Subject code is required.';
        } elseif (!preg_match('/^[A-Z0-9]{1,20}$/', $code)) {
            $errors[] =
                'Subject code may only contain letters and numbers (up to 20 characters).';
        } elseif ($this->subjectModel->codeExists($code, $excludeId)) {
            $errors[] = 'This subject code is already in use.';
        }

        return $errors;
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