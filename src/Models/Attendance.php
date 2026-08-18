<?php

require_once __DIR__ . '/../../config/database.php';

class Attendance
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Marks attendance for one subject-period on a given date.
     * $statuses is an array like [studentId => 'present', studentId => 'absent', ...]
     * Uses ON DUPLICATE KEY UPDATE so re-marking the same period/day corrects it,
     * instead of throwing a duplicate-entry error.
     */
    public function markBulk(int $assignmentId, string $date, array $statuses, int $teacherId): void
    {
        $sql = "INSERT INTO attendance (student_id, class_subject_teacher_id, date, status, marked_by) 
                VALUES (:student_id, :assignment_id, :date, :status, :marked_by)
                ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)";
        $stmt = $this->db->prepare($sql);

        foreach ($statuses as $studentId => $status) {
            $stmt->execute([
                'student_id' => $studentId,
                'assignment_id' => $assignmentId,
                'date' => $date,
                'status' => $status,
                'marked_by' => $teacherId
            ]);
        }
    }

    /**
     * Fetches existing attendance for a subject-period on a given date,
     * keyed by student_id, so a form can pre-fill if already marked.
     */
    public function getForAssignmentDate(int $assignmentId, string $date): array
    {
        $sql = "SELECT student_id, status FROM attendance 
                WHERE class_subject_teacher_id = :assignment_id AND date = :date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['assignment_id' => $assignmentId, 'date' => $date]);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['student_id']] = $row['status'];
        }

        return $result;
    }

    public function forStudent(int $studentId): array
    {
        $sql = "SELECT a.date, a.status, s.name AS subject_name
                FROM attendance a
                JOIN class_subject_teacher cst ON a.class_subject_teacher_id = cst.id
                JOIN subjects s ON cst.subject_id = s.id
                WHERE a.student_id = :student_id
                ORDER BY a.date DESC, s.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }
}