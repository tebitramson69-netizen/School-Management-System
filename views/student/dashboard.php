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

        <h2>Announcements</h2>
        <?php if (empty($announcements)): ?>
            <p>No announcements right now.</p>
        <?php else: ?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="alert alert-success">
                    <strong><?= htmlspecialchars($announcement['title']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($announcement['body'])) ?><br>
                    <small><?= date('F j, Y', strtotime($announcement['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>Report Card</h2>
        <?php if (empty($reportCard)): ?>
            <p>No scores recorded yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Average</th>
                        <th>Grade</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportCard as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><?= number_format($row['average_score'], 2) ?> / <?= htmlspecialchars($row['max_score']) ?></td>
                            <td><?= htmlspecialchars($row['letter']) ?></td>
                            <td><?= htmlspecialchars($row['remark']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($overallAverage !== null): ?>
                        <tr>
                            <td><strong>Overall Average</strong></td>
                            <td><strong><?= number_format($overallAverage, 2) ?> / 20</strong></td>
                            <td><strong><?= htmlspecialchars($overallGrade['letter'] ?? '—') ?></strong></td>
                            <td><strong><?= htmlspecialchars($overallGrade['remark'] ?? '—') ?></strong></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>My Scores (Detailed)</h2>
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