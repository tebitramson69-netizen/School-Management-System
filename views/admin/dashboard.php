<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - School Management System</title>

    <!-- Bootstrap 5.3.3 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- School Management System Theme -->
    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/auth.css"
    >

    <!-- Application JavaScript -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        defer
    ></script>

    <script
        src="<?= BASE_URL ?>/js/app.js"
        defer
    ></script>
</head>

<body>

<div class="dashboard">

    <!-- =====================================================
         HEADER
         ===================================================== -->
    <header class="dashboard-header">

        <div class="d-flex align-items-center gap-3">

            <!-- Temporary school logo -->
            <a
                href="<?= BASE_URL ?>/index.php"
                class="school-logo"
                aria-label="School logo"
            >
                SMS
            </a>

            <div>
                <h1>Admin Dashboard</h1>
                <p class="mb-0">School Management System</p>
            </div>

        </div>

        <a
            href="<?= BASE_URL ?>/index.php?action=logout"
            class="btn btn-danger btn-logout"
        >
            Logout
        </a>

    </header>


    <!-- =====================================================
         MAIN CONTENT
         ===================================================== -->
    <main class="dashboard-content">

        <!-- Success message -->
        <?php if ($success): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <!-- =================================================
             PAGE INTRO
             ================================================= -->
        <section class="page-header mb-4">

            <div>
                <h2>Administration</h2>

                <p class="text-muted mb-0">
                    Manage teachers, students, parents, classes,
                    assignments and school announcements.
                </p>
            </div>

        </section>


        <!-- =================================================
             QUICK ACTIONS
             ================================================= -->
        <section>

            <div class="dashboard-actions">

                <a
                    href="<?= BASE_URL ?>/index.php?action=create_teacher_form"
                    class="btn-card"
                >
                    <span class="btn-card-icon">+</span>

                    <span>
                        <strong>Add Teacher</strong>
                        <small>Create a teacher account</small>
                    </span>
                </a>


                <a
                    href="<?= BASE_URL ?>/index.php?action=create_student_form"
                    class="btn-card"
                >
                    <span class="btn-card-icon">+</span>

                    <span>
                        <strong>Add Student</strong>
                        <small>Register a new student</small>
                    </span>
                </a>


                <a
                    href="<?= BASE_URL ?>/index.php?action=create_parent_form"
                    class="btn-card"
                >
                    <span class="btn-card-icon">+</span>

                    <span>
                        <strong>Add Parent</strong>
                        <small>Create a parent account</small>
                    </span>
                </a>


                <a
                    href="<?= BASE_URL ?>/index.php?action=assign_teacher_form"
                    class="btn-card"
                >
                    <span class="btn-card-icon">↗</span>

                    <span>
                        <strong>Assign Teacher</strong>
                        <small>Class and subject assignment</small>
                    </span>
                </a>


                <a
                    href="<?= BASE_URL ?>/index.php?action=view_teachers"
                    class="btn-card"
                >
                    <span class="btn-card-icon">T</span>

                    <span>
                        <strong>Teachers & Assignments</strong>
                        <small>View teaching staff</small>
                    </span>
                </a>


                <a
                    href="<?= BASE_URL ?>/index.php?action=view_classes"
                    class="btn-card"
                >
                    <span class="btn-card-icon">C</span>

                    <span>
                        <strong>Classes & Lists</strong>
                        <small>Manage classes and students</small>
                    </span>
                </a>


                <a
                    href="<?= BASE_URL ?>/index.php?action=post_announcement_form"
                    class="btn-card"
                >
                    <span class="btn-card-icon">+</span>

                    <span>
                        <strong>Post Announcement</strong>
                        <small>Send a school announcement</small>
                    </span>
                </a>


                <a
                    href="<?= BASE_URL ?>/index.php?action=view_announcements"
                    class="btn-card"
                >
                    <span class="btn-card-icon">A</span>

                    <span>
                        <strong>Announcements</strong>
                        <small>View all school announcements</small>
                    </span>
                </a>

            </div>

        </section>

    </main>

</div>

</body>
</html>