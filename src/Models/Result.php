<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Score.php';

class Result
{
    private PDO $db;

    private Score $scoreModel;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->scoreModel = new Score();
    }

    /**
     * Get the grade and remark configured in the database
     * for a particular score.
     *
     * The grading scale is NOT hard-coded here.
     * It comes directly from the grade_scale table.
     */
    public function getGrade(float $score): array|false
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

    /**
     * Get all students enrolled in a class for an academic year.
     *
     * This is the foundation of class ranking.
     */
    public function getClassStudents(
        int $classId,
        int $academicYearId
    ): array {
        $sql = "
            SELECT
                s.id AS student_id,
                s.full_name,
                s.gender,
                e.id AS enrollment_id,
                e.class_id,
                e.academic_year_id
            FROM enrollments e

            INNER JOIN students s
                ON e.student_id = s.id

            WHERE e.class_id = :class_id
              AND e.academic_year_id = :academic_year_id

            ORDER BY
                s.full_name ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'class_id' => $classId,
            'academic_year_id' => $academicYearId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate subject averages for one student in one term.
     *
     * If a student has:
     *
     * Sequence 1 = 14
     * Sequence 2 = 16
     *
     * The subject average becomes:
     *
     * 15
     */
    public function getStudentSubjectResults(
        int $studentId,
        int $termId
    ): array {
        $sql = "
            SELECT
                sub.id AS subject_id,
                sub.name AS subject_name,
                sub.code AS subject_code,

                ROUND(AVG(sc.score), 2) AS average_score,

                COUNT(sc.id) AS sequence_count,

                MAX(sc.max_score) AS max_score

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

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$result) {
            $average = (float) $result['average_score'];

            $grade = $this->getGrade($average);

            $result['average_score'] = round($average, 2);
            $result['grade'] = $grade['letter'] ?? null;
            $result['remark'] = $grade['remark'] ?? null;
        }

        unset($result);

        return $results;
    }

    /**
     * Calculate the overall average for one student in one term.
     *
     * COEFFICIENT-WEIGHTED. The overall figure is produced by the
     * SAME engine the student's report card uses —
     * Score::overallAverageForStudentTerm() — so a student's rank
     * and their displayed overall average always share one
     * definition (weighting AND term-name/sequence aggregation).
     *
     * $classId is required for weighting: it selects the per-class
     * coefficients. When it is 0 (or no coefficients are configured)
     * every subject defaults to coefficient 1, reproducing the
     * previous equal-weight mean — so existing callers that cannot
     * supply a class id keep their old behaviour.
     */
    public function getStudentOverallResult(
        int $studentId,
        int $termId,
        int $classId = 0
    ): array {
        $subjects = $this->getStudentSubjectResults(
            $studentId,
            $termId
        );

        $subjectCount = 0;

        foreach ($subjects as $subject) {
            if ($subject['average_score'] === null) {
                continue;
            }

            $subjectCount++;
        }

        /*
         * Delegate the overall figure to the shared Score engine so
         * ranking == displayed report-card average by construction.
         */
        $overallAverage =
            $this->scoreModel->overallAverageForStudentTerm(
                $studentId,
                $termId,
                $classId
            );

        if ($overallAverage === null) {
            return [
                'student_id' => $studentId,
                'subject_count' => 0,
                'overall_average' => null,
                'grade' => null,
                'remark' => null,
                'status' => 'NO RESULT'
            ];
        }

        $grade = $this->getGrade($overallAverage);

        return [
            'student_id' => $studentId,
            'subject_count' => $subjectCount,
            'overall_average' => $overallAverage,
            'grade' => $grade['letter'] ?? null,
            'remark' => $grade['remark'] ?? null,
            'status' => $this->determinePassFail($overallAverage)
        ];
    }

    /**
     * Determine whether a student has passed based on
     * the school's configured grading scale.
     *
     * Current school rule:
     *
     * 8 and above = PASS
     * below 8 = FAIL
     *
     * This corresponds to the current E/F boundary.
     *
     * This method can later be made configurable if the school
     * introduces a different pass requirement.
     */
    public function determinePassFail(float $average): string
    {
        return $average >= 8.00
            ? 'PASS'
            : 'FAIL';
    }

    /**
     * Calculate the complete result for every student
     * enrolled in a class for a specific term.
     *
     * Returns students ordered by average, highest first.
     */
    public function getClassResults(
        int $classId,
        int $academicYearId,
        int $termId
    ): array {
        $students = $this->getClassStudents(
            $classId,
            $academicYearId
        );

        $results = [];

        foreach ($students as $student) {

            $studentResult = $this->getStudentOverallResult(
                (int) $student['student_id'],
                $termId,
                $classId
            );

            $results[] = array_merge(
                $student,
                $studentResult
            );
        }

        /*
         * Students with actual results come first.
         * Within the result group, highest average comes first.
         *
         * Students without results are placed at the bottom.
         */
        usort(
            $results,
            function (array $a, array $b): int {

                $averageA = $a['overall_average'];
                $averageB = $b['overall_average'];

                if ($averageA === null && $averageB === null) {
                    return strcasecmp(
                        $a['full_name'],
                        $b['full_name']
                    );
                }

                if ($averageA === null) {
                    return 1;
                }

                if ($averageB === null) {
                    return -1;
                }

                if ((float) $averageA === (float) $averageB) {
                    return strcasecmp(
                        $a['full_name'],
                        $b['full_name']
                    );
                }

                return ((float) $averageA < (float) $averageB)
                    ? 1
                    : -1;
            }
        );

        /*
         * Assign positions.
         *
         * Example:
         *
         * 1st = 17.50
         * 2nd = 16.75
         * 2nd = 16.75
         * 4th = 15.20
         *
         * Equal averages receive the same position.
         */
        $position = 0;
        $previousAverage = null;
        $numberedStudents = 0;

        foreach ($results as &$result) {

            if ($result['overall_average'] === null) {
                $result['position'] = null;
                continue;
            }

            $numberedStudents++;

            $currentAverage = (float) $result['overall_average'];

            if (
                $previousAverage === null ||
                $currentAverage !== $previousAverage
            ) {
                $position = $numberedStudents;
            }

            $result['position'] = $position;

            $previousAverage = $currentAverage;
        }

        unset($result);

        return $results;
    }

    /**
     * Get the result of one specific student within their class.
     *
     * This is useful for student dashboards and parent dashboards.
     */
    public function getStudentClassPosition(
        int $studentId,
        int $classId,
        int $academicYearId,
        int $termId
    ): array|false {
        $classResults = $this->getClassResults(
            $classId,
            $academicYearId,
            $termId
        );

        foreach ($classResults as $result) {

            if ((int) $result['student_id'] === $studentId) {
                return $result;
            }
        }

        return false;
    }

    /**
     * Get summary statistics for a class.
     *
     * Useful for the admin results dashboard.
     */
    public function getClassSummary(
        int $classId,
        int $academicYearId,
        int $termId
    ): array {
        $results = $this->getClassResults(
            $classId,
            $academicYearId,
            $termId
        );

        $totalStudents = count($results);
        $studentsWithResults = 0;
        $passed = 0;
        $failed = 0;

        $averages = [];

        foreach ($results as $result) {

            if ($result['overall_average'] === null) {
                continue;
            }

            $studentsWithResults++;

            $average = (float) $result['overall_average'];

            $averages[] = $average;

            if ($result['status'] === 'PASS') {
                $passed++;
            } elseif ($result['status'] === 'FAIL') {
                $failed++;
            }
        }

        $classAverage = null;

        if (!empty($averages)) {
            $classAverage = round(
                array_sum($averages) / count($averages),
                2
            );
        }

        $passRate = null;

        if ($studentsWithResults > 0) {
            $passRate = round(
                ($passed / $studentsWithResults) * 100,
                2
            );
        }

        return [
            'total_students' => $totalStudents,
            'students_with_results' => $studentsWithResults,
            'passed' => $passed,
            'failed' => $failed,
            'class_average' => $classAverage,
            'pass_rate' => $passRate
        ];
    }
}