<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class Score
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Record or update scores for multiple students.
     *
     * $scores format:
     *
     * [
     *     studentId => score,
     *     studentId => score,
     * ]
     *
     * Blank values are skipped so that an ungraded student
     * is not automatically given zero.
     *
     * Scores are currently recorded on a 0-20 scale.
     */
    public function recordBulk(
        int $subjectId,
        int $termId,
        int $sequence,
        array $scores
    ): void {
        if ($subjectId <= 0) {
            throw new InvalidArgumentException('Invalid subject ID.');
        }

        if ($termId <= 0) {
            throw new InvalidArgumentException('Invalid term ID.');
        }

        if ($sequence <= 0) {
            throw new InvalidArgumentException('Invalid sequence number.');
        }

        $sql = "
            INSERT INTO scores (
                student_id,
                subject_id,
                term_id,
                sequence,
                score,
                max_score
            )
            VALUES (
                :student_id,
                :subject_id,
                :term_id,
                :sequence,
                :score,
                20.00
            )
            ON DUPLICATE KEY UPDATE
                score = VALUES(score),
                max_score = VALUES(max_score)
        ";

        $stmt = $this->db->prepare($sql);

        $this->db->beginTransaction();

        try {
            foreach ($scores as $studentId => $scoreValue) {

                $studentId = (int) $studentId;

                if ($studentId <= 0) {
                    continue;
                }

                /*
                 * Allow an empty field to remain ungraded.
                 */
                if ($scoreValue === '' || $scoreValue === null) {
                    continue;
                }

                /*
                 * Convert numeric input safely.
                 */
                if (!is_numeric($scoreValue)) {
                    throw new InvalidArgumentException(
                        "Invalid score supplied for student {$studentId}."
                    );
                }

                $score = (float) $scoreValue;

                /*
                 * Current school grading scale is 0-20.
                 */
                if ($score < 0 || $score > 20) {
                    throw new InvalidArgumentException(
                        "Score for student {$studentId} must be between 0 and 20."
                    );
                }

                $stmt->execute([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'term_id' => $termId,
                    'sequence' => $sequence,
                    'score' => $score
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
     * Fetch scores for one subject, one term and one sequence.
     *
     * Result:
     *
     * [
     *     studentId => score,
     *     studentId => score
     * ]
     */
    public function getForSubjectTermSequence(
        int $subjectId,
        int $termId,
        int $sequence
    ): array {
        $sql = "
            SELECT
                student_id,
                score
            FROM scores
            WHERE subject_id = :subject_id
              AND term_id = :term_id
              AND sequence = :sequence
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'subject_id' => $subjectId,
            'term_id' => $termId,
            'sequence' => $sequence
        ]);

        $result = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[(int) $row['student_id']] = (float) $row['score'];
        }

        return $result;
    }

    /**
     * Backward-compatible method.
     *
     * Existing controllers may already call getForSubjectTerm().
     *
     * We keep the method so existing pages don't immediately break.
     *
     * If a specific sequence is needed, use:
     *
     * getForSubjectTermSequence()
     */
    public function getForSubjectTerm(
        int $subjectId,
        int $termId
    ): array {
        $sql = "
            SELECT
                student_id,
                sequence,
                score
            FROM scores
            WHERE subject_id = :subject_id
              AND term_id = :term_id
            ORDER BY
                sequence ASC,
                student_id ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'subject_id' => $subjectId,
            'term_id' => $termId
        ]);

        $result = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $studentId = (int) $row['student_id'];
            $sequence = (int) $row['sequence'];

            /*
             * Preserve both sequences.
             *
             * Example:
             *
             * [
             *     12 => [
             *         1 => 15,
             *         2 => 17
             *     ]
             * ]
             */
            $result[$studentId][$sequence] = (float) $row['score'];
        }

        return $result;
    }

    /**
     * Get all scores belonging to one student.
     *
     * Results are restricted to the current academic year by default.
     */
    public function forStudent(int $studentId): array
    {
        $sql = "
            SELECT
                sc.id,
                sc.score,
                sc.max_score,
                sc.subject_id,
                sc.term_id,
                sc.sequence,
                sub.name AS subject_name,
                sub.code AS subject_code,
                t.name AS term_name,
                t.sequence_number,
                ay.id AS academic_year_id,
                ay.name AS academic_year_name
            FROM scores sc

            INNER JOIN subjects sub
                ON sc.subject_id = sub.id

            INNER JOIN terms t
                ON sc.term_id = t.id

            INNER JOIN academic_years ay
                ON t.academic_year_id = ay.id

            WHERE sc.student_id = :student_id
              AND ay.is_current = 1

            ORDER BY
                t.name ASC,
                t.sequence_number ASC,
                sub.name ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get scores for a student in one term.
     */
    public function forStudentTerm(
        int $studentId,
        int $termId
    ): array {
        $sql = "
            SELECT
                sc.id,
                sc.score,
                sc.max_score,
                sc.subject_id,
                sc.term_id,
                sc.sequence,
                sub.name AS subject_name,
                sub.code AS subject_code,
                t.name AS term_name,
                t.sequence_number
            FROM scores sc

            INNER JOIN subjects sub
                ON sc.subject_id = sub.id

            INNER JOIN terms t
                ON sc.term_id = t.id

            WHERE sc.student_id = :student_id
              AND sc.term_id = :term_id

            ORDER BY
                sub.name ASC,
                sc.sequence ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId,
            'term_id' => $termId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get scores for a student in one specific sequence.
     */
    public function forStudentTermSequence(
        int $studentId,
        int $termId,
        int $sequence
    ): array {
        $sql = "
            SELECT
                sc.id,
                sc.score,
                sc.max_score,
                sc.subject_id,
                sc.term_id,
                sc.sequence,
                sub.name AS subject_name,
                sub.code AS subject_code,
                t.name AS term_name,
                t.sequence_number
            FROM scores sc

            INNER JOIN subjects sub
                ON sc.subject_id = sub.id

            INNER JOIN terms t
                ON sc.term_id = t.id

            WHERE sc.student_id = :student_id
              AND sc.term_id = :term_id
              AND sc.sequence = :sequence

            ORDER BY
                sub.name ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId,
            'term_id' => $termId,
            'sequence' => $sequence
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate the average for each subject during one term.
     *
     * If both Sequence 1 and Sequence 2 exist, their scores
     * are averaged.
     *
     * Example:
     *
     * Sequence 1 = 14
     * Sequence 2 = 16
     *
     * Subject average = 15
     */
    public function subjectAveragesForStudentTerm(
        int $studentId,
        int $termId
    ): array {
        $sql = "
            SELECT
                sc.subject_id,
                sub.name AS subject_name,
                sub.code AS subject_code,

                AVG(sc.score) AS average_score,

                COUNT(sc.id) AS sequence_count,

                MAX(sc.max_score) AS max_score

            FROM scores sc

            INNER JOIN subjects sub
                ON sc.subject_id = sub.id

            WHERE sc.student_id = :student_id
              AND sc.term_id = :term_id

            GROUP BY
                sc.subject_id,
                sub.name,
                sub.code

            ORDER BY
                sub.name ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId,
            'term_id' => $termId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate the overall average for one student during one term.
     *
     * The result is based on subject averages, rather than treating
     * every sequence score as an independent subject result.
     */
    public function overallAverageForStudentTerm(
        int $studentId,
        int $termId
    ): ?float {
        $sql = "
            SELECT
                AVG(subject_average) AS overall_average
            FROM (
                SELECT
                    sc.subject_id,
                    AVG(sc.score) AS subject_average
                FROM scores sc
                WHERE sc.student_id = :student_id
                  AND sc.term_id = :term_id
                GROUP BY sc.subject_id
            ) AS subject_results
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId,
            'term_id' => $termId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || $result['overall_average'] === null) {
            return null;
        }

        return round((float) $result['overall_average'], 2);
    }

    /**
     * Get a student's current report-card subject averages
     * for a specific term.
     *
     * This is intentionally term-specific.
     *
     * An official report card must NEVER accidentally combine
     * scores from multiple academic years or terms.
     */
    public function reportCardForStudent(
        int $studentId,
        int $termId
    ): array {
        $sql = "
            SELECT
                sub.id AS subject_id,
                sub.name AS subject_name,
                sub.code AS subject_code,

                ROUND(AVG(sc.score), 2) AS average_score,

                MAX(sc.max_score) AS max_score,

                COUNT(sc.id) AS sequence_count

            FROM scores sc

            INNER JOIN subjects sub
                ON sc.subject_id = sub.id

            WHERE sc.student_id = :student_id
              AND sc.term_id = :term_id

            GROUP BY
                sub.id,
                sub.name,
                sub.code

            ORDER BY
                sub.name ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId,
            'term_id' => $termId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Determine the grade information for a score using
     * the existing grade_scale table.
     */
    public function gradeForScore(float $score): array|false
    {
        $sql = "
            SELECT
                id,
                min_score,
                max_score,
                letter,
                remark
            FROM grade_scale
            WHERE :score >= min_score
              AND :score <= max_score
            ORDER BY min_score DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'score' => $score
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}