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
    <title>Teacher Dashboard - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Welcome, <?= htmlspecialchars($teacher['full_name']) ?></h1>
            <a href="<?= BASE_URL ?>/index.php?action=logout" class="btn-logout">Logout</a>
        </header>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <h2>Your Classes & Subjects</h2>

        <?php if (empty($assignments)): ?>
            <p>You have not been assigned to any classes yet. Please contact the administrator.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?= htmlspecialchars($assignment['class_name']) ?></td>
                            <td><?= htmlspecialchars($assignment['subject_name']) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/index.php?action=mark_attendance_form&assignment_id=<?= $assignment['id'] ?>">Mark Attendance</a> |
                                <a href="<?= BASE_URL ?>/index.php?action=enter_scores_form&assignment_id=<?= $assignment['id'] ?>">Enter Scores</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>