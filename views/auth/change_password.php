<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
    <script src="<?= BASE_URL ?>/js/app.js" defer></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Change Your Password</h1>
            <p class="subtitle">You must set a new password before continuing.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/index.php?action=change_password">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>

                <button type="submit" class="btn-submit">Set New Password</button>
            </form>
        </div>
    </div>
</body>
</html>