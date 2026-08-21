/*
 * =========================================================
 * SCHOOL MANAGEMENT SYSTEM
 * public/js/app.js
 *
 * Global vanilla JavaScript
 * PHP 8+ / XAMPP / Apache
 * No frameworks / npm / build tools
 *
 * Responsibilities:
 * - Alerts
 * - Form submission protection
 * - Unsaved POST form protection
 * - Mobile sidebar
 * - Status badges
 * - Grade badges
 * - Print controls
 * - Basic dashboard interactions
 * =========================================================
 */

"use strict";

document.addEventListener("DOMContentLoaded", () => {
    initializeAlerts();
    initializeFormSubmitProtection();
    initializeDirtyPostFormWarning();
    initializeStatusBadges();
    initializeGradeBadges();
    initializeMobileSidebar();
    initializeSidebarLinks();
    initializePrintButton();
});

/* =========================================================
   1. ALERTS
   ========================================================= */

function initializeAlerts() {
    const successAlerts = document.querySelectorAll(
        ".alert-success, .alert.success"
    );

    successAlerts.forEach((alert) => {
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
   2. FORM SUBMIT PROTECTION
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

    document.addEventListener("click", (event) => {
        const link = event.target.closest("a");

        if (!link) {
            return;
        }

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
            return;
        }

        dirtyForm.dataset.dirty = "false";
    });
}

function isPostForm(form) {
    const method = (
        form.getAttribute("method") || "get"
    ).trim().toLowerCase();

    return method === "post";
}

/* =========================================================
   4. MOBILE SIDEBAR
   ========================================================= */

function initializeMobileSidebar() {
    const sidebar = document.querySelector(
        ".sidebar, .dashboard-sidebar"
    );

    const toggle = document.querySelector(
        "[data-sidebar-toggle]"
    );

    const overlay = document.querySelector(
        "[data-sidebar-overlay]"
    );

    if (!sidebar || !toggle) {
        return;
    }

    toggle.addEventListener("click", () => {
        const isOpen = sidebar.classList.toggle("is-open");

        toggle.setAttribute(
            "aria-expanded",
            isOpen ? "true" : "false"
        );

        if (overlay) {
            overlay.classList.toggle("is-visible", isOpen);
        }

        document.body.classList.toggle(
            "sidebar-open",
            isOpen
        );
    });

    if (overlay) {
        overlay.addEventListener("click", () => {
            closeMobileSidebar(
                sidebar,
                toggle,
                overlay
            );
        });
    }

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") {
            return;
        }

        if (!sidebar.classList.contains("is-open")) {
            return;
        }

        closeMobileSidebar(
            sidebar,
            toggle,
            overlay
        );
    });
}

function closeMobileSidebar(sidebar, toggle, overlay) {
    sidebar.classList.remove("is-open");

    toggle.setAttribute(
        "aria-expanded",
        "false"
    );

    if (overlay) {
        overlay.classList.remove("is-visible");
    }

    document.body.classList.remove("sidebar-open");
}

/* =========================================================
   5. SIDEBAR NAVIGATION
   ========================================================= */

function initializeSidebarLinks() {
    const links = document.querySelectorAll(
        ".sidebar a, .dashboard-sidebar a"
    );

    links.forEach((link) => {
        link.addEventListener("click", () => {
            const sidebar = document.querySelector(
                ".sidebar.is-open, .dashboard-sidebar.is-open"
            );

            if (!sidebar) {
                return;
            }

            const toggle = document.querySelector(
                "[data-sidebar-toggle]"
            );

            const overlay = document.querySelector(
                "[data-sidebar-overlay]"
            );

            if (toggle) {
                closeMobileSidebar(
                    sidebar,
                    toggle,
                    overlay
                );
            }
        });
    });
}

/* =========================================================
   6. ATTENDANCE STATUS BADGES
   ========================================================= */

function initializeStatusBadges() {
    const tables = document.querySelectorAll(
        ".data-table"
    );

    tables.forEach((table) => {
        const statusColumnIndex = findColumnIndex(
            table,
            "status"
        );

        if (statusColumnIndex === -1) {
            return;
        }

        const rows = table.querySelectorAll(
            "tbody tr"
        );

        rows.forEach((row) => {
            const cells = row.children;

            if (!cells[statusColumnIndex]) {
                return;
            }

            const cell = cells[statusColumnIndex];

            if (
                cell.querySelector(
                    "input, select, textarea, button"
                )
            ) {
                return;
            }

            const normalized = cell.textContent
                .trim()
                .toLowerCase();

            let badgeClass = "";

            if (normalized === "present") {
                badgeClass = "status-present";
            }

            if (normalized === "absent") {
                badgeClass = "status-absent";
            }

            if (normalized === "late") {
                badgeClass = "status-late";
            }

            if (!badgeClass) {
                return;
            }

            if (cell.querySelector(".status-badge")) {
                return;
            }

            const badge = document.createElement("span");

            badge.className =
                "status-badge " + badgeClass;

            badge.textContent =
                capitalizeFirstLetter(normalized);

            cell.textContent = "";
            cell.appendChild(badge);
        });
    });
}

/* =========================================================
   7. GRADE BADGES
   ========================================================= */

function initializeGradeBadges() {
    const tables = document.querySelectorAll(
        ".data-table"
    );

    tables.forEach((table) => {
        const gradeColumnIndex = findColumnIndex(
            table,
            "grade"
        );

        if (gradeColumnIndex === -1) {
            return;
        }

        const rows = table.querySelectorAll(
            "tbody tr"
        );

        rows.forEach((row) => {
            const cells = row.children;

            if (!cells[gradeColumnIndex]) {
                return;
            }

            const cell = cells[gradeColumnIndex];

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
   8. TABLE COLUMN HELPER
   ========================================================= */

function findColumnIndex(table, expectedHeader) {
    const headerRow = table.querySelector(
        "thead tr"
    );

    if (!headerRow) {
        return -1;
    }

    const headerCells = headerRow.children;

    const target = expectedHeader
        .trim()
        .toLowerCase();

    for (
        let index = 0;
        index < headerCells.length;
        index++
    ) {
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
   9. PRINT
   ========================================================= */

function initializePrintButton() {
    const tables = document.querySelectorAll(
        ".data-table"
    );

    if (tables.length === 0) {
        return;
    }

    const pageHeader = document.querySelector(
        ".page-header"
    );

    if (!pageHeader) {
        return;
    }

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

    printButton.dataset.printButton = "true";

    printButton.setAttribute(
        "aria-label",
        "Print this page"
    );

    printButton.textContent = "Print";

    printButton.addEventListener("click", () => {
        window.print();
    });

    const actionContainer =
        pageHeader.querySelector(
            ".actions, .buttons, .page-actions"
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
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (!isPostForm(form)) {
            return;
        }

        window.setTimeout(() => {
            form.dataset.dirty = "false";
            form.dataset.submitted = "false";
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