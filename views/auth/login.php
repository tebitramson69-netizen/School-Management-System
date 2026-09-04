<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
    <script src="<?= BASE_URL ?>/js/app.js" defer></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>School Management System</h1>
            <p class="subtitle">Sign in to your account</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/index.php?action=login_submit">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-submit">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>