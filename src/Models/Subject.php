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

    public function find(int $id): array|false
    {
        $sql = "SELECT id, name, code FROM subjects WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Check whether a subject code already exists.
     *
     * $excludeId lets an edit keep its own code without a false
     * "already taken" clash.
     */
    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM subjects WHERE code = :code";
        $params = ['code' => $code];

        if ($excludeId !== null) {
            $sql .= " AND id <> :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch() !== false;
    }

    public function create(string $name, string $code): int
    {
        $sql = "INSERT INTO subjects (name, code)
                VALUES (:name, :code)";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'name' => $name,
            'code' => $code
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $code): void
    {
        $sql = "UPDATE subjects
                SET name = :name,
                    code = :code
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'name' => $name,
            'code' => $code,
            'id' => $id
        ]);
    }

    public function delete(int $id): void
    {
        $sql = "DELETE FROM subjects WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * Whether a subject is referenced by existing school data.
     *
     * The foreign keys on scores, class_subject_teacher and
     * subject_coefficients all cascade on delete, so removing a
     * subject that is in use would silently destroy recorded
     * marks. Callers must block deletion when this returns true.
     */
    public function isInUse(int $id): bool
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM scores WHERE subject_id = :id1)
                  + (SELECT COUNT(*) FROM class_subject_teacher WHERE subject_id = :id2)
                  + (SELECT COUNT(*) FROM subject_coefficients WHERE subject_id = :id3)
                    AS total";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id1' => $id,
            'id2' => $id,
            'id3' => $id
        ]);

        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0) > 0;
    }
}