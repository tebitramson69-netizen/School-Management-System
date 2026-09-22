<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$student = $student ?? [];
$class = $class ?? [];
$term = $term ?? [];
$school = $school ?? [];
$academicYear = $academicYear ?? [];
$rows = $rows ?? [];
$totalCoefficient = $totalCoefficient ?? 0;
$totalWeighted = $totalWeighted ?? 0.0;
$overallAverage = $overallAverage ?? null;
$overallGrade = $overallGrade ?? null;
$classPosition = $classPosition ?? null;
$classSize = $classSize ?? null;
$classAverage = $classAverage ?? null;
$positionLabel = $positionLabel ?? null;
$passMark = $passMark ?? 10;
$passed = $passed ?? null;
$termReport = $termReport ?? null;

$studentName = $student['full_name'] ?? 'Student';
$gender = ($student['gender'] ?? '') === 'M'
    ? 'Male'
    : (($student['gender'] ?? '') === 'F' ? 'Female' : '—');

$dobRaw = $student['dob'] ?? '';
$dob = ($dobRaw && strtotime((string) $dobRaw))
    ? date('j M Y', strtotime((string) $dobRaw))
    : '—';

$className = $class['name'] ?? '—';
$termName = $term['name'] ?? '—';
$yearName = $academicYear['name'] ?? ($term['academic_year_name'] ?? '—');

$schoolName = $school['school_name'] ?? 'School Management System';
$schoolMotto = $school['motto'] ?? '';
$schoolAddress = $school['address'] ?? '';
$logoPath = $school['logo_path'] ?? '';

$studentId = (int) ($student['id'] ?? 0);
$classId = (int) ($class['id'] ?? 0);
$termId = (int) ($term['id'] ?? 0);

// Attendance / conduct (class-master entered).
$absJustified = (int) ($termReport['absence_justified'] ?? 0);
$absUnjustified = (int) ($termReport['absence_unjustified'] ?? 0);
$absTotal = $absJustified + $absUnjustified;
$timesLate = (int) ($termReport['times_late'] ?? 0);
$conduct = trim((string) ($termReport['conduct'] ?? ''));
$sanctions = trim((string) ($termReport['sanctions'] ?? ''));
$distinction = trim((string) ($termReport['distinction'] ?? ''));
$cmRemark = trim((string) ($termReport['class_master_remark'] ?? ''));
$prRemark = trim((string) ($termReport['principal_remark'] ?? ''));

$fmt = static function ($v): string {
    return $v === null || $v === ''
        ? '—'
        : number_format((float) $v, 2);
};
$dash = static function (string $v): string {
    return $v !== '' ? htmlspecialchars($v, ENT_QUOTES, 'UTF-8') : '—';
};

$pageTitle = 'Report Card — ' . $studentName;

ob_start();
?>

