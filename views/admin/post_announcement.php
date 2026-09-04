<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$pageTitle = 'Post Announcement';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Post Announcement</h1>
        <p>Share news with the school or a specific class.</p>
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

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=post_announcement">
            <?= Security::csrfField() ?>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title"
                       value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="body">Message</label>
                <textarea id="body" name="body" rows="5" required><?= htmlspecialchars($old['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-group">
                <label for="class_id">Audience</label>
                <select id="class_id" name="class_id">
                    <option value="">Whole School</option>
                    <?php foreach ($classes as $class): ?>
                        <?php $selected = (isset($old['class_id']) && (int) $old['class_id'] === (int) $class['id']) ? 'selected' : ''; ?>
                        <option value="<?= (int) $class['id'] ?>" <?= $selected ?>><?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Post Announcement</button>
                <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
