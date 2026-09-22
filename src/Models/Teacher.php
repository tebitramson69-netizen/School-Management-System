<?php

require_once __DIR__ . '/../../config/database.php';

class Teacher
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $userId, string $fullName, ?string $phone = null): int
    {
        $sql = "INSERT INTO teachers (user_id, full_name, phone) VALUES (:user_id, :full_name, :phone)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'full_name' => $fullName,
            'phone' => $phone
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        $sql = "SELECT t.id, t.full_name, t.phone, u.email, u.is_active 
                FROM teachers t 
                JOIN users u ON t.user_id = u.id 
                ORDER BY t.full_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function findByUserId(int $userId): array|false
    {
        $sql = "SELECT * FROM teachers WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    public function find(int $id): array|false
    {
        $sql = "SELECT t.id, t.user_id, t.full_name, t.phone,
                       u.email, u.is_active
                FROM teachers t
                JOIN users u ON t.user_id = u.id
                WHERE t.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update(int $id, string $fullName, ?string $phone): void
    {
        $sql = "UPDATE teachers
                SET full_name = :full_name,
                    phone = :phone
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'full_name' => $fullName,
            'phone' => ($phone === '' ? null : $phone),
            'id' => $id
        ]);
    }

    /**
     * Whether a teacher has class/subject assignments or has
     * marked attendance. attendance.marked_by has no cascade, so
     * a teacher with attendance cannot be hard-deleted at all;
     * this guards deletion with a friendly message instead.
     */
    public function hasRecords(int $id): bool
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM class_subject_teacher WHERE teacher_id = :id1)
                  + (SELECT COUNT(*) FROM attendance WHERE marked_by = :id2)
                    AS total";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id1' => $id, 'id2' => $id]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0) > 0;
    }
    public function getTotalCount(): int
{
    $sql = "SELECT COUNT(*) FROM teachers";

    return (int) $this->db->query($sql)->fetchColumn();
}
}