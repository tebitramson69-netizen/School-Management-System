<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - School Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Add Student</h1>
            <p class="subtitle">Create a new student account</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/index.php?action=create_student">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-suffix-group">
                        <input type="text" id="username" name="username"
                               value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
                        <span class="input-suffix"><?= SCHOOL_EMAIL_DOMAIN ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob"
                           value="<?= htmlspecialchars($old['dob'] ?? '') ?>" required>
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
                            <?php $selected = (isset($old['class_id']) && (int)$old['class_id'] === (int)$class['id']) ? 'selected' : ''; ?>
                            <option value="<?= $class['id'] ?>" <?= $selected ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Temporary Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <button type="submit" class="btn-submit">Create Student Account</button>
                <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>