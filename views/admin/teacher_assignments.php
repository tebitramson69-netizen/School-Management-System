<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers & Assignments - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Teachers & Their Assignments</h1>
            <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn-logout">Back to Dashboard</a>
        </header>

        <?php if (empty($byTeacher)): ?>
            <p>No teacher assignments have been made yet.</p>
        <?php else: ?>
            <?php foreach ($byTeacher as $teacherName => $assignments): ?>
                <h2><?= htmlspecialchars($teacherName) ?></h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Subject</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?= htmlspecialchars($assignment['class_name']) ?></td>
                                <td><?= htmlspecialchars($assignment['subject_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>