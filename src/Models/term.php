<?php

require_once __DIR__ . '/../../config/database.php';

class Term
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(): array
    {
        $sql = "SELECT t.id, t.name, t.sequence_number
                FROM terms t
                JOIN academic_years ay ON t.academic_year_id = ay.id
                WHERE ay.is_current = TRUE
                ORDER BY t.id";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $sql = "SELECT * FROM terms WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}