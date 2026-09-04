<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$classPosition = $classPosition ?? null;
$classSize = $classSize ?? null;

$positionLabel = null;
if ($classPosition !== null) {
    $n = (int) $classPosition;
    $suffix = 'th';
    if (!in_array($n % 100, [11, 12, 13], true)) {
        $suffix = match ($n % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
    $positionLabel = $n . $suffix;
}

$pageTitle = htmlspecialchars($student['full_name'] ?? 'Child', ENT_QUOTES, 'UTF-8');

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">CHILD PROFILE</span>
        <h1><?= htmlspecialchars($student['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        <p>Academic profile, results and attendance.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/index.php?action=parent_dashboard" class="btn btn-secondary">
            ← Back to Children
        </a>
    </div>
</section>


<!-- OVERALL RESULT SUMMARY -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Overall Result</h2>
        </div>
    </div>

    <div class="dashboard-stats-grid">
        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Overall Average</span>
            <strong class="dashboard-stat-value">
                <?= $overallAverage !== null
                    ? htmlspecialchars(number_format((float) $overallAverage, 2), ENT_QUOTES, 'UTF-8')
                    : '—' ?>
            </strong>
            <span class="dashboard-stat-note">out of 20</span>
        </article>

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Class Position</span>
            <strong class="dashboard-stat-value">
                <?= $positionLabel !== null ? htmlspecialchars($positionLabel, ENT_QUOTES, 'UTF-8') : '—' ?>
            </strong>
            <span class="dashboard-stat-note">
                <?= $classSize ? 'of ' . (int) $classSize . ' ranked' : 'Not ranked yet' ?>
            </span>
        </article>

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Overall Grade</span>
            <strong class="dashboard-stat-value">
                <?= htmlspecialchars($overallGrade['letter'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
            </strong>
            <span class="dashboard-stat-note">
                <?= htmlspecialchars($overallGrade['remark'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </span>
        </article>
    </div>
</section>


<!-- REPORT CARD -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Report Card</h2>
            <p>Coefficient-weighted subject averages for the latest term.</p>
        </div>
    </div>

    <?php if (empty($reportCard)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No scores recorded</h3>
            <p>No scores have been recorded for this child yet.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
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
                            <td><?= htmlspecialchars($row['subject_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format((float) $row['average_score'], 2) ?> / <?= htmlspecialchars((string) $row['max_score'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['letter'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['remark'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($overallAverage !== null): ?>
                        <tr>
                            <td><strong>Overall Average</strong></td>
                            <td><strong><?= number_format((float) $overallAverage, 2) ?> / 20</strong></td>
                            <td><strong><?= htmlspecialchars($overallGrade['letter'] ?? '—', ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><strong><?= htmlspecialchars($overallGrade['remark'] ?? '—', ENT_QUOTES, 'UTF-8') ?></strong></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</section>


<!-- DETAILED SCORES -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Scores (Detailed)</h2>
        </div>
    </div>

    <?php if (empty($scores)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No scores</h3>
            <p>No scores have been recorded yet.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
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
                            <td><?= htmlspecialchars($score['subject_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($score['term_name'], ENT_QUOTES, 'UTF-8') ?> — Sequence <?= (int) $score['sequence_number'] ?></td>
                            <td><?= htmlspecialchars((string) $score['score'], ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string) $score['max_score'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</section>


<!-- ATTENDANCE -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Attendance</h2>
        </div>
    </div>

    <?php if (empty($attendance)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No attendance records</h3>
            <p>No attendance records are available yet.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
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
                            <td><?= htmlspecialchars($record['subject_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(ucfirst($record['status']), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</section>


<!-- ANNOUNCEMENTS -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Announcements</h2>
        </div>
    </div>

    <?php if (empty($announcements)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No announcements</h3>
            <p>No announcements right now.</p>
        </div>
    <?php else: ?>
        <div class="announcement-list">
            <?php foreach ($announcements as $announcement): ?>
                <article class="announcement-card">
                    <div class="announcement-card-header">
                        <h3><?= htmlspecialchars($announcement['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <time><?= date('F j, Y', strtotime($announcement['created_at'])) ?></time>
                    </div>
                    <p><?= nl2br(htmlspecialchars($announcement['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
