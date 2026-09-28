<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$student = $student ?? [];
$account = $account ?? [];
$currentClass = $currentClass ?? null;
$classes = $classes ?? [];

$currentClassId = (int) ($currentClass['id'] ?? 0);

$pageTitle = 'Edit Student';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Edit Student</h1>
        <p>Update this student's details.</p>
    </div>
</section>

<section class="dashboard-section">
    <div class="card">
      <div class="card-body">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=update_student">
            <?= Security::csrfField() ?>
            <input type="hidden" name="student_id" value="<?= (int) ($student['id'] ?? 0) ?>">

            <div class="form-group">
                <label>Email (login)</label>
                <input type="text" value="<?= htmlspecialchars($account['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?>" disabled>
                <small class="form-help">The login email cannot be changed here.</small>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" maxlength="150"
                       value="<?= htmlspecialchars($student['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob"
                       value="<?= htmlspecialchars($student['dob'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label>Gender</label>
                <?php $g = $student['gender'] ?? ''; ?>
                <label class="radio-label"><input type="radio" name="gender" value="M" <?= $g === 'M' ? 'checked' : '' ?> required> Male</label>
                <label class="radio-label"><input type="radio" name="gender" value="F" <?= $g === 'F' ? 'checked' : '' ?>> Female</label>
            </div>

            <div class="form-group">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id" required>
                    <option value="">-- Select a class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= (int) $class['id'] ?>" <?= $currentClassId === (int) $class['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Sets the class for the current academic year.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= BASE_URL ?>/index.php?action=manage_students" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
