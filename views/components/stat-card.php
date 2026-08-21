<?php
/*
 * Expected variables:
 *
 * $label
 * $value
 * $meta
 * $icon
 * $variant
 */

$label =
    $label
    ?? 'Statistic';

$value =
    $value
    ?? '0';

$meta =
    $meta
    ?? '';

$icon =
    $icon
    ?? '•';

$variant =
    $variant
    ?? '';
?>

<div class="premium-stat-card <?= htmlspecialchars($variant) ?>">

    <div class="premium-stat-card-top">

        <span class="premium-stat-card-label">
            <?= htmlspecialchars($label) ?>
        </span>

        <span
            class="premium-stat-card-icon"
            aria-hidden="true"
        >
            <?= htmlspecialchars($icon) ?>
        </span>

    </div>


    <div class="premium-stat-card-value">
        <?= htmlspecialchars((string) $value) ?>
    </div>


    <?php if ($meta !== ''): ?>

        <div class="premium-stat-card-meta">
            <?= htmlspecialchars($meta) ?>
        </div>

    <?php endif; ?>

</div>