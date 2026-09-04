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
    public function getTotalCount(): int
{
    $sql = "SELECT COUNT(*) FROM teachers";

    return (int) $this->db->query($sql)->fetchColumn();
}
}