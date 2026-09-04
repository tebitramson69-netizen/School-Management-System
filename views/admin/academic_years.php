<?php

declare(strict_types=1);

/**
 * Academic Year & Period Management — uses the shared dashboard layout.
 * Presentation only; business logic stays in the controller/model.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$successMessage = $_SESSION['success_message'] ?? '';
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['success_message'], $_SESSION['old_input']);

$academicYears = is_array($academicYears ?? null) ? $academicYears : [];
$terms = is_array($terms ?? null) ? $terms : [];
$currentAcademicYear = $currentAcademicYear ?? null;
$currentTerm = $currentTerm ?? null;

if (!$currentAcademicYear) {
    foreach ($academicYears as $academicYear) {
        if ((int) ($academicYear['is_current'] ?? 0) === 1) {
            $currentAcademicYear = $academicYear;
            break;
        }
    }
}

/* Group terms by term name (Term 1 -> Seq 1, Seq 2 ...) */
$groupedTerms = [];
foreach ($terms as $term) {
    $termName = trim((string) ($term['name'] ?? 'Unknown Term'));
    if ($termName === '') {
        $termName = 'Unknown Term';
    }
    $groupedTerms[$termName][] = $term;
}

uksort($groupedTerms, static function (string $a, string $b): int {
    preg_match('/(\d+)/', $a, $aMatch);
    preg_match('/(\d+)/', $b, $bMatch);
    $aNumber = isset($aMatch[1]) ? (int) $aMatch[1] : PHP_INT_MAX;
    $bNumber = isset($bMatch[1]) ? (int) $bMatch[1] : PHP_INT_MAX;
    return $aNumber <=> $bNumber;
});

$currentPeriodLabel = 'No current period';
if ($currentTerm) {
    $currentPeriodLabel =
        ($currentTerm['name'] ?? 'Term') .
        ' — Sequence ' .
        (int) ($currentTerm['sequence_number'] ?? 0);
}

$pageTitle = 'Academic Management';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Academic Management</h1>
        <p>Manage academic years, terms and sequences for the school.</p>
    </div>
</section>

<?php if ($successMessage !== ''): ?>
    <div class="status-message status-message-success" role="alert">
        <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error" role="alert">
        <strong>Please correct the following:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>


<!-- CURRENT YEAR + PERIOD -->
<section class="dashboard-section">
    <div class="dashboard-stats-grid">

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Current Academic Year</span>
            <strong class="dashboard-stat-value" style="font-size:1.4rem;">
                <?= $currentAcademicYear
                    ? htmlspecialchars($currentAcademicYear['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8')
                    : 'Not set' ?>
            </strong>
            <span class="dashboard-stat-note">
                <?= $currentAcademicYear
                    ? 'Used for new enrollments'
                    : 'Activate a year before registering students' ?>
            </span>
        </article>

        <article class="dashboard-stat-card">
            <span class="dashboard-stat-label">Current Academic Period</span>
            <strong class="dashboard-stat-value" style="font-size:1.4rem;">
                <?= $currentTerm
                    ? htmlspecialchars($currentPeriodLabel, ENT_QUOTES, 'UTF-8')
                    : 'Not set' ?>
            </strong>
            <span class="dashboard-stat-note">
                <?= $currentTerm ? 'Current assessment context' : 'Select a term and sequence below' ?>
            </span>
        </article>

    </div>
</section>


<!-- TERMS & SEQUENCES -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Terms &amp; Sequences</h2>
            <p>Select the academic period currently in use.</p>
        </div>
    </div>

    <?php if (!$currentAcademicYear): ?>

        <div class="empty-state">
            <div class="empty-state-icon">!</div>
            <h3>No active academic year</h3>
            <p>Activate an academic year before managing its terms and sequences.</p>
        </div>

    <?php elseif (empty($terms)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No terms configured</h3>
            <p>No terms or sequences have been configured for the current academic year.</p>
        </div>

    <?php else: ?>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Term</th>
                        <th>Sequence</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupedTerms as $termName => $termSequences): ?>
                        <?php foreach ($termSequences as $term): ?>
                            <?php
                            $termId = (int) ($term['id'] ?? 0);
                            $sequenceNumber = (int) ($term['sequence_number'] ?? 0);
                            $isCurrent = (int) ($term['is_current'] ?? 0) === 1;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($termName, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>Sequence <?= $sequenceNumber ?></td>
                                <td>
                                    <?php if ($isCurrent): ?>
                                        <span class="status-badge active">Current</span>
                                    <?php else: ?>
                                        <span class="status-badge">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$isCurrent): ?>
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/index.php?action=set_current_term"
                                              style="display:inline;">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="term_id" value="<?= $termId ?>">
                                            <button type="submit" class="btn btn-sm btn-primary"
                                                onclick="return confirm('Set <?= htmlspecialchars($termName, ENT_QUOTES, 'UTF-8') ?> — Sequence <?= $sequenceNumber ?> as the current academic period?');">
                                                Set as Current
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span>Active period</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</section>


<!-- CREATE ACADEMIC YEAR -->
<section class="dashboard-section">
    <div class="card">
      <div class="card-body">

        <div class="section-heading">
            <div>
                <h2>Add Academic Year</h2>
                <p>Example: <strong>2027/2028</strong></p>
            </div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=create_academic_year">
            <?= Security::csrfField() ?>

            <div class="form-group">
                <label for="name">Academic Year</label>
                <input type="text" id="name" name="name"
                       placeholder="2027/2028"
                       value="<?= htmlspecialchars($oldInput['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       maxlength="9" pattern="\d{4}/\d{4}" required>
                <small class="form-help">Use the format YYYY/YYYY. For example: 2027/2028.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Academic Year</button>
            </div>
        </form>

      </div>
    </div>
</section>


<!-- ACADEMIC YEAR HISTORY -->
<section class="dashboard-section">

    <div class="section-heading">
        <div>
            <h2>Academic Years</h2>
            <p>Current and historical academic sessions.</p>
        </div>
    </div>

    <?php if (empty($academicYears)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No academic years</h3>
            <p>No academic year has been created yet.</p>
        </div>

    <?php else: ?>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Academic Year</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $serialNumber = 1; ?>
                    <?php foreach ($academicYears as $academicYear): ?>
                        <?php
                        $academicYearId = (int) ($academicYear['id'] ?? 0);
                        $isCurrent = (int) ($academicYear['is_current'] ?? 0) === 1;
                        ?>
                        <tr>
                            <td><?= $serialNumber++ ?></td>
                            <td><strong><?= htmlspecialchars($academicYear['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td>
                                <?php if ($isCurrent): ?>
                                    <span class="status-badge active">Active</span>
                                <?php else: ?>
                                    <span class="status-badge">Historical</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$isCurrent): ?>
                                    <form method="POST"
                                          action="<?= BASE_URL ?>/index.php?action=activate_academic_year"
                                          style="display:inline;">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="academic_year_id" value="<?= $academicYearId ?>">
                                        <button type="submit" class="btn btn-sm btn-primary"
                                            onclick="return confirm('Activate <?= htmlspecialchars($academicYear['name'] ?? 'this academic year', ENT_QUOTES, 'UTF-8') ?> as the current academic year?');">
                                            Set as Current
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span>Current academic year</span>
                                <?php endif; ?>
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
