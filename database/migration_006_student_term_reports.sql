-- ============================================
-- Migration 006: student_term_reports
-- ============================================
--
-- Per-student, per-term report-card details that are entered by
-- the class master rather than derived from daily attendance
-- (which is not term-scoped): absence hours (justified /
-- unjustified), lateness, a conduct rating, sanctions/discipline,
-- an optional distinction, and the class-master / principal
-- remarks shown on the bulletin.
-- ============================================

CREATE TABLE IF NOT EXISTS student_term_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    term_id INT NOT NULL,
    absence_justified INT NOT NULL DEFAULT 0,   -- hours
    absence_unjustified INT NOT NULL DEFAULT 0, -- hours
    times_late INT NOT NULL DEFAULT 0,
    conduct VARCHAR(20) DEFAULT NULL,           -- Excellent / Very Good / Good / Fair / Poor
    sanctions VARCHAR(255) DEFAULT NULL,        -- discipline / sanctions note
    distinction VARCHAR(40) DEFAULT NULL,       -- Roll of Honour / Encouragement / Warning ...
    class_master_remark VARCHAR(255) DEFAULT NULL,
    principal_remark VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    UNIQUE (student_id, term_id)
) ENGINE=InnoDB;
