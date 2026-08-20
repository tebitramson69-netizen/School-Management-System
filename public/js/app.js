/*
 * =========================================================
 * SCHOOL MANAGEMENT SYSTEM
 * app.js
 *
 * Plain vanilla JavaScript.
 * Designed for PHP 8+ / XAMPP / Apache.
 * No frameworks, npm packages or build tools.
 * =========================================================
 */

"use strict";

document.addEventListener("DOMContentLoaded", () => {
    initializeAlerts();
    initializeFormSubmitProtection();
    initializeDirtyPostFormWarning();
    initializeStatusBadges();
    initializeGradeBadges();
    initializePrintButton();
});

/* =========================================================
   1. AUTO-DISMISS SUCCESS ALERTS
   ========================================================= */

function initializeAlerts() {
    const successAlerts = document.querySelectorAll(
        ".alert-success, .alert.success"
    );

    successAlerts.forEach((alert) => {
        /*
         * Keep the alert visible long enough for the user
         * to comfortably read a success message.
         */
        window.setTimeout(() => {
            dismissElement(alert);
        }, 4500);
    });
}

function dismissElement(element) {
    if (!element || !element.parentNode) {
        return;
    }

    /*
     * Respect reduced-motion preferences.
     */
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
   2. FORM SUBMIT PROTECTION
   ========================================================= */

function initializeFormSubmitProtection() {
    const forms = document.querySelectorAll("form");

    forms.forEach((form) => {
        form.addEventListener("submit", (event) => {
            /*
             * Mark the form as successfully submitted so the
             * dirty-form navigation warning doesn't appear.
             */
            form.dataset.submitted = "true";

            const submitButtons = form.querySelectorAll(
                'button[type="submit"], input[type="submit"]'
            );

            submitButtons.forEach((button) => {
                /*
                 * Avoid changing an already disabled button.
                 */
                if (button.disabled) {
                    return;
                }

                button.disabled = true;

                if (button.tagName.toLowerCase() === "input") {
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
   3. UNSAVED POST FORM WARNING
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

        /*
         * Track changes to inputs.
         */
        form.addEventListener("input", () => {
            form.dataset.dirty = "true";
        });

        form.addEventListener("change", () => {
            form.dataset.dirty = "true";
        });

        /*
         * A successful submit should clear the warning.
         */
        form.addEventListener("submit", () => {
            form.dataset.submitted = "true";
            form.dataset.dirty = "false";
        });
    });

    /*
     * Browser-level warning when the user closes/reloads
     * the page or enters another URL.
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

        /*
         * Modern browsers ignore custom text and display
         * their own standard confirmation message.
         */
        event.returnValue = "";
    });

    /*
     * Also protect normal internal navigation links.
     */
    document.addEventListener("click", (event) => {
        const link = event.target.closest("a");

        if (!link) {
            return;
        }

        /*
         * Don't interfere with modifier-clicks, downloads,
         * anchors, javascript links or new-tab navigation.
         */
        if (
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

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
        } else {
            /*
             * Prevent the beforeunload handler from creating
             * a second warning after the user has confirmed.
             */
            dirtyForm.dataset.dirty = "false";
        }
    });
}

function isPostForm(form) {
    const method = (
        form.getAttribute("method") || "get"
    ).trim().toLowerCase();

    return method === "post";
}


/* =========================================================
   4. STATUS BADGES
   =========================================================
 *
 * Finds a table column by looking for a header whose text is
 * "Status", then converts plain text:
 *
 * Present -> green pill
 * Absent  -> red pill
 * Late    -> amber pill
 *
 * Existing markup does not need to change.
 */

function initializeStatusBadges() {
    const tables = document.querySelectorAll(".data-table");

    tables.forEach((table) => {
        const statusColumnIndex = findColumnIndex(
            table,
            "status"
        );

        if (statusColumnIndex === -1) {
            return;
        }

        const rows = table.querySelectorAll("tbody tr");

        rows.forEach((row) => {
            const cells = row.children;

            if (!cells[statusColumnIndex]) {
                return;
            }

            const cell = cells[statusColumnIndex];

            /*
             * Do not modify cells containing form controls.
             * Attendance entry radio buttons should remain usable.
             */
            if (
                cell.querySelector(
                    "input, select, textarea, button"
                )
            ) {
                return;
            }

            const text = cell.textContent.trim();

            const normalized = text.toLowerCase();

            let badgeClass = "";

            if (normalized === "present") {
                badgeClass = "status-present";
            } else if (normalized === "absent") {
                badgeClass = "status-absent";
            } else if (normalized === "late") {
                badgeClass = "status-late";
            }

            if (!badgeClass) {
                return;
            }

            /*
             * Avoid wrapping an existing badge.
             */
            if (cell.querySelector(".status-badge")) {
                return;
            }

            const badge = document.createElement("span");

            badge.className =
                "status-badge " + badgeClass;

            badge.textContent = capitalizeFirstLetter(
                normalized
            );

            cell.textContent = "";
            cell.appendChild(badge);
        });
    });
}


/* =========================================================
   5. GRADE BADGES
   =========================================================
 *
 * Finds a table column by matching the header "Grade".
 *
 * A -> green
 * B -> blue
 * C -> neutral
 * D -> amber
 * E -> orange
 * F -> red
 */

function initializeGradeBadges() {
    const tables = document.querySelectorAll(".data-table");

    tables.forEach((table) => {
        const gradeColumnIndex = findColumnIndex(
            table,
            "grade"
        );

        if (gradeColumnIndex === -1) {
            return;
        }

        const rows = table.querySelectorAll("tbody tr");

        rows.forEach((row) => {
            const cells = row.children;

            if (!cells[gradeColumnIndex]) {
                return;
            }

            const cell = cells[gradeColumnIndex];

            /*
             * Don't touch editable grade/score controls.
             */
            if (
                cell.querySelector(
                    "input, select, textarea, button"
                )
            ) {
                return;
            }

            const grade = cell.textContent
                .trim()
                .toUpperCase();

            if (!/^[A-F]$/.test(grade)) {
                return;
            }

            if (cell.querySelector(".grade-badge")) {
                return;
            }

            const badge = document.createElement("span");

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
   6. FIND TABLE COLUMN
   ========================================================= */

function findColumnIndex(table, expectedHeader) {
    const headerRows = table.querySelectorAll("thead tr");

    if (headerRows.length === 0) {
        return -1;
    }

    /*
     * Use the first header row.
     */
    const headerCells = headerRows[0].children;

    const target = expectedHeader
        .trim()
        .toLowerCase();

    for (let index = 0; index < headerCells.length; index++) {
        const text = headerCells[index]
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
   7. PRINT BUTTON
   =========================================================
 *
 * Whenever .data-table exists, inject a Print button beside
 * the page header.
 *
 * The button is generated only if one does not already exist.
 */

function initializePrintButton() {
    const tables = document.querySelectorAll(".data-table");

    if (tables.length === 0) {
        return;
    }

    const pageHeader = document.querySelector(
        ".page-header"
    );

    if (!pageHeader) {
        return;
    }

    /*
     * Don't create duplicate print controls.
     */
    const existingPrintButton = pageHeader.querySelector(
        '[data-print-button="true"]'
    );

    if (existingPrintButton) {
        return;
    }

    const printButton = document.createElement("button");

    printButton.type = "button";
    printButton.className = "btn btn-secondary no-print";
    printButton.dataset.printButton = "true";
    printButton.setAttribute(
        "aria-label",
        "Print this page"
    );

    printButton.textContent = "Print";

    printButton.addEventListener("click", () => {
        window.print();
    });

    /*
     * If the page header already contains an action/button
     * container, put the Print button there.
     */
    const actionContainer =
        pageHeader.querySelector(
            ".actions, .buttons"
        );

    if (actionContainer) {
        actionContainer.appendChild(printButton);
        return;
    }

    /*
     * Otherwise append directly to the page header.
     */
    pageHeader.appendChild(printButton);
}


/* =========================================================
   8. SMALL UTILITY FUNCTIONS
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


/* =========================================================
   9. OPTIONAL FORM RESET HANDLING
   =========================================================
 *
 * If a POST form is reset, consider it clean again.
 */

document.addEventListener("reset", (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (!isPostForm(form)) {
        return;
    }

    /*
     * Reset happens after the reset event has fired.
     */
    window.setTimeout(() => {
        form.dataset.dirty = "false";
        form.dataset.submitted = "false";
    }, 0);
});