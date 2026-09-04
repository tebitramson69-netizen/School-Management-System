<?php

/*
 * =========================================================
 * SHARED DASHBOARD LAYOUT
 * =========================================================
 *
 * This file is a reusable shell.
 *
 * A dashboard view should normally provide:
 *
 * $school
 * $pageTitle
 * $content
 *
 * Example:
 *
 * $pageTitle = 'Admin Dashboard';
 * ob_start();
 * require __DIR__ . '/../admin/dashboard-content.php';
 * $content = ob_get_clean();
 *
 * The sidebar and header are shared by all roles.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   DEFAULT VALUES
   ========================================================= */

$pageTitle = $pageTitle ?? 'Dashboard';

/*
 * If the controller did not provide school settings, load them
 * here so every view that uses this layout shows real branding.
 */
if (!isset($school) || !is_array($school)) {

    require_once __DIR__ . '/../../src/Core/School.php';

    $school = School::settings();
}

$school = $school ?? [
    'school_name' => 'School Management System',
    'motto'       => null,
    'logo_path'   => null
];

$content = $content ?? '';

$bodyClass = $bodyClass ?? '';

$useSchoolBackground = $useSchoolBackground ?? false;

$schoolBackground = $schoolBackground ?? null;


/* =========================================================
   SCHOOL BACKGROUND
   ========================================================= */

$backgroundStyle = '';

if ($useSchoolBackground && !empty($schoolBackground)) {

    $backgroundUrl =
        BASE_URL . '/' . ltrim($schoolBackground, '/');

    $backgroundStyle =
        '--school-background-image:url("' .
        htmlspecialchars(
            $backgroundUrl,
            ENT_QUOTES,
            'UTF-8'
        ) .
        '");';
}


/* =========================================================
   USER INFORMATION
   ========================================================= */

$currentRole = $_SESSION['role'] ?? '';

$currentUserName =
    $_SESSION['full_name']
    ?? $_SESSION['name']
    ?? $_SESSION['email']
    ?? 'User';


/*
 * Initials for the avatar.
 */
$nameParts = preg_split(
    '/\s+/',
    trim($currentUserName)
);

if (count($nameParts) >= 2) {

    $userInitials =
        strtoupper(
            substr($nameParts[0], 0, 1) .
            substr(
                $nameParts[count($nameParts) - 1],
                0,
                1
            )
        );

} else {

    $userInitials =
        strtoupper(
            substr($currentUserName, 0, 2)
        );
}


/*
 * Human-readable role.
 */
$roleLabels = [
    'admin'   => 'Administrator',
    'teacher' => 'Teacher',
    'student' => 'Student',
    'parent'  => 'Parent'
];

$currentRoleLabel =
    $roleLabels[$currentRole]
    ?? ucfirst($currentRole ?: 'User');

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="theme-color"
        content="#0D3B4C"
    >

    <title>
        <?= htmlspecialchars($pageTitle) ?>
        -
        <?= htmlspecialchars($school['school_name']) ?>
    </title>


    <!-- =====================================================
         BOOTSTRAP
         ===================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         APPLICATION STYLES
         ===================================================== -->

    <!--
         Global design system.
         This MUST load first because the other CSS files
         use the variables defined here.
    -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/theme.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/auth.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/components.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/dashboard.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/responsive.css"
    >


    <!-- =====================================================
         BOOTSTRAP JS
         ===================================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        defer
    ></script>


    <!-- =====================================================
         APPLICATION JS
         ===================================================== -->

    <script
        src="<?= BASE_URL ?>/js/app.js"
        defer
    ></script>

</head>


<body>

<div
    class="dashboard-shell app-shell <?= $useSchoolBackground ? 'has-school-background' : '' ?> <?= htmlspecialchars($bodyClass) ?>"
    <?php if ($backgroundStyle !== ''): ?>
        style="<?= htmlspecialchars(
            $backgroundStyle,
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    <?php endif; ?>
>


    <!-- =====================================================
         TOPBAR
         ===================================================== -->

    <?php
    require __DIR__ . '/../components/header.php';
    ?>


    <!-- =====================================================
         APPLICATION BODY
         ===================================================== -->

    <div class="app-body">


        <!-- =================================================
             SIDEBAR
             ================================================= -->

        <?php
        require __DIR__ . '/../components/sidebar.php';
        ?>


        <!-- =================================================
             MAIN CONTENT
             ================================================= -->

        <main
            class="app-main"
            id="mainContent"
        >

            <div class="app-content">

                <?= $content ?>

            </div>

        </main>


    </div>


    <!-- =====================================================
         MOBILE SIDEBAR OVERLAY
         ===================================================== -->

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
        aria-hidden="true"
    ></div>


</div>

</body>

</html>