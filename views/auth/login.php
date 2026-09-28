<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
 * Load school branding the same way the dashboard layout does, so the
 * auth pages show the real logo / name instead of a hardcoded value.
 */
require_once __DIR__ . '/../../src/Core/School.php';

$school = School::settings();

$schoolName = trim(
    (string) ($school['school_name'] ?? 'School Management System')
);

$logoPath = $school['logo_path'] ?? null;

/*
 * School-initials fallback (mirrors views/components/header.php).
 */
$schoolInitials = 'SMS';

$schoolWords = preg_split(
    '/\s+/',
    $schoolName,
    -1,
    PREG_SPLIT_NO_EMPTY
);

if (is_array($schoolWords) && count($schoolWords) >= 2) {
    $schoolInitials = strtoupper(
        substr($schoolWords[0], 0, 1) . substr($schoolWords[1], 0, 1)
    );
} elseif ($schoolName !== '') {
    $schoolInitials = strtoupper(substr($schoolName, 0, 3));
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($schoolName) ?></title>

    <!--
         theme.css defines the shared design tokens and MUST load before
         auth.css, matching the order used by views/layouts/dashboard.php.
    -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/theme.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">

    <script src="<?= BASE_URL ?>/js/app.js" defer></script>
</head>
<body>
    <div class="login-page">
        <div class="login-card">

            <div class="auth-logo">
                <span class="school-logo">
                    <?php if (!empty($logoPath)): ?>
                        <img
                            src="<?= htmlspecialchars(
                                BASE_URL . '/' . ltrim((string) $logoPath, '/'),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            alt="<?= htmlspecialchars($schoolName, ENT_QUOTES, 'UTF-8') ?> logo"
                        >
                    <?php else: ?>
                        <?= htmlspecialchars($schoolInitials, ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </span>
            </div>

            <h1><?= htmlspecialchars($schoolName) ?></h1>
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