<style>
    .rc-toolbar { display:flex; gap:10px; margin-bottom:18px; flex-wrap:wrap; }

    .report-card {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        color: #1a1a1a;
        border: 1px solid #cfd6df;
        border-radius: 10px;
        padding: 30px 34px;
        font-family: "Segoe UI", Arial, sans-serif;
    }

    /* Header */
    .rc-head {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 16px; padding-bottom: 14px; margin-bottom: 4px;
        border-bottom: 3px double #0F2C4C;
    }
    .rc-head-side { flex: 1 1 0; text-align: center; }
    .rc-head-center { flex: 0 0 auto; max-width: 36%; text-align: center; }
    .rc-head-center img { width: 78px; height: 78px; object-fit: contain; }
    .rc-head-logo-fallback {
        width: 78px; height: 78px; border-radius: 10px; margin: 0 auto;
        background: #0F2C4C; color: #fff; display: flex;
        align-items: center; justify-content: center; font-weight: 700; font-size: 1.4rem;
    }
    .rc-nation { font-size: 0.8rem; font-weight: 700; letter-spacing: .03em; color: #0F2C4C; margin: 0; }
    .rc-nation-motto { font-size: 0.72rem; font-style: italic; color: #444; margin: 2px 0; }
    .rc-star { color: #D69E2E; font-size: 0.9rem; letter-spacing: .35em; margin: 2px 0; }
    .rc-ministry { font-size: 0.68rem; color: #555; margin: 2px 0 0; }
    .rc-school { font-size: 1.35rem; font-weight: 800; margin: 6px 0 0; color: #0F2C4C; }
    .rc-motto { font-size: 0.8rem; font-style: italic; color: #555; margin: 2px 0 0; }

    .rc-title {
        text-align: center; font-size: 1.05rem; font-weight: 700; letter-spacing: .1em;
        color: #fff; background: #0F2C4C; border-radius: 5px;
        padding: 7px 0; margin: 14px 0 20px;
    }

    /* Student meta */
    .rc-meta {
        display: grid; grid-template-columns: repeat(2, 1fr);
        gap: 7px 30px; margin-bottom: 20px; font-size: 0.9rem;
        background: #f6f8fb; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px;
    }
    .rc-meta div { display: flex; gap: 8px; }
    .rc-meta .rc-label { color: #556; min-width: 110px; }
    .rc-meta .rc-value { font-weight: 700; }

    /* Section title */
    .rc-section-title {
        font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        color: #0F2C4C; margin: 22px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #d7dee7;
    }

    /* Subject table */
    .rc-table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
    .rc-table th, .rc-table td { border: 1px solid #c3ccd8; padding: 7px 8px; text-align: center; }
    .rc-table thead th { background: #0F2C4C; color: #fff; font-weight: 600; }
    .rc-table tbody tr:nth-child(even) { background: #f6f8fb; }
    .rc-table td.rc-subject { text-align: left; }
    .rc-table tfoot td { font-weight: 700; background: #eef2f7; color: #0F2C4C; }

    /* Summary tiles */
    .rc-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 16px 0; }
    .rc-summary .rc-box {
        border: 1px solid #dfe5ec; border-top: 3px solid #0F2C4C; border-radius: 6px;
        padding: 10px 12px; text-align: center; background: #fff;
    }
    .rc-summary .rc-box .k { font-size: 0.68rem; color: #667; text-transform: uppercase; letter-spacing: .04em; }
    .rc-summary .rc-box .v { font-size: 1.2rem; font-weight: 800; color: #0F2C4C; margin-top: 3px; }
    .rc-verdict-pass { color: #16855b; font-weight: 800; }
    .rc-verdict-fail { color: #c0392b; font-weight: 800; }

    /* Attendance & conduct */
    .rc-ac { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .rc-ac table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
    .rc-ac th, .rc-ac td { border: 1px solid #c3ccd8; padding: 6px 9px; text-align: left; }
    .rc-ac th { background: #f0f3f8; color: #0F2C4C; font-weight: 600; width: 55%; }
    .rc-distinction {
        display: inline-block; margin-top: 6px; padding: 3px 10px; border-radius: 12px;
        background: #fff6e5; color: #8a5a00; border: 1px solid #e6c98a; font-size: 0.8rem; font-weight: 700;
    }

    .rc-remarks { margin-top: 6px; font-size: 0.88rem; }
    .rc-remarks p { margin: 6px 0; }
    .rc-remarks .rc-rk-label { color: #556; font-weight: 700; }

    .rc-result-line { font-size: 0.95rem; margin: 14px 0 0; }

    .rc-signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 40px; font-size: 0.85rem; }
    .rc-sign-line { border-top: 1px solid #333; margin-top: 44px; padding-top: 5px; text-align: center; color: #444; }

    .rc-footer { margin-top: 24px; text-align: center; font-size: 0.7rem; color: #8a94a2; }

    @media print {
        @page { size: A4 portrait; margin: 11mm; }
        body { background: #fff !important; }
        .rc-toolbar { display: none !important; }
        .report-card { border: none; border-radius: 0; padding: 0; max-width: none; }
        .rc-table thead th, .rc-title { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

<div class="rc-toolbar no-print">
    <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
    <a class="btn btn-secondary"
       href="<?= BASE_URL ?>/index.php?action=report_card_details_form&student_id=<?= $studentId ?>&class_id=<?= $classId ?>&term_id=<?= $termId ?>">
        Edit details
    </a>
    <a class="btn btn-secondary"
       href="<?= BASE_URL ?>/index.php?action=report_cards&class_id=<?= $classId ?>&term_id=<?= $termId ?>">
        ← Back
    </a>
</div>

<div class="report-card">

    <!-- BILINGUAL HEADER -->
    <div class="rc-head">
        <div class="rc-head-side" lang="en">
            <p class="rc-nation">REPUBLIC OF CAMEROON</p>
            <p class="rc-nation-motto">Peace &ndash; Work &ndash; Fatherland</p>
            <p class="rc-star">&#9733;</p>
            <p class="rc-ministry">Ministry of Secondary Education</p>
        </div>

        <div class="rc-head-center">
            <?php if (!empty($logoPath)): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars(ltrim((string) $logoPath, '/'), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($schoolName, ENT_QUOTES, 'UTF-8') ?> logo">
            <?php else: ?>
                <div class="rc-head-logo-fallback"><?= htmlspecialchars(strtoupper(substr($schoolName, 0, 2)), ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <h1 class="rc-school"><?= htmlspecialchars($schoolName, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($schoolMotto !== ''): ?>
                <p class="rc-motto"><?= htmlspecialchars($schoolMotto, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($schoolAddress !== ''): ?>
                <p class="rc-motto"><?= htmlspecialchars($schoolAddress, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <div class="rc-head-side" lang="fr">
            <p class="rc-nation">R&Eacute;PUBLIQUE DU CAMEROUN</p>
            <p class="rc-nation-motto">Paix &ndash; Travail &ndash; Patrie</p>
            <p class="rc-star">&#9733;</p>
            <p class="rc-ministry">Minist&egrave;re des Enseignements Secondaires</p>
        </div>
    </div>

    <div class="rc-title">REPORT CARD</div>

    <!-- STUDENT / TERM INFO -->
    <div class="rc-meta">
        <div><span class="rc-label">Name</span><span class="rc-value"><?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?></span></div>
        <div><span class="rc-label">Class</span><span class="rc-value"><?= htmlspecialchars($className, ENT_QUOTES, 'UTF-8') ?></span></div>
        <div><span class="rc-label">Sex</span><span class="rc-value"><?= htmlspecialchars($gender, ENT_QUOTES, 'UTF-8') ?></span></div>
        <div><span class="rc-label">Date of Birth</span><span class="rc-value"><?= htmlspecialchars($dob, ENT_QUOTES, 'UTF-8') ?></span></div>
        <div><span class="rc-label">Academic Year</span><span class="rc-value"><?= htmlspecialchars((string) $yearName, ENT_QUOTES, 'UTF-8') ?></span></div>
        <div><span class="rc-label">Term</span><span class="rc-value"><?= htmlspecialchars($termName, ENT_QUOTES, 'UTF-8') ?></span></div>
    </div>

    <?php if (empty($rows)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">i</div>
            <h3>No marks recorded</h3>
            <p>No scores have been recorded for this student in <?= htmlspecialchars($termName, ENT_QUOTES, 'UTF-8') ?>.</p>
        </div>

    <?php else: ?>

        <div class="rc-section-title">Academic Performance</div>
        <table class="rc-table">
            <thead>
                <tr>
                    <th style="text-align:left">Subject</th>
                    <th>Coef.</th>
                    <th>Seq 1</th>
                    <th>Seq 2</th>
                    <th>Average /20</th>
                    <th>Avg × Coef</th>
                    <th>Grade</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="rc-subject"><?= htmlspecialchars($row['subject_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $row['coefficient'] ?></td>
                        <td><?= $fmt($row['seq1']) ?></td>
                        <td><?= $fmt($row['seq2']) ?></td>
                        <td><?= $fmt($row['average']) ?></td>
                        <td><?= $fmt($row['weighted']) ?></td>
                        <td><?= htmlspecialchars((string) $row['letter'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $row['remark'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="rc-subject">TOTAL</td>
                    <td><?= (int) $totalCoefficient ?></td>
                    <td colspan="3"></td>
                    <td><?= number_format((float) $totalWeighted, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>

        <div class="rc-summary">
            <div class="rc-box">
                <div class="k">Term Average</div>
                <div class="v"><?= $overallAverage !== null ? $fmt($overallAverage) : '—' ?></div>
            </div>
            <div class="rc-box">
                <div class="k">Class Average</div>
                <div class="v"><?= $classAverage !== null ? $fmt($classAverage) : '—' ?></div>
            </div>
            <div class="rc-box">
                <div class="k">Position</div>
                <div class="v">
                    <?= $positionLabel !== null ? htmlspecialchars($positionLabel, ENT_QUOTES, 'UTF-8') : '—' ?>
                    <?php if ($classSize): ?><span style="font-size:.7rem;color:#667;"> / <?= (int) $classSize ?></span><?php endif; ?>
                </div>
            </div>
            <div class="rc-box">
                <div class="k">Grade</div>
                <div class="v"><?= htmlspecialchars($overallGrade['letter'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>

    <?php endif; ?>

    <!-- ATTENDANCE & CONDUCT -->
    <div class="rc-section-title">Attendance &amp; Conduct</div>
    <div class="rc-ac">
        <table>
            <tr><th>Absences — Justified (hrs)</th><td><?= $absJustified ?></td></tr>
            <tr><th>Absences — Unjustified (hrs)</th><td><?= $absUnjustified ?></td></tr>
            <tr><th>Total Absences (hrs)</th><td><?= $absTotal ?></td></tr>
            <tr><th>Times Late</th><td><?= $timesLate ?></td></tr>
        </table>
        <table>
            <tr><th>Conduct</th><td><?= $dash($conduct) ?></td></tr>
            <tr><th>Sanctions / Discipline</th><td><?= $dash($sanctions) ?></td></tr>
            <tr><th>Distinction</th><td>
                <?php if ($distinction !== ''): ?>
                    <span class="rc-distinction"><?= htmlspecialchars($distinction, ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>—<?php endif; ?>
            </td></tr>
        </table>
    </div>

    <!-- REMARKS & RESULT -->
    <div class="rc-section-title">Remarks &amp; Decision</div>
    <div class="rc-remarks">
        <p><span class="rc-rk-label">Overall grade remark:</span> <?= htmlspecialchars($overallGrade['remark'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
        <p><span class="rc-rk-label">Class Master's remark:</span> <?= $dash($cmRemark) ?></p>
        <p><span class="rc-rk-label">Principal's remark:</span> <?= $dash($prRemark) ?></p>
        <p class="rc-result-line">
            <span class="rc-rk-label">Result:</span>
            <?php if ($passed === null): ?>
                —
            <?php elseif ($passed): ?>
                <span class="rc-verdict-pass">PASS</span>
            <?php else: ?>
                <span class="rc-verdict-fail">FAIL</span>
            <?php endif; ?>
            <span style="color:#889;font-size:.8rem;">(pass mark <?= number_format((float) $passMark, 2) ?>/20)</span>
        </p>
    </div>

    <!-- SIGNATURES -->
    <div class="rc-signatures">
        <div><div class="rc-sign-line">Class Master</div></div>
        <div><div class="rc-sign-line">Principal</div></div>
        <div><div class="rc-sign-line">Parent / Guardian</div></div>
    </div>

    <div class="rc-footer">
        Generated by <?= htmlspecialchars($schoolName, ENT_QUOTES, 'UTF-8') ?> · <?= date('j M Y') ?>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
