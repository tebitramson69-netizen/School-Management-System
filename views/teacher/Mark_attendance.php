<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Mark Attendance';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">TEACHER PORTAL</span>
        <h1>Mark Attendance</h1>
        <p>
            <strong><?= htmlspecialchars($assignment['subject_name'], ENT_QUOTES, 'UTF-8') ?></strong>
            — <?= htmlspecialchars($assignment['class_name'], ENT_QUOTES, 'UTF-8') ?>
            — <?= date('F j, Y') ?>
        </p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/index.php?action=teacher_dashboard" class="btn btn-secondary">
            ← Back to Dashboard
        </a>
    </div>
</section>

<section class="dashboard-section">

    <?php if (empty($students)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">!</div>
            <h3>No students enrolled</h3>
            <p>No students are enrolled in this class yet.</p>
        </div>

    <?php else: ?>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=mark_attendance">
            <?= Security::csrfField() ?>
            <input type="hidden" name="assignment_id" value="<?= (int) $assignmentId ?>">
            <input type="hidden" name="date" value="<?= htmlspecialchars((string) $date, ENT_QUOTES, 'UTF-8') ?>">

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Student</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $serialNumber = 1; ?>
                        <?php foreach ($students as $student): ?>
                            <?php $currentStatus = $existingAttendance[$student['id']] ?? 'present'; ?>
                            <tr>
                                <td><?= $serialNumber++ ?></td>
                                <td><?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <input type="radio" name="status[<?= (int) $student['id'] ?>]" value="present"
                                           <?= $currentStatus === 'present' ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <input type="radio" name="status[<?= (int) $student['id'] ?>]" value="absent"
                                           <?= $currentStatus === 'absent' ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <input type="radio" name="status[<?= (int) $student['id'] ?>]" value="late"
                                           <?= $currentStatus === 'late' ? 'checked' : '' ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">Save Attendance</button>
        </form>

    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
