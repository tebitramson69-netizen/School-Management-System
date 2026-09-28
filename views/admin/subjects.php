<?php
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

$subjects = $subjects ?? [];

$pageTitle = 'Subjects';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Subjects</h1>
        <p>All subjects offered across the school.</p>
    </div>
    <div class="actions">
        <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php?action=create_subject_form">
            Add Subject
        </a>
    </div>
</section>

<?php if ($successMessage !== ''): ?>
    <div class="status-message status-message-success" role="alert">
        <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error" role="alert">
        <strong>Please note:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<section class="dashboard-section">

    <?php if (empty($subjects)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No subjects</h3>
            <p>No subjects have been configured yet.</p>
        </div>

    <?php else: ?>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Code</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?= htmlspecialchars($subject['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($subject['code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a class="btn btn-sm btn-secondary"
                                   href="<?= BASE_URL ?>/index.php?action=edit_subject_form&subject_id=<?= (int) $subject['id'] ?>">
                                    Edit
                                </a>
                                <form
                                    method="POST"
                                    action="<?= BASE_URL ?>/index.php?action=delete_subject"
                                    style="display:inline"
                                    onsubmit="return confirm('Delete this subject? This cannot be undone.');"
                                >
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="subject_id" value="<?= (int) $subject['id'] ?>">
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
