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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/></svg>
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V8l7-4 7 4v13"/><path d="M9 21v-5h6v5"/><path d="M9 11h.01"/><path d="M15 11h.01"/></svg>
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg>
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg>
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18"/><path d="M8 2v4"/><path d="M16 2v4"/></svg>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="4"/><path d="M3 20c0-3.3 2.7-6 6-6h1"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="4"/><path d="M3 20c0-3.3 2.7-6 6-6h1"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11v2"/><path d="M6 9l11-4v14L6 15z"/><path d="M17 9a4 4 0 0 1 0 6"/></svg>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 8-6-16-3 8H2"/></svg>
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