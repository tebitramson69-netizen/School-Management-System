<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class SubjectCoefficient
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get the coefficient assigned to a subject
     * for a specific class.
     */
    public function getForSubjectClass(
        int $subjectId,
        int $classId
    ): ?int {
        if ($subjectId <= 0 || $classId <= 0) {
            return null;
        }

        $sql = "
            SELECT coefficient
            FROM subject_coefficients
            WHERE subject_id = :subject_id
              AND class_id = :class_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'subject_id' => $subjectId,
            'class_id' => $classId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        return (int) $result['coefficient'];
    }

    /**
     * Get all subject coefficients for a class.
     *
     * Returns:
     *
     * [
     *     subjectId => coefficient,
     *     subjectId => coefficient
     * ]
     */
    public function getForClass(int $classId): array
    {
        if ($classId <= 0) {
            return [];
        }

        $sql = "
            SELECT
                subject_id,
                coefficient
            FROM subject_coefficients
            WHERE class_id = :class_id
            ORDER BY subject_id ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'class_id' => $classId
        ]);

        $result = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[(int) $row['subject_id']] =
                (int) $row['coefficient'];
        }

        return $result;
    }

    /**
     * Get all subjects and their coefficients
     * for a particular class.
     *
     * This version includes subject information,
     * making it useful for an admin configuration page.
     */
    public function getSubjectsForClass(int $classId): array
    {
        if ($classId <= 0) {
            return [];
        }

        $sql = "
            SELECT
                s.id AS subject_id,
                s.name AS subject_name,
                s.code AS subject_code,
                sc.coefficient
            FROM subjects s

            LEFT JOIN subject_coefficients sc
                ON sc.subject_id = s.id
                AND sc.class_id = :class_id

            ORDER BY
                s.name ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'class_id' => $classId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create or update a coefficient.
     */
    public function save(
        int $subjectId,
        int $classId,
        int $coefficient
    ): void {
        if ($subjectId <= 0) {
            throw new InvalidArgumentException(
                'Invalid subject ID.'
            );
        }

        if ($classId <= 0) {
            throw new InvalidArgumentException(
                'Invalid class ID.'
            );
        }

        if ($coefficient <= 0) {
            throw new InvalidArgumentException(
                'Coefficient must be greater than zero.'
            );
        }

        $sql = "
            INSERT INTO subject_coefficients (
                subject_id,
                class_id,
                coefficient
            )
            VALUES (
                :subject_id,
                :class_id,
                :coefficient
            )
            ON DUPLICATE KEY UPDATE
                coefficient = VALUES(coefficient)
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'subject_id' => $subjectId,
            'class_id' => $classId,
            'coefficient' => $coefficient
        ]);
    }

    /**
     * Save multiple subject coefficients for a class.
     *
     * Expected format:
     *
     * [
     *     subjectId => coefficient,
     *     subjectId => coefficient
     * ]
     */
    public function saveBulk(
        int $classId,
        array $coefficients
    ): void {
        if ($classId <= 0) {
            throw new InvalidArgumentException(
                'Invalid class ID.'
            );
        }

        $this->db->beginTransaction();

        try {
            foreach ($coefficients as $subjectId => $coefficient) {

                $subjectId = (int) $subjectId;
                $coefficient = (int) $coefficient;

                if ($subjectId <= 0) {
                    continue;
                }

                if ($coefficient <= 0) {
                    throw new InvalidArgumentException(
                        "Invalid coefficient for subject {$subjectId}."
                    );
                }

                $sql = "
                    INSERT INTO subject_coefficients (
                        subject_id,
                        class_id,
                        coefficient
                    )
                    VALUES (
                        :subject_id,
                        :class_id,
                        :coefficient
                    )
                    ON DUPLICATE KEY UPDATE
                        coefficient = VALUES(coefficient)
                ";

                $stmt = $this->db->prepare($sql);

                $stmt->execute([
                    'subject_id' => $subjectId,
                    'class_id' => $classId,
                    'coefficient' => $coefficient
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
     * Delete a coefficient assignment.
     */
    public function delete(
        int $subjectId,
        int $classId
    ): void {
        if ($subjectId <= 0 || $classId <= 0) {
            throw new InvalidArgumentException(
                'Invalid subject or class ID.'
            );
        }

        $sql = "
            DELETE FROM subject_coefficients
            WHERE subject_id = :subject_id
              AND class_id = :class_id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'subject_id' => $subjectId,
            'class_id' => $classId
        ]);
    }
}