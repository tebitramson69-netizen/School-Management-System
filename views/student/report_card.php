<?php

declare(strict_types=1);

/**
 * Student Report Card View — uses the shared dashboard layout.
 *
 * Latest-term subject averages plus the academic summary and
 * class ranking. Business logic lives in
 * StudentController::reportCard().
 */

$student = $student ?? [];
$reportCard = $reportCard ?? [];
$overallAverage = $overallAverage ?? null;
$overallGrade = $overallGrade ?? null;
$classPosition = $classPosition ?? null;
$classSize = $classSize ?? null;

$studentName =
    $student['full_name']
    ?? $student['name']
    ?? 'Student';

/*
 * Ordinal helper for ranking position (1st, 2nd, 3rd...).
 */
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

$pageTitle = 'Report Card';

ob_start();
?>

<?php require __DIR__ . '/_header.php'; ?>


<!-- ACADEMIC SUMMARY -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Academic Summary</h2>
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
                <?= $positionLabel !== null
                    ? htmlspecialchars($positionLabel, ENT_QUOTES, 'UTF-8')
                    : '—' ?>
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

    <?php if (!empty($reportCard)): ?>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Average Score</th>
                        <th>Grade</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reportCard as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['subject_name'] ?? 'Unknown Subject', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= isset($row['average_score'])
                                ? htmlspecialchars(number_format((float) $row['average_score'], 2), ENT_QUOTES, 'UTF-8')
                                : '—' ?>
                        </td>
                        <td><?= htmlspecialchars($row['letter'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['remark'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No report card yet</h3>
            <p>No report card records are available for you yet.</p>
        </div>

    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
