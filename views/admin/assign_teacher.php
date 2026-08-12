<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Teacher - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Assign Teacher</h1>
            <p class="subtitle">Assign a teacher to teach a subject in a class</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
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
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select id="class_id" name="class_id" required>
                            <option value="">-- Select a class --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject_id">Subject</label>
                        <select id="subject_id" name="subject_id" required>
                            <option value="">-- Select a subject --</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="teacher_id">Teacher</label>
                        <select id="teacher_id" name="teacher_id" required>
                            <option value="">-- Select a teacher --</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Assign Teacher</button>
                    <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn-cancel">Cancel</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>