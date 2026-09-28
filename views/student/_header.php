<?php

declare(strict_types=1);

/**
 * Student page header partial — the "Welcome, ..." block.
 *
 * Shared across the split student pages. Expects the including
 * view to have computed $studentName; falls back gracefully so
 * it never emits an undefined-variable notice.
 */

$studentName = $studentName ?? 'Student';

?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">STUDENT PORTAL</span>
        <h1>Welcome, <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?></h1>
        <p>
            View your academic information, attendance, scores,
            report card and school announcements.
        </p>
    </div>
</section>
