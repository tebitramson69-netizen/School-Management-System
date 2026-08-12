<?php

require_once __DIR__ . '/../../config/database.php';

class ClassModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(): array
    {
        $sql = "SELECT id, name, level, class_option FROM classes ORDER BY id";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $sql = "SELECT * FROM classes WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}