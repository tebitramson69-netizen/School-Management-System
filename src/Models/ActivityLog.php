<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Activity feed for the admin dashboard.
 */
class ActivityLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Record an activity. Failures are swallowed so logging never
     * breaks the action that triggered it.
     */
    public function log(
        string $type,
        string $description,
        ?int $actorUserId = null
    ): void {
        try {
            $sql = "INSERT INTO activity_log (actor_user_id, type, description)
                    VALUES (:actor_user_id, :type, :description)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'actor_user_id' => $actorUserId,
                'type' => $type,
                'description' => mb_substr($description, 0, 255),
            ]);
        } catch (Throwable $e) {
            // Non-fatal: activity logging must never block an action.
        }
    }

    /**
     * The most recent activity entries (newest first).
     */
    public function recent(int $limit = 8): array
    {
        $limit = max(1, min(50, $limit));

        $sql = "SELECT id, actor_user_id, type, description, created_at
                FROM activity_log
                ORDER BY created_at DESC, id DESC
                LIMIT {$limit}";

        try {
            return $this->db->query($sql)->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }
}
