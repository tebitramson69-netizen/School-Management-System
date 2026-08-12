<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$oldStudentIds = array_map('intval', $old['student_ids'] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Parent - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Add Parent</h1>
            <p class="subtitle">Create a new parent account</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
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
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name"
                               value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-suffix-group">
                            <input type="text" id="username" name="username"
                                   value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
                            <span class="input-suffix"><?= SCHOOL_EMAIL_DOMAIN ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone (optional)</label>
                        <input type="text" id="phone" name="phone"
                               value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="student_ids">Child(ren)</label>
                        <select id="student_ids" name="student_ids[]" multiple required size="6">
                            <?php foreach ($students as $student): ?>
                                <?php $selected = in_array((int) $student['id'], $oldStudentIds, true) ? 'selected' : ''; ?>
                                <option value="<?= $student['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($student['full_name']) ?> (<?= htmlspecialchars($student['admission_no']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Hold Ctrl (Windows) or Cmd (Mac) to select multiple children.</small>
                    </div>

                    <div class="form-group">
                        <label for="password">Temporary Password</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>

                    <button type="submit" class="btn-submit">Create Parent Account</button>
                    <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn-cancel">Cancel</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>