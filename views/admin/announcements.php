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
    <title>Announcements - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>All Announcements</h1>
            <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn-logout">Back to Dashboard</a>
        </header>

        <?php if (empty($announcements)): ?>
            <p>No announcements posted yet.</p>
        <?php else: ?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="alert alert-success">
                    <strong><?= htmlspecialchars($announcement['title']) ?></strong>
                    (<?= htmlspecialchars($announcement['class_name'] ?? 'Whole School') ?>)
                    <br>
                    <?= nl2br(htmlspecialchars($announcement['body'])) ?>
                    <br>
                    <small><?= date('F j, Y g:i A', strtotime($announcement['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>