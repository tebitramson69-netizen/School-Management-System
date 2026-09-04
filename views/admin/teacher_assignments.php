<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Teachers & Assignments';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Teachers &amp; Their Assignments</h1>
        <p>Subjects and classes each teacher is responsible for.</p>
    </div>
</section>

<section class="dashboard-section">

    <?php if (empty($byTeacher)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No assignments</h3>
            <p>No teacher assignments have been made yet.</p>
        </div>

    <?php else: ?>

        <?php foreach ($byTeacher as $teacherName => $assignments): ?>

            <div class="section-heading">
                <div>
                    <h2><?= htmlspecialchars($teacherName, ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
            </div>

            <div class="table-wrapper" style="margin-bottom:1.5rem;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Subject</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?= htmlspecialchars($assignment['class_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($assignment['subject_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
