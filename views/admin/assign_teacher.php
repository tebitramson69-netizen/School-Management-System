<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$classes = $classes ?? [];
$teachers = $teachers ?? [];
$classSubjectsMap = $classSubjectsMap ?? [];

$oldClassId = (int) ($old['class_id'] ?? 0);
$oldSubjectId = (int) ($old['subject_id'] ?? 0);
$oldTeacherId = (int) ($old['teacher_id'] ?? 0);

$pageTitle = 'Assign Teacher';

ob_start();
?>

<section class="page-header">
    <div>
        <span class="page-eyebrow">ADMINISTRATION</span>
        <h1>Assign Teacher</h1>
        <p>Assign a teacher to teach a subject in a class. Only subjects the class offers can be chosen.</p>
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
                            <option value="<?= (int) $class['id'] ?>" <?= $oldClassId === (int) $class['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject_id">Subject</label>
                    <select id="subject_id" name="subject_id" required disabled
                            data-preselect="<?= $oldSubjectId ?>">
                        <option value="">-- Select a class first --</option>
                    </select>
                    <small class="form-help">The list shows only subjects offered by the selected class.</small>
                </div>

                <div class="form-group">
                    <label for="teacher_id">Teacher</label>
                    <select id="teacher_id" name="teacher_id" required>
                        <option value="">-- Select a teacher --</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= (int) $teacher['id'] ?>" <?= $oldTeacherId === (int) $teacher['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($teacher['full_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Assign Teacher</button>
                    <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

            <script>
                (function () {
                    "use strict";

                    var classSubjects = <?= json_encode(
                        $classSubjectsMap,
                        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                    ) ?>;

                    var classSel = document.getElementById("class_id");
                    var subjectSel = document.getElementById("subject_id");
                    var preselect = String(subjectSel.getAttribute("data-preselect") || "0");

                    function setPlaceholder(text) {
                        subjectSel.replaceChildren();
                        var opt = document.createElement("option");
                        opt.value = "";
                        opt.textContent = text;
                        subjectSel.appendChild(opt);
                    }

                    function populateSubjects() {
                        var cid = classSel.value;

                        if (!cid) {
                            setPlaceholder("-- Select a class first --");
                            subjectSel.disabled = true;
                            return;
                        }

                        var list = classSubjects[cid] || [];

                        if (list.length === 0) {
                            setPlaceholder("-- No subjects set for this class --");
                            subjectSel.disabled = true;
                            return;
                        }

                        subjectSel.replaceChildren();

                        var placeholder = document.createElement("option");
                        placeholder.value = "";
                        placeholder.textContent = "-- Select a subject --";
                        subjectSel.appendChild(placeholder);

                        list.forEach(function (s) {
                            var opt = document.createElement("option");
                            opt.value = s.id;
                            opt.textContent = s.name;
                            if (String(s.id) === preselect) {
                                opt.selected = true;
                            }
                            subjectSel.appendChild(opt);
                        });

                        subjectSel.disabled = false;
                    }

                    classSel.addEventListener("change", populateSubjects);
                    populateSubjects();
                })();
            </script>

        <?php endif; ?>

      </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
