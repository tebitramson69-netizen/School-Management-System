<?php

/*
 * =========================================================
 * SCHOOL MANAGEMENT SYSTEM
 * PREMIUM SHARED SIDEBAR
 * =========================================================
 *
 * File:
 * views/components/sidebar.php
 *
 * Responsibilities:
 * - School branding
 * - Current user identity
 * - Role-based navigation
 * - Active navigation state
 * - Account navigation
 *
 * PHP 8+
 * =========================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   CURRENT USER
   ========================================================= */

$currentRole = $_SESSION['role'] ?? '';

$currentAction = $_GET['action'] ?? '';

$currentUserName =
    $_SESSION['full_name']
    ?? $_SESSION['name']
    ?? $_SESSION['email']
    ?? 'User';


/* =========================================================
   SCHOOL INFORMATION
   ========================================================= */

$schoolName =
    $school['school_name']
    ?? 'School Management System';

$schoolMotto =
    $school['motto']
    ?? null;

$logoPath =
    $school['logo_path']
    ?? null;


/* =========================================================
   SCHOOL INITIALS
   ========================================================= */

$schoolInitials = 'SMS';

if (!empty($schoolName)) {

    $words = preg_split(
        '/\s+/',
        trim($schoolName)
    );

    if (count($words) >= 2) {

        $schoolInitials = strtoupper(
            substr($words[0], 0, 1) .
            substr($words[1], 0, 1)
        );

    } else {

        $schoolInitials = strtoupper(
            substr($schoolName, 0, 3)
        );
    }
}


/* =========================================================
   USER INITIALS
   ========================================================= */

$nameParts = preg_split(
    '/\s+/',
    trim($currentUserName)
);

if (count($nameParts) >= 2) {

    $userInitials = strtoupper(
        substr($nameParts[0], 0, 1) .
        substr(
            $nameParts[count($nameParts) - 1],
            0,
            1
        )
    );

} else {

    $userInitials = strtoupper(
        substr($currentUserName, 0, 2)
    );
}


/* =========================================================
   ROLE LABEL
   ========================================================= */

$roleLabels = [
    'admin'   => 'Administrator',
    'teacher' => 'Teacher',
    'student' => 'Student',
    'parent'  => 'Parent'
];

$roleLabel =
    $roleLabels[$currentRole]
    ?? ucfirst($currentRole ?: 'User');


/* =========================================================
   ACTIVE LINK HELPER
   ========================================================= */

function sidebarIsActive(
    string|array $actions
): string {

    global $currentAction;

    $actions = is_array($actions)
        ? $actions
        : [$actions];

    return in_array(
        $currentAction,
        $actions,
        true
    )
        ? 'active'
        : '';
}

?>

<aside
    class="app-sidebar"
    id="appSidebar"
    aria-label="Main navigation"
