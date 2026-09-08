<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$pageTitle = 'Add Student';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Add Student</h1>
        <p>Create a new student account.</p>
    </div>
</section>

<section class="dashboard-section">
    <div class="card">
      <div class="card-body">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=create_student">
            <?= Security::csrfField() ?>

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?= htmlspecialchars($old['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-suffix-group">
                    <input type="text" id="username" name="username"
                           value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    <span class="input-suffix"><?= htmlspecialchars(SCHOOL_EMAIL_DOMAIN, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

        

            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob"
                       value="<?= htmlspecialchars($old['dob'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label>Gender</label>
                <?php $selectedGender = $old['gender'] ?? ''; ?>
                <label class="radio-label">
                    <input type="radio" name="gender" value="M" <?= $selectedGender === 'M' ? 'checked' : '' ?> required>
                    Male
                </label>
                <label class="radio-label">
                    <input type="radio" name="gender" value="F" <?= $selectedGender === 'F' ? 'checked' : '' ?>>
                    Female
                </label>
            </div>

            <div class="form-group">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id" required>
                    <option value="">-- Select a class --</option>
                    <?php foreach ($classes as $class): ?>
                        <?php $selected = (isset($old['class_id']) && (int) $old['class_id'] === (int) $class['id']) ? 'selected' : ''; ?>
                        <option value="<?= (int) $class['id'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Temporary Password</label>
                <input type="password" id="password" name="password" required minlength="8">
                <small class="form-help">At least 8 characters, with uppercase, lowercase and a number.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Student Account</button>
                <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
