<?php

declare(strict_types=1);

/**
 * Student Announcements View — uses the shared dashboard layout.
 *
 * Business logic lives in StudentController::announcements().
 */

$student = $student ?? [];
$announcements = $announcements ?? [];

$studentName =
    $student['full_name']
    ?? $student['name']
    ?? 'Student';

$pageTitle = 'Announcements';

ob_start();
?>

<?php require __DIR__ . '/_header.php'; ?>


<!-- ANNOUNCEMENTS -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>School Announcements</h2>
        </div>
    </div>

    <?php if (!empty($announcements)): ?>

        <div class="announcement-list">
            <?php foreach ($announcements as $announcement): ?>
                <article class="announcement-card">
                    <div class="announcement-card-header">
                        <h3><?= htmlspecialchars($announcement['title'] ?? 'Announcement', ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($announcement['created_at'])): ?>
                            <time><?= date('F j, Y', strtotime($announcement['created_at'])) ?></time>
                        <?php endif; ?>
                    </div>
                    <p><?= nl2br(htmlspecialchars($announcement['body'] ?? $announcement['message'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No announcements</h3>
            <p>There are no announcements at the moment.</p>
        </div>

    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
