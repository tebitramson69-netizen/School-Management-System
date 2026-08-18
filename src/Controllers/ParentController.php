<?php

require_once __DIR__ . '/../Models/ParentModel.php';
require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/Attendance.php';
require_once __DIR__ . '/../Models/Score.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class ParentController
{
    private ParentModel $parentModel;
    private Student $studentModel;
    private Attendance $attendanceModel;
    private Score $scoreModel;

    public function __construct()
    {
        $this->parentModel = new ParentModel();
        $this->studentModel = new Student();
        $this->attendanceModel = new Attendance();
        $this->scoreModel = new Score();
    }

    public function dashboard(): void
    {
        AuthMiddleware::requireRole('parent');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $parent = $this->parentModel->findByUserId($_SESSION['user_id']);

        if (!$parent) {
            echo "Parent profile not found. Please contact the administrator.";
            exit;
        }

        $children = $this->parentModel->getChildren($parent['id']);

        require __DIR__ . '/../../views/parent/dashboard.php';
    }

    public function childDetail(): void
    {
        AuthMiddleware::requireRole('parent');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $parent = $this->parentModel->findByUserId($_SESSION['user_id']);
        $studentId = (int) ($_GET['student_id'] ?? 0);

        // Security check: confirm this parent is actually linked to this child,
        // so nobody can view another family's child by editing the URL
        if (!$this->parentModel->isLinkedToChild($parent['id'], $studentId)) {
            http_response_code(403);
            echo "Access denied. This is not your child.";
            exit;
        }

        $student = $this->studentModel->findByUserId($studentId); // placeholder, fixed below
        $attendance = $this->attendanceModel->forStudent($studentId);
        $scores = $this->scoreModel->forStudent($studentId);

        require __DIR__ . '/../../views/parent/child_detail.php';
    }
}