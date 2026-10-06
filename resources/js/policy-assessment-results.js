import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import { LEVELS, BADGE_NAMES, badgeSvg, levelPill } from "./policy-assessment/levels";
import { attemptReviewHtml, attemptsTableHtml, formatScore } from "./policy-assessment/attempt-review";
import { roleTagHtml } from "./policy-assessment/assign-role-picker";

("use strict");

/*
 * Policy Assessments › Overview & Results (HR). One row per member of staff
 * (with the badges they hold); a row click opens their policies (each with
 * the role it was assigned through) and attempts, and each attempt opens the
 * answer review (both drawn by
 * policy-assessment/attempt-review.js, shared with the other HR screens).
 * Everything HR or staff typed is escaped before it goes into HTML.
 */
(function () {
    const tableNode = document.querySelector("#policyResultsTable");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const getModal = (selector) => tailwind.Modal.getOrCreateInstance(document.querySelector(selector));
    const employeeModal = getModal("#employeeResultsModal");
    const reviewModal = getModal("#attemptReviewModal");
    let tableContent = null;
    let reviewReturnModal = null;

    const STATUS_LABELS = {
        passed: "Target met",
        locked: "No attempts left",
        in_progress: "In progress",
        overdue: "Overdue",
        failed: "Target not met – retake available",
        pending: "Not started",
    };

    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    };

    const escapeHtml = (value) => {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    const statusPill = (status, label) => {
        const key = STATUS_LABELS[status] ? status : "pending";

        return `<span class="pa-state-pill pa-state-pill--${key}"><span></span>${escapeHtml(label || STATUS_LABELS[key])}</span>`;
    };

    const errorMessage = (error, fallback) => {
        return error?.response?.data?.message || fallback;
    };

    const loadingHtml = '<div class="pa-loading"><span class="pa-loading__dot"></span>Loading...</div>';

    /* ---------- Table ---------- */
    /* Name with department · job title underneath, so the table fits the card. */
    const employeeFormatter = (cell) => {
        const data = cell.getData();
        const tag = data.employee_active ? "" : ' <em class="pa-tag pa-tag--muted">Inactive</em>';
        const meta = [data.department, data.job_title].filter((value) => value).map(escapeHtml).join(" &middot; ");

        return `<div class="pa-cell-stack"><strong>${escapeHtml(data.employee_name)}${tag}</strong>${meta ? `<small>${meta}</small>` : ""}</div>`;
    };

    /* A long label over a narrow count column: the header shows it on two lines.
       The column's plain `title` is still what the exports use. */
    const twoLineTitle = (first, second) => () => `<span class="pa-th-2">${first}<br>${second}</span>`;

    const countFormatter = (tone) => (cell) => {
        const value = Number(cell.getValue() || 0);

        if (value === 0) {
            return '<span class="pa-num pa-num--zero">0</span>';
        }

        return `<span class="pa-num${tone ? ` pa-num--${tone}` : ""}">${value}</span>`;
    };

    const failedFormatter = (cell) => {
        const data = cell.getData();
        const failed = Number(data.failed_count || 0);
        const locked = Number(data.locked_count || 0);

        if (failed === 0) {
            return '<span class="pa-num pa-num--zero">0</span>';
        }

        return `<div class="pa-cell-stack pa-cell-stack--center"><span class="pa-num pa-num--amber">${failed}</span>${locked > 0 ? `<small>${locked} locked</small>` : ""}</div>`;
    };

    /* Three tiny medals (bronze, silver, gold) with how many of each are held. */
    const badgeCountsHtml = (counts) => {
        const parts = LEVELS.map((level) => {
            const count = Number(counts[level] || 0);

            return `<span class="pa-badge-count pa-badge-count--${level}${count > 0 ? "" : " is-zero"}" title="${BADGE_NAMES[level]}">${badgeSvg(level, "sm")}<strong>${count}</strong></span>`;
        });
        const label = LEVELS.map((level) => `${Number(counts[level] || 0)} ${BADGE_NAMES[level].toLowerCase()}`).join(", ");

        return `<span class="pa-badge-counts" role="img" aria-label="Badges: ${escapeHtml(label)}">${parts.join("")}</span>`;
    };

    const badgesFormatter = (cell) => {
        const data = cell.getData();

        return badgeCountsHtml({ beginner: data.badges_beginner, intermediate: data.badges_intermediate, expert: data.badges_expert });
    };

    /* "30 Sep 2026 18:15" as the date with the time underneath, to keep the column narrow. */
    const lastActivityFormatter = (cell) => {
        const value = String(cell.getValue() || "");

        if (value === "") {
            return '<span class="pa-muted">—</span>';
        }

        const cut = value.lastIndexOf(" ");
        const time = cut > 0 && /^\d{1,2}:\d{2}$/.test(value.slice(cut + 1)) ? value.slice(cut + 1) : "";

        return time
            ? `<div class="pa-cell-stack"><span>${escapeHtml(value.slice(0, cut))}</span><small>${escapeHtml(time)}</small></div>`
            : escapeHtml(value);
    };

    const completionFormatter = (cell) => {
        const percent = Math.max(0, Math.min(100, Number(cell.getValue() || 0)));
        const tone = percent >= 100 ? "done" : percent >= 50 ? "mid" : "low";

        return `<div class="pa-mini-progress pa-mini-progress--${tone}"><span class="pa-mini-progress__track"><span style="width: ${percent}%;"></span></span><strong>${percent}%</strong></div>`;
    };

    const buildTable = () => {
        const params = {
            querystr: $("#query").val() || "",
            department: $("#filter_department").val() || "",
            staff: $("#filter_staff").val() || "current",
        };

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#policyResultsTable", {
            ajaxURL: route("policy.assessment.results.list"),
            ajaxParams: params,
            ajaxFiltering: true,
            ajaxSorting: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No staff with policy assignments yet",
            columns: [
                /* Widths add up to under 1,000px so the whole table fits the card at 1440 wide. */
                { title: "Employee", field: "employee_name", headerHozAlign: "left", minWidth: 190, widthGrow: 2, formatter: employeeFormatter },
                { title: "Department", field: "department", visible: false, download: true },
                { title: "Job title", field: "job_title", visible: false, download: true },
                { title: "Assigned", field: "assigned_count", hozAlign: "center", headerHozAlign: "center", width: 88, formatter: countFormatter("") },
                { title: "Target met", titleFormatter: twoLineTitle("Target", "met"), field: "passed_count", hozAlign: "center", headerHozAlign: "center", width: 82, formatter: countFormatter("green") },
                { title: "Target not met", titleFormatter: twoLineTitle("Target", "not met"), field: "failed_count", hozAlign: "center", headerHozAlign: "center", width: 82, formatter: failedFormatter },
                { title: "No attempts left", field: "locked_count", visible: false, download: true },
                { title: "In progress", field: "in_progress_count", visible: false, download: true },
                { title: "Not started", field: "not_started_count", hozAlign: "center", headerHozAlign: "center", width: 104, formatter: countFormatter("") },
                { title: "Overdue", field: "overdue_count", hozAlign: "center", headerHozAlign: "center", width: 88, formatter: countFormatter("red") },
                {
                    title: "Completion",
                    field: "completion_percent",
                    headerHozAlign: "left",
                    minWidth: 124,
                    widthGrow: 1,
                    formatter: completionFormatter,
                    accessorDownload: (value) => `${Number(value || 0)}%`,
                },
                {
                    title: "Badges",
                    field: "badges_total",
                    headerHozAlign: "left",
                    width: 126,
                    formatter: badgesFormatter,
                    accessorDownload: (value, data) => `${Number(data.badges_beginner || 0)} bronze, ${Number(data.badges_intermediate || 0)} silver, ${Number(data.badges_expert || 0)} gold`,
                },
                {
                    title: "Last activity",
                    field: "last_activity_at",
                    headerHozAlign: "left",
                    minWidth: 110,
                    formatter: lastActivityFormatter,
                },
            ],
            rowClick(event, row) {
                openEmployee(row.getData().id);
            },
            renderComplete() {
                refreshIcons();
            },
        });
    };

    buildTable();
    refreshIcons();

    $("#tabulatorFilterForm").on("keypress.pa-results", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.pa-results", buildTable);

    $("#tabulator-html-filter-reset").on("click.pa-results", function () {
        $("#query").val("");
        $("#filter_department").val("");
        $("#filter_staff").val("current");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.pa-results", function () {
        tableContent?.download("csv", "policy-assessment-results.csv");
    });

    $("#tabulator-export-xlsx").on("click.pa-results", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "policy-assessment-results.xlsx", {
            sheetName: "Policy Results",
        });
    });

    /* ---------- One member of staff ---------- */
    const summaryHtml = (summary) => {
        const chips = [
            ["", summary.assigned, "assigned"],
            ["green", summary.passed, "target met"],
            ["red", summary.overdue, "overdue"],
            ["blue", summary.in_progress, "in progress"],
            ["amber", summary.locked, "no attempts left"],
        ].map(([tone, value, label]) => `<span class="pa-review-chip${tone ? ` pa-review-chip--${tone}` : ""}"><strong>${Number(value || 0)}</strong> ${label}</span>`);

        chips.push(`<span class="pa-review-chip"><strong>${Number(summary.completion_percent || 0)}%</strong> complete</span>`);

        const badges = summary.badges || {};
        const total = Number(badges.total || 0);

        chips.push(`<span class="pa-review-chip pa-review-chip--badges">${badgeCountsHtml(badges)}<span><strong>${total}</strong> ${total == 1 ? "badge" : "badges"}</span></span>`);

        return `<div class="pa-review-chips">${chips.join("")}</div>`;
    };

    /* "Badge awarded 12 Oct 2026" with the medal, once the badge is held. */
    const badgeNoteHtml = (assignment) => {
        if (!assignment.has_badge) {
            return "";
        }

        return `<span class="pa-badge-note">${badgeSvg(assignment.level, "sm")}<span>Badge awarded${assignment.badge_awarded_at ? ` ${escapeHtml(assignment.badge_awarded_at)}` : ""}</span></span>`;
    };

    const assignmentMetaHtml = (assignment) => {
        const attempts = assignment.max_attempts !== null && assignment.max_attempts !== undefined ? `${assignment.attempts_count} of ${assignment.max_attempts}` : `${assignment.attempts_count} (unlimited)`;
        const items = [
            ["Due", assignment.due_date ? `<span class="${assignment.is_overdue ? "pa-text-red" : ""}">${escapeHtml(assignment.due_date)}</span>` : "No due date"],
            ["Attempts used", escapeHtml(attempts)],
            ["Best score", escapeHtml(formatScore(assignment.best_score))],
            ["Opened PDF", escapeHtml(assignment.policy_opened_at || "Not yet")],
            ["Confirmed read", escapeHtml(assignment.acknowledged_at || "Not yet")],
        ];

        if (assignment.passed_at) {
            items.push(["Target met on", escapeHtml(assignment.passed_at)]);
        }

        return `<dl class="pa-meta-list">${items.map(([label, value]) => `<div><dt>${label}</dt><dd>${value}</dd></div>`).join("")}</dl>`;
    };

    const employeeBodyHtml = (payload) => {
        const assignments = payload.assignments || [];

        if (!assignments.length) {
            return `${summaryHtml(payload.summary || {})}<p class="pa-empty-note">No live policy assignments.</p>`;
        }

        const cards = assignments.map((assignment) => {
            const inactive = assignment.policy_inactive ? ' <em class="pa-tag pa-tag--muted">Inactive policy</em>' : "";
            /* Category, then the role it was assigned through (nothing for a policy picked by hand). */
            const meta = `${assignment.category ? `<small>${escapeHtml(assignment.category)}</small>` : ""}${roleTagHtml(assignment)}`;

            return `<article class="pa-emp-assignment">
                <header class="pa-emp-assignment__head">
                    <div class="pa-cell-stack"><strong>${escapeHtml(assignment.policy_title)}${inactive}</strong>${meta ? `<span class="pa-cell-meta">${meta}</span>` : ""}</div>
                    <div class="pa-emp-assignment__state">${levelPill(assignment.level)}${statusPill(assignment.display_status, assignment.display_status_label)}${badgeNoteHtml(assignment)}</div>
                </header>
                ${assignmentMetaHtml(assignment)}
                ${attemptsTableHtml(assignment.attempts, { emptyClass: "pa-empty-note pa-empty-note--inline" })}
            </article>`;
        }).join("");

        return `${summaryHtml(payload.summary || {})}<div class="pa-emp-assignments">${cards}</div>`;
    };

    const openEmployee = (employeeId) => {
        reviewReturnModal = null;
        $("#employeeResultsModal .employeeResultsTitle").text("Staff results");
        $("#employeeResultsModal .employeeResultsSubtitle").text("");
        $("#employeeResultsModal .employeeResultsBody").html(loadingHtml);
        $("#employeeResultsModal .employeeResultsProfile").attr("href", route("employee.policy.assessment", employeeId));
        $("#employeeResultsModal .employeeResultsAssign").attr("href", `${route("policy.assessment.assignment")}?assign=1&employee=${encodeURIComponent(employeeId)}`);
        employeeModal.show();

        axios({
            method: "get",
            url: route("policy.assessment.results.employee", employeeId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            const payload = response.data || {};
            const employee = payload.employee || {};
            const subtitle = [employee.job_title, employee.department].filter((value) => value).join(" · ");

            $("#employeeResultsModal .employeeResultsTitle").text(employee.name || "Staff results");
            $("#employeeResultsModal .employeeResultsSubtitle").text(subtitle);
            $("#employeeResultsModal .employeeResultsBody").html(employeeBodyHtml(payload));
            refreshIcons();
        }).catch((error) => {
            $("#employeeResultsModal .employeeResultsBody").html(`<p class="pa-empty-note">${escapeHtml(errorMessage(error, "Could not load this member of staff."))}</p>`);
        });
    };

    /* ---------- Attempt review ---------- */
    const openReview = (attemptId, returnModal) => {
        reviewReturnModal = returnModal;
        $("#attemptReviewModal .attemptReviewTitle").text("Attempt review");
        $("#attemptReviewModal .attemptReviewSubtitle").text("");
        $("#attemptReviewModal .attemptReviewBody").html(loadingHtml);
        $("#attemptReviewModal .attemptReviewBack").toggle(!!returnModal);
        reviewModal.show();

        axios({
            method: "get",
            url: route("policy.assessment.attempt.show", attemptId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            const payload = response.data || {};

            $("#attemptReviewModal .attemptReviewTitle").text(`Attempt ${payload.attempt?.attempt_no ?? ""} — ${payload.policy?.title ?? ""}`);
            $("#attemptReviewModal .attemptReviewSubtitle").text(payload.employee?.name ?? "");
            $("#attemptReviewModal .attemptReviewBody").html(attemptReviewHtml(payload));
            refreshIcons();
        }).catch((error) => {
            $("#attemptReviewModal .attemptReviewBody").html(`<p class="pa-empty-note">${escapeHtml(errorMessage(error, "Could not load this attempt."))}</p>`);
        });
    };

    $("#employeeResultsModal").on("click.pa-results", ".review_attempt_btn", function () {
        const attemptId = $(this).attr("data-id");

        employeeModal.hide();
        openReview(attemptId, employeeModal);
    });

    $("#attemptReviewModal .attemptReviewBack").on("click.pa-results", function () {
        reviewModal.hide();

        if (reviewReturnModal) {
            reviewReturnModal.show();
        }
    });
})();
