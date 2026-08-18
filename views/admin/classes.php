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
    <title>Classes - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Classes</h1>
            <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn-logout">Back to Dashboard</a>
        </header>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Level</th>
                    <th>Option</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?= htmlspecialchars($class['name']) ?></td>
                        <td><?= htmlspecialchars($class['level']) ?></td>
                        <td><?= htmlspecialchars($class['class_option'] ?? '—') ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/index.php?action=view_class_list&class_id=<?= $class['id'] ?>">
                                View Class List
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>