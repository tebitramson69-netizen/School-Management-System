<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

$pageTitle = 'Assign Teacher';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Assign Teacher</h1>
        <p>Assign a teacher to teach a subject in a class.</p>
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

        <?php if (empty($teachers)): ?>

            <div class="alert alert-error">
                No teachers exist yet. Please add a teacher first.
            </div>

        <?php else: ?>

            <form method="POST" action="<?= BASE_URL ?>/index.php?action=assign_teacher">
                <?= Security::csrfField() ?>

                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" required>
                        <option value="">-- Select a class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= (int) $class['id'] ?>"><?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject_id">Subject</label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">-- Select a subject --</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= (int) $subject['id'] ?>"><?= htmlspecialchars($subject['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="teacher_id">Teacher</label>
                    <select id="teacher_id" name="teacher_id" required>
                        <option value="">-- Select a teacher --</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= (int) $teacher['id'] ?>"><?= htmlspecialchars($teacher['full_name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Assign Teacher</button>
                    <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

        <?php endif; ?>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
