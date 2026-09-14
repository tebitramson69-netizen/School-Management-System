<?php

/**
 * Admin — Subject Coefficients configuration.
 *
 * Per-class subject coefficients for GCE weighted averages.
 * Business logic lives in AdminController::showClassCoefficientsForm().
 *
 * Expects:
 *   $classes        array   all classes (for the picker)
 *   $classId        int     selected class id (0 = none)
 *   $selectedClass  ?array  the selected class row, or null
 *   $subjects       array   subjects taught in the class, each with
 *                           id, name, code, coefficient (pre-filled)
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
$classId = $classId ?? 0;
$selectedClass = $selectedClass ?? null;
$subjects = $subjects ?? [];

$pageTitle = 'Subject Coefficients';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Subject Coefficients</h1>
        <p>Set the per-class subject coefficients used for GCE weighted averages.</p>
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
            <input type="hidden" name="action" value="class_coefficients_form">

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
                <p>Coefficients for the subjects taught in this class.</p>
            </div>
        </div>

        <?php if (empty($subjects)): ?>

            <div class="empty-state">
                <div class="empty-state-icon">i</div>
                <h3>No subjects assigned</h3>
                <p>No subjects are taught in this class yet. Assign a teacher to a subject for this class first.</p>
            </div>

        <?php else: ?>

            <div class="card">
              <div class="card-body">

                <form method="POST" action="<?= BASE_URL ?>/index.php?action=save_class_coefficients">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="class_id" value="<?= (int) $classId ?>">

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Code</th>
                                    <th>Coefficient</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><?= htmlspecialchars($subject['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($subject['code'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <input
                                            type="number"
                                            name="coefficients[<?= (int) $subject['id'] ?>]"
                                            value="<?= (int) $subject['coefficient'] ?>"
                                            min="1"
                                            step="1"
                                            required
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Coefficients</button>
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
