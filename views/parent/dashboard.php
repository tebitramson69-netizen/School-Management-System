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
    <title>Parent Dashboard - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Welcome, <?= htmlspecialchars($parent['full_name']) ?></h1>
            <a href="<?= BASE_URL ?>/index.php?action=logout" class="btn-logout">Logout</a>
        </header>

        <h2>Your Children</h2>

        <?php if (empty($children)): ?>
            <p>No children are linked to your account yet. Please contact the administrator.</p>
        <?php else: ?>
            <div class="dashboard-actions">
                <?php foreach ($children as $child): ?>
                    <a href="<?= BASE_URL ?>/index.php?action=parent_child_detail&student_id=<?= $child['id'] ?>" class="btn-card">
                        <?= htmlspecialchars($child['full_name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
