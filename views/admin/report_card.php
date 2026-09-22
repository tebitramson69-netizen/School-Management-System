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

$fmt = static function ($v): string {
    return $v === null || $v === ''
        ? '—'
        : number_format((float) $v, 2);
};

$pageTitle = 'Report Card — ' . $studentName;

ob_start();
?>

<style>
    .rc-toolbar { display:flex; gap:10px; margin-bottom:18px; }

    .report-card {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        color: #111;
        border: 1px solid #cfd6df;
        border-radius: 8px;
        padding: 26px 30px;
    }

    .rc-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 2px solid #0F2C4C;
        padding-bottom: 14px;
        margin-bottom: 8px;
    }
    .rc-head-side { flex: 1 1 0; text-align: center; }
    .rc-head-center { flex: 0 0 auto; max-width: 36%; text-align: center; }
    .rc-head-center img { width: 76px; height: 76px; object-fit: contain; }
    .rc-head-logo-fallback {
        width: 76px; height: 76px; border-radius: 8px; margin: 0 auto;
        background: #0F2C4C; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 1.4rem;
    }
    .rc-nation { font-size: 0.78rem; font-weight: 700; letter-spacing: .03em; color: #0F2C4C; margin: 0; }
    .rc-nation-motto { font-size: 0.72rem; font-style: italic; color: #444; margin: 2px 0; }
    .rc-star { color: #D69E2E; font-size: 0.9rem; letter-spacing: .35em; margin: 2px 0; }
    .rc-ministry { font-size: 0.68rem; color: #555; margin: 2px 0 0; }
    .rc-school { font-size: 1.3rem; font-weight: 700; margin: 6px 0 0; color: #0F2C4C; }
    .rc-motto { font-size: 0.8rem; font-style: italic; color: #555; margin: 2px 0 0; }
    .rc-title {
        text-align: center;
        font-size: 1.05rem; font-weight: 700; letter-spacing: .06em;
        margin: 14px 0 18px; color: #0F2C4C;
    }

    .rc-meta {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px 30px;
        margin-bottom: 18px;
        font-size: 0.9rem;
    }
    .rc-meta div { display: flex; gap: 8px; }
    .rc-meta .rc-label { color: #555; min-width: 108px; }
    .rc-meta .rc-value { font-weight: 600; }

    .rc-table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
    .rc-table th, .rc-table td {
        border: 1px solid #b8c0cc;
        padding: 6px 8px;
        text-align: center;
    }
    .rc-table th { background: #eef2f7; color: #0F2C4C; }
    .rc-table td.rc-subject { text-align: left; }
    .rc-table tfoot td { font-weight: 700; background: #f5f7fa; }

    .rc-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin: 18px 0;
    }
    .rc-summary .rc-box {
        border: 1px solid #cfd6df; border-radius: 6px;
        padding: 10px 12px; text-align: center;
    }
    .rc-summary .rc-box .k { font-size: 0.72rem; color: #555; text-transform: uppercase; letter-spacing: .03em; }
    .rc-summary .rc-box .v { font-size: 1.15rem; font-weight: 700; color: #0F2C4C; }
    .rc-verdict-pass { color: #16855b; }
    .rc-verdict-fail { color: #c0392b; }

    .rc-signatures {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-top: 34px;
        font-size: 0.85rem;
    }
    .rc-sign-line { border-top: 1px solid #333; margin-top: 42px; padding-top: 5px; text-align: center; color: #444; }

    @media print {
        @page { size: A4 portrait; margin: 12mm; }
        body { background: #fff !important; }
        .rc-toolbar { display: none !important; }
        .report-card { border: none; border-radius: 0; padding: 0; max-width: none; }
    }
</style>

<div class="rc-toolbar no-print">
    <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
    <a class="btn btn-secondary"
       href="<?= BASE_URL ?>/index.php?action=report_cards&class_id=<?= (int) ($class['id'] ?? 0) ?>&term_id=<?= (int) ($term['id'] ?? 0) ?>">
        ← Back
    </a>
</div>

<div class="report-card">

    <!-- BILINGUAL HEADER -->
    <div class="rc-head">

        <!-- English (left) -->
        <div class="rc-head-side" lang="en">
            <p class="rc-nation">REPUBLIC OF CAMEROON</p>
            <p class="rc-nation-motto">Peace &ndash; Work &ndash; Fatherland</p>
            <p class="rc-star">&#9733;</p>
            <p class="rc-ministry">Ministry of Secondary Education</p>
        </div>

        <!-- School (centre) -->
        <div class="rc-head-center">
            <?php if (!empty($logoPath)): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars(ltrim((string) $logoPath, '/'), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($schoolName, ENT_QUOTES, 'UTF-8') ?> logo">
            <?php else: ?>
                <div class="rc-head-logo-fallback">
                    <?= htmlspecialchars(strtoupper(substr($schoolName, 0, 2)), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <h1 class="rc-school"><?= htmlspecialchars($schoolName, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($schoolMotto !== ''): ?>
                <p class="rc-motto"><?= htmlspecialchars($schoolMotto, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($schoolAddress !== ''): ?>
                <p class="rc-motto"><?= htmlspecialchars($schoolAddress, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <!-- French (right) -->
        <div class="rc-head-side" lang="fr">
            <p class="rc-nation">R&Eacute;PUBLIQUE DU CAMEROUN</p>
            <p class="rc-nation-motto">Paix &ndash; Travail &ndash; Patrie</p>
            <p class="rc-star">&#9733;</p>
            <p class="rc-ministry">Minist&egrave;re des Enseignements Secondaires</p>
        </div>

    </div>

    <div class="rc-title">
        REPORT CARD
    </div>

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

        <!-- SUBJECT TABLE -->
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

        <!-- SUMMARY -->
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
                    <?php if ($classSize): ?><span style="font-size:.7rem;color:#555;"> / <?= (int) $classSize ?></span><?php endif; ?>
                </div>
            </div>
            <div class="rc-box">
                <div class="k">Grade</div>
                <div class="v"><?= htmlspecialchars($overallGrade['letter'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>

        <p style="font-size:0.9rem;margin:0 0 4px;">
            <strong>Remark:</strong>
            <?= htmlspecialchars($overallGrade['remark'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
        </p>
        <p style="font-size:0.9rem;margin:0;">
            <strong>Result:</strong>
            <?php if ($passed === null): ?>
                —
            <?php elseif ($passed): ?>
                <span class="rc-verdict-pass">PASS</span>
            <?php else: ?>
                <span class="rc-verdict-fail">FAIL</span>
            <?php endif; ?>
            <span style="color:#777;font-size:.8rem;">(pass mark <?= number_format((float) $passMark, 2) ?>/20)</span>
        </p>

    <?php endif; ?>

    <!-- SIGNATURES -->
    <div class="rc-signatures">
        <div><div class="rc-sign-line">Class Master</div></div>
        <div><div class="rc-sign-line">Principal</div></div>
        <div><div class="rc-sign-line">Parent / Guardian</div></div>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
