<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

$pageTitle = 'Teacher Dashboard';

ob_start();
?>

<!-- PAGE HEADER -->
<section class="page-header">

    <div>

        <span class="page-eyebrow">
            TEACHER PORTAL
        </span>

        <h1>
            Welcome,
            <?= htmlspecialchars($teacher['full_name']) ?>
        </h1>

        <p>
            Manage your classes, attendance, scores and
            school announcements.
        </p>

    </div>

</section>


<?php if ($success): ?>

    <div class="status-message status-message-success" role="alert">
        <?= htmlspecialchars($success) ?>
    </div>

<?php endif; ?>


<!-- =================================================
     ANNOUNCEMENTS
     ================================================= -->
<section class="dashboard-section">

    <div class="section-heading">

        <div>
            <h2>Announcements</h2>
            <p>Latest information from the school administration.</p>
        </div>

    </div>


    <?php if (empty($announcements)): ?>

        <div class="empty-state">

            <div class="empty-state-icon">i</div>

            <h3>No announcements</h3>

            <p>
                There are no school announcements available
                at the moment.
            </p>

        </div>

    <?php else: ?>

        <div class="announcement-list">

            <?php foreach ($announcements as $announcement): ?>

                <article class="announcement-card">

                    <div class="announcement-card-header">

                        <h3>
                            <?= htmlspecialchars(
                                $announcement['title']
                            ) ?>
                        </h3>

                        <time>
                            <?= date(
                                'F j, Y',
                                strtotime(
                                    $announcement['created_at']
                                )
                            ) ?>
                        </time>

                    </div>

                    <p>
                        <?= nl2br(
                            htmlspecialchars(
                                $announcement['body']
                            )
                        ) ?>
                    </p>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>


<!-- =================================================
     CLASSES
     ================================================= -->
<section class="dashboard-section">

    <div class="section-heading">

        <div>
            <h2>Your Classes &amp; Subjects</h2>
            <p>
                Classes and subjects currently assigned
                to you.
            </p>
        </div>

    </div>


    <?php if (empty($assignments)): ?>

        <div class="empty-state">

            <div class="empty-state-icon">!</div>

            <h3>No assignments yet</h3>

            <p>
                You have not been assigned to any classes
                or subjects. Please contact the administrator.
            </p>

        </div>

    <?php else: ?>

        <div class="table-wrapper">

            <table class="data-table">

                <thead>

                    <tr>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach ($assignments as $assignment): ?>

                    <tr>

                        <td>
                            <strong>
                                <?= htmlspecialchars(
                                    $assignment['class_name']
                                ) ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $assignment['subject_name']
                            ) ?>
                        </td>

                        <td>

                            <div class="table-actions">

                                <a
                                    href="<?= BASE_URL ?>/index.php?action=mark_attendance_form&assignment_id=<?= (int) $assignment['id'] ?>"
                                    class="btn btn-sm btn-primary"
                                >
                                    Attendance
                                </a>

                                <a
                                    href="<?= BASE_URL ?>/index.php?action=enter_scores_form&assignment_id=<?= (int) $assignment['id'] ?>"
                                    class="btn btn-sm btn-secondary"
                                >
                                    Scores
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
