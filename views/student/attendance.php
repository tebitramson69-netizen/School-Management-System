<?php

declare(strict_types=1);

/**
 * Student Attendance View — uses the shared dashboard layout.
 *
 * Business logic lives in StudentController::attendance().
 */

$student = $student ?? [];
$attendance = $attendance ?? [];

$studentName =
    $student['full_name']
    ?? $student['name']
    ?? 'Student';

$totalAttendance = is_array($attendance) ? count($attendance) : 0;

$pageTitle = 'Attendance';

ob_start();
?>

<?php require __DIR__ . '/_header.php'; ?>


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

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
