<?php

require_once __DIR__ . '/../../config/database.php';

class ParentModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $userId, string $fullName, ?string $phone = null): int
    {
        $sql = "INSERT INTO parents (user_id, full_name, phone) VALUES (:user_id, :full_name, :phone)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'full_name' => $fullName,
            'phone' => $phone
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function linkChild(int $parentId, int $studentId): void
    {
        $sql = "INSERT INTO parent_student (parent_id, student_id) VALUES (:parent_id, :student_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'parent_id' => $parentId,
            'student_id' => $studentId
        ]);
    }

    public function all(): array
    {
        $sql = "SELECT p.id, p.full_name, p.phone, u.email, u.is_active
                FROM parents p
                JOIN users u ON p.user_id = u.id
                ORDER BY p.full_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}