<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   USER INFORMATION
   ========================================================= */

$currentRole = (string) (
    $_SESSION['role'] ?? ''
); 
$userName = trim(
    (string) (
        $_SESSION['full_name']
        ?? $_SESSION['name']
        ?? $_SESSION['email']
        ?? 'User'
    )
);


/*
 * User initials
 */
$nameParts = preg_split(
    '/\s+/',
    $userName,
    -1,
    PREG_SPLIT_NO_EMPTY
);

if (
    is_array($nameParts) &&
    count($nameParts) >= 2
) {
    $userInitials = strtoupper(
        substr($nameParts[0], 0, 1) .
        substr(
            $nameParts[count($nameParts) - 1],
            0,
            1
        )
    );
} else {
    $userInitials = strtoupper(
        substr($userName, 0, 2)
    );
}


/*
 * Friendly role name
 */
$roleLabels = [
    'admin'   => 'Administrator',
    'teacher' => 'Teacher',
    'student' => 'Student',
    'parent'  => 'Parent'
];

$roleLabel =
    $roleLabels[$currentRole]
    ?? ucfirst(
        $currentRole ?: 'User'
    );


/* =========================================================
   SCHOOL INFORMATION
   ========================================================= */

$schoolName = trim(
    (string) (
        $school['school_name']
        ?? 'School Management System'
    )
);

$schoolMotto = trim(
    (string) (
        $school['motto']
        ?? ''
    )
);

$logoPath =
    $school['logo_path']
    ?? null;


/*
 * School initials fallback.
 */
$schoolInitials = 'SMS';

$schoolWords = preg_split(
    '/\s+/',
    $schoolName,
    -1,
    PREG_SPLIT_NO_EMPTY
);

if (
    is_array($schoolWords) &&
    count($schoolWords) >= 2
) {

    $schoolInitials = strtoupper(
        substr($schoolWords[0], 0, 1) .
        substr($schoolWords[1], 0, 1)
    );

} elseif ($schoolName !== '') {

    $schoolInitials = strtoupper(
        substr($schoolName, 0, 3)
    );
}


/* =========================================================
   OPTIONAL ACADEMIC CONTEXT
   =========================================================
 *
 * These variables can be supplied by a controller/layout
 * later without requiring this header to change.
 */

$headerAcademicYear =
    $academicYear
    ?? $currentAcademicYear
    ?? null;

$headerCurrentTerm =
    $currentTerm
    ?? null;


/*
 * Academic year label
 */
$academicYearLabel = '';

if (
    is_array($headerAcademicYear) &&
    !empty($headerAcademicYear['name'])
) {
    $academicYearLabel =
        (string) $headerAcademicYear['name'];
}


/*
 * Academic period label
 */
$academicPeriodLabel = '';

if (
    is_array($headerCurrentTerm)
) {

    $termName =
        trim(
            (string) (
                $headerCurrentTerm['name']
                ?? ''
            )
        );

    $sequenceNumber =
        (int) (
            $headerCurrentTerm['sequence_number']
            ?? 0
        );

    if (
        $termName !== '' &&
        $sequenceNumber > 0
    ) {

        $academicPeriodLabel =
            $termName .
            ' · Sequence ' .
            $sequenceNumber;
    }
}

?>

