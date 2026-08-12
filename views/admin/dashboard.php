<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <a href="<?= BASE_URL ?>/index.php?action=logout" class="btn-logout">Logout</a>
        </header>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-actions">
            <a href="<?= BASE_URL ?>/index.php?action=create_teacher_form" class="btn-card">
                + Add Teacher
            </a>
            <a href="<?= BASE_URL ?>/index.php?action=create_student_form" class="btn-card">
                + Add Student
            </a>
            <a href="<?= BASE_URL ?>/index.php?action=create_parent_form" class="btn-card">
                + Add Parent
            </a>
        </div>
    </div>
</body>
</html>