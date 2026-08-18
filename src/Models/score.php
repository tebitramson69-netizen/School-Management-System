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

    public function forStudent(int $studentId): array
    {
        $sql = "SELECT sc.score, sc.max_score, sub.name AS subject_name, 
                       t.name AS term_name, t.sequence_number
                FROM scores sc
                JOIN subjects sub ON sc.subject_id = sub.id
                JOIN terms t ON sc.term_id = t.id
                WHERE sc.student_id = :student_id
                ORDER BY t.id, sub.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Computes each subject's average across all sequence scores entered so far,
     * for a simple running report card (not tied to one specific term).
     */
    public function reportCardForStudent(int $studentId): array
    {
        $sql = "SELECT sub.name AS subject_name, 
                       AVG(sc.score) AS average_score, 
                       MAX(sc.max_score) AS max_score
                FROM scores sc
                JOIN subjects sub ON sc.subject_id = sub.id
                WHERE sc.student_id = :student_id
                GROUP BY sub.id, sub.name
                ORDER BY sub.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }
}