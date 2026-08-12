<?php

require_once __DIR__ . '/../../config/database.php';

class Subject
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(): array
    {
        $sql = "SELECT id, name, code FROM subjects ORDER BY name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}