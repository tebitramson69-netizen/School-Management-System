<?php

require_once __DIR__ . '/../../config/database.php';

class ClassSubjectTeacher
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function assign(int $classId, int $subjectId, int $teacherId): void
    {
        $sql = "INSERT INTO class_subject_teacher (class_id, subject_id, teacher_id) 
                VALUES (:class_id, :subject_id, :teacher_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'teacher_id' => $teacherId
        ]);
    }

    public function existsForClassSubject(int $classId, int $subjectId): bool
    {
        $sql = "SELECT id FROM class_subject_teacher WHERE class_id = :class_id AND subject_id = :subject_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['class_id' => $classId, 'subject_id' => $subjectId]);
        return $stmt->fetch() !== false;
    }

    public function all(): array
    {
        $sql = "SELECT cst.id, c.name AS class_name, s.name AS subject_name, t.full_name AS teacher_name
                FROM class_subject_teacher cst
                JOIN classes c ON cst.class_id = c.id
                JOIN subjects s ON cst.subject_id = s.id
                JOIN teachers t ON cst.teacher_id = t.id
                ORDER BY c.name, s.name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function forTeacher(int $teacherId): array
    {
        $sql = "SELECT cst.id, cst.class_id, cst.subject_id, c.name AS class_name, s.name AS subject_name
                FROM class_subject_teacher cst
                JOIN classes c ON cst.class_id = c.id
                JOIN subjects s ON cst.subject_id = s.id
                WHERE cst.teacher_id = :teacher_id
                ORDER BY c.name, s.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['teacher_id' => $teacherId]);
        return $stmt->fetchAll();
    }

    /**
     * Get the subjects actually taught in a class.
     *
     * A class "offers" a subject only when a teacher has been
     * assigned to teach it there, so the authoritative list of a
     * class's subjects is the set of class_subject_teacher rows —
     * not every subject in the subjects table.
     *
     * Returns one row per subject: id, name, code.
     */
    public function subjectsForClass(int $classId): array
    {
        $sql = "SELECT s.id, s.name, s.code
                FROM class_subject_teacher cst
                JOIN subjects s ON cst.subject_id = s.id
                WHERE cst.class_id = :class_id
                ORDER BY s.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['class_id' => $classId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $sql = "SELECT cst.id, cst.class_id, cst.subject_id, cst.teacher_id, 
                       c.name AS class_name, s.name AS subject_name
                FROM class_subject_teacher cst
                JOIN classes c ON cst.class_id = c.id
                JOIN subjects s ON cst.subject_id = s.id
                WHERE cst.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}