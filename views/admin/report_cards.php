<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

$classes = $classes ?? [];
$terms = $terms ?? [];
$classId = $classId ?? 0;
$termId = $termId ?? 0;
$selectedClass = $selectedClass ?? null;
$selectedTerm = $selectedTerm ?? null;
$students = $students ?? [];

$pageTitle = 'Report Cards';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Report Cards</h1>
        <p>Select a class and term, then open a student's report card to view or print.</p>
    </div>
</section>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error" role="alert">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<section class="dashboard-section">
    <div class="card">
      <div class="card-body">

        <form method="GET" action="<?= BASE_URL ?>/index.php">
            <input type="hidden" name="action" value="report_cards">

            <div class="form-group">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id" required>
                    <option value="">-- Select a class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= (int) $class['id'] ?>" <?= (int) $classId === (int) $class['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="term_id">Term</label>
                <select id="term_id" name="term_id" required>
                    <option value="">-- Select a term --</option>
                    <?php foreach ($terms as $term): ?>
                        <option value="<?= (int) $term['term_id'] ?>" <?= (int) $termId === (int) $term['term_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($term['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Load Students</button>
            </div>
        </form>

      </div>
    </div>
</section>


<?php if ($selectedClass !== null && $selectedTerm !== null): ?>

    <section class="dashboard-section">
        <div class="section-heading">
            <div>
                <h2><?= htmlspecialchars($selectedClass['name'], ENT_QUOTES, 'UTF-8') ?>
                    — <?= htmlspecialchars($selectedTerm['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p>Open a student's report card for this term.</p>
            </div>
        </div>

        <?php if (empty($students)): ?>

            <div class="empty-state">
                <div class="empty-state-icon">i</div>
                <h3>No students</h3>
                <p>No students are enrolled in this class for the current academic year.</p>
            </div>

        <?php else: ?>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Student</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sn = 1; ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= $sn++ ?></td>
                                <td><?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a class="btn btn-sm btn-primary"
                                       href="<?= BASE_URL ?>/index.php?action=report_card&student_id=<?= (int) $student['id'] ?>&class_id=<?= (int) $selectedClass['id'] ?>&term_id=<?= (int) $selectedTerm['id'] ?>">
                                        View Report Card
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </section>

<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
