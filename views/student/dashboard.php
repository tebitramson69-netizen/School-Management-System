<?php

declare(strict_types=1);

/**
 * Student Dashboard View — uses the shared dashboard layout.
 * Business logic lives in StudentController.
 */

$student = $student ?? [];
$attendance = $attendance ?? [];
$scores = $scores ?? [];
$announcements = $announcements ?? [];
$reportCard = $reportCard ?? [];
$overallAverage = $overallAverage ?? null;
$overallGrade = $overallGrade ?? null;
$classPosition = $classPosition ?? null;
$classSize = $classSize ?? null;

$studentName =
    $student['full_name']
    ?? $student['name']
    ?? 'Student';

$studentEmail = $student['email'] ?? '';
$admissionNo = $student['admission_no'] ?? '';
$className = $student['class_name'] ?? $student['class'] ?? '';

$totalAttendance = is_array($attendance) ? count($attendance) : 0;
$totalScores = is_array($scores) ? count($scores) : 0;
$totalAnnouncements = is_array($announcements) ? count($announcements) : 0;

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

$pageTitle = 'Student Dashboard';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">STUDENT PORTAL</span>
        <h1>Welcome, <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?></h1>
        <p>
            View your academic information, attendance, scores,
            report card and school announcements.
        </p>
    </div>
</section>


<!-- STUDENT INFORMATION -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Student Information</h2>
        </div>
    </div>

    <div class="dashboard-stats-grid">

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Name</span>
            <strong class="dashboard-stat-value" style="font-size:1.1rem;">
                <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>
            </strong>
        </article>

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Admission No.</span>
            <strong class="dashboard-stat-value" style="font-size:1.1rem;">
                <?= $admissionNo !== '' ? htmlspecialchars($admissionNo, ENT_QUOTES, 'UTF-8') : 'Not available' ?>
            </strong>
        </article>

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Class</span>
            <strong class="dashboard-stat-value" style="font-size:1.1rem;">
                <?= $className !== '' ? htmlspecialchars($className, ENT_QUOTES, 'UTF-8') : 'Not assigned' ?>
            </strong>
        </article>

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Email</span>
            <strong class="dashboard-stat-value" style="font-size:1.1rem;">
                <?= $studentEmail !== '' ? htmlspecialchars($studentEmail, ENT_QUOTES, 'UTF-8') : 'Not available' ?>
            </strong>
        </article>

    </div>
</section>


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

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Announcements</span>
            <strong class="dashboard-stat-value"><?= (int) $totalAnnouncements ?></strong>
            <span class="dashboard-stat-note">School updates</span>
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


<!-- ATTENDANCE -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Attendance</h2>
            <p><?= (int) $totalAttendance ?> record<?= $totalAttendance === 1 ? '' : 's' ?> on file.</p>
        </div>
    </div>

    <?php if (!empty($attendance)): ?>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><?= htmlspecialchars($record['date'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(ucfirst($record['status'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No attendance records</h3>
            <p>No attendance records are available yet.</p>
        </div>

    <?php endif; ?>

</section>


<!-- ANNOUNCEMENTS -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>School Announcements</h2>
        </div>
    </div>

    <?php if (!empty($announcements)): ?>

        <div class="announcement-list">
            <?php foreach ($announcements as $announcement): ?>
                <article class="announcement-card">
                    <div class="announcement-card-header">
                        <h3><?= htmlspecialchars($announcement['title'] ?? 'Announcement', ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($announcement['created_at'])): ?>
                            <time><?= date('F j, Y', strtotime($announcement['created_at'])) ?></time>
                        <?php endif; ?>
                    </div>
                    <p><?= nl2br(htmlspecialchars($announcement['body'] ?? $announcement['message'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No announcements</h3>
            <p>There are no announcements at the moment.</p>
        </div>

    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
