<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class Term
{
    private PDO $db;


    /**
     * -----------------------------------------------------
     * Constructor
     * -----------------------------------------------------
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }


    /**
     * -----------------------------------------------------
     * Get all term/sequence records for the current
     * academic year.
     * -----------------------------------------------------
     */
    public function all(): array
    {
        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number,
                t.is_current,
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

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * -----------------------------------------------------
     * Get the CURRENT term/sequence.
     *
     * The current academic year and the term/sequence must
     * both be marked as current.
     * -----------------------------------------------------
     */
    public function getCurrentTerm(): array|false
    {
        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number,
                t.is_current,
                ay.name AS academic_year_name
            FROM terms t

            INNER JOIN academic_years ay
                ON t.academic_year_id = ay.id

            WHERE ay.is_current = 1
              AND t.is_current = 1

            ORDER BY
                t.id ASC

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * -----------------------------------------------------
     * Find one term/sequence record by ID.
     * -----------------------------------------------------
     */
    public function find(int $id): array|false
    {
        if ($id <= 0) {
            return false;
        }

        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number,
                t.is_current,
                ay.name AS academic_year_name,
                ay.is_current AS academic_year_is_current
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
     * -----------------------------------------------------
     * Find one term/sequence belonging to the current
     * academic year.
     * -----------------------------------------------------
     */
    public function findCurrent(int $id): array|false
    {
        if ($id <= 0) {
            return false;
        }

        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number,
                t.is_current,
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
     * -----------------------------------------------------
     * Get all sequences for a specific academic year and
     * term name.
     * -----------------------------------------------------
     */
    public function getSequences(
        int $academicYearId,
        string $termName
    ): array {
        if ($academicYearId <= 0) {
            return [];
        }

        $termName = trim($termName);

        if ($termName === '') {
            return [];
        }

        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number,
                t.is_current
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
     * -----------------------------------------------------
     * Get all term/sequence records for one academic year.
     *
     * This is important for historical-year viewing.
     * -----------------------------------------------------
     */
    public function forAcademicYear(
        int $academicYearId
    ): array {
        if ($academicYearId <= 0) {
            return [];
        }

        $sql = "
            SELECT
                t.id,
                t.academic_year_id,
                t.name,
                t.sequence_number,
                t.is_current
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
     * -----------------------------------------------------
     * Get the current academic year.
     * -----------------------------------------------------
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

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    /**
     * -----------------------------------------------------
     * Set ONE term/sequence as the current period.
     *
     * Important:
     *
     * - Only one sequence may be current inside the
     *   current academic year.
     * - The selected term must belong to the current
     *   academic year.
     *
     * We use a transaction so we never intentionally leave
     * multiple current sequences active.
     * -----------------------------------------------------
     */
    public function setCurrent(
        int $termId
    ): void {
        if ($termId <= 0) {
            throw new InvalidArgumentException(
                'Invalid term ID.'
            );
        }

        $this->db->beginTransaction();

        try {

            /*
             * Verify that the selected term belongs to
             * the current academic year.
             */
            $checkSql = "
                SELECT
                    t.id,
                    t.academic_year_id
                FROM terms t

                INNER JOIN academic_years ay
                    ON t.academic_year_id = ay.id

                WHERE t.id = :term_id
                  AND ay.is_current = 1

                LIMIT 1
            ";

            $checkStmt =
                $this->db->prepare($checkSql);

            $checkStmt->execute([
                'term_id' => $termId
            ]);

            $term = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$term) {
                throw new InvalidArgumentException(
                    'The selected term does not belong to the current academic year.'
                );
            }

            $academicYearId =
                (int) $term['academic_year_id'];


            /*
             * Clear the current flag for all sequences
             * belonging to the current academic year.
             */
            $resetSql = "
                UPDATE terms

                SET is_current = 0

                WHERE academic_year_id = :academic_year_id
            ";

            $resetStmt =
                $this->db->prepare($resetSql);

            $resetStmt->execute([
                'academic_year_id' => $academicYearId
            ]);


            /*
             * Make the selected sequence current.
             */
            $activateSql = "
                UPDATE terms

                SET is_current = 1

                WHERE id = :term_id
                  AND academic_year_id = :academic_year_id
            ";

            $activateStmt =
                $this->db->prepare($activateSql);

            $activateStmt->execute([
                'term_id' => $termId,
                'academic_year_id' => $academicYearId
            ]);


            if ($activateStmt->rowCount() === 0) {
                throw new RuntimeException(
                    'Unable to activate the selected term.'
                );
            }


            $this->db->commit();

        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}