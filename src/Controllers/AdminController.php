<?php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Teacher.php';
require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/ClassModel.php';
require_once __DIR__ . '/../Models/ParentModel.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class AdminController
{
    private User $userModel;
    private Teacher $teacherModel;
    private Student $studentModel;
    private ClassModel $classModel;
    private ParentModel $parentModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->teacherModel = new Teacher();
        $this->studentModel = new Student();
        $this->classModel = new ClassModel();
        $this->parentModel = new ParentModel();
    }

    public function dashboard(): void
    {
        AuthMiddleware::requireRole('admin');
        require __DIR__ . '/../../views/admin/dashboard.php';
    }

    public function showCreateTeacherForm(): void
    {
        AuthMiddleware::requireRole('admin');
        require __DIR__ . '/../../views/admin/create_teacher.php';
    }

    public function createTeacher(): void
    {
        AuthMiddleware::requireRole('admin');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = trim($_POST['username'] ?? '');
        $email = $username . SCHOOL_EMAIL_DOMAIN;
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic validation
        $errors = [];

        if (empty($username) || !preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            $errors[] = 'Username is required and can only contain letters, numbers, dots, underscores, and hyphens.';
        }

        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if ($this->userModel->emailExists($email)) {
            $errors[] = 'This username is already taken.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = ['username' => $username, 'full_name' => $fullName, 'phone' => $phone];
            header('Location: ' . BASE_URL . '/index.php?action=create_teacher_form');
            exit;
        }

        // Create the user record, then the teacher profile linked to it
        $userId = $this->userModel->create($email, $password, 'teacher');
        $this->teacherModel->create($userId, $fullName, $phone);

        $_SESSION['success_message'] = "Teacher account created successfully for {$fullName}.";
        header('Location: ' . BASE_URL . '/index.php?action=admin_dashboard');
        exit;
    }

    public function showCreateStudentForm(): void
    {
        AuthMiddleware::requireRole('admin');
        $classes = $this->classModel->all();
        require __DIR__ . '/../../views/admin/create_student.php';
    }

    public function createStudent(): void
    {
        AuthMiddleware::requireRole('admin');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = trim($_POST['username'] ?? '');
        $email = $username . SCHOOL_EMAIL_DOMAIN;
        $fullName = trim($_POST['full_name'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $admissionNo = trim($_POST['admission_no'] ?? '');
        $classId = (int) ($_POST['class_id'] ?? 0);
        $password = $_POST['password'] ?? '';

        $errors = [];

        if (empty($username) || !preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            $errors[] = 'Username is required and can only contain letters, numbers, dots, underscores, and hyphens.';
        }

        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        }

        if (empty($dob)) {
            $errors[] = 'Date of birth is required.';
        }

        if (!in_array($gender, ['M', 'F'], true)) {
            $errors[] = 'Please select a gender.';
        }

        if (empty($admissionNo)) {
            $errors[] = 'Admission number is required.';
        }

        if ($classId <= 0) {
            $errors[] = 'Please select a class.';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if (!empty($username) && $this->userModel->emailExists($email)) {
            $errors[] = 'This username is already taken.';
        }

        if (!empty($admissionNo) && $this->studentModel->admissionNoExists($admissionNo)) {
            $errors[] = 'This admission number is already in use.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = [
                'username' => $username, 'full_name' => $fullName, 'dob' => $dob,
                'gender' => $gender, 'admission_no' => $admissionNo, 'class_id' => $classId
            ];
            header('Location: ' . BASE_URL . '/index.php?action=create_student_form');
            exit;
        }

        // Create user + student profile, then enroll into the current academic year
        $userId = $this->userModel->create($email, $password, 'student');
        $studentId = $this->studentModel->create($userId, $fullName, $dob, $gender, $admissionNo);
        $this->studentModel->enroll($studentId, $classId, 1); // academic_year_id 1 = 2025/2026 (current)

        $_SESSION['success_message'] = "Student account created successfully for {$fullName}.";
        header('Location: ' . BASE_URL . '/index.php?action=admin_dashboard');
        exit;
    }

    public function showCreateParentForm(): void
    {
        AuthMiddleware::requireRole('admin');
        $students = $this->studentModel->all();
        require __DIR__ . '/../../views/admin/create_parent.php';
    }

    public function createParent(): void
    {
        AuthMiddleware::requireRole('admin');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = trim($_POST['username'] ?? '');
        $email = $username . SCHOOL_EMAIL_DOMAIN;
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $studentIds = $_POST['student_ids'] ?? []; // array of selected student IDs from multi-select

        $errors = [];

        if (empty($username) || !preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            $errors[] = 'Username is required and can only contain letters, numbers, dots, underscores, and hyphens.';
        }

        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if (empty($studentIds)) {
            $errors[] = 'Please select at least one child.';
        }

        if (!empty($username) && $this->userModel->emailExists($email)) {
            $errors[] = 'This username is already taken.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = [
                'username' => $username, 'full_name' => $fullName, 'phone' => $phone,
                'student_ids' => $studentIds
            ];
            header('Location: ' . BASE_URL . '/index.php?action=create_parent_form');
            exit;
        }

        // Create user + parent profile, then link every selected child
        $userId = $this->userModel->create($email, $password, 'parent');
        $parentId = $this->parentModel->create($userId, $fullName, $phone);

        foreach ($studentIds as $studentId) {
            $this->parentModel->linkChild($parentId, (int) $studentId);
        }

        $_SESSION['success_message'] = "Parent account created successfully for {$fullName}.";
        header('Location: ' . BASE_URL . '/index.php?action=admin_dashboard');
        exit;
    }
}