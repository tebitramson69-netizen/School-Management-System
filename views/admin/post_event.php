<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$pageTitle = 'Add Event';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Add Event</h1>
        <p>Add an upcoming school event.</p>
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

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=post_event">
            <?= Security::csrfField() ?>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" maxlength="150"
                       value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="event_date">Date</label>
                <input type="date" id="event_date" name="event_date"
                       value="<?= htmlspecialchars($old['event_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Details (optional)</label>
                <textarea id="description" name="description" rows="3" maxlength="500"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Event</button>
                <a href="<?= BASE_URL ?>/index.php?action=view_events" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
