<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Per-student, per-term report-card details (attendance, conduct,
 * sanctions, remarks) entered by the class master.
 */
class StudentTermReport
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Fetch the record for a student in a term, or null.
     */
    public function getFor(int $studentId, int $termId): ?array
    {
        $sql = "SELECT *
                FROM student_term_reports
                WHERE student_id = :student_id
                  AND term_id = :term_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'student_id' => $studentId,
            'term_id' => $termId
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Create or update the record for a student in a term.
     *
     * $data keys: absence_justified, absence_unjustified,
     * times_late, conduct, sanctions, distinction,
     * class_master_remark, principal_remark.
     */
    public function save(int $studentId, int $termId, array $data): void
    {
        $sql = "INSERT INTO student_term_reports (
                    student_id, term_id,
                    absence_justified, absence_unjustified, times_late,
                    conduct, sanctions, distinction,
                    class_master_remark, principal_remark
                ) VALUES (
                    :student_id, :term_id,
                    :absence_justified, :absence_unjustified, :times_late,
                    :conduct, :sanctions, :distinction,
                    :class_master_remark, :principal_remark
                )
                ON DUPLICATE KEY UPDATE
                    absence_justified   = VALUES(absence_justified),
                    absence_unjustified = VALUES(absence_unjustified),
                    times_late          = VALUES(times_late),
                    conduct             = VALUES(conduct),
                    sanctions           = VALUES(sanctions),
                    distinction         = VALUES(distinction),
                    class_master_remark = VALUES(class_master_remark),
                    principal_remark    = VALUES(principal_remark)";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId,
            'term_id' => $termId,
            'absence_justified' => (int) ($data['absence_justified'] ?? 0),
            'absence_unjustified' => (int) ($data['absence_unjustified'] ?? 0),
            'times_late' => (int) ($data['times_late'] ?? 0),
            'conduct' => ($data['conduct'] ?? '') !== '' ? $data['conduct'] : null,
            'sanctions' => ($data['sanctions'] ?? '') !== '' ? $data['sanctions'] : null,
            'distinction' => ($data['distinction'] ?? '') !== '' ? $data['distinction'] : null,
            'class_master_remark' => ($data['class_master_remark'] ?? '') !== '' ? $data['class_master_remark'] : null,
            'principal_remark' => ($data['principal_remark'] ?? '') !== '' ? $data['principal_remark'] : null,
        ]);
    }

    /**
     * Allowed conduct ratings (best first).
     */
    public static function conductOptions(): array
    {
        return ['Excellent', 'Very Good', 'Good', 'Fair', 'Poor'];
    }

    /**
     * Allowed end-of-term distinctions / decisions.
     */
    public static function distinctionOptions(): array
    {
        return [
            'Roll of Honour',
            'Honourable Mention',
            'Encouragement',
            'Warning (Work)',
            'Warning (Conduct)',
        ];
    }
}
