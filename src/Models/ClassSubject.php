<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * class_subjects — the authoritative set of subjects a class
 * offers, independent of teacher assignment.
 */
class ClassSubject
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Subjects offered by a class, with details (for report cards
     * and the coefficient screen).
     */
    public function getForClass(int $classId): array
    {
        $sql = "SELECT s.id, s.name, s.code
                FROM class_subjects cs
                JOIN subjects s ON cs.subject_id = s.id
                WHERE cs.class_id = :class_id
                ORDER BY s.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['class_id' => $classId]);

        return $stmt->fetchAll();
    }

    /**
     * All offered subjects grouped by class, for building a
     * dependent (class -> subjects) picker.
     *
     * Returns [ classId => [ ['id' => int, 'name' => string], ... ] ].
     */
    public function mapByClass(): array
    {
        $sql = "SELECT cs.class_id, s.id, s.name
                FROM class_subjects cs
                JOIN subjects s ON cs.subject_id = s.id
                ORDER BY cs.class_id, s.name";

        $rows = $this->db->query($sql)->fetchAll();

        $map = [];

        foreach ($rows as $row) {
            $map[(int) $row['class_id']][] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
            ];
        }

        return $map;
    }

    /**
     * Just the offered subject ids, for pre-checking a form.
     */
    public function getSubjectIdsForClass(int $classId): array
    {
        $sql = "SELECT subject_id
                FROM class_subjects
                WHERE class_id = :class_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['class_id' => $classId]);

        return array_map(
            'intval',
            array_column($stmt->fetchAll(), 'subject_id')
        );
    }

    /**
     * Replace the whole offered set for a class in one transaction.
     * An empty $subjectIds clears the class's subjects.
     */
    public function saveForClass(int $classId, array $subjectIds): void
    {
        $this->db->beginTransaction();

        try {

            $delete = $this->db->prepare(
                "DELETE FROM class_subjects WHERE class_id = :class_id"
            );
            $delete->execute(['class_id' => $classId]);

            $insert = $this->db->prepare(
                "INSERT INTO class_subjects (class_id, subject_id)
                 VALUES (:class_id, :subject_id)"
            );

            $seen = [];

            foreach ($subjectIds as $subjectId) {

                $subjectId = (int) $subjectId;

                if ($subjectId <= 0 || isset($seen[$subjectId])) {
                    continue;
                }

                $seen[$subjectId] = true;

                $insert->execute([
                    'class_id' => $classId,
                    'subject_id' => $subjectId
                ]);
            }

            $this->db->commit();

        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Default subject CODES for a class, by (level + stream).
     *
     * These are sensible Cameroon GCE starting points; the admin
     * edits per class afterwards. Returned as codes (stable across
     * installs); the caller maps them to ids using the subjects
     * table.
     */
    public static function defaultSubjectCodes(
        ?string $level,
        ?string $option
    ): array {

        $level = trim((string) $level);
        $option = trim((string) $option);

        $firstCycle = [
            'Science'    => ['ENG', 'FRE', 'MATH', 'PHY', 'CHEM', 'BIO', 'CS', 'CE'],
            'Arts'       => ['ENG', 'FRE', 'MATH', 'LIT', 'HIST', 'GEO', 'ECON', 'CE'],
            'Commercial' => ['ENG', 'FRE', 'MATH', 'ACC', 'COMM', 'ECON', 'CS', 'CE'],
        ];

        $secondCycle = [
            'Science'    => ['PMM', 'PHY', 'CHEM', 'BIO', 'CS', 'CE'],
            'Arts'       => ['LIT', 'HIST', 'GEO', 'ECON', 'RS', 'FRE', 'CE'],
            'Commercial' => ['ACC', 'ECON', 'COMM', 'MATH', 'CS', 'CE'],
        ];

        $general = ['ENG', 'FRE', 'MATH', 'BIO', 'CHEM', 'PHY', 'GEO', 'HIST', 'LIT', 'CE', 'CS'];

        // First cycle, lower forms: common general curriculum.
        if (in_array($level, ['Form 1', 'Form 2', 'Form 3'], true)) {
            return $general;
        }

        // First cycle, streamed: Forms 4 & 5.
        if (in_array($level, ['Form 4', 'Form 5'], true)) {
            return $firstCycle[$option] ?? $general;
        }

        // Second cycle: Lower / Upper Sixth.
        if (in_array($level, ['Lower Sixth', 'Upper Sixth'], true)) {
            return $secondCycle[$option] ?? [];
        }

        return $general;
    }
}