<header class="app-topbar">

    <!-- =====================================================
         LEFT SIDE
         ===================================================== -->

    <div class="app-topbar-left">


        <!-- Mobile navigation toggle -->

        <button
            type="button"
            class="sidebar-toggle"
            id="sidebarToggle"
            aria-label="Open navigation menu"
            aria-controls="appSidebar"
            aria-expanded="false"
        >

            <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                aria-hidden="true"
            >
                <line x1="4" y1="6" x2="20" y2="6"></line>
                <line x1="4" y1="12" x2="20" y2="12"></line>
                <line x1="4" y1="18" x2="20" y2="18"></line>
            </svg>

        </button>


        <!-- School identity -->

        <a
            href="<?= htmlspecialchars(
                BASE_URL .
                '/index.php?action=' .
                rawurlencode(
                    $currentRole === 'admin'
                        ? 'admin_dashboard'
                        : (
                            $currentRole === 'teacher'
                                ? 'teacher_dashboard'
                                : (
                                    $currentRole === 'student'
                                        ? 'student_dashboard'
                                        : (
                                            $currentRole === 'parent'
                                                ? 'parent_dashboard'
                                                : 'login'
                                        )
                                )
                        )
                ),
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            class="school-identity"
            aria-label="<?= htmlspecialchars(
                $schoolName,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

            <div class="school-identity-logo">

                <?php if (!empty($logoPath)): ?>

                    <img
                        src="<?= htmlspecialchars(
                            BASE_URL .
                            '/' .
                            ltrim(
                                (string) $logoPath,
                                '/'
                            ),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $schoolName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?> logo"
                    >

                <?php else: ?>

                    <?= htmlspecialchars(
                        $schoolInitials,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                <?php endif; ?>

            </div>


            <div class="school-identity-text">

                <span class="school-identity-name">
                    <?= htmlspecialchars(
                        $schoolName,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </span>


                <?php if ($schoolMotto !== ''): ?>

                    <span class="school-identity-motto">
                        <?= htmlspecialchars(
                            $schoolMotto,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                <?php endif; ?>

            </div>

        </a>

    </div>


    <!-- =====================================================
         RIGHT SIDE
         ===================================================== -->

    <div class="app-topbar-right">


        <!-- =================================================
             ACADEMIC CONTEXT
             ================================================= -->

        <?php if (
            $academicYearLabel !== '' ||
            $academicPeriodLabel !== ''
        ): ?>

            <div
                class="topbar-academic-context"
                aria-label="Current academic period"
            >

                <?php if (
                    $academicYearLabel !== ''
                ): ?>

                    <span class="topbar-academic-year">
                        <?= htmlspecialchars(
                            $academicYearLabel,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                <?php endif; ?>


                <?php if (
                    $academicPeriodLabel !== ''
                ): ?>

                    <span
                        class="topbar-academic-period"
                    >
                        <?= htmlspecialchars(
                            $academicPeriodLabel,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                <?php endif; ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             NOTIFICATIONS
             ================================================= -->

        <button
            type="button"
            class="notification-button"
            aria-label="Notifications"
            title="Notifications"
        >

            <svg
                width="19"
                height="19"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.9"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path
                    d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
                ></path>

                <path
                    d="M13.73 21a2 2 0 0 1-3.46 0"
                ></path>
            </svg>


            <span
                class="notification-count"
                hidden
            >
                0
            </span>

        </button>


        <!-- =================================================
             USER PROFILE
             ================================================= -->

        <div
            class="user-menu"
            title="<?= htmlspecialchars(
                $roleLabel,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

            <div class="user-menu-avatar">

                <?= htmlspecialchars(
                    $userInitials,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>


            <div class="user-menu-text">

                <span class="user-menu-name">

                    <?= htmlspecialchars(
                        $userName,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </span>


                <span class="user-menu-role">

                    <?= htmlspecialchars(
                        $roleLabel,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </span>

            </div>


            <span
                class="user-menu-chevron"
                aria-hidden="true"
            >

                <svg
                    width="15"
                    height="15"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >

                    <polyline
                        points="6 9 12 15 18 9"
                    ></polyline>

                </svg>

            </span>

        </div>


        <!-- =================================================
             LOGOUT
             ================================================= -->

        <a
            href="<?= htmlspecialchars(
                BASE_URL .
                '/index.php?action=logout',
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            class="header-logout"
            aria-label="Log out of your account"
        >

            <svg
                width="17"
                height="17"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.9"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path
                    d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"
                ></path>

                <polyline
                    points="16 17 21 12 16 7"
                ></polyline>

                <line
                    x1="21"
                    y1="12"
                    x2="9"
                    y2="12"
                ></line>
            </svg>


            <span>
                Logout
            </span>

        </a>

    </div>

</header>
