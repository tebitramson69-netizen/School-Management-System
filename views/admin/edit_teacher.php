<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$teacher = $teacher ?? [];

$pageTitle = 'Edit Teacher';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Edit Teacher</h1>
        <p>Update this teacher's details.</p>
    </div>
</section>

<section class="dashboard-section">
    <div class="card">
      <div class="card-body">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=update_teacher">
            <?= Security::csrfField() ?>
            <input type="hidden" name="teacher_id" value="<?= (int) ($teacher['id'] ?? 0) ?>">

            <div class="form-group">
                <label>Email (login)</label>
                <input type="text" value="<?= htmlspecialchars($teacher['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?>" disabled>
                <small class="form-help">The login email cannot be changed here.</small>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" maxlength="150"
                       value="<?= htmlspecialchars($teacher['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" maxlength="20"
                       value="<?= htmlspecialchars($teacher['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= BASE_URL ?>/index.php?action=manage_teachers" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
