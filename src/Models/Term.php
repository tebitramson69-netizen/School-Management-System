<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class Term
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get all terms and sequences belonging to the current academic year.
     *
     * The existing database stores each term/sequence combination
     * as a row in the terms table.
     *
     * Example:
     *
     * Term 1 - Sequence 1
     * Term 1 - Sequence 2
     * Term 2 - Sequence 1
     * Term 2 - Sequence 2
     * Term 3 - Sequence 1
     * Term 3 - Sequence 2
     */
    public function all(): array
    {
        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number,
                ay.name AS academic_year_name
            FROM terms t
            INNER JOIN academic_years ay
                ON t.academic_year_id = ay.id
            WHERE ay.is_current = 1
            ORDER BY
                t.name ASC,
                t.sequence_number ASC,
                t.id ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find one term/sequence record by ID.
     */
    public function find(int $id): array|false
    {
        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number,
                ay.name AS academic_year_name,
                ay.is_current
            FROM terms t
            INNER JOIN academic_years ay
                ON t.academic_year_id = ay.id
            WHERE t.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find a term/sequence belonging to the current academic year.
     *
     * This prevents a form from accidentally using a term
     * belonging to an old academic year.
     */
    public function findCurrent(int $id): array|false
    {
        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number,
                ay.name AS academic_year_name
            FROM terms t
            INNER JOIN academic_years ay
                ON t.academic_year_id = ay.id
            WHERE t.id = :id
              AND ay.is_current = 1
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all sequences for a specific academic year and term name.
     *
     * Example:
     *
     * getSequences(1, 'Term 1')
     *
     * returns Sequence 1 and Sequence 2.
     */
    public function getSequences(
        int $academicYearId,
        string $termName
    ): array {
        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number
            FROM terms t
            WHERE t.academic_year_id = :academic_year_id
              AND t.name = :term_name
            ORDER BY
                t.sequence_number ASC,
                t.id ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'academic_year_id' => $academicYearId,
            'term_name' => $termName
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all term/sequence records for a specific academic year.
     */
    public function forAcademicYear(int $academicYearId): array
    {
        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number
            FROM terms t
            WHERE t.academic_year_id = :academic_year_id
            ORDER BY
                t.name ASC,
                t.sequence_number ASC,
                t.id ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'academic_year_id' => $academicYearId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the current academic year.
     */
    public function getCurrentAcademicYear(): array|false
    {
        $sql = "
            SELECT
                id,
                name,
                is_current
            FROM academic_years
            WHERE is_current = 1
            LIMIT 1
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}