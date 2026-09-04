<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/SubjectCoefficient.php';


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
     * Blank values remain ungraded.
     *
     * Scores are stored on a 0-20 scale.
     */
    public function recordBulk(
        int $subjectId,
        int $termId,
        int $sequence,
        array $scores
    ): void {


        if ($subjectId <= 0) {

            throw new InvalidArgumentException(
                'Invalid subject ID.'
            );
        }


        if ($termId <= 0) {

            throw new InvalidArgumentException(
                'Invalid term ID.'
            );
        }


        if ($sequence <= 0) {

            throw new InvalidArgumentException(
                'Invalid sequence number.'
            );
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


                $studentId = (int)$studentId;


                if ($studentId <= 0) {

                    continue;
                }



                /*
                 * Empty score means not graded.
                 */

                if ($scoreValue === '' || $scoreValue === null) {

                    continue;
                }



                if (!is_numeric($scoreValue)) {

                    throw new InvalidArgumentException(

                        "Invalid score supplied for student {$studentId}."

                    );
                }



                $score = (float)$scoreValue;



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
     * Fetch scores for one subject, term and sequence.
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


            $result[(int)$row['student_id']] =

                (float)$row['score'];

        }



        return $result;

    }





    /**
     * Returns all sequences for one subject and term.
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


            $studentId = (int)$row['student_id'];

            $sequence = (int)$row['sequence'];



            $result[$studentId][$sequence] =

                (float)$row['score'];

        }



        return $result;

    }


    /**
     * Get all scores belonging to one student.
     */
    public function forStudent(
        int $studentId
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


            ORDER BY

                ay.name DESC,
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
     * Get scores for one student and sequence.
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
     * Calculate subject averages.
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


                ROUND(
                    AVG(sc.score),
                    2
                ) AS average_score,


                COUNT(sc.id) AS sequence_count,


                MAX(sc.max_score) AS max_score


            FROM scores sc


            INNER JOIN subjects sub
                ON sc.subject_id = sub.id


            INNER JOIN terms selected_term
                ON selected_term.id = :term_id


            WHERE sc.student_id = :student_id


              AND sc.term_id IN (

                    SELECT t.id

                    FROM terms t

                    WHERE t.name = selected_term.name

                    AND t.academic_year_id =
                        selected_term.academic_year_id

              )


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
     * Calculate the overall coefficient-weighted average.
     *
     * Cameroon GCE weighting:
     *
     *     Σ(subject_average × coefficient) / Σ(coefficients)
     *
     * When $classId is 0 or no coefficients are configured,
     * every subject defaults to coefficient 1, which reproduces
     * a plain arithmetic mean of the subject averages.
     */
    public function overallAverageForStudentTerm(
        int $studentId,
        int $termId,
        int $classId = 0
    ): ?float {

        $subjects = $this->subjectAveragesForStudentTerm(
            $studentId,
            $termId
        );

        if (empty($subjects)) {
            return null;
        }

        $coefficients = [];

        if ($classId > 0) {
            $coefficientModel = new SubjectCoefficient();
            $coefficients =
                $coefficientModel->getForClass($classId);
        }

        $weightedTotal = 0.0;
        $totalCoefficients = 0;

        foreach ($subjects as $subject) {

            if ($subject['average_score'] === null) {
                continue;
            }

            $coefficient =
                $coefficients[(int) $subject['subject_id']] ?? 1;

            $weightedTotal +=
                (float) $subject['average_score'] * $coefficient;

            $totalCoefficients += $coefficient;
        }

        if ($totalCoefficients === 0) {
            return null;
        }

        return round(
            $weightedTotal / $totalCoefficients,
            2
        );

    }



    /**
     * Find the latest term containing scores for a student.
     *
     * Used by the dashboard to automatically display
     * the latest available report card.
     */
    public function latestTermIdForStudent(
        int $studentId
    ): ?int {


        $sql = "

            SELECT

                term_id


            FROM scores


            WHERE student_id = :student_id


            ORDER BY

                term_id DESC


            LIMIT 1

        ";


        $stmt = $this->db->prepare($sql);


        $stmt->execute([

            'student_id' => $studentId

        ]);


        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$result) {

            return null;

        }


        return (int)$result['term_id'];

    }

}