<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$oldStudentIds = array_map('intval', $old['student_ids'] ?? []);

$pageTitle = 'Add Parent';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Add Parent</h1>
        <p>Create a new parent account.</p>
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

        <?php if (empty($students)): ?>

            <div class="alert alert-error">
                No students exist yet. Please add a student before creating a parent account.
            </div>

        <?php else: ?>

            <form method="POST" action="<?= BASE_URL ?>/index.php?action=create_parent">
                <?= Security::csrfField() ?>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?= htmlspecialchars($old['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-suffix-group">
                        <input type="text" id="username" name="username"
                               value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <span class="input-suffix"><?= htmlspecialchars(SCHOOL_EMAIL_DOMAIN, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone (optional)</label>
                    <input type="text" id="phone" name="phone"
                           value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="student_ids">Child(ren)</label>
                    <select id="student_ids" name="student_ids[]" multiple required size="6">
                        <?php foreach ($students as $student): ?>
                            <?php $selected = in_array((int) $student['id'], $oldStudentIds, true) ? 'selected' : ''; ?>
                            <option value="<?= (int) $student['id'] ?>" <?= $selected ?>>
                                <?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-help">Hold Ctrl (Windows) or Cmd (Mac) to select multiple children.</small>
                </div>

                <div class="form-group">
                    <label for="password">Temporary Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <small class="form-help">At least 8 characters, with uppercase, lowercase and a number.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Parent Account</button>
                    <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

        <?php endif; ?>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
