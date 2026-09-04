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

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - <?= htmlspecialchars($schoolName) ?></title>

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