>


    <!-- =====================================================
         SIDEBAR BRAND
         ===================================================== -->

    <div class="sidebar-brand">

        <a
            href="<?= BASE_URL ?>/index.php?action=<?= $currentRole === 'admin'
                ? 'admin_dashboard'
                : ($currentRole === 'teacher'
                    ? 'teacher_dashboard'
                    : ($currentRole === 'student'
                        ? 'student_dashboard'
                        : 'parent_dashboard')) ?>"
            class="sidebar-brand-link"
            aria-label="Go to dashboard"
        >

            <div class="sidebar-brand-logo">

                <?php if (!empty($logoPath)): ?>

                    <img
                        src="<?= BASE_URL ?>/<?= htmlspecialchars(
                            ltrim($logoPath, '/'),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $schoolName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?> logo"
                    >

                <?php else: ?>

                    <span>
                        <?= htmlspecialchars(
                            $schoolInitials,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                <?php endif; ?>

            </div>


            <div class="sidebar-brand-content">

                <strong class="sidebar-school-name">
                    <?= htmlspecialchars(
                        $schoolName,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>

                <?php if (!empty($schoolMotto)): ?>

                    <span class="sidebar-school-motto">
                        <?= htmlspecialchars(
                            $schoolMotto,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                <?php else: ?>

                    <span class="sidebar-school-motto">
                        School Management Portal
                    </span>

                <?php endif; ?>

            </div>

        </a>

    </div>


    <!-- =====================================================
         USER PROFILE
         ===================================================== -->

    <div class="sidebar-user-card">

        <div
            class="sidebar-user-avatar"
            aria-hidden="true"
        >
            <?= htmlspecialchars(
                $userInitials,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>


        <div class="sidebar-user-info">

            <strong>
                <?= htmlspecialchars(
                    $currentUserName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>

            <span>
                <?= htmlspecialchars(
                    $roleLabel,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>

        </div>

    </div>


    <!-- =====================================================
         NAVIGATION
         ===================================================== -->

    <nav
        class="sidebar-navigation"
        aria-label="Primary navigation"
    >


        <?php if ($currentRole === 'admin'): ?>

            <!-- =================================================
                 ADMINISTRATION
                 ================================================= -->

            <div class="sidebar-section">

                <span class="sidebar-section-label">
                    Administration
                </span>


                <ul class="sidebar-menu">


                    <!-- Dashboard -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=admin_dashboard"
                            class="sidebar-menu-link <?= sidebarIsActive([
                                'admin_dashboard',
                                ''
                            ]) ?>"
                            <?= sidebarIsActive([
                                'admin_dashboard',
                                ''
                            ]) === 'active'
                                ? 'aria-current="page"'
                                : '' ?>
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect
                                        x="3"
                                        y="3"
                                        width="7"
                                        height="7"
                                        rx="1"
                                    />
                                    <rect
                                        x="14"
                                        y="3"
                                        width="7"
                                        height="7"
                                        rx="1"
                                    />
                                    <rect
                                        x="3"
                                        y="14"
                                        width="7"
                                        height="7"
                                        rx="1"
                                    />
                                    <rect
                                        x="14"
                                        y="14"
                                        width="7"
                                        height="7"
                                        rx="1"
                                    />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Dashboard
                            </span>

                        </a>

                    </li>


                    <!-- Students -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=create_student_form"
                            class="sidebar-menu-link <?= sidebarIsActive('create_student_form') ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                    />
                                    <circle
                                        cx="9"
                                        cy="7"
                                        r="4"
                                    />
                                    <path
                                        d="M19 8v6"
                                    />
                                    <path
                                        d="M22 11h-6"
                                    />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Students
                            </span>

                        </a>

                    </li>


                    <!-- Teachers -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=create_teacher_form"
                            class="sidebar-menu-link <?= sidebarIsActive('create_teacher_form') ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle
                                        cx="9"
                                        cy="7"
                                        r="4"
                                    />
                                    <path
                                        d="M3 21v-2a6 6 0 0 1 6-6"
                                    />
                                    <path
                                        d="M17 11l2 2 4-4"
                                    />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Teachers
                            </span>

                        </a>

                    </li>


                    <!-- Parents -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=create_parent_form"
                            class="sidebar-menu-link <?= sidebarIsActive('create_parent_form') ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle
                                        cx="9"
                                        cy="7"
                                        r="4"
                                    />
                                    <path
                                        d="M3 21v-2a6 6 0 0 1 6-6h2"
                                    />
                                    <circle
                                        cx="18"
                                        cy="17"
                                        r="3"
                                    />
                                    <path
                                        d="M18 15.5v3"
                                    />
                                    <path
                                        d="M16.5 17h3"
                                    />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Parents
                            </span>

                        </a>

                    </li>


                    <!-- Classes -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=view_classes"
                            class="sidebar-menu-link <?= sidebarIsActive([
                                'view_classes',
                                'view_class_list'
                            ]) ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="M4 4h16v16H4z"
                                    />
                                    <path
                                        d="M8 8h8"
                                    />
                                    <path
                                        d="M8 12h8"
                                    />
                                    <path
                                        d="M8 16h5"
                                    />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Classes
                            </span>

                        </a>

                    </li>


                    <!-- Assign Teachers -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=assign_teacher_form"
                            class="sidebar-menu-link <?= sidebarIsActive('assign_teacher_form') ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="M12 5v14"
                                    />
                                    <path
                                        d="M5 12h14"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                    />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Assign Teachers
                            </span>

                        </a>

                    </li>


                    <!-- Teacher Assignments -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=view_teachers"
                            class="sidebar-menu-link <?= sidebarIsActive('view_teachers') ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="M4 19V5"
                                    />
                                    <path
                                        d="M4 5h11"
                                    />
                                    <path
                                        d="M15 5v4"
                                    />
                                    <path
                                        d="M15 9h5"
                                    />
                                    <path
                                        d="M20 9v10"
                                    />
                                    <path
                                        d="M4 19h16"
                                    />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Teacher Assignments
                            </span>

                        </a>

                    </li>

 <!-- Subject Coefficients -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=class_coefficients_form"
                            class="sidebar-menu-link <?= sidebarIsActive([
                                'class_coefficients_form'
                            ]) ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <line x1="4" y1="6" x2="20" y2="6" />
                                    <circle cx="9" cy="6" r="2" />
                                    <line x1="4" y1="12" x2="20" y2="12" />
                                    <circle cx="15" cy="12" r="2" />
                                    <line x1="4" y1="18" x2="20" y2="18" />
                                    <circle cx="8" cy="18" r="2" />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Subject Coefficients
                            </span>

                        </a>

                    </li>

 <!-- Academic Management -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=academic_years"
                            class="sidebar-menu-link <?= sidebarIsActive([
                                'academic_years',
                                'create_academic_year',
                                'activate_academic_year'
                            ]) ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect x="3" y="4" width="18" height="17" rx="2" />
                                    <path d="M3 9h18" />
                                    <path d="M8 2v4" />
                                    <path d="M16 2v4" />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Academic Management
                            </span>

                        </a>

                    </li>
                    <!-- Post Announcement -->

                    <li class="sidebar-menu-item">
                        <a
                            href="<?= BASE_URL ?>/index.php?action=post_announcement_form"
                            class="sidebar-menu-link <?= sidebarIsActive('post_announcement_form') ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="M3 11v2"
                                    />
                                    <path
                                        d="M6 9l11-4v14L6 15z"
                                    />
                                    <path
                                        d="M17 9a4 4 0 0 1 0 6"
                                    />
                                    <path
                                        d="M6 15l1 5h3l-1-5"
                                    />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Post Announcement
                            </span>

                        </a>

                    </li>


                    <!-- Announcements -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=view_announcements"
                            class="sidebar-menu-link <?= sidebarIsActive('view_announcements') ?>"
                        >

                            <span
                                class="sidebar-menu-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="M4 4h16v16H4z"
                                    />
                                    <path
                                        d="M8 9h8"
                                    />
                                    <path
                                        d="M8 13h8"
                                    />
                                    <path
                                        d="M8 17h5"
                                    />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Announcements
                            </span>

                        </a>

                    </li>

                </ul>

            </div>


        <?php elseif ($currentRole === 'teacher'): ?>


            <!-- =================================================
                 TEACHER
                 ================================================= -->

            <div class="sidebar-section">

                <span class="sidebar-section-label">
                    Teaching
                </span>

                <ul class="sidebar-menu">

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=teacher_dashboard"
                            class="sidebar-menu-link <?= sidebarIsActive([
                                'teacher_dashboard',
                                'mark_attendance_form',
                                'enter_scores_form',
                                ''
                            ]) ?>"
                        >

                            <span class="sidebar-menu-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Dashboard
                            </span>

                        </a>

                    </li>

                </ul>

            </div>


        <?php elseif ($currentRole === 'student'): ?>


            <!-- =================================================
                 STUDENT
                 ================================================= -->

            <div class="sidebar-section">

                <span class="sidebar-section-label">
                    Student Portal
                </span>

                <ul class="sidebar-menu">

                    <!-- Student Information -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=student_information"
                            class="sidebar-menu-link <?= sidebarIsActive([
                                'student_information',
                                'student_dashboard',
                                ''
                            ]) ?>"
                        >

                            <span class="sidebar-menu-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1" />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Student Information
                            </span>

                        </a>

                    </li>

                    <!-- Report Card -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=student_report_card"
                            class="sidebar-menu-link <?= sidebarIsActive('student_report_card') ?>"
                        >

                            <span class="sidebar-menu-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M9 3h6a1 1 0 0 1 1 1v1h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h1V4a1 1 0 0 1 1-1z" />
                                    <path d="M9 3.5h6" />
                                    <path d="M9 12h6" />
                                    <path d="M9 16h4" />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Report Card
                            </span>

                        </a>

                    </li>


                    <!-- Attendance -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=student_attendance"
                            class="sidebar-menu-link <?= sidebarIsActive('student_attendance') ?>"
                        >

                            <span class="sidebar-menu-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="4" width="18" height="17" rx="2" />
                                    <path d="M3 9h18" />
                                    <path d="M8 2v4" />
                                    <path d="M16 2v4" />
                                    <path d="M9 15l2 2 4-4" />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Attendance
                            </span>

                        </a>

                    </li>


                    <!-- Announcements -->

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=student_announcements"
                            class="sidebar-menu-link <?= sidebarIsActive('student_announcements') ?>"
                        >

                            <span class="sidebar-menu-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                                    <path d="M13.7 21a2 2 0 0 1-3.4 0" />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Announcements
                            </span>

                        </a>

                    </li>

                </ul>

            </div>


        <?php elseif ($currentRole === 'parent'): ?>


            <!-- =================================================
                 PARENT
                 ================================================= -->

            <div class="sidebar-section">

                <span class="sidebar-section-label">
                    Parent Portal
                </span>

                <ul class="sidebar-menu">

                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=parent_dashboard"
                            class="sidebar-menu-link <?= sidebarIsActive([
                                'parent_dashboard',
                                'parent_child_detail',
                                ''
                            ]) ?>"
                        >

                            <span class="sidebar-menu-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                Dashboard
                            </span>

                        </a>

                    </li>


                    <li class="sidebar-menu-item">

                        <a
                            href="<?= BASE_URL ?>/index.php?action=parent_dashboard"
                            class="sidebar-menu-link"
                        >

                            <span class="sidebar-menu-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M3 21v-2a6 6 0 0 1 6-6h0a6 6 0 0 1 6 6v2" />
                                    <circle cx="18" cy="8" r="2.5" />
                                    <path d="M17 21v-1a4 4 0 0 1 4-4" />
                                </svg>
                            </span>

                            <span class="sidebar-menu-label">
                                My Children
                            </span>

                        </a>

                    </li>

                </ul>

            </div>


        <?php endif; ?>


        <!-- =====================================================
             ACCOUNT
             ===================================================== -->

        <div class="sidebar-section sidebar-account-section">

            <span class="sidebar-section-label">
                Account
            </span>


            <ul class="sidebar-menu">


                <!-- Change Password -->

                <li class="sidebar-menu-item">

                    <a
                        href="<?= BASE_URL ?>/index.php?action=change_password_form"
                        class="sidebar-menu-link <?= sidebarIsActive('change_password_form') ?>"
                    >

                        <span
                            class="sidebar-menu-icon"
                            aria-hidden="true"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect
                                    x="4"
                                    y="10"
                                    width="16"
                                    height="10"
                                    rx="2"
                                />
                                <path
                                    d="M8 10V7a4 4 0 0 1 8 0v3"
                                />
                                <circle
                                    cx="12"
                                    cy="15"
                                    r="1"
                                />
                            </svg>
                        </span>

                        <span class="sidebar-menu-label">
                            Change Password
                        </span>

                    </a>

                </li>


                <!-- Logout -->

                <li class="sidebar-menu-item sidebar-logout-item">

                    <a
                        href="<?= BASE_URL ?>/index.php?action=logout"
                        class="sidebar-menu-link sidebar-logout-link"
                    >

                        <span
                            class="sidebar-menu-icon"
                            aria-hidden="true"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    d="M10 17l5-5-5-5"
                                />
                                <path
                                    d="M15 12H3"
                                />
                                <path
                                    d="M21 19V5a2 2 0 0 0-2-2h-6"
                                />
                            </svg>
                        </span>

                        <span class="sidebar-menu-label">
                            Logout
                        </span>

                    </a>

                </li>

            </ul>

        </div>

    </nav>


    <!-- =====================================================
         SIDEBAR FOOTER
         ===================================================== -->

    <div class="sidebar-footer">

        <span>
            School Management System
        </span>

        <small>
            <?= date('Y') ?>
        </small>

    </div>

</aside>