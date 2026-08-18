<?php

require_once __DIR__ . '/../../config/database.php';

class GradeScale
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function forScore(float $score): array|false
    {
        $sql = "SELECT letter, remark FROM grade_scale 
                WHERE :score BETWEEN min_score AND max_score 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['score' => $score]);
        return $stmt->fetch();
    }
}