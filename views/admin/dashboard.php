<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Admin Dashboard';

$adminName = $_SESSION['user_name'] ?? 'Administrator';

$hour = (int) date('G');

if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 18) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}

$success = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

ob_start();

?>

<!-- =========================================================
     ADMIN DASHBOARD
     ========================================================= -->

<div class="admin-dashboard">

    <!-- =====================================================
         WELCOME
         ===================================================== -->

    <section class="dashboard-welcome">

        <div class="dashboard-welcome-content">

            <span class="dashboard-eyebrow">
                Administration
            </span>

            <h1 class="dashboard-welcome-title">
                <?= htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') ?>,
                <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p class="dashboard-welcome-description">
                Manage your school from one place.
            </p>

        </div>

    </section>


    <?php if ($success): ?>

        <div
            class="status-message status-message-success"
            role="alert"
        >
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>

    <?php endif; ?>


    <!-- =====================================================
         SCHOOL OVERVIEW
         ===================================================== -->

    <section class="dashboard-section">

        <div class="dashboard-section-heading">

            <div>
                <span class="dashboard-eyebrow">
                    School overview
                </span>

                <h2 class="dashboard-section-title">
                    At a glance
                </h2>
            </div>

        </div>


        <div class="dashboard-stats-grid">

            <!-- Students -->
            <article class="dashboard-stat-card">

                <div class="dashboard-stat-header">

                    <span class="dashboard-stat-label">
                        Students
                    </span>

                    <span
                        class="dashboard-stat-icon"
                        aria-hidden="true"
                    >
                        S
                    </span>

                </div>

                <strong class="dashboard-stat-value">
                    <?= number_format($dashboardStats['students'] ?? 0) ?>
                </strong>

                <span class="dashboard-stat-note">
                    Registered students
                </span>

            </article>


            <!-- Teachers -->
            <article class="dashboard-stat-card">

                <div class="dashboard-stat-header">

                    <span class="dashboard-stat-label">
                        Teachers
                    </span>

                    <span
                        class="dashboard-stat-icon"
                        aria-hidden="true"
                    >
                        T
                    </span>

                </div>

                <strong class="dashboard-stat-value">
                    <?= number_format($dashboardStats['teachers'] ?? 0) ?>
                </strong>

                <span class="dashboard-stat-note">
                    Teaching staff
                </span>

            </article>


            <!-- Classes -->
            <article class="dashboard-stat-card">

                <div class="dashboard-stat-header">

                    <span class="dashboard-stat-label">
                        Classes
                    </span>

                    <span
                        class="dashboard-stat-icon"
                        aria-hidden="true"
                    >
                        C
                    </span>

                </div>

                <strong class="dashboard-stat-value">
                    <?= number_format($dashboardStats['classes'] ?? 0) ?>
                </strong>

                <span class="dashboard-stat-note">
                    Active classes
                </span>

            </article>


            <!-- GCE Candidates -->
            <article class="dashboard-stat-card">

                <div class="dashboard-stat-header">

                    <span class="dashboard-stat-label">
                        GCE Candidates
                    </span>

                    <span
                        class="dashboard-stat-icon"
                        aria-hidden="true"
                    >
                        G
                    </span>

                </div>

                <strong class="dashboard-stat-value">
                    <?= number_format($dashboardStats['gce_candidates'] ?? 0) ?>
                </strong>

                <span class="dashboard-stat-note">
                    Registered candidates
                </span>

            </article>

        </div>

    </section>


    <!-- =====================================================
         ACADEMIC OVERVIEW
         ===================================================== -->

    <section class="dashboard-section">

        <div class="dashboard-section-heading">

            <div>
                <span class="dashboard-eyebrow">
                    Academic overview
                </span>

                <h2 class="dashboard-section-title">
                    School performance
                </h2>
            </div>

        </div>


        <div class="dashboard-main-grid">


            <!-- Academic Performance -->
            <article class="dashboard-panel dashboard-performance-panel">

                <div class="dashboard-panel-header">

                    <div>

                        <span class="dashboard-panel-eyebrow">
                            Results
                        </span>

                        <h3>
                            Academic Performance
                        </h3>

                    </div>

                </div>


                <div class="dashboard-chart-placeholder">

                    <div class="chart-placeholder-icon">
                        ↗
                    </div>

                    <h4>
                        Performance overview
                    </h4>

                    <p>
                        Academic performance data will appear here
                        once results have been recorded.
                    </p>

                </div>


                <div class="performance-summary">

                    <div class="performance-item">

                        <span>
                            O/L Pass Rate
                        </span>

                        <strong>
                            —
                        </strong>

                    </div>


                    <div class="performance-item">

                        <span>
                            A/L Pass Rate
                        </span>

                        <strong>
                            —
                        </strong>

                    </div>

                </div>

            </article>


            <!-- Upcoming -->
            <article class="dashboard-panel dashboard-upcoming-panel">

                <div class="dashboard-panel-header">

                    <div>

                        <span class="dashboard-panel-eyebrow">
                            Schedule
                        </span>

                        <h3>
                            Upcoming
                        </h3>

                    </div>

                </div>


                <div class="dashboard-empty-content">

                    <div class="dashboard-empty-icon">
                        •
                    </div>

                    <h4>
                        No upcoming events
                    </h4>

                    <p>
                        Tests, report cards, GCE registration
                        deadlines and staff events will appear here.
                    </p>

                </div>

            </article>

        </div>

    </section>


    <!-- =====================================================
         QUICK ACTIONS
         ===================================================== -->

    <section class="dashboard-section">

        <div class="dashboard-section-heading">

            <div>
                <span class="dashboard-eyebrow">
                    Quick access
                </span>

                <h2 class="dashboard-section-title">
                    Quick Actions
                </h2>
            </div>

        </div>


        <div class="dashboard-quick-actions">

            <a
                href="<?= BASE_URL ?>/index.php?action=create_student_form"
                class="dashboard-action"
            >
                <span class="dashboard-action-icon">
                    +
                </span>

                <span class="dashboard-action-content">

                    <strong>
                        Add Student
                    </strong>

                    <small>
                        Register a student
                    </small>

                </span>

                <span class="dashboard-action-arrow">
                    →
                </span>
            </a>


            <a
                href="<?= BASE_URL ?>/index.php?action=create_teacher_form"
                class="dashboard-action"
            >
                <span class="dashboard-action-icon">
                    +
                </span>

                <span class="dashboard-action-content">

                    <strong>
                        Add Teacher
                    </strong>

                    <small>
                        Create a teacher account
                    </small>

                </span>

                <span class="dashboard-action-arrow">
                    →
                </span>
            </a>


            <a
                href="<?= BASE_URL ?>/index.php?action=create_parent_form"
                class="dashboard-action"
            >
                <span class="dashboard-action-icon">
                    +
                </span>

                <span class="dashboard-action-content">

                    <strong>
                        Add Parent
                    </strong>

                    <small>
                        Create a parent account
                    </small>

                </span>

                <span class="dashboard-action-arrow">
                    →
                </span>
            </a>


            <a
                href="<?= BASE_URL ?>/index.php?action=assign_teacher_form"
                class="dashboard-action"
            >
                <span class="dashboard-action-icon">
                    ↗
                </span>

                <span class="dashboard-action-content">

                    <strong>
                        Assign Teacher
                    </strong>

                    <small>
                        Manage class assignments
                    </small>

                </span>

                <span class="dashboard-action-arrow">
                    →
                </span>
            </a>


            <a
                href="<?= BASE_URL ?>/index.php?action=view_classes"
                class="dashboard-action"
            >
                <span class="dashboard-action-icon">
                    C
                </span>

                <span class="dashboard-action-content">

                    <strong>
                        Classes
                    </strong>

                    <small>
                        Manage school classes
                    </small>

                </span>

                <span class="dashboard-action-arrow">
                    →
                </span>
            </a>


            <a
                href="<?= BASE_URL ?>/index.php?action=post_announcement_form"
                class="dashboard-action"
            >
                <span class="dashboard-action-icon">
                    +
                </span>

                <span class="dashboard-action-content">

                    <strong>
                        Announcement
                    </strong>

                    <small>
                        Publish school news
                    </small>

                </span>

                <span class="dashboard-action-arrow">
                    →
                </span>
            </a>

        </div>

    </section>


    <!-- =====================================================
         RECENT ACTIVITY
         ===================================================== -->

    <section class="dashboard-section">

        <div class="dashboard-section-heading">

            <div>
                <span class="dashboard-eyebrow">
                    Activity
                </span>

                <h2 class="dashboard-section-title">
                    Recent Activity
                </h2>
            </div>

        </div>


        <article class="dashboard-panel">

            <div class="dashboard-empty-content dashboard-activity-empty">

                <div class="dashboard-empty-icon">
                    •
                </div>

                <h4>
                    No recent activity
                </h4>

                <p>
                    New registrations, assignments,
                    announcements and other school activity
                    will appear here.
                </p>

            </div>

        </article>

    </section>

</div>


<?php

$content = ob_get_clean();

require_once __DIR__ . '/../layouts/dashboard.php';