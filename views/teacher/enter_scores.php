<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Enter Scores';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">TEACHER PORTAL</span>
        <h1>Enter Scores</h1>
        <p>
            <strong><?= htmlspecialchars($assignment['subject_name'], ENT_QUOTES, 'UTF-8') ?></strong>
            — <?= htmlspecialchars($assignment['class_name'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/index.php?action=teacher_dashboard" class="btn btn-secondary">
            ← Back to Dashboard
        </a>
    </div>
</section>

<section class="dashboard-section">

    <?php if (!$selectedTerm): ?>

        <!-- Step 1: pick which sequence to enter scores for -->
        <form method="GET" action="<?= BASE_URL ?>/index.php">
            <input type="hidden" name="action" value="enter_scores_form">
            <input type="hidden" name="assignment_id" value="<?= (int) $assignmentId ?>">

            <div class="form-group">
                <label for="term_id">Select Sequence</label>
                <select id="term_id" name="term_id" required>
                    <option value="">-- Select a sequence --</option>
                    <?php foreach ($terms as $term): ?>
                        <option value="<?= (int) $term['id'] ?>">
                            <?= htmlspecialchars($term['name'], ENT_QUOTES, 'UTF-8') ?> — Sequence <?= (int) $term['sequence_number'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Load Class List</button>
        </form>

    <?php else: ?>

        <!-- Step 2: enter scores for the chosen sequence -->
        <p>Sequence:
            <strong><?= htmlspecialchars($selectedTerm['name'], ENT_QUOTES, 'UTF-8') ?> — Sequence <?= (int) $selectedTerm['sequence_number'] ?></strong>
        </p>

        <?php if (empty($students)): ?>

            <div class="empty-state">
                <div class="empty-state-icon">!</div>
                <h3>No students enrolled</h3>
                <p>No students are enrolled in this class yet.</p>
            </div>

        <?php else: ?>

            <form method="POST" action="<?= BASE_URL ?>/index.php?action=enter_scores">
                <?= Security::csrfField() ?>
                <input type="hidden" name="assignment_id" value="<?= (int) $assignmentId ?>">
                <input type="hidden" name="term_id" value="<?= (int) $selectedTerm['id'] ?>">
                <input type="hidden" name="sequence" value="<?= (int) $selectedTerm['sequence_number'] ?>">

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Student</th>
                                <th>Score (out of 20)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serialNumber = 1; ?>
                            <?php foreach ($students as $student): ?>
                                <?php $currentScore = $existingScores[$student['id']] ?? ''; ?>
                                <tr>
                                    <td><?= $serialNumber++ ?></td>
                                    <td><?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <input type="number" name="score[<?= (int) $student['id'] ?>]"
                                               min="0" max="20" step="0.5"
                                               value="<?= is_array($currentScore) ? '' : htmlspecialchars((string) $currentScore, ENT_QUOTES, 'UTF-8') ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary">Save Scores</button>
            </form>

        <?php endif; ?>

    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
