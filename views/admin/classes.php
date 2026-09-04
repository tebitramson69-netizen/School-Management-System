<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Classes';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Classes</h1>
        <p>All classes configured in the school.</p>
    </div>
</section>

<section class="dashboard-section">

    <?php if (empty($classes)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No classes</h3>
            <p>No classes have been configured yet.</p>
        </div>

    <?php else: ?>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Level</th>
                        <th>Option</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($class['level'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($class['class_option'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a class="btn btn-sm btn-primary"
                                   href="<?= BASE_URL ?>/index.php?action=view_class_list&class_id=<?= (int) $class['id'] ?>">
                                    View Class List
                                </a>
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
