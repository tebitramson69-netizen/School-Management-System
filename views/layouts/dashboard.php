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
 * require __DIR__ . '/../layouts/dashboard.php';
 *
 * =========================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle =
    $pageTitle
    ?? 'Dashboard';

$school =
    $school
    ?? [
        'school_name' => 'School Management System',
        'motto' => null,
        'logo_path' => null
    ];

$content =
    $content
    ?? '';

$bodyClass =
    $bodyClass
    ?? '';

$useSchoolBackground =
    $useSchoolBackground
    ?? false;

$schoolBackground =
    $schoolBackground
    ?? null;


/*
 * School background CSS variable.
 */
$backgroundStyle = '';

if (
    $useSchoolBackground &&
    !empty($schoolBackground)
) {
    $backgroundUrl =
        BASE_URL .
        '/' .
        ltrim(
            $schoolBackground,
            '/'
        );

    $backgroundStyle =
        '--school-background-image:url("' .
        htmlspecialchars(
            $backgroundUrl,
            ENT_QUOTES,
            'UTF-8'
        ) .
        '");';
}

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
        content="#0B2946"
    >

    <title>
        <?= htmlspecialchars($pageTitle) ?>
        -
        <?= htmlspecialchars($school['school_name']) ?>
    </title>


    <!-- Bootstrap 5.3.3 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Base school-management styles -->
    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/auth.css"
    >


    <!-- Dashboard shell -->
    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/dashboard.css"
    >


    <!-- Components -->
    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/components.css"
    >


    <!-- Responsive rules -->
    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/responsive.css"
    >


    <!-- Bootstrap JavaScript -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        defer
    ></script>


    <!-- Application JavaScript -->
    <script
        src="<?= BASE_URL ?>/js/app.js"
        defer
    ></script>

</head>


<body>

<div
    class="dashboard-shell app-shell <?= $useSchoolBackground ? 'has-school-background' : '' ?> <?= htmlspecialchars($bodyClass) ?>"
    <?= $backgroundStyle !== ''
        ? 'style="' .
          htmlspecialchars(
              $backgroundStyle,
              ENT_QUOTES,
              'UTF-8'
          ) .
          '"'
        : ''
    ?>
>


    <!-- =====================================================
         TOP HEADER
         ===================================================== -->

    <?php require __DIR__ . '/../components/header.php'; ?>


    <!-- =====================================================
         BODY
         ===================================================== -->

    <div class="app-body">


        <!-- Sidebar -->

        <?php require __DIR__ . '/../components/sidebar.php'; ?>


        <!-- Main content -->

        <main
            class="app-main"
            id="mainContent"
        >

            <div class="app-content">

                <?= $content ?>

            </div>

        </main>

    </div>

</div>

</body>

</html>