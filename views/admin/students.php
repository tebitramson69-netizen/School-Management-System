<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['form_errors'], $_SESSION['success_message'], $_SESSION['old_input']);

$students = $students ?? [];

$pageTitle = 'Manage Students';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Students</h1>
        <p>Add, edit, deactivate or remove student accounts.</p>
    </div>
    <div class="actions">
        <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php?action=create_student_form">Add Student</a>
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
    <?php if (empty($students)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No students</h3>
            <p>No student accounts have been created yet.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sn = 1; foreach ($students as $s): ?>
                        <?php $active = (int) ($s['is_active'] ?? 1) === 1; ?>
                        <tr>
                            <td><?= $sn++ ?></td>
                            <td><?= htmlspecialchars($s['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s['class_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $active ? 'Active' : '<span style="color:#c0392b">Inactive</span>' ?></td>
                            <td>
                                <a class="btn btn-sm btn-secondary" href="<?= BASE_URL ?>/index.php?action=edit_student_form&student_id=<?= (int) $s['id'] ?>">Edit</a>

                                <form method="POST" action="<?= BASE_URL ?>/index.php?action=set_student_active" style="display:inline">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="student_id" value="<?= (int) $s['id'] ?>">
                                    <input type="hidden" name="active" value="<?= $active ? 0 : 1 ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary"><?= $active ? 'Deactivate' : 'Activate' ?></button>
                                </form>

                                <form method="POST" action="<?= BASE_URL ?>/index.php?action=delete_student" style="display:inline"
                                      onsubmit="return confirm('Permanently delete this student? This cannot be undone.');">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="student_id" value="<?= (int) $s['id'] ?>">
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
