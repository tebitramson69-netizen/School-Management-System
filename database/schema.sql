-- ============================================
-- School Management System - Database Schema
-- Cameroon Anglophone Secondary (GCE) System
-- ============================================

-- 1. USERS: central auth table for all 4 roles
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'parent') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    must_change_password BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. ROLE PROFILE TABLES: extra details per role, linked back to users
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    dob DATE,
    gender ENUM('M', 'F') NOT NULL,
  
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. MANY-TO-MANY: a parent can have multiple children, a child can (rarely) have multiple guardians
CREATE TABLE parent_student (
    parent_id INT NOT NULL,
    student_id INT NOT NULL,
    PRIMARY KEY (parent_id, student_id),
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. ACADEMIC STRUCTURE
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,          -- e.g. "Form 4 Science A"
    level VARCHAR(30) NOT NULL,         -- e.g. "Form 4"
    class_option VARCHAR(30) DEFAULT NULL -- e.g. "Science", "Arts", "Commercial", NULL for lower forms
) ENGINE=InnoDB;

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Who teaches what, in which class (this is your permission-check table)
CREATE TABLE class_subject_teacher (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    UNIQUE (class_id, subject_id) -- one teacher per subject per class
) ENGINE=InnoDB;

-- 5. TIME STRUCTURE
CREATE TABLE academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL UNIQUE,   -- e.g. "2026/2027"
    is_current BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB;

CREATE TABLE terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    name VARCHAR(30) NOT NULL,          -- e.g. "Term 1"
    sequence_number INT NOT NULL,       -- 1 or 2 (each term has 2 sequences)
      is_current BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    UNIQUE (academic_year_id, name, sequence_number)
) ENGINE=InnoDB;

-- Which class a student belongs to, per academic year (supports promotions)
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    UNIQUE (student_id, academic_year_id) -- one class per student per year
) ENGINE=InnoDB;

-- 6. ATTENDANCE
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_subject_teacher_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    marked_by INT NOT NULL,             -- teacher's id from teachers table
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_subject_teacher_id) REFERENCES class_subject_teacher(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES teachers(id),
    UNIQUE (student_id, class_subject_teacher_id, date) -- one record per student per subject-period per day
) ENGINE=InnoDB;

-- 7. SCORES (raw per-sequence entries; averages calculated on the fly)
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    term_id INT NOT NULL,
    sequence INT NOT NULL,              -- 1 or 2
    score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    UNIQUE (student_id, subject_id, term_id, sequence)
) ENGINE=InnoDB;

-- 8. GRADE SCALE (admin-configurable, so grading policy can change without touching scores)
CREATE TABLE grade_scale (
    id INT AUTO_INCREMENT PRIMARY KEY,
    min_score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL,
    letter CHAR(1) NOT NULL,
    remark VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- 9. ANNOUNCEMENTS
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    posted_by INT NOT NULL,             -- user_id of admin/teacher who posted
    class_id INT DEFAULT NULL,          -- NULL = school-wide announcement
    title VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. SCHOOL SETTINGS
CREATE TABLE school_settings (
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
    pass_mark DECIMAL(5,2) NOT NULL DEFAULT 10.00, -- pass threshold on the 0-20 scale
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 11. SUBJECT COEFFICIENTS (per class weighting for GCE subjects)
CREATE TABLE subject_coefficients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    coefficient INT NOT NULL DEFAULT 1,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE (subject_id, class_id)
) ENGINE=InnoDB;

-- 12. SEED DATA: default Cameroon GCE grade scale (admin can edit later)
INSERT INTO grade_scale (min_score, max_score, letter, remark) VALUES
(16.00, 20.00, 'A', 'Excellent'),
(14.00, 15.99, 'B', 'Very Good'),
(12.00, 13.99, 'C', 'Good'),
(10.00, 11.99, 'D', 'Fair'),
(8.00, 9.99, 'E', 'Weak'),
(0.00, 7.99, 'F', 'Fail');

-- 13. SEED DATA: default school settings row
INSERT INTO school_settings (school_name) VALUES ('School Management System');
