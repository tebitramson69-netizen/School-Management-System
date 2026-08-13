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
    <title>Mark Attendance - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Mark Attendance</h1>
            <a href="<?= BASE_URL ?>/index.php?action=teacher_dashboard" class="btn-logout">Back to Dashboard</a>
        </header>

        <p>
            <strong><?= htmlspecialchars($assignment['subject_name']) ?></strong>
            — <?= htmlspecialchars($assignment['class_name']) ?>
            — <?= date('F j, Y') ?>
        </p>

        <?php if (empty($students)): ?>
            <p>No students are enrolled in this class yet.</p>
        <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/index.php?action=mark_attendance">
                <input type="hidden" name="assignment_id" value="<?= $assignmentId ?>">
                <input type="hidden" name="date" value="<?= $date ?>">

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Admission No.</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php $currentStatus = $existingAttendance[$student['id']] ?? 'present'; ?>
                            <tr>
                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                <td><?= htmlspecialchars($student['admission_no']) ?></td>
                                <td>
                                    <input type="radio" name="status[<?= $student['id'] ?>]" value="present"
                                           <?= $currentStatus === 'present' ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <input type="radio" name="status[<?= $student['id'] ?>]" value="absent"
                                           <?= $currentStatus === 'absent' ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <input type="radio" name="status[<?= $student['id'] ?>]" value="late"
                                           <?= $currentStatus === 'late' ? 'checked' : '' ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="submit" class="btn-submit">Save Attendance</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>