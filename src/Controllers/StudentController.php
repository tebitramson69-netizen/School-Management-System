<?php

declare(strict_types=1);

/**
 * =========================================================
 * SCHOOL MANAGEMENT SYSTEM
 * StudentController.php
 *
 * Handles:
 * - Student dashboard
 * - Student profile lookup
 * - Attendance
 * - Scores
 * - Announcements
 * - Report card
 * - Grades
 *
 * PHP 8+
 * =========================================================
 */


/*
 * ---------------------------------------------------------
 * LOAD MODELS
 * ---------------------------------------------------------
 */

require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/Attendance.php';
require_once __DIR__ . '/../Models/Score.php';
require_once __DIR__ . '/../Models/Announcement.php';
require_once __DIR__ . '/../Models/GradeScale.php';
require_once __DIR__ . '/../Models/Result.php';
require_once __DIR__ . '/../Core/School.php';


/*
 * ---------------------------------------------------------
 * LOAD MIDDLEWARE
 * ---------------------------------------------------------
 */

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';


/*
 * ---------------------------------------------------------
 * STUDENT CONTROLLER
 * ---------------------------------------------------------
 */

class StudentController
{
    /*
     * -----------------------------------------------------
     * MODEL PROPERTIES
     * -----------------------------------------------------
     */

    private Student $studentModel;

    private Attendance $attendanceModel;

    private Score $scoreModel;

    private Announcement $announcementModel;

    private GradeScale $gradeScaleModel;

    private Result $resultModel;


    /*
     * -----------------------------------------------------
     * CONSTRUCTOR
     * -----------------------------------------------------
     */

    public function __construct()
    {
        $this->studentModel =
            new Student();

        $this->attendanceModel =
            new Attendance();

        $this->scoreModel =
            new Score();

        $this->announcementModel =
            new Announcement();

        $this->gradeScaleModel =
            new GradeScale();

        $this->resultModel =
            new Result();
    }


    /*
     * =====================================================
     * STUDENT DASHBOARD
     * =====================================================
     */

