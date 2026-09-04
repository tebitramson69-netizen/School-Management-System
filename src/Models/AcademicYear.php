<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class AcademicYear
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }


    /**
     * Get the currently active academic year.
     *
     * Returns:
     *
     * [
     *     'id' => 1,
     *     'name' => '2026/2027',
     *     'is_current' => 1
     * ]
     *
     * Returns null when no academic year is currently active.
     */
    public function getCurrent(): ?array
    {
        $sql = "
            SELECT
                id,
                name,
                is_current
            FROM academic_years
            WHERE is_current = 1
            ORDER BY id DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }


    /**
     * Find an academic year by ID.
     */
    public function find(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                id,
                name,
                is_current
            FROM academic_years
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }


    /**
     * Get all academic years.
     *
     * Newest IDs are displayed first.
     */
    public function all(): array
    {
        $sql = "
            SELECT
                id,
                name,
                is_current
            FROM academic_years
            ORDER BY
                id DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Create a new academic year.
     *
     * The new year is NOT automatically activated.
     */
    public function create(string $name): int
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException(
                'Academic year name is required.'
            );
        }

        if (
            !preg_match(
                '/^\d{4}\/\d{4}$/',
                $name
            )
        ) {
            throw new InvalidArgumentException(
                'Academic year must use the format YYYY/YYYY.'
            );
        }

        $sql = "
            INSERT INTO academic_years (
                name,
                is_current
            )
            VALUES (
                :name,
                0
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'name' => $name
        ]);

        return (int) $this->db->lastInsertId();
    }


    /**
     * Activate one academic year.
     *
     * Only one academic year should be current at a time.
     *
     * This operation is transactional so the database does
     * not temporarily end up with multiple active years.
     */
    public function setCurrent(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Invalid academic year ID.'
            );
        }

        $this->db->beginTransaction();

        try {

            /*
             * Verify that the academic year exists.
             */
            $checkSql = "
                SELECT id
                FROM academic_years
                WHERE id = :id
                LIMIT 1
            ";

            $checkStmt = $this->db->prepare($checkSql);

            $checkStmt->execute([
                'id' => $id
            ]);

            if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {

                throw new InvalidArgumentException(
                    'Academic year not found.'
                );
            }


            /*
             * Deactivate all academic years.
             */
            $resetSql = "
                UPDATE academic_years
                SET is_current = 0
            ";

            $resetStmt = $this->db->prepare($resetSql);

            $resetStmt->execute();


            /*
             * Activate the selected academic year.
             */
            $activateSql = "
                UPDATE academic_years
                SET is_current = 1
                WHERE id = :id
            ";

            $activateStmt =
                $this->db->prepare($activateSql);

            $activateStmt->execute([
                'id' => $id
            ]);


            $this->db->commit();

        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}