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
public function getTotalCount(): int
{
    $sql = "SELECT COUNT(*) FROM classes";

    return (int) $this->db->query($sql)->fetchColumn();
}
    public function find(int $id): array|false
    {
        $sql = "SELECT * FROM classes WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(string $name, string $level, ?string $classOption): int
    {
        $sql = "INSERT INTO classes (name, level, class_option)
                VALUES (:name, :level, :class_option)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'level' => $level,
            'class_option' => ($classOption === '' ? null : $classOption),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $level, ?string $classOption): void
    {
        $sql = "UPDATE classes
                SET name = :name, level = :level, class_option = :class_option
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'level' => $level,
            'class_option' => ($classOption === '' ? null : $classOption),
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $sql = "DELETE FROM classes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * Whether any students are enrolled in this class. Deleting a
     * class cascades to enrollments and attendance, so deletion is
     * blocked while students are enrolled.
     */
    public function hasEnrollments(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM enrollments WHERE class_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Allowed levels — kept in step with O/L (Form 5) / A/L (Upper
     * Sixth) detection and the per-stream subject templates.
     */
    public static function levels(): array
    {
        return ['Form 1', 'Form 2', 'Form 3', 'Form 4', 'Form 5', 'Lower Sixth', 'Upper Sixth'];
    }

    /**
     * Allowed streams (an empty option = general, for Forms 1-3).
     */
    public static function options(): array
    {
        return ['Science', 'Arts', 'Commercial'];
    }
}