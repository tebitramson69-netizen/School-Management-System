-- ============================================
-- Migration 001: Sync schema with application code
-- ============================================
-- Run this on existing databases to add missing
-- columns and tables that the application expects.
-- Safe to run multiple times (uses IF NOT EXISTS).
-- ============================================

-- 1. Add must_change_password to users
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS must_change_password BOOLEAN DEFAULT TRUE
    AFTER is_active;

-- 2. Add is_current to terms
ALTER TABLE terms
    ADD COLUMN IF NOT EXISTS is_current BOOLEAN DEFAULT FALSE
    AFTER sequence_number;

-- 3. Fix attendance table to match application code
-- The app uses class_subject_teacher_id instead of class_id
ALTER TABLE attendance
    ADD COLUMN IF NOT EXISTS class_subject_teacher_id INT DEFAULT NULL
    AFTER student_id;

-- If the old class_id column exists, we keep it for now but the app uses class_subject_teacher_id
-- Add the foreign key if not present (ignore error if already exists)
-- ALTER TABLE attendance ADD FOREIGN KEY (class_subject_teacher_id) REFERENCES class_subject_teacher(id) ON DELETE CASCADE;

-- Update unique constraint: drop old one if exists, add new one
-- Note: run these manually if needed, as ALTER TABLE DROP INDEX may fail if index doesn't exist
-- ALTER TABLE attendance DROP INDEX student_id;
-- ALTER TABLE attendance ADD UNIQUE (student_id, class_subject_teacher_id, date);

-- 4. Create school_settings table
CREATE TABLE IF NOT EXISTS school_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_name VARCHAR(200) NOT NULL DEFAULT 'School Management System',
    motto VARCHAR(255) DEFAULT NULL,
    logo_path VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    website VARCHAR(200) DEFAULT NULL,
    school_type VARCHAR(50) DEFAULT NULL,
    primary_color VARCHAR(20) DEFAULT '#123B63',
    secondary_color VARCHAR(20) DEFAULT '#D69E2E',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default row if table is empty
INSERT INTO school_settings (school_name)
SELECT 'School Management System'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM school_settings LIMIT 1);

-- 5. Create subject_coefficients table
CREATE TABLE IF NOT EXISTS subject_coefficients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    coefficient INT NOT NULL DEFAULT 1,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE (subject_id, class_id)
) ENGINE=InnoDB;
