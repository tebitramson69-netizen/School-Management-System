<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['form_errors'], $_SESSION['success_message'], $_SESSION['old_input']);

$events = $events ?? [];
$today = date('Y-m-d');

$pageTitle = 'Events';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Events</h1>
        <p>School events shown on the dashboard's Upcoming panel.</p>
    </div>
    <div class="actions">
        <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php?action=post_event_form">Add Event</a>
    </div>
</section>

<?php if ($successMessage !== ''): ?>
    <div class="status-message status-message-success" role="alert"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error" role="alert">
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<section class="dashboard-section">
    <?php if (empty($events)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No events</h3>
            <p>No events have been added yet.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                        <?php $isPast = ($ev['event_date'] ?? '') < $today; ?>
                        <tr>
                            <td><?= htmlspecialchars(date('j M Y', strtotime((string) $ev['event_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($ev['title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($ev['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $isPast ? '<span style="color:#889">Past</span>' : 'Upcoming' ?></td>
                            <td>
                                <form method="POST" action="<?= BASE_URL ?>/index.php?action=delete_event" style="display:inline"
                                      onsubmit="return confirm('Delete this event?');">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="event_id" value="<?= (int) $ev['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
