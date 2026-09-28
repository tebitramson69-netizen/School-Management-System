<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$classes = $classes ?? [];

$pageTitle = 'Add Subject';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Add Subject</h1>
        <p>Create a new subject.</p>
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

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=create_subject">
            <?= Security::csrfField() ?>

            <div class="form-group">
                <label for="name">Subject Name</label>
                <input type="text" id="name" name="name" maxlength="100"
                       value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="code">Subject Code</label>
                <input type="text" id="code" name="code" maxlength="20"
                       value="<?= htmlspecialchars($old['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required
                       style="text-transform:uppercase">
                <small class="form-help">Letters and numbers only, e.g. ADMATH, RS, LAW.</small>
            </div>

            <div class="form-group">
                <label>Offered in classes</label>
                <?php if (empty($classes)): ?>
                    <p class="form-help">No classes exist yet.</p>
                <?php else: ?>
                    <div class="checkbox-grid">
                        <?php foreach ($classes as $class): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="classes[]" value="<?= (int) $class['id'] ?>">
                                <?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small class="form-help">Tick every class that studies this subject. You can also manage this on the Class Subjects screen.</small>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Subject</button>
                <a href="<?= BASE_URL ?>/index.php?action=view_subjects" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
