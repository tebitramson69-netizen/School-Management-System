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
    <title><?= htmlspecialchars($class['name']) ?> - Class List</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1><?= htmlspecialchars($class['name']) ?> — Class List</h1>
            <a href="<?= BASE_URL ?>/index.php?action=view_classes" class="btn-logout">Back to Classes</a>
        </header>

        <p><?= count($students) ?> student<?= count($students) === 1 ? '' : 's' ?> enrolled</p>

        <?php if (empty($students)): ?>
            <p>No students are enrolled in this class yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Student Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $serialNumber = 1; ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= $serialNumber++ ?></td>
                            <td><?= htmlspecialchars($student['full_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>