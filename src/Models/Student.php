<?php

require_once __DIR__ . '/../../config/database.php';

class Student
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function create(
        int $userId,
        string $fullName,
        string $dob,
        string $gender
    ): int {
        $sql = "INSERT INTO students (user_id, full_name, dob, gender)
                VALUES (:user_id, :full_name, :dob, :gender)";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
            'full_name' => $fullName,
            'dob' => $dob,
            'gender' => $gender
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function enroll(
        int $studentId,
        int $classId,
        int $academicYearId
    ): void {
        $sql = "INSERT INTO enrollments (
                    student_id,
                    class_id,
                    academic_year_id
                ) VALUES (
                    :student_id,
                    :class_id,
                    :academic_year_id
                )";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId,
            'class_id' => $classId,
            'academic_year_id' => $academicYearId
        ]);
    }

    public function all(): array
    {
        $sql = "SELECT
                    s.id,
                    s.full_name,
                    s.gender,
                    u.email,
                    u.is_active,
                    c.name AS class_name
                FROM students s
                JOIN users u
                    ON s.user_id = u.id
                LEFT JOIN enrollments e
                    ON e.student_id = s.id
                LEFT JOIN academic_years ay
                    ON e.academic_year_id = ay.id
                    AND ay.is_current = TRUE
                LEFT JOIN classes c
                    ON e.class_id = c.id
                ORDER BY s.full_name";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    
    /**
     * -----------------------------------------------------
     * ALL STUDENTS ENROLLED IN THE CURRENT ACADEMIC YEAR
     * -----------------------------------------------------
     *
     * Returns one row per student who has an enrollment in
     * the academic year currently marked as current.
     *
     * Because enrollments are UNIQUE per (student, year) and
     * both the enrollment and academic-year joins are INNER,
     * each current-year student appears exactly once — no
     * duplicates, and no students carried over from previous
     * academic years.
     */
    public function allEnrolledInCurrentYear(): array
    {
        $sql = "SELECT
                    s.id,
                    s.full_name,
                    s.gender,
                    u.email,
                    u.is_active,
                    c.name AS class_name
                FROM students s
                JOIN users u
                    ON s.user_id = u.id
                JOIN enrollments e
                    ON e.student_id = s.id
                JOIN academic_years ay
                    ON e.academic_year_id = ay.id
                    AND ay.is_current = TRUE
                LEFT JOIN classes c
                    ON e.class_id = c.id
                ORDER BY s.full_name";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function allByClass(int $classId): array
    {
        $sql = "SELECT
                    s.id,
                    s.full_name
                FROM students s
                JOIN enrollments e
                    ON e.student_id = s.id
                JOIN academic_years ay
                    ON e.academic_year_id = ay.id
                    AND ay.is_current = TRUE
                WHERE e.class_id = :class_id
                ORDER BY s.full_name";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'class_id' => $classId
        ]);

        return $stmt->fetchAll();
    }

    public function findByUserId(int $userId): array|false
    {
        $sql = "SELECT *
                FROM students
                WHERE user_id = :user_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetch();
    }

    public function find(int $id): array|false
    {
        $sql = "SELECT *
                FROM students
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch();
    }

    public function getCurrentClassId(int $studentId): ?int
    {
        $sql = "SELECT e.class_id
                FROM enrollments e
                JOIN academic_years ay
                    ON e.academic_year_id = ay.id
                WHERE e.student_id = :student_id
                AND ay.is_current = TRUE
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId
        ]);

        $result = $stmt->fetch();

        return $result
            ? (int) $result['class_id']
            : null;
    }

    /**
     * Get the student's current enrollment (class + academic year)
     * for the active academic year. Used for class ranking lookups.
     */
    public function getCurrentEnrollment(int $studentId): ?array
    {
        $sql = "SELECT e.class_id, e.academic_year_id
                FROM enrollments e
                JOIN academic_years ay
                    ON e.academic_year_id = ay.id
                WHERE e.student_id = :student_id
                AND ay.is_current = TRUE
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId
        ]);

        $result = $stmt->fetch();

        return $result
            ? [
                'class_id' => (int) $result['class_id'],
                'academic_year_id' => (int) $result['academic_year_id'],
            ]
            : null;
    }

    public function allByClassDetailed(int $classId): array
    {
        $sql = "SELECT
                    s.id,
                    s.full_name,
                    s.gender,
                    s.dob,
                    u.email
                FROM students s
                JOIN users u
                    ON s.user_id = u.id
                JOIN enrollments e
                    ON e.student_id = s.id
                JOIN academic_years ay
                    ON e.academic_year_id = ay.id
                    AND ay.is_current = TRUE
                WHERE e.class_id = :class_id
                ORDER BY s.full_name";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'class_id' => $classId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get statistics used by the administrator dashboard.
     */
    public function getDashboardStatistics(): array
    {
        $sql = "
            SELECT
                COUNT(DISTINCT e.student_id) AS total_students,

                COUNT(
                    DISTINCT CASE
                        WHEN c.level IN (
                            
                            'Form 5',
                            
                            'Upper Sixth'
                        )
                        THEN e.student_id
                    END
                ) AS gce_candidates

            FROM enrollments e

            INNER JOIN academic_years ay
                ON e.academic_year_id = ay.id

            INNER JOIN classes c
                ON e.class_id = c.id

            WHERE ay.is_current = TRUE
        ";

        $stmt = $this->db->query($sql);

        $result = $stmt->fetch();

        return [
            'total_students' =>
                (int) ($result['total_students'] ?? 0),

            'gce_candidates' =>
                (int) ($result['gce_candidates'] ?? 0),
        ];
    }
}