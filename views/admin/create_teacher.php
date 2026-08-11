<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Add Teacher</h1>
            <p class="subtitle">Create a new teacher account</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/index.php?action=create_teacher">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone (optional)</label>
                    <input type="text" id="phone" name="phone"
                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Temporary Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <button type="submit" class="btn-submit">Create Teacher Account</button>
                <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>