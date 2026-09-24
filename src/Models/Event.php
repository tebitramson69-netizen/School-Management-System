<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * School events shown in the dashboard "Upcoming" panel.
 */
class Event
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(
        string $title,
        string $eventDate,
        ?string $description,
        ?int $createdBy
    ): int {
        $sql = "INSERT INTO events (title, event_date, description, created_by)
                VALUES (:title, :event_date, :description, :created_by)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'title' => $title,
            'event_date' => $eventDate,
            'description' => ($description === '' ? null : $description),
            'created_by' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Upcoming events (today onward), soonest first.
     */
    public function upcoming(int $limit = 5): array
    {
        $limit = max(1, min(50, $limit));

        $sql = "SELECT id, title, event_date, description
                FROM events
                WHERE event_date >= CURDATE()
                ORDER BY event_date ASC, id ASC
                LIMIT {$limit}";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * All events, most recent date first (for management).
     */
    public function all(): array
    {
        $sql = "SELECT id, title, event_date, description
                FROM events
                ORDER BY event_date DESC, id DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): array|false
    {
        $sql = "SELECT id, title, event_date, description FROM events WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function delete(int $id): void
    {
        $sql = "DELETE FROM events WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
}
