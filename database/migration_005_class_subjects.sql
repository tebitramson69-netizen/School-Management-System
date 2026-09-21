-- ============================================
-- Migration 005: class_subjects (subjects a class offers)
-- ============================================
--
-- Until now the only class<->subject link was
-- class_subject_teacher, which records TEACHER ASSIGNMENTS, not
-- which subjects a class studies. That made the subject list for
-- a class incomplete (only subjects with a teacher) and sometimes
-- wrong. This table is the authoritative "this class offers this
-- subject", independent of whether a teacher has been assigned.
--
-- Populated per (level + stream) defaults from the admin
-- Class Subjects screen, and used by the coefficient screen and
-- report cards as the canonical subject list.
-- ============================================

CREATE TABLE IF NOT EXISTS class_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE (class_id, subject_id) -- a subject appears once per class
) ENGINE=InnoDB;
