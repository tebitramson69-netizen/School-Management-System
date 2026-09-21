<?php

/**
 * Admin — Class Subjects.
 *
 * Defines which subjects a class offers. Business logic lives in
 * AdminController::showClassSubjectsForm().
 *
 * Expects:
 *   $classes        array   all classes (for the picker)
 *   $allSubjects    array   every subject (id, name, code)
 *   $classId        int     selected class id (0 = none)
 *   $selectedClass  ?array  the selected class row, or null
 *   $checkedIds     int[]   subject ids to pre-check
 *   $usingDefaults  bool    true when $checkedIds came from the
 *                           stream template (not yet saved)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$successMessage = $_SESSION['success_message'] ?? '';

unset(
    $_SESSION['form_errors'],
    $_SESSION['success_message'],
    $_SESSION['old_input']
);

$classes = $classes ?? [];
$allSubjects = $allSubjects ?? [];
$classId = $classId ?? 0;
$selectedClass = $selectedClass ?? null;
$checkedIds = $checkedIds ?? [];
$usingDefaults = $usingDefaults ?? false;

$checkedLookup = array_fill_keys(
    array_map('intval', $checkedIds),
    true
);

$pageTitle = 'Class Subjects';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Class Subjects</h1>
        <p>Choose which subjects each class offers.</p>
    </div>
</section>

<?php if ($successMessage !== ''): ?>
    <div class="status-message status-message-success" role="alert">
        <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error" role="alert">
        <strong>Please correct the following:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>


<!-- CLASS PICKER -->
<section class="dashboard-section">
    <div class="card">
      <div class="card-body">

        <form method="GET" action="<?= BASE_URL ?>/index.php">
            <input type="hidden" name="action" value="class_subjects_form">

            <div class="form-group">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id" required onchange="this.form.submit()">
                    <option value="">-- Select a class --</option>
                    <?php foreach ($classes as $class): ?>
                        <?php $selected = ((int) $classId === (int) $class['id']) ? 'selected' : ''; ?>
                        <option value="<?= (int) $class['id'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-secondary">Load Class</button>
            </div>
        </form>

      </div>
    </div>
</section>


<?php if ($classId > 0 && $selectedClass === null): ?>

    <section class="dashboard-section">
        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>Class not found</h3>
            <p>The selected class could not be found.</p>
        </div>
    </section>

<?php elseif ($selectedClass !== null): ?>

    <section class="dashboard-section">
        <div class="section-heading">
            <div>
                <h2><?= htmlspecialchars($selectedClass['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p>Tick every subject this class studies.</p>
            </div>
        </div>

        <?php if ($usingDefaults): ?>
            <div class="status-message" role="status">
                Suggested subjects for this class are pre-selected from the standard
                stream defaults. Review them and click Save to apply.
            </div>
        <?php endif; ?>

        <?php if (empty($allSubjects)): ?>

            <div class="empty-state">
                <div class="empty-state-icon">i</div>
                <h3>No subjects</h3>
                <p>No subjects exist yet. Add subjects first on the Subjects screen.</p>
            </div>

        <?php else: ?>

            <div class="card">
              <div class="card-body">

                <form method="POST" action="<?= BASE_URL ?>/index.php?action=save_class_subjects">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="class_id" value="<?= (int) $classId ?>">

                    <div class="checkbox-grid">
                        <?php foreach ($allSubjects as $subject): ?>
                            <?php $sid = (int) $subject['id']; ?>
                            <label class="checkbox-label">
                                <input
                                    type="checkbox"
                                    name="subjects[]"
                                    value="<?= $sid ?>"
                                    <?= isset($checkedLookup[$sid]) ? 'checked' : '' ?>
                                >
                                <?= htmlspecialchars($subject['name'], ENT_QUOTES, 'UTF-8') ?>
                                <span class="subject-code">(<?= htmlspecialchars($subject['code'], ENT_QUOTES, 'UTF-8') ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Subjects</button>
                        <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

              </div>
            </div>

        <?php endif; ?>
    </section>

<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
