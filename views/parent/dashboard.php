<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Parent Dashboard';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">PARENT PORTAL</span>
        <h1>Welcome, <?= htmlspecialchars($parent['full_name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p>Select a child to view their academic profile, attendance and results.</p>
    </div>
</section>

<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Your Children</h2>
        </div>
    </div>

    <?php if (empty($children)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No children linked</h3>
            <p>No children are linked to your account yet. Please contact the administrator.</p>
        </div>

    <?php else: ?>

        <div class="dashboard-quick-actions">
            <?php foreach ($children as $child): ?>
                <a
                    href="<?= BASE_URL ?>/index.php?action=parent_child_detail&student_id=<?= (int) $child['id'] ?>"
                    class="dashboard-action"
                >
                    <span class="dashboard-action-icon">👤</span>
                    <span class="dashboard-action-content">
                        <strong><?= htmlspecialchars($child['full_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <small>View academic profile</small>
                    </span>
                    <span class="dashboard-action-arrow">→</span>
                </a>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
