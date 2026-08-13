<?php

require_once __DIR__ . '/../../config/database.php';

class Student
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $userId, string $fullName, string $dob, string $gender, string $admissionNo): int
    {
        $sql = "INSERT INTO students (user_id, full_name, dob, gender, admission_no) 
                VALUES (:user_id, :full_name, :dob, :gender, :admission_no)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'full_name' => $fullName,
            'dob' => $dob,
            'gender' => $gender,
            'admission_no' => $admissionNo
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function enroll(int $studentId, int $classId, int $academicYearId): void
    {
        $sql = "INSERT INTO enrollments (student_id, class_id, academic_year_id) 
                VALUES (:student_id, :class_id, :academic_year_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'student_id' => $studentId,
            'class_id' => $classId,
            'academic_year_id' => $academicYearId
        ]);
    }

    public function admissionNoExists(string $admissionNo): bool
    {
        $sql = "SELECT id FROM students WHERE admission_no = :admission_no LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['admission_no' => $admissionNo]);
        return $stmt->fetch() !== false;
    }

    public function all(): array
    {
        $sql = "SELECT s.id, s.full_name, s.admission_no, s.gender, u.email, u.is_active,
                       c.name AS class_name
                FROM students s
                JOIN users u ON s.user_id = u.id
                LEFT JOIN enrollments e ON e.student_id = s.id
                LEFT JOIN academic_years ay ON e.academic_year_id = ay.id AND ay.is_current = TRUE
                LEFT JOIN classes c ON e.class_id = c.id
                ORDER BY s.full_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function allByClass(int $classId): array
    {
        $sql = "SELECT s.id, s.full_name, s.admission_no
                FROM students s
                JOIN enrollments e ON e.student_id = s.id
                JOIN academic_years ay ON e.academic_year_id = ay.id AND ay.is_current = TRUE
                WHERE e.class_id = :class_id
                ORDER BY s.full_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['class_id' => $classId]);
        return $stmt->fetchAll();
    }
}