<?php

require_once __DIR__ . '/../../config/database.php';

class Score
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Records scores for one subject, one term/sequence, for multiple students at once.
     * $scores is [studentId => scoreValue, ...]. Blank/empty entries are skipped,
     * so a teacher can leave a student ungraded rather than forcing a 0.
     */
    public function recordBulk(int $subjectId, int $termId, int $sequence, array $scores): void
    {
        $sql = "INSERT INTO scores (student_id, subject_id, term_id, sequence, score, max_score) 
                VALUES (:student_id, :subject_id, :term_id, :sequence, :score, 20.00)
                ON DUPLICATE KEY UPDATE score = VALUES(score)";
        $stmt = $this->db->prepare($sql);

        foreach ($scores as $studentId => $scoreValue) {
            if ($scoreValue === '' || $scoreValue === null) {
                continue; // skip ungraded students instead of forcing a 0
            }

            $stmt->execute([
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'term_id' => $termId,
                'sequence' => $sequence,
                'score' => (float) $scoreValue
            ]);
        }
    }

    /**
     * Fetches existing scores for a subject+term, keyed by student_id, for pre-filling the form.
     */
    public function getForSubjectTerm(int $subjectId, int $termId): array
    {
        $sql = "SELECT student_id, score FROM scores WHERE subject_id = :subject_id AND term_id = :term_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['subject_id' => $subjectId, 'term_id' => $termId]);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['student_id']] = $row['score'];
        }

        return $result;
    }
}