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
    <title>Enter Scores - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Enter Scores</h1>
            <a href="<?= BASE_URL ?>/index.php?action=teacher_dashboard" class="btn-logout">Back to Dashboard</a>
        </header>

        <p>
            <strong><?= htmlspecialchars($assignment['subject_name']) ?></strong>
            — <?= htmlspecialchars($assignment['class_name']) ?>
        </p>

        <?php if (!$selectedTerm): ?>
            <!-- Step 1: pick which sequence to enter scores for -->
            <form method="GET" action="<?= BASE_URL ?>/index.php">
                <input type="hidden" name="action" value="enter_scores_form">
                <input type="hidden" name="assignment_id" value="<?= $assignmentId ?>">

                <div class="form-group">
                    <label for="term_id">Select Sequence</label>
                    <select id="term_id" name="term_id" required>
                        <option value="">-- Select a sequence --</option>
                        <?php foreach ($terms as $term): ?>
                            <option value="<?= $term['id'] ?>">
                                <?= htmlspecialchars($term['name']) ?> — Sequence <?= $term['sequence_number'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Load Class List</button>
            </form>
        <?php else: ?>
            <!-- Step 2: enter scores for the chosen sequence -->
            <p>Sequence: <strong><?= htmlspecialchars($selectedTerm['name']) ?> — Sequence <?= $selectedTerm['sequence_number'] ?></strong></p>

            <?php if (empty($students)): ?>
                <p>No students are enrolled in this class yet.</p>
            <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>/index.php?action=enter_scores">
                    <input type="hidden" name="assignment_id" value="<?= $assignmentId ?>">
                    <input type="hidden" name="term_id" value="<?= $selectedTerm['id'] ?>">
                    <input type="hidden" name="sequence" value="<?= $selectedTerm['sequence_number'] ?>">

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Student</th>
                                <th>Admission No.</th>
                                <th>Score (out of 20)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serialNumber = 1; ?>
                            <?php foreach ($students as $student): ?>
                                <?php $currentScore = $existingScores[$student['id']] ?? ''; ?>
                                <tr>
                                    <td><?= $serialNumber++ ?></td>
                                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                                    <td><?= htmlspecialchars($student['admission_no']) ?></td>
                                    <td>
                                        <input type="number" name="score[<?= $student['id'] ?>]"
                                               min="0" max="20" step="0.5"
                                               value="<?= htmlspecialchars((string) $currentScore) ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button type="submit" class="btn-submit">Save Scores</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>