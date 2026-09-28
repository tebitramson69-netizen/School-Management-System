<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$student = $student ?? [];
$class = $class ?? [];
$term = $term ?? [];
$record = $record ?? null;
$conductOptions = $conductOptions ?? [];
$distinctionOptions = $distinctionOptions ?? [];

$studentId = (int) ($student['id'] ?? 0);
$classId = (int) ($class['id'] ?? 0);
$termId = (int) ($term['id'] ?? 0);

$v = static function (string $key, $default = '') use ($record) {
    return $record[$key] ?? $default;
};

$pageTitle = 'Report Details';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Report Details</h1>
        <p>
            <?= htmlspecialchars($student['full_name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?>
            — <?= htmlspecialchars($class['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            · <?= htmlspecialchars($term['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>
</section>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error" role="alert">
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<section class="dashboard-section">
    <div class="card">
      <div class="card-body">

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=save_report_card_details">
            <?= Security::csrfField() ?>
            <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <input type="hidden" name="class_id" value="<?= $classId ?>">
            <input type="hidden" name="term_id" value="<?= $termId ?>">

            <div class="form-group">
                <label for="absence_justified">Justified absences (hours)</label>
                <input type="number" id="absence_justified" name="absence_justified" min="0" step="1"
                       value="<?= (int) $v('absence_justified', 0) ?>">
            </div>

            <div class="form-group">
                <label for="absence_unjustified">Unjustified absences (hours)</label>
                <input type="number" id="absence_unjustified" name="absence_unjustified" min="0" step="1"
                       value="<?= (int) $v('absence_unjustified', 0) ?>">
            </div>

            <div class="form-group">
                <label for="times_late">Times late</label>
                <input type="number" id="times_late" name="times_late" min="0" step="1"
                       value="<?= (int) $v('times_late', 0) ?>">
            </div>

            <div class="form-group">
                <label for="conduct">Conduct</label>
                <select id="conduct" name="conduct">
                    <option value="">-- Not set --</option>
                    <?php foreach ($conductOptions as $opt): ?>
                        <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= (string) $v('conduct') === $opt ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="sanctions">Sanctions / discipline</label>
                <input type="text" id="sanctions" name="sanctions" maxlength="255"
                       value="<?= htmlspecialchars((string) $v('sanctions'), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="distinction">Distinction / decision</label>
                <select id="distinction" name="distinction">
                    <option value="">-- None --</option>
                    <?php foreach ($distinctionOptions as $opt): ?>
                        <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= (string) $v('distinction') === $opt ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="class_master_remark">Class Master's remark</label>
                <input type="text" id="class_master_remark" name="class_master_remark" maxlength="255"
                       value="<?= htmlspecialchars((string) $v('class_master_remark'), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="principal_remark">Principal's remark</label>
                <input type="text" id="principal_remark" name="principal_remark" maxlength="255"
                       value="<?= htmlspecialchars((string) $v('principal_remark'), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Details</button>
                <a class="btn btn-secondary"
                   href="<?= BASE_URL ?>/index.php?action=report_card&student_id=<?= $studentId ?>&class_id=<?= $classId ?>&term_id=<?= $termId ?>">
                    Back to Report Card
                </a>
            </div>
        </form>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
