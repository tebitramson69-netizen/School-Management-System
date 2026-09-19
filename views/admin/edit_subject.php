<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$subject = $subject ?? [];

/*
 * Prefer submitted old input (after a validation error), then the
 * stored subject row.
 */
$nameValue = $old['name'] ?? $subject['name'] ?? '';
$codeValue = $old['code'] ?? $subject['code'] ?? '';

$pageTitle = 'Edit Subject';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Edit Subject</h1>
        <p>Update this subject's details.</p>
    </div>
</section>

<section class="dashboard-section">
    <div class="card">
      <div class="card-body">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=update_subject">
            <?= Security::csrfField() ?>
            <input type="hidden" name="subject_id" value="<?= (int) ($subject['id'] ?? 0) ?>">

            <div class="form-group">
                <label for="name">Subject Name</label>
                <input type="text" id="name" name="name" maxlength="100"
                       value="<?= htmlspecialchars($nameValue, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="code">Subject Code</label>
                <input type="text" id="code" name="code" maxlength="20"
                       value="<?= htmlspecialchars($codeValue, ENT_QUOTES, 'UTF-8') ?>" required
                       style="text-transform:uppercase">
                <small class="form-help">Letters and numbers only, e.g. ADMATH, RS, LAW.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= BASE_URL ?>/index.php?action=view_subjects" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
