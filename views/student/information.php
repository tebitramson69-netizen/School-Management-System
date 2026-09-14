<?php

declare(strict_types=1);

/**
 * Student Information View — uses the shared dashboard layout.
 *
 * Shows profile essentials only (Name / Class / Email). Business
 * logic lives in StudentController::information() via
 * bootStudentContext().
 */

$student = $student ?? [];

$studentName =
    $student['full_name']
    ?? $student['name']
    ?? 'Student';

$studentEmail = $student['email'] ?? '';

$className = $student['class_name'] ?? $student['class'] ?? '';

$pageTitle = 'Student Information';

ob_start();
?>

<?php require __DIR__ . '/_header.php'; ?>


<!-- STUDENT INFORMATION -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Student Information</h2>
        </div>
    </div>

    <div class="dashboard-stats-grid">

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Name</span>
            <strong class="dashboard-stat-value" style="font-size:1.1rem;">
                <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>
            </strong>
        </article>



        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Class</span>
            <strong class="dashboard-stat-value" style="font-size:1.1rem;">
                <?= $className !== '' ? htmlspecialchars($className, ENT_QUOTES, 'UTF-8') : 'Not assigned' ?>
            </strong>
        </article>

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Email</span>
            <strong class="dashboard-stat-value" style="font-size:1.1rem;">
                <?= $studentEmail !== '' ? htmlspecialchars($studentEmail, ENT_QUOTES, 'UTF-8') : 'Not available' ?>
            </strong>
        </article>

    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
