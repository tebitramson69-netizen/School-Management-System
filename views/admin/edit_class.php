<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$class = $class ?? [];
$levels = $levels ?? [];
$options = $options ?? [];

$curLevel = $class['level'] ?? '';
$curOption = $class['class_option'] ?? '';

$pageTitle = 'Edit Class';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Edit Class</h1>
        <p>Update this class.</p>
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

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=update_class">
            <?= Security::csrfField() ?>
            <input type="hidden" name="class_id" value="<?= (int) ($class['id'] ?? 0) ?>">

            <div class="form-group">
                <label for="name">Class Name</label>
                <input type="text" id="name" name="name" maxlength="50"
                       value="<?= htmlspecialchars($class['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="level">Level</label>
                <select id="level" name="level" required>
                    <option value="">-- Select a level --</option>
                    <?php foreach ($levels as $lvl): ?>
                        <option value="<?= htmlspecialchars($lvl, ENT_QUOTES, 'UTF-8') ?>" <?= $curLevel === $lvl ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lvl, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="class_option">Stream</label>
                <select id="class_option" name="class_option">
                    <option value="">General (no stream)</option>
                    <?php foreach ($options as $opt): ?>
                        <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= $curOption === $opt ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= BASE_URL ?>/index.php?action=view_classes" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
