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
    <title>Student Dashboard - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Welcome, <?= htmlspecialchars($student['full_name']) ?></h1>
            <a href="<?= BASE_URL ?>/index.php?action=logout" class="btn-logout">Logout</a>
        </header>

        <h2>My Scores</h2>
        <?php if (empty($scores)): ?>
            <p>No scores have been recorded yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Sequence</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scores as $score): ?>
                        <tr>
                            <td><?= htmlspecialchars($score['subject_name']) ?></td>
                            <td><?= htmlspecialchars($score['term_name']) ?> — Sequence <?= $score['sequence_number'] ?></td>
                            <td><?= htmlspecialchars($score['score']) ?> / <?= htmlspecialchars($score['max_score']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>My Attendance</h2>
        <?php if (empty($attendance)): ?>
            <p>No attendance records yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $record): ?>
                        <tr>
                            <td><?= date('F j, Y', strtotime($record['date'])) ?></td>
                            <td><?= htmlspecialchars($record['subject_name']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($record['status'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>