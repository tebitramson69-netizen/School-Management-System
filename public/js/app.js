```javascript
/*
 * =========================================================
 * SCHOOL MANAGEMENT SYSTEM
 * public/js/app.js
 *
 * Global vanilla JavaScript
 *
 * Responsibilities:
 * - Flash alerts
 * - Form submission protection
 * - Unsaved POST form protection
 * - Unified mobile dashboard sidebar
 * - Sidebar navigation
 * - Attendance status badges
 * - Grade badges
 * - Print controls
 * - General dashboard interactions
 *
 * PHP 8+ / XAMPP / Apache
 * No frameworks / npm / build tools
 * =========================================================
 */

"use strict";


/* =========================================================
   1. APPLICATION INITIALIZATION
   ========================================================= */

document.addEventListener("DOMContentLoaded", () => {
    initializeAlerts();
    initializeFormSubmitProtection();
    initializeDirtyPostFormWarning();
    initializeStatusBadges();
    initializeGradeBadges();
    initializeDashboardSidebar();
    initializePrintButton();
});


/* =========================================================
   2. ALERTS
   ========================================================= */

function initializeAlerts() {

    const alerts = document.querySelectorAll(
        ".alert-success, .alert.success"
    );

    alerts.forEach((alert) => {

        window.setTimeout(() => {
            dismissElement(alert);
        }, 4500);
    });
}


function dismissElement(element) {

    if (!element || !element.parentNode) {
        return;
    }

    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)"
    ).matches;

    if (reducedMotion) {
        element.remove();
        return;
    }

    element.classList.add("is-dismissing");

    window.setTimeout(() => {

        if (element.parentNode) {
            element.remove();
        }

    }, 220);
}


/* =========================================================
   3. FORM SUBMISSION PROTECTION
   ========================================================= */

function initializeFormSubmitProtection() {

    const forms = document.querySelectorAll("form");

    forms.forEach((form) => {

        form.addEventListener("submit", () => {

            form.dataset.submitted = "true";

            const submitButtons = form.querySelectorAll(
                'button[type="submit"], input[type="submit"]'
            );

            submitButtons.forEach((button) => {

                if (button.disabled) {
                    return;
                }

                button.disabled = true;

                if (
                    button.tagName.toLowerCase() === "input"
                ) {

                    button.dataset.originalValue =
                        button.value || "Submit";

                    button.value = "Saving...";

                } else {

                    button.dataset.originalText =
                        button.textContent.trim();

                    button.textContent = "Saving...";
                }
            });
        });
    });
}


/* =========================================================
   4. UNSAVED POST FORM WARNING
   ========================================================= */

function initializeDirtyPostFormWarning() {

    const postForms = Array.from(
        document.querySelectorAll("form")
    ).filter(isPostForm);

    if (postForms.length === 0) {
        return;
    }


    postForms.forEach((form) => {

        form.dataset.dirty = "false";
        form.dataset.submitted = "false";


        form.addEventListener("input", () => {
            form.dataset.dirty = "true";
        });


        form.addEventListener("change", () => {
            form.dataset.dirty = "true";
        });


        form.addEventListener("submit", () => {

            form.dataset.submitted = "true";
            form.dataset.dirty = "false";
        });
    });


    /*
     * Browser-level warning when the user tries to
     * leave the page with unsaved POST data.
     */
    window.addEventListener("beforeunload", (event) => {

        const dirtyFormExists = postForms.some((form) => {

            return (
                form.dataset.dirty === "true" &&
                form.dataset.submitted !== "true"
            );
        });


        if (!dirtyFormExists) {
            return;
        }


        event.preventDefault();
        event.returnValue = "";
    });


    /*
     * Application-level confirmation for normal links.
     */
    document.addEventListener("click", (event) => {

        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }


        const link = target.closest("a");

        if (!link) {
            return;
        }


        /*
         * Ignore modified clicks.
         */
        if (
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }


        /*
         * Ignore links intentionally opened elsewhere.
         */
        if (link.target === "_blank") {
            return;
        }


        if (link.hasAttribute("download")) {
            return;
        }


        const href = link.getAttribute("href");

        if (
            !href ||
            href === "#" ||
            href.startsWith("#") ||
            href.startsWith("javascript:")
        ) {
            return;
        }


        const dirtyForm = postForms.find((form) => {

            return (
                form.dataset.dirty === "true" &&
                form.dataset.submitted !== "true"
            );
        });


        if (!dirtyForm) {
            return;
        }


        const confirmed = window.confirm(
            "You have unsaved changes. Are you sure you want to leave this page?"
        );


        if (!confirmed) {

            event.preventDefault();
            return;
        }


        dirtyForm.dataset.dirty = "false";
    });
}


function isPostForm(form) {

    const method = (
        form.getAttribute("method") || "get"
    )
        .trim()
        .toLowerCase();


    return method === "post";
}


/* =========================================================
   5. UNIFIED DASHBOARD SIDEBAR
   ========================================================= */

/*
 * Supported dashboard markup:
 *
 * #appSidebar
 * #sidebarToggle
 * #sidebarOverlay
 *
 * This is now the ONLY sidebar implementation.
 */

function initializeDashboardSidebar() {

    const sidebar =
        document.getElementById("appSidebar");

    const toggle =
        document.getElementById("sidebarToggle");

    const overlay =
        document.getElementById("sidebarOverlay");


    /*
     * These elements only exist on dashboard pages.
     */
    if (!sidebar || !toggle) {
        return;
    }


    const mobileBreakpoint = 900;


    function isMobileView() {

        return window.innerWidth <= mobileBreakpoint;
    }


    function openSidebar() {

        sidebar.classList.add("is-open");


        if (overlay) {

            overlay.classList.add("is-visible");

            overlay.setAttribute(
                "aria-hidden",
                "false"
            );
        }


        toggle.setAttribute(
            "aria-expanded",
            "true"
        );


        document.body.classList.add(
            "sidebar-open"
        );
    }


    function closeSidebar() {

        sidebar.classList.remove("is-open");


        if (overlay) {

            overlay.classList.remove(
                "is-visible"
            );

            overlay.setAttribute(
                "aria-hidden",
                "true"
            );
        }


        toggle.setAttribute(
            "aria-expanded",
            "false"
        );


        document.body.classList.remove(
            "sidebar-open"
        );
    }


    function toggleSidebar() {

        if (
            sidebar.classList.contains("is-open")
        ) {

            closeSidebar();

        } else {

            openSidebar();
        }
    }


    /*
     * Toggle button.
     */
    toggle.addEventListener(
        "click",
        toggleSidebar
    );


    /*
     * Overlay closes sidebar.
     */
    if (overlay) {

        overlay.addEventListener(
            "click",
            closeSidebar
        );
    }


    /*
     * Navigation closes sidebar on mobile.
     */
    sidebar.addEventListener(
        "click",
        (event) => {

            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }


            const link = target.closest("a");

            if (!link) {
                return;
            }


            if (isMobileView()) {
                closeSidebar();
            }
        }
    );


    /*
     * Escape closes mobile sidebar.
     */
    document.addEventListener(
        "keydown",
        (event) => {

            if (event.key !== "Escape") {
                return;
            }


            if (
                !sidebar.classList.contains(
                    "is-open"
                )
            ) {
                return;
            }


            closeSidebar();
        }
    );


    /*
     * When returning to desktop, remove mobile state.
     */
    window.addEventListener(
        "resize",
        () => {

            if (!isMobileView()) {
                closeSidebar();
            }
        }
    );


    /*
     * Ensure correct initial accessibility state.
     */
    toggle.setAttribute(
        "aria-expanded",
        sidebar.classList.contains("is-open")
            ? "true"
            : "false"
    );


    if (overlay) {

        overlay.setAttribute(
            "aria-hidden",
            overlay.classList.contains(
                "is-visible"
            )
                ? "false"
                : "true"
        );
    }
}


/* =========================================================
   6. ATTENDANCE STATUS BADGES
   ========================================================= */

function initializeStatusBadges() {

    const tables =
        document.querySelectorAll(".data-table");


    tables.forEach((table) => {

        const statusColumnIndex =
            findColumnIndex(
                table,
                "status"
            );


        if (statusColumnIndex === -1) {
            return;
        }


        const rows =
            table.querySelectorAll(
                "tbody tr"
            );


        rows.forEach((row) => {

            const cells = row.children;


            if (!cells[statusColumnIndex]) {
                return;
            }


            const cell =
                cells[statusColumnIndex];


            /*
             * Never replace form controls.
             */
            if (
                cell.querySelector(
                    "input, select, textarea, button"
                )
            ) {
                return;
            }


            const normalized =
                cell.textContent
                    .trim()
                    .toLowerCase();


            let badgeClass = "";


            if (normalized === "present") {
                badgeClass = "status-present";
            } else if (
                normalized === "absent"
            ) {
                badgeClass = "status-absent";
            } else if (
                normalized === "late"
            ) {
                badgeClass = "status-late";
            }


            if (!badgeClass) {
                return;
            }


            if (
                cell.querySelector(
                    ".status-badge"
                )
            ) {
                return;
            }


            const badge =
                document.createElement("span");


            badge.className =
                "status-badge " +
                badgeClass;


            badge.textContent =
                capitalizeFirstLetter(
                    normalized
                );


            cell.textContent = "";

            cell.appendChild(badge);
        });
    });
}


/* =========================================================
   7. GRADE BADGES
   ========================================================= */

function initializeGradeBadges() {

    const tables =
        document.querySelectorAll(".data-table");


    tables.forEach((table) => {

        const gradeColumnIndex =
            findColumnIndex(
                table,
                "grade"
            );


        if (gradeColumnIndex === -1) {
            return;
        }


        const rows =
            table.querySelectorAll(
                "tbody tr"
            );


        rows.forEach((row) => {

            const cells = row.children;


            if (!cells[gradeColumnIndex]) {
                return;
            }


            const cell =
                cells[gradeColumnIndex];


            /*
             * Never replace form controls.
             */
            if (
                cell.querySelector(
                    "input, select, textarea, button"
                )
            ) {
                return;
            }


            const grade =
                cell.textContent
                    .trim()
                    .toUpperCase();


            if (!/^[A-F]$/.test(grade)) {
                return;
            }


            if (
                cell.querySelector(
                    ".grade-badge"
                )
            ) {
                return;
            }


            const badge =
                document.createElement("span");


            badge.className =
                "grade-badge grade-" +
                grade.toLowerCase();


            badge.textContent = grade;


            cell.textContent = "";

            cell.appendChild(badge);
        });
    });
}


/* =========================================================
   8. TABLE COLUMN HELPER
   ========================================================= */

function findColumnIndex(
    table,
    expectedHeader
) {

    const headerRow =
        table.querySelector(
            "thead tr"
        );


    if (!headerRow) {
        return -1;
    }


    const headerCells =
        headerRow.children;


    const target =
        expectedHeader
            .trim()
            .toLowerCase();


    for (
        let index = 0;
        index < headerCells.length;
        index++
    ) {

        const text =
            headerCells[index]
                .textContent
                .trim()
                .toLowerCase();


        if (text === target) {
            return index;
        }
    }


    return -1;
}


/* =========================================================
   9. PRINT
   ========================================================= */

function initializePrintButton() {

    const tables =
        document.querySelectorAll(
            ".data-table"
        );


    if (tables.length === 0) {
        return;
    }


    const pageHeader =
        document.querySelector(
            ".page-header, .app-page-header"
        );


    if (!pageHeader) {
        return;
    }


    /*
     * Don't create a duplicate print button.
     */
    if (
        pageHeader.querySelector(
            '[data-print-button="true"]'
        )
    ) {
        return;
    }


    const printButton =
        document.createElement("button");


    printButton.type = "button";


    printButton.className =
        "btn btn-secondary no-print";


    printButton.dataset.printButton =
        "true";


    printButton.setAttribute(
        "aria-label",
        "Print this page"
    );


    printButton.textContent = "Print";


    printButton.addEventListener(
        "click",
        () => {
            window.print();
        }
    );


    const actionContainer =
        pageHeader.querySelector(
            ".actions, " +
            ".buttons, " +
            ".page-actions, " +
            ".app-page-actions"
        );


    if (actionContainer) {

        actionContainer.appendChild(
            printButton
        );

    } else {

        pageHeader.appendChild(
            printButton
        );
    }
}


/* =========================================================
   10. FORM RESET
   ========================================================= */

document.addEventListener(
    "reset",
    (event) => {

        const form =
            event.target;


        if (
            !(form instanceof HTMLFormElement)
        ) {
            return;
        }


        if (!isPostForm(form)) {
            return;
        }


        window.setTimeout(() => {

            form.dataset.dirty =
                "false";

            form.dataset.submitted =
                "false";

        }, 0);
    }
);


/* =========================================================
   11. UTILITY
   ========================================================= */

function capitalizeFirstLetter(value) {

    if (!value) {
        return "";
    }


    return (
        value.charAt(0).toUpperCase() +
        value.slice(1).toLowerCase()
    );
}
```
