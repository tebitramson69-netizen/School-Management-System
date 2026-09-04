<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Announcements';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>All Announcements</h1>
        <p>Every announcement posted to the school or a class.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/index.php?action=post_announcement_form" class="btn btn-primary">
            + Post Announcement
        </a>
    </div>
</section>

<section class="dashboard-section">

    <?php if (empty($announcements)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No announcements</h3>
            <p>No announcements have been posted yet.</p>
        </div>

    <?php else: ?>

        <div class="announcement-list">
            <?php foreach ($announcements as $announcement): ?>
                <article class="announcement-card">
                    <div class="announcement-card-header">
                        <h3>
                            <?= htmlspecialchars($announcement['title'], ENT_QUOTES, 'UTF-8') ?>
                            <small>(<?= htmlspecialchars($announcement['class_name'] ?? 'Whole School', ENT_QUOTES, 'UTF-8') ?>)</small>
                        </h3>
                        <time><?= date('F j, Y g:i A', strtotime($announcement['created_at'])) ?></time>
                    </div>
                    <p><?= nl2br(htmlspecialchars($announcement['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
