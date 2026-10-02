import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { badgeSvg, levelLabel, levelPill } from "./policy-assessment/levels";
import { assignedLevelHtml, initLevelPicker } from "./policy-assessment/assign-level-picker";
import { assignedRolesHtml, initRolePicker, roleTagHtml } from "./policy-assessment/assign-role-picker";
import { attemptReviewHtml, attemptsTableHtml, formatScore } from "./policy-assessment/attempt-review";

("use strict");

/*
 * Policy Assessments › Assignments (HR). Table of every assignment with its
 * level (and the badge, once earned) and the role it was assigned through, the
 * Assign modal (also opened by ?assign=1) with its level cards and its role
 * picker (policy-assessment/assign-role-picker.js: a role fills the policy
 * picker, and the picker is what is posted), edit due date / note, allow a retake,
 * archive / restore, and the attempts + attempt review modals (drawn by
 * policy-assessment/attempt-review.js, shared with the other HR screens).
 * Everything HR or staff typed is escaped before it goes into HTML.
 */
(function () {
    const tableNode = document.querySelector("#policyAssignmentTable");

    if (!tableNode) {
        return;
    }

    const pageNode = document.querySelector("#siteSettingsPage");
    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const getModal = (selector) => tailwind.Modal.getOrCreateInstance(document.querySelector(selector));
    const successModal = getModal("#successModal");
    const warningModal = getModal("#warningModal");
    const confirmModal = getModal("#confirmModal");
    const assignModal = getModal("#assignModal");
    const editModal = getModal("#editAssignmentModal");
    const attemptsModal = getModal("#attemptsModal");
    const reviewModal = getModal("#attemptReviewModal");
    let tableContent = null;
    let reviewReturnModal = null;

    const STATUS_LABELS = {
        passed: "Passed",
        locked: "No attempts left",
        in_progress: "In progress",
        overdue: "Overdue",
        failed: "Failed – retake available",
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

    const plural = (count, one, many) => `${count} ${count == 1 ? one : many}`;

    const statusPill = (status, label) => {
        const key = STATUS_LABELS[status] ? status : "pending";

        return `<span class="pa-state-pill pa-state-pill--${key}"><span></span>${escapeHtml(label || STATUS_LABELS[key])}</span>`;
    };

    const clearErrors = ($form) => {
        $form.find(".acc__input-error").text("");
        $form.find(".border-danger").removeClass("border-danger");
    };

    /* Laravel keys array errors as "employee_ids.0"; the markup uses the base name. */
    const showErrors = ($form, errors) => {
        clearErrors($form);

        Object.entries(errors || {}).forEach(([key, value]) => {
            const base = String(key).split(".")[0];
            const message = Array.isArray(value) ? value[0] : value;
            const $error = $form.find(`.error-${base}`);

            $form.find(`.${base}`).addClass("border-danger");

            if ($error.text() === "") {
                $error.text(message);
            }
        });
    };

    const setBusy = (selector, busy) => {
        const button = document.querySelector(selector);

        if (!button) {
            return;
        }

        button.disabled = busy;
        const spinner = button.querySelector(".ss-spinner");

        if (spinner) {
            spinner.style.cssText = busy ? "display: inline-block;" : "display: none;";
        }
    };

    const showSuccess = (title, description, descriptionHtml = null) => {
        $("#successModal .successModalTitle").text(title);

        if (descriptionHtml !== null) {
            $("#successModal .successModalDesc").html(descriptionHtml);
        } else {
            $("#successModal .successModalDesc").text(description);
        }

        successModal.show();
    };

    const showWarning = (title, description) => {
        $("#warningModal .warningModalTitle").text(title);
        $("#warningModal .warningModalDesc").text(description);
        warningModal.show();
    };

    const errorMessage = (error, fallback) => {
        return error?.response?.data?.message || fallback;
    };

    const showConfirm = (title, description, action, recordID) => {
        $("#confirmModal .confModTitle").text(title);
        $("#confirmModal .confModDesc").text(description);
        $("#confirmModal .agreeWith").attr("data-id", recordID);
        $("#confirmModal .agreeWith").attr("data-action", action);
        confirmModal.show();
    };

    /* ---------- TomSelect ---------- */
    const tomBase = {
        dropdownParent: "body",
        dropdownClass: "ts-dropdown ss-settings-tom-dropdown lcc-tom-float pa-tom-dropdown",
        copyClassesToDropdown: false,
        create: false,
        maxOptions: null,
        allowEmptyOption: true,
        plugins: { dropdown_input: {} },
    };
    const tomMulti = {
        ...tomBase,
        allowEmptyOption: false,
        plugins: {
            dropdown_input: {},
            remove_button: { title: "Remove" },
        },
    };

    const filterEmployee = new TomSelect("#filter_employee", { ...tomBase, placeholder: "All staff" });
    const assignEmployees = new TomSelect("#pa_assign_employees", { ...tomMulti, placeholder: "Search staff by name..." });

    /* "Please choose at least one member of staff" goes once somebody is chosen. */
    assignEmployees.on("change", () => {
        if (assignEmployees.items.length) {
            $("#assignForm .error-employee_ids").text("");
        }
    });
    const assignPolicies = new TomSelect("#pa_assign_policies", { ...tomMulti, placeholder: "Search policies..." });
    /* Many roles: the role picker is one searchable multi-select instead of cards. */
    const roleSelectNode = document.querySelector("#assignForm [data-role-select]");
    const assignRoles = roleSelectNode ? new TomSelect(roleSelectNode, { ...tomMulti, placeholder: "Search roles..." }) : null;
    const rolePicker = initRolePicker(document.getElementById("assignForm"), assignPolicies, assignRoles);
    const levelPicker = initLevelPicker(document.getElementById("assignForm"), assignPolicies, rolePicker.showLevel);

    /* ---------- Table ---------- */
    /* Level pill, plus the medal once the badge for this policy and level is held. */
    const levelFormatter = (cell) => {
        const data = cell.getData();
        const medal = data.has_badge ? badgeSvg(data.level, "sm") : "";

        return `<div class="pa-level-cell">${levelPill(data.level)}${medal}</div>`;
    };

    const withLevel = (data) => {
        const label = data.level_label || levelLabel(data.level);

        return label ? `${data.policy_title} (${label})` : data.policy_title;
    };
    const employeeFormatter = (cell) => {
        const data = cell.getData();
        const meta = [data.job_title, data.department].filter((value) => value).map(escapeHtml).join(" &middot; ");
        const archived = data.employee_archived ? ' <em class="pa-tag pa-tag--muted">Archived</em>' : "";

        return `<div class="pa-cell-stack"><strong>${escapeHtml(data.employee_name)}${archived}</strong>${meta ? `<small>${meta}</small>` : ""}</div>`;
    };

    const policyFormatter = (cell) => {
        const data = cell.getData();
        let tags = "";

        if (data.policy_archived) {
            tags += ' <em class="pa-tag pa-tag--red">Policy deleted</em>';
        } else if (data.policy_inactive) {
            tags += ' <em class="pa-tag pa-tag--muted">Inactive</em>';
        }

        /* Category, then the role it was assigned through (nothing for a policy picked by hand). */
        const meta = `${data.category ? `<small>${escapeHtml(data.category)}</small>` : ""}${roleTagHtml(data)}`;

        return `<div class="pa-cell-stack"><strong>${escapeHtml(data.policy_title)}${tags}</strong>${meta ? `<span class="pa-cell-meta">${meta}</span>` : ""}</div>`;
    };

    const assignedFormatter = (cell) => {
        const data = cell.getData();

        return `<div class="pa-cell-stack"><span>${escapeHtml(data.assigned_at || "—")}</span>${data.assigned_by ? `<small>by ${escapeHtml(data.assigned_by)}</small>` : ""}</div>`;
    };

    const dueFormatter = (cell) => {
        const data = cell.getData();

        if (!data.due_date) {
            return '<span class="pa-muted">—</span>';
        }

        if (data.is_overdue) {
            return `<span class="pa-due is-overdue"><i data-lucide="alarm-clock"></i>${escapeHtml(data.due_date)}</span>`;
        }

        return `<span class="pa-due">${escapeHtml(data.due_date)}</span>`;
    };

    const statusFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at) {
            return '<span class="pa-state-pill pa-state-pill--archived"><span></span>Archived</span>';
        }

        return statusPill(data.display_status, data.display_status_label);
    };

    const attemptsText = (data) => {
        return data.max_attempts !== null && data.max_attempts !== undefined ? `${data.attempts_count} / ${data.max_attempts}` : `${data.attempts_count}`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();
        const id = escapeHtml(data.id);

        if (data.deleted_at) {
            return `<div class="pa-row-actions"><button data-id="${id}" type="button" class="restore_btn ss-row-action ss-row-action--restore" title="Restore assignment" aria-label="Restore assignment"><i data-lucide="rotate-cw"></i></button></div>`;
        }

        const actions = [
            `<button data-id="${id}" data-employee="${escapeHtml(data.employee_id)}" type="button" class="attempts_btn ss-row-action ss-row-action--view" title="View attempts" aria-label="View attempts"><i data-lucide="list-checks"></i></button>`,
            `<button data-id="${id}" type="button" class="edit_btn ss-row-action ss-row-action--edit" title="Edit due date or note" aria-label="Edit due date or note"><i data-lucide="pencil"></i></button>`,
        ];

        if (data.can_retake) {
            actions.push(`<button data-id="${id}" type="button" class="retake_btn ss-row-action pa-row-action--retake" title="Allow one more attempt" aria-label="Allow one more attempt"><i data-lucide="rotate-ccw"></i></button>`);
        }

        actions.push(`<button data-id="${id}" type="button" class="delete_btn ss-row-action ss-row-action--delete" title="Archive assignment" aria-label="Archive assignment"><i data-lucide="trash-2"></i></button>`);

        return `<div class="pa-row-actions">${actions.join("")}</div>`;
    };

    const buildTable = () => {
        const params = {
            querystr: $("#query").val() || "",
            employee: filterEmployee.getValue() || "",
            category: $("#filter_category").val() || "",
            policy: $("#filter_policy").val() || "",
            role: $("#filter_role").val() || "",
            level: $("#filter_level").val() || "",
            status: $("#status").val() || "",
        };

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#policyAssignmentTable", {
            ajaxURL: route("policy.assessment.assignment.list"),
            ajaxParams: params,
            ajaxFiltering: true,
            ajaxSorting: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            /* Columns that do not fit move into the row's detail list (the + toggle), lowest
               priority first, so nothing hides under Actions. Phones start with it open.
               download: true keeps a collapsed column in the CSV/XLSX export. */
            responsiveLayout: "collapse",
            responsiveLayoutCollapseStartOpen: window.matchMedia("(max-width: 767px)").matches,
            placeholder: "No matching assignments found",
            columns: [
                { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", headerSort: false, resizable: false, responsive: 0, download: false, print: false },
                { title: "Employee", field: "employee_name", headerHozAlign: "left", minWidth: 160, widthGrow: 1.6, responsive: 0, formatter: employeeFormatter },
                { title: "Job title", field: "job_title", visible: false, download: true },
                { title: "Department", field: "department", visible: false, download: true },
                { title: "Policy", field: "policy_title", headerHozAlign: "left", minWidth: 170, widthGrow: 1.8, responsive: 2, download: true, formatter: policyFormatter },
                { title: "Category", field: "category", visible: false, download: true },
                { title: "Role", field: "role_name", visible: false, download: true },
                {
                    title: "Level",
                    field: "level",
                    headerHozAlign: "left",
                    minWidth: 160,
                    responsive: 2,
                    download: true,
                    formatter: levelFormatter,
                    accessorDownload: (value, data) => data.level_label || levelLabel(value),
                },
                { title: "Badge awarded", field: "badge_awarded_at", visible: false, download: true },
                { title: "Assigned", field: "assigned_at", headerHozAlign: "left", minWidth: 116, responsive: 7, download: true, formatter: assignedFormatter },
                { title: "Due date", field: "due_date", headerHozAlign: "left", minWidth: 136, responsive: 4, download: true, formatter: dueFormatter },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    minWidth: 204,
                    responsive: 3,
                    download: true,
                    formatter: statusFormatter,
                    accessorDownload: (value, data) => (data.deleted_at ? "Archived" : data.display_status_label),
                },
                {
                    title: "Attempts",
                    field: "attempts_count",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 100,
                    responsive: 5,
                    download: true,
                    formatter: (cell) => escapeHtml(attemptsText(cell.getData())),
                    accessorDownload: (value, data) => attemptsText(data),
                },
                {
                    title: "Best score",
                    field: "best_score",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 110,
                    responsive: 6,
                    download: true,
                    formatter: (cell) => escapeHtml(formatScore(cell.getValue())),
                },
                {
                    title: "Opened PDF",
                    field: "policy_opened_at",
                    headerHozAlign: "left",
                    minWidth: 124,
                    responsive: 8,
                    download: true,
                    formatter: (cell) => (cell.getValue() ? escapeHtml(cell.getValue()) : '<span class="pa-muted">—</span>'),
                },
                { title: "Confirmed read", field: "acknowledged_at", visible: false, download: true },
                {
                    title: "Last attempt",
                    field: "last_attempt_at",
                    headerHozAlign: "left",
                    minWidth: 124,
                    responsive: 9,
                    download: true,
                    formatter: (cell) => (cell.getValue() ? escapeHtml(cell.getValue()) : '<span class="pa-muted">—</span>'),
                },
                /* Export only: times the test tab or window was left, over every attempt of the assignment. */
                { title: "Tab exits", field: "tab_exits", visible: false, download: true },
                { title: "Time away", field: "away_label", visible: false, download: true },
                { title: "Note", field: "note", visible: false, download: true },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 168,
                    minWidth: 168,
                    responsive: 1,
                    download: false,
                    formatter: actionFormatter,
                },
            ],
            renderComplete() {
                refreshIcons();
            },
        });
    };

    buildTable();
    refreshIcons();

    $("#tabulatorFilterForm").on("keypress.pa-assignments", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.pa-assignments", buildTable);

    $("#tabulator-html-filter-reset").on("click.pa-assignments", function () {
        $("#query").val("");
        filterEmployee.clear(true);
        $("#filter_category").val("");
        $("#filter_policy").val("");
        $("#filter_role").val("");
        $("#filter_level").val("");
        $("#status").val("");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.pa-assignments", function () {
        tableContent?.download("csv", "policy-assignments.csv");
    });

    $("#tabulator-export-xlsx").on("click.pa-assignments", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "policy-assignments.xlsx", {
            sheetName: "Policy Assignments",
        });
    });

    /* ---------- Assign modal ---------- */
    const resetAssignForm = () => {
        const $form = $("#assignForm");

        clearErrors($form);
        assignEmployees.clear(true);
        /* Unticks every role and empties the policy picker. */
        rolePicker.reset();
        $form.find('input[name="due_date"]').val("");
        $form.find('textarea[name="note"]').val("");
        $form.find('input[name="send_email"]').prop("checked", true);
        levelPicker.reset();
    };

    document.getElementById("assignModal")?.addEventListener("hidden.tw.modal", resetAssignForm);

    const assignSummaryHtml = (result) => {
        const lines = [];
        const created = Number(result.created || 0);
        const updated = Number(result.updated || 0);
        const skipped = Number(result.skipped || 0);
        const emailed = Number(result.emailed || 0);

        lines.push(`<li><strong>${created}</strong> new ${created == 1 ? "assignment" : "assignments"} created</li>`);

        if (updated > 0) {
            lines.push(`<li><strong>${updated}</strong> existing ${updated == 1 ? "assignment" : "assignments"} given the new due date</li>`);
        }

        lines.push(`<li><strong>${skipped}</strong> already assigned at this level and left as ${skipped == 1 ? "it was" : "they were"}</li>`);

        if (result.email_requested) {
            lines.push(`<li><strong>${emailed}</strong> ${emailed == 1 ? "member" : "members"} of staff emailed</li>`);
        }

        return `${assignedLevelHtml(result.level)}${assignedRolesHtml(result.roles)}<ul class="pa-result-counts">${lines.join("")}</ul>`;
    };

    $("#assignForm").on("submit.pa-assignments", function (event) {
        event.preventDefault();

        const form = document.getElementById("assignForm");
        const $form = $("#assignForm");

        clearErrors($form);
        setBusy("#assignSave", true);

        axios({
            method: "post",
            url: route("policy.assessment.assignment.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#assignSave", false);

            if (response.status == 200) {
                assignModal.hide();
                showSuccess("Policies assigned", "", assignSummaryHtml(response.data || {}));
                buildTable();
            }
        }).catch((error) => {
            setBusy("#assignSave", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            showWarning("Could not assign policies", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    /* ?assign=1 (the Overview page's "Assign policies" button) opens the modal straight away. */
    if (pageNode && pageNode.dataset.autoOpenAssign === "1") {
        const preselect = Number(pageNode.dataset.preselectEmployee || 0);

        assignModal.show();

        if (preselect > 0) {
            assignEmployees.addItem(String(preselect), true);
        }

        try {
            const url = new URL(window.location.href);

            url.searchParams.delete("assign");
            url.searchParams.delete("employee");
            window.history.replaceState({}, document.title, url.pathname + (url.search ? url.search : ""));
        } catch (e) {
            /* Older browsers: leave the address as it is. */
        }
    }

    /* ---------- Edit due date / note ---------- */
    const rowData = (id) => {
        const row = tableContent ? tableContent.getRow(Number(id)) : false;

        return row ? row.getData() : null;
    };

    document.getElementById("editAssignmentModal")?.addEventListener("hidden.tw.modal", function () {
        const $form = $("#editAssignmentForm");

        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[name="id"]').val("0");
        $("#editAssignmentModal .editAssignmentSubtitle").text("");
    });

    $("#policyAssignmentTable").on("click.pa-assignments", ".edit_btn", function () {
        const id = $(this).attr("data-id");
        const data = rowData(id);
        const $form = $("#editAssignmentForm");

        if (!data) {
            return;
        }

        clearErrors($form);
        $form.find('input[name="due_date"]').val(data.due_date_input || "");
        $form.find('textarea[name="note"]').val(data.note || "");
        $form.find('input[name="id"]').val(id);
        $("#editAssignmentModal .editAssignmentSubtitle").text(`${data.employee_name} — ${withLevel(data)}`);
        editModal.show();
    });

    $("#editAssignmentForm").on("submit.pa-assignments", function (event) {
        event.preventDefault();

        const form = document.getElementById("editAssignmentForm");
        const $form = $("#editAssignmentForm");
        const id = $form.find('input[name="id"]').val();

        clearErrors($form);
        setBusy("#updateAssignment", true);

        axios({
            method: "post",
            url: route("policy.assessment.assignment.update", id),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#updateAssignment", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Saved", "The assignment has been updated.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#updateAssignment", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            if (error.response?.status == 304) {
                editModal.hide();
                showSuccess("No changes", "The assignment was already up to date.");
                return;
            }

            showWarning("Could not save", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    /* ---------- Retake / archive / restore ---------- */
    $("#policyAssignmentTable").on("click.pa-assignments", ".retake_btn", function () {
        const data = rowData($(this).attr("data-id"));
        const who = data ? `${data.employee_name} will get one more attempt at ${withLevel(data)}.` : "This member of staff will get one more attempt.";

        showConfirm("Allow a retake?", who, "RETAKE", $(this).attr("data-id"));
    });

    $("#policyAssignmentTable").on("click.pa-assignments", ".delete_btn", function () {
        showConfirm("Archive assignment?", "The member of staff will no longer see this policy at this level. Their attempts and any badge they earned are kept, and you can restore it later.", "DELETE", $(this).attr("data-id"));
    });

    $("#policyAssignmentTable").on("click.pa-assignments", ".restore_btn", function () {
        showConfirm("Restore assignment?", "The policy will appear for the member of staff again.", "RESTORE", $(this).attr("data-id"));
    });

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#confirmModal .agreeWith").on("click.pa-assignments", function () {
        const recordID = $(this).attr("data-id");
        const action = $(this).attr("data-action");
        const requests = {
            RETAKE: { method: "post", url: () => route("policy.assessment.assignment.retake", recordID), title: "Retake allowed" },
            DELETE: { method: "delete", url: () => route("policy.assessment.assignment.destroy", recordID), title: "Archived", text: "The assignment has been archived." },
            RESTORE: { method: "post", url: () => route("policy.assessment.assignment.restore", recordID), title: "Restored", text: "The assignment has been restored." },
        };
        const request = requests[action];

        if (!request) {
            return;
        }

        $("#confirmModal button").attr("disabled", "disabled");

        axios({
            method: request.method,
            url: request.url(),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();

            if (action == "RETAKE") {
                const left = response.data?.attempts_left;
                showSuccess(request.title, left !== null && left !== undefined ? `They now have ${plural(left, "attempt", "attempts")} left.` : "They can take the test again.");
            } else {
                showSuccess(request.title, request.text);
            }

            buildTable();
        }).catch((error) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showWarning("That did not work", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    /* ---------- Attempts + attempt review ---------- */
    const loadingHtml = '<div class="pa-loading"><span class="pa-loading__dot"></span>Loading...</div>';

    const assignmentMetaHtml = (assignment) => {
        const attempts = assignment.max_attempts !== null && assignment.max_attempts !== undefined ? `${assignment.attempts_count} of ${assignment.max_attempts}` : `${assignment.attempts_count} (unlimited)`;
        const items = [
            ["Due", assignment.due_date ? `<span class="${assignment.is_overdue ? "pa-text-red" : ""}">${escapeHtml(assignment.due_date)}</span>` : "No due date"],
            ["Attempts used", escapeHtml(attempts)],
            ["Best score", escapeHtml(formatScore(assignment.best_score))],
            ["Opened PDF", escapeHtml(assignment.policy_opened_at || "Not yet")],
            ["Confirmed read", escapeHtml(assignment.acknowledged_at || "Not yet")],
        ];

        if (assignment.role_name) {
            items.unshift(["Role", roleTagHtml(assignment)]);
        }

        if (assignment.passed_at) {
            items.push(["Passed on", escapeHtml(assignment.passed_at)]);
        }

        return `<dl class="pa-meta-list">${items.map(([label, value]) => `<div><dt>${label}</dt><dd>${value}</dd></div>`).join("")}</dl>`;
    };

    /* "Silver badge · 12 Oct 2026" beside the status once the badge is held. */
    const badgeNoteHtml = (assignment) => {
        if (!assignment.has_badge) {
            return "";
        }

        return `<span class="pa-badge-note">${badgeSvg(assignment.level, "sm")}<span>Badge awarded${assignment.badge_awarded_at ? ` ${escapeHtml(assignment.badge_awarded_at)}` : ""}</span></span>`;
    };

    $("#policyAssignmentTable").on("click.pa-assignments", ".attempts_btn", function () {
        const id = Number($(this).attr("data-id"));
        const employeeId = $(this).attr("data-employee");
        const data = rowData(id);

        $("#attemptsModal .attemptsModalTitle").text(data ? data.policy_title : "Attempts");
        $("#attemptsModal .attemptsModalSubtitle").text(data ? `${data.employee_name} · ${data.level_label || levelLabel(data.level)} exam` : "");
        $("#attemptsModal .attemptsModalBody").html(loadingHtml);
        attemptsModal.show();

        axios({
            method: "get",
            url: route("policy.assessment.results.employee", employeeId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            const assignment = (response.data?.assignments || []).find((item) => Number(item.id) === id);

            if (!assignment) {
                $("#attemptsModal .attemptsModalBody").html('<p class="pa-empty-note">Attempts are not available for this assignment (its policy may have been deleted).</p>');
                return;
            }

            $("#attemptsModal .attemptsModalBody").html(
                `<div class="pa-attempt-summary"><div class="pa-attempt-summary__head">${levelPill(assignment.level)}${statusPill(assignment.display_status, assignment.display_status_label)}${badgeNoteHtml(assignment)}</div>${assignmentMetaHtml(assignment)}</div>${attemptsTableHtml(assignment.attempts)}`
            );
            refreshIcons();
        }).catch((error) => {
            $("#attemptsModal .attemptsModalBody").html(`<p class="pa-empty-note">${escapeHtml(errorMessage(error, "Could not load the attempts."))}</p>`);
        });
    });

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

    $("#attemptsModal").on("click.pa-assignments", ".review_attempt_btn", function () {
        const attemptId = $(this).attr("data-id");

        attemptsModal.hide();
        openReview(attemptId, attemptsModal);
    });

    $("#attemptReviewModal .attemptReviewBack").on("click.pa-assignments", function () {
        reviewModal.hide();

        if (reviewReturnModal) {
            reviewReturnModal.show();
        }
    });
})();
