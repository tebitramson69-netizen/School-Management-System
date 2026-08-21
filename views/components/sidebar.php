<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentRole = $_SESSION['role'] ?? '';
$currentAction = $_GET['action'] ?? '';

$schoolName = $school['school_name'] ?? 'School Management System';
$schoolMotto = $school['motto'] ?? null;
$logoPath = $school['logo_path'] ?? null;

$schoolInitials = 'SMS';

if (!empty($schoolName)) {
    $words = preg_split(
        '/\s+/',
        trim($schoolName)
    );

    if (count($words) >= 2) {
        $schoolInitials =
            strtoupper(
                substr($words[0], 0, 1) .
                substr($words[1], 0, 1)
            );
    } else {
        $schoolInitials =
            strtoupper(
                substr($schoolName, 0, 3)
            );
    }
}
?>

<aside class="app-sidebar" id="appSidebar">

    <!-- School identity -->
    <div class="sidebar-brand">

        <div class="school-identity-logo">

            <?php if (!empty($logoPath)): ?>

                <img
                    src="<?= BASE_URL ?>/<?= htmlspecialchars($logoPath) ?>"
                    alt="<?= htmlspecialchars($schoolName) ?> logo"
                >

            <?php else: ?>

                <?= htmlspecialchars($schoolInitials) ?>

            <?php endif; ?>

        </div>

        <div class="school-identity-text">

            <p class="school-identity-name">
                <?= htmlspecialchars($schoolName) ?>
            </p>

            <?php if (!empty($schoolMotto)): ?>

                <p class="school-identity-motto">
                    <?= htmlspecialchars($schoolMotto) ?>
                </p>

            <?php endif; ?>

        </div>

    </div>


    <!-- Logged-in user -->
    <div class="sidebar-user">

        <div class="sidebar-user-avatar">
            <?= strtoupper(
                substr(
                    $_SESSION['email'] ?? 'U',
                    0,
                    1
                )
            ) ?>
        </div>

        <div class="sidebar-user-name">

            <strong>
                <?= htmlspecialchars(
                    $_SESSION['email'] ?? 'User'
                ) ?>
            </strong>

            <span class="sidebar-user-role">
                <?= htmlspecialchars(
                    ucfirst($currentRole)
                ) ?>
            </span>

        </div>

    </div>


    <!-- Navigation -->
    <nav
        class="sidebar-navigation"
        aria-label="Main navigation"
    >

        <?php if ($currentRole === 'admin'): ?>

            <p class="sidebar-navigation-section">
                Administration
            </p>

            <ul>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=admin_dashboard"
                        class="<?= $currentAction === 'admin_dashboard' || $currentAction === '' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            ▦
                        </span>

                        <span class="sidebar-navigation-label">
                            Dashboard
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=create_student_form"
                        class="<?= $currentAction === 'create_student_form' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            +
                        </span>

                        <span class="sidebar-navigation-label">
                            Students
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=create_teacher_form"
                        class="<?= $currentAction === 'create_teacher_form' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            +
                        </span>

                        <span class="sidebar-navigation-label">
                            Teachers
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=create_parent_form"
                        class="<?= $currentAction === 'create_parent_form' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            +
                        </span>

                        <span class="sidebar-navigation-label">
                            Parents
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=view_classes"
                        class="<?= $currentAction === 'view_classes' || $currentAction === 'view_class_list' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            C
                        </span>

                        <span class="sidebar-navigation-label">
                            Classes
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=assign_teacher_form"
                        class="<?= $currentAction === 'assign_teacher_form' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            ↗
                        </span>

                        <span class="sidebar-navigation-label">
                            Assign Teachers
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=view_teachers"
                        class="<?= $currentAction === 'view_teachers' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            T
                        </span>

                        <span class="sidebar-navigation-label">
                            Teacher Assignments
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=post_announcement_form"
                        class="<?= $currentAction === 'post_announcement_form' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            +
                        </span>

                        <span class="sidebar-navigation-label">
                            Post Announcement
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=view_announcements"
                        class="<?= $currentAction === 'view_announcements' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            A
                        </span>

                        <span class="sidebar-navigation-label">
                            Announcements
                        </span>
                    </a>
                </li>

            </ul>

        <?php elseif ($currentRole === 'teacher'): ?>

            <p class="sidebar-navigation-section">
                Teaching
            </p>

            <ul>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=teacher_dashboard"
                        class="<?= $currentAction === 'teacher_dashboard' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            ▦
                        </span>

                        <span class="sidebar-navigation-label">
                            Dashboard
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=teacher_dashboard"
                    >
                        <span class="sidebar-navigation-icon">
                            C
                        </span>

                        <span class="sidebar-navigation-label">
                            My Classes
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=teacher_dashboard"
                    >
                        <span class="sidebar-navigation-icon">
                            A
                        </span>

                        <span class="sidebar-navigation-label">
                            Attendance
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=teacher_dashboard"
                    >
                        <span class="sidebar-navigation-icon">
                            S
                        </span>

                        <span class="sidebar-navigation-label">
                            Scores
                        </span>
                    </a>
                </li>

            </ul>

        <?php elseif ($currentRole === 'student'): ?>

            <p class="sidebar-navigation-section">
                Student Portal
            </p>

            <ul>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=student_dashboard"
                        class="<?= $currentAction === 'student_dashboard' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            ▦
                        </span>

                        <span class="sidebar-navigation-label">
                            Dashboard
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=student_dashboard"
                    >
                        <span class="sidebar-navigation-icon">
                            R
                        </span>

                        <span class="sidebar-navigation-label">
                            Results
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=student_dashboard"
                    >
                        <span class="sidebar-navigation-icon">
                            A
                        </span>

                        <span class="sidebar-navigation-label">
                            Attendance
                        </span>
                    </a>
                </li>

            </ul>

        <?php elseif ($currentRole === 'parent'): ?>

            <p class="sidebar-navigation-section">
                Parent Portal
            </p>

            <ul>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=parent_dashboard"
                        class="<?= $currentAction === 'parent_dashboard' ? 'active' : '' ?>"
                    >
                        <span class="sidebar-navigation-icon">
                            ▦
                        </span>

                        <span class="sidebar-navigation-label">
                            Dashboard
                        </span>
                    </a>
                </li>

                <li>
                    <a
                        href="<?= BASE_URL ?>/index.php?action=parent_dashboard"
                    >
                        <span class="sidebar-navigation-icon">
                            C
                        </span>

                        <span class="sidebar-navigation-label">
                            My Children
                        </span>
                    </a>
                </li>

            </ul>

        <?php endif; ?>


        <!-- Account -->
        <p class="sidebar-navigation-section">
            Account
        </p>

        <ul>

            <li>
                <a
                    href="<?= BASE_URL ?>/index.php?action=change_password_form"
                >
                    <span class="sidebar-navigation-icon">
                        *
                    </span>

                    <span class="sidebar-navigation-label">
                        Change Password
                    </span>
                </a>
            </li>

            <li>
                <a
                    href="<?= BASE_URL ?>/index.php?action=logout"
                >
                    <span class="sidebar-navigation-icon">
                        ←
                    </span>

                    <span class="sidebar-navigation-label">
                        Logout
                    </span>
                </a>
            </li>

        </ul>

    </nav>

</aside>