    public function dashboard(): void
    {
        /*
         * -------------------------------------------------
         * 1. AUTHENTICATION
         * -------------------------------------------------
         *
         * Only authenticated students can access the
         * student dashboard.
         */

        AuthMiddleware::requireRole('student');


        /*
         * -------------------------------------------------
         * 2. START SESSION
         * -------------------------------------------------
         */

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        /*
         * -------------------------------------------------
         * 3. CHECK USER SESSION
         * -------------------------------------------------
         */

        $userId =
            (int) ($_SESSION['user_id'] ?? 0);


        if ($userId <= 0) {

            header(
                'Location: ' .
                BASE_URL .
                '/index.php?action=login'
            );

            exit;
        }


        /*
         * -------------------------------------------------
         * 4. GET STUDENT PROFILE
         * -------------------------------------------------
         *
         * Connect the logged-in user account with the
         * corresponding student record.
         */

        $student =
            $this->studentModel->findByUserId($userId);


        if (!$student) {

            echo 'Student profile not found. Please contact the administrator.';

            exit;
        }


        /*
         * -------------------------------------------------
         * 5. STUDENT ID
         * -------------------------------------------------
         */

        $studentId =
            (int) $student['id'];


        /*
         * -------------------------------------------------
         * 6. ATTENDANCE
         * -------------------------------------------------
         */

        $attendance =
            $this->attendanceModel->forStudent(
                $studentId
            );


        /*
         * -------------------------------------------------
         * 7. SCORES
         * -------------------------------------------------
         */

        $scores =
            $this->scoreModel->forStudent(
                $studentId
            );


        /*
         * -------------------------------------------------
         * 8. CURRENT CLASS
         * -------------------------------------------------
         */

        $classId =
            $this->studentModel->getCurrentClassId(
                $studentId
            );


        /*
         * -------------------------------------------------
         * 8b. CURRENT CLASS DETAILS (NAME + DESCRIPTORS)
         * -------------------------------------------------
         *
         * getCurrentClassId() above returns only the class id.
         * The dashboard view displays the class *name*, so we
         * fetch the full class row (scoped to the current
         * academic year) and merge it into $student. This is
         * why the header previously showed "Not assigned".
         */

        $currentClass =
            $this->studentModel->getCurrentClass(
                $studentId
            );

        $student['class_name'] =
            $currentClass['name'] ?? null;

        $student['class_level'] =
            $currentClass['level'] ?? null;

        $student['class_option'] =
            $currentClass['class_option'] ?? null;

        /*
         * -------------------------------------------------
         * 9. ANNOUNCEMENTS
         * -------------------------------------------------
         */

        $announcements =
            $this->announcementModel->forDashboard(
                $classId
            );


        /*
         * =================================================
         * REPORT CARD
         * =================================================
         *
         * We use the latest term for which the student
         * actually has recorded scores.
         *
         * This avoids depending on:
         *
         * Term::getCurrentTerm()
         *
         * which is not available in the current Term model.
         */


        /*
         * -------------------------------------------------
         * 10. FIND LATEST TERM
         * -------------------------------------------------
         */

        $latestTermId =
            $this->scoreModel->latestTermIdForStudent(
                $studentId
            );


        /*
         * -------------------------------------------------
         * 11. INITIAL REPORT CARD VALUES
         * -------------------------------------------------
         */

        $reportCard = [];

        $overallAverage = null;

        $overallGrade = null;


        /*
         * -------------------------------------------------
         * 12. BUILD REPORT CARD
         * -------------------------------------------------
         */

        if ($latestTermId !== null) {

            $reportCard =
                $this->scoreModel->subjectAveragesForStudentTerm(
                    $studentId,
                    $latestTermId
                );


            /*
             * -------------------------------------------------
             * ADD GRADE AND REMARK TO EACH SUBJECT
             * -------------------------------------------------
             */

            foreach ($reportCard as &$row) {

                $averageScore =
                    (float) (
                        $row['average_score'] ?? 0
                    );


                $grade =
                    $this->gradeScaleModel->forScore(
                        $averageScore
                    );


                $row['letter'] =
                    $grade['letter'] ?? '—';


                $row['remark'] =
                    $grade['remark'] ?? '—';
            }


            unset($row);


            /*
             * -------------------------------------------------
             * 13. CALCULATE OVERALL AVERAGE
             * -------------------------------------------------
             */

            $overallAverage =
                $this->scoreModel->overallAverageForStudentTerm(
                    $studentId,
                    $latestTermId,
                    (int) ($classId ?? 0)
                );


            /*
             * -------------------------------------------------
             * 14. CALCULATE OVERALL GRADE
             * -------------------------------------------------
             */

            if ($overallAverage !== null) {

                $overallGrade =
                    $this->gradeScaleModel->forScore(
                        $overallAverage
                    );
            }
        }


        /*
         * -------------------------------------------------
         * 15. CLASS POSITION (RANKING)
         * -------------------------------------------------
         *
         * Show the student's rank within their class for the
         * latest term with recorded scores.
         */

        $classPosition = null;
        $classSize = null;

        $enrollment =
            $this->studentModel->getCurrentEnrollment($studentId);

        if ($latestTermId !== null && $enrollment !== null) {

            $classResults =
                $this->resultModel->getClassResults(
                    $enrollment['class_id'],
                    $enrollment['academic_year_id'],
                    $latestTermId
                );

            foreach ($classResults as $classResult) {

                if ((int) $classResult['student_id'] === $studentId) {
                    $classPosition = $classResult['position'];
                    break;
                }
            }

            /*
             * Class size counts only students who have results,
             * so the position denominator is meaningful.
             */
            $classSize = 0;

            foreach ($classResults as $classResult) {
                if ($classResult['overall_average'] !== null) {
                    $classSize++;
                }
            }
        }


        /*
         * -------------------------------------------------
         * 16. SCHOOL SETTINGS (for shared layout)
         * -------------------------------------------------
         */

        $school = School::settings();


        /*
         * =================================================
         * LOAD STUDENT DASHBOARD VIEW
         * =================================================
         */

        require __DIR__ .
            '/../../views/student/dashboard.php';
    }
}