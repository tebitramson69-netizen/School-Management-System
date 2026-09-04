<?php

declare(strict_types=1);

/**
 * =========================================================
 * SCHOOL MANAGEMENT SYSTEM
 * AcademicYearController.php
 *
 * Handles:
 * - Viewing academic years
 * - Creating academic years
 * - Activating an academic year
 * - Viewing terms and sequences
 * - Changing the current term/sequence
 *
 * PHP 8+
 * =========================================================
 */

require_once __DIR__ . '/../Models/AcademicYear.php';
require_once __DIR__ . '/../Models/Term.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';


class AcademicYearController
{
    private AcademicYear $academicYearModel;

    private Term $termModel;


    /**
     * -----------------------------------------------------
     * Constructor
     * -----------------------------------------------------
     */
    public function __construct()
    {
        $this->academicYearModel =
            new AcademicYear();

        $this->termModel =
            new Term();
    }


    /**
     * -----------------------------------------------------
     * VIEW ACADEMIC YEARS
     * -----------------------------------------------------
     *
     * Loads:
     *
     * - all academic years
     * - current academic year
     * - current term/sequence
     * - all term/sequence records for the current year
     */
    public function index(): void
    {
        AuthMiddleware::requireRole('admin');


        /*
         * -------------------------------------------------
         * ACADEMIC YEARS
         * -------------------------------------------------
         */
        $academicYears =
            $this->academicYearModel->all();


        /*
         * -------------------------------------------------
         * CURRENT ACADEMIC YEAR
         * -------------------------------------------------
         */
        $currentAcademicYear =
            $this->academicYearModel->getCurrent();


        /*
         * -------------------------------------------------
         * DEFAULT VALUES
         * -------------------------------------------------
         */
        $terms = [];

        $currentTerm = null;


        /*
         * -------------------------------------------------
         * LOAD CURRENT YEAR'S TERMS
         * -------------------------------------------------
         */
        if ($currentAcademicYear) {

            $academicYearId =
                (int) $currentAcademicYear['id'];


            $terms =
                $this->termModel->forAcademicYear(
                    $academicYearId
                );


            /*
             * Find the active term/sequence.
             */
            foreach ($terms as $term) {

                if (
                    (int) ($term['is_current'] ?? 0) === 1
                ) {
                    $currentTerm = $term;
                    break;
                }
            }
        }


        /*
         * -------------------------------------------------
         * LOAD VIEW
         * -------------------------------------------------
         */
        require __DIR__ .
            '/../../views/admin/academic_years.php';
    }


    /**
     * -----------------------------------------------------
     * CREATE ACADEMIC YEAR
     * -----------------------------------------------------
     */
    public function create(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();


        $name = trim(
            $_POST['name'] ?? ''
        );


        $errors = [];


        /*
         * -------------------------------------------------
         * VALIDATE ACADEMIC YEAR
         * -------------------------------------------------
         */
        if ($name === '') {

            $errors[] =
                'Academic year is required.';

        } elseif (
            !preg_match(
                '/^\d{4}\/\d{4}$/',
                $name
            )
        ) {

            $errors[] =
                'Academic year must use the format YYYY/YYYY.';

        } else {

            [$startYear, $endYear] =
                array_map(
                    'intval',
                    explode('/', $name)
                );


            if (
                $endYear !== $startYear + 1
            ) {

                $errors[] =
                    'Academic year must contain two consecutive years, for example 2026/2027.';
            }
        }


        /*
         * -------------------------------------------------
         * RETURN VALIDATION ERRORS
         * -------------------------------------------------
         */
        if (!empty($errors)) {

            $_SESSION['form_errors'] =
                $errors;

            $_SESSION['old_input'] = [
                'name' => $name
            ];

            self::redirect(
                'academic_years'
            );
        }


        /*
         * -------------------------------------------------
         * CREATE ACADEMIC YEAR
         * -------------------------------------------------
         */
        try {

            $this->academicYearModel->create(
                $name
            );


            $_SESSION['success_message'] =
                "Academic year {$name} created successfully.";

        } catch (Throwable $e) {

            /*
             * Do not expose internal database details
             * directly to the browser.
             */
            $_SESSION['form_errors'] = [
                'Unable to create the academic year. Please check the information and try again.'
            ];

            $_SESSION['old_input'] = [
                'name' => $name
            ];
        }


        self::redirect(
            'academic_years'
        );
    }


    /**
     * -----------------------------------------------------
     * ACTIVATE ACADEMIC YEAR
     * -----------------------------------------------------
     */
    public function activate(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();


        $academicYearId =
            (int) (
                $_POST['academic_year_id']
                ?? 0
            );


        if ($academicYearId <= 0) {

            $_SESSION['form_errors'] = [
                'Please select a valid academic year.'
            ];

            self::redirect(
                'academic_years'
            );
        }


        try {

            $this->academicYearModel->setCurrent(
                $academicYearId
            );


            /*
             * Retrieve the newly active year for the
             * success message.
             */
            $current =
                $this->academicYearModel->find(
                    $academicYearId
                );


            $name =
                $current['name']
                ?? 'academic year';


            $_SESSION['success_message'] =
                "{$name} is now the active academic year.";

        } catch (Throwable $e) {

            /*
             * Keep internal database errors away from
             * the user interface.
             */
            $_SESSION['form_errors'] = [
                'Unable to activate the academic year. Please try again.'
            ];
        }


        self::redirect(
            'academic_years'
        );
    }


    /**
     * -----------------------------------------------------
     * SET CURRENT TERM / SEQUENCE
     * -----------------------------------------------------
     *
     * The selected term/sequence must belong to the
     * currently active academic year.
     */
    public function setCurrentTerm(): void
    {
        AuthMiddleware::requireRole('admin');

        self::startSession();


        $termId =
            (int) (
                $_POST['term_id']
                ?? 0
            );


        if ($termId <= 0) {

            $_SESSION['form_errors'] = [
                'Please select a valid term or sequence.'
            ];

            self::redirect(
                'academic_years'
            );
        }


        try {

            $this->termModel->setCurrent(
                $termId
            );


            /*
             * Retrieve the selected term so that the
             * success message contains the academic context.
             */
            $term =
                $this->termModel->find(
                    $termId
                );


            if (!$term) {

                throw new RuntimeException(
                    'Selected term could not be found.'
                );
            }


            $termName =
                $term['name']
                ?? 'Term';


            $sequence =
                (int) (
                    $term['sequence_number']
                    ?? 0
                );


            $_SESSION['success_message'] =
                "{$termName} — Sequence {$sequence} is now the current academic period.";

        } catch (Throwable $e) {

            $_SESSION['form_errors'] = [
                'Unable to change the current academic period. Please try again.'
            ];
        }


        self::redirect(
            'academic_years'
        );
    }


    /**
     * -----------------------------------------------------
     * PRIVATE HELPERS
     * -----------------------------------------------------
     */


    /**
     * Start session safely.
     */
    private static function startSession(): void
    {
        if (
            session_status() ===
            PHP_SESSION_NONE
        ) {
            session_start();
        }
    }


    /**
     * Redirect to an application action.
     */
    private static function redirect(
        string $action
    ): void {

        header(
            'Location: ' .
            BASE_URL .
            '/index.php?action=' .
            rawurlencode($action)
        );

        exit;
    }
}