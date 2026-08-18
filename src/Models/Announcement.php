<?php

require_once __DIR__ . '/../../config/database.php';

class Announcement
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $postedBy, ?int $classId, string $title, string $body): int
    {
        $sql = "INSERT INTO announcements (posted_by, class_id, title, body) 
                VALUES (:posted_by, :class_id, :title, :body)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'posted_by' => $postedBy,
            'class_id' => $classId,
            'title' => $title,
            'body' => $body
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Fetches announcements relevant to a dashboard: always includes school-wide
     * announcements (class_id IS NULL), plus class-specific ones if a class is given.
     */
    public function forDashboard(?int $classId = null): array
    {
        if ($classId === null) {
            $sql = "SELECT title, body, created_at FROM announcements 
                    WHERE class_id IS NULL 
                    ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            $sql = "SELECT title, body, created_at FROM announcements 
                    WHERE class_id IS NULL OR class_id = :class_id
                    ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['class_id' => $classId]);
        }

        return $stmt->fetchAll();
    }

    public function all(): array
    {
        $sql = "SELECT a.title, a.body, a.created_at, c.name AS class_name
                FROM announcements a
                LEFT JOIN classes c ON a.class_id = c.id
                ORDER BY a.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}