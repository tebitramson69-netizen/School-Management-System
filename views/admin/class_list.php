<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = htmlspecialchars($class['name'] ?? 'Class', ENT_QUOTES, 'UTF-8') . ' — Class List';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1><?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?> — Class List</h1>
        <p><?= count($students) ?> student<?= count($students) === 1 ? '' : 's' ?> enrolled.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/index.php?action=view_classes" class="btn btn-secondary">
            ← Back to Classes
        </a>
    </div>
</section>

<section class="dashboard-section">

    <?php if (empty($students)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No students enrolled</h3>
            <p>No students are enrolled in this class yet.</p>
        </div>

    <?php else: ?>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Student Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $serialNumber = 1; ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= $serialNumber++ ?></td>
                            <td><?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
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
