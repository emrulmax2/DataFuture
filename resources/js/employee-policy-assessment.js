import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { LEVELS, BADGE_NAMES, badgeSvg, isLevel, levelLabel, levelPill } from "./policy-assessment/levels";
import { assignedLevelHtml, initLevelPicker } from "./policy-assessment/assign-level-picker";
import { assignedRolesHtml, initRolePicker, roleTagHtml } from "./policy-assessment/assign-role-picker";
import { attemptReviewHtml, attemptsTableHtml, formatScore as formatScorePA } from "./policy-assessment/attempt-review";

("use strict");

/*
 * Employee profile › Policy Assessments tab (HR). This employee's badges
 * (revoke / restore; policy.assessment.badge.list), their assignments with
 * the level of each and the role it came through (the Assignments list
 * endpoint with `employee`), assign more by role at a chosen level
 * (policy-assessment/assign-role-picker.js: a role fills the policy picker,
 * and the picker is what is posted), allow a retake, archive / restore, and
 * attempt review (policy-assessment/attempt-review.js). Element ids end in
 * -PA. Everything HR or staff typed is escaped before it goes into HTML.
 */
const renderPolicyIcons = () => {
    createIcons({
        icons,
        "stroke-width": 1.5,
        nameAttr: "data-lucide",
    });
};

const escapeHtmlPA = (value) => {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
};

const STATUS_LABELS_PA = {
    passed: "Target met",
    locked: "No attempts left",
    in_progress: "In progress",
    overdue: "Overdue",
    failed: "Target not met – retake available",
    pending: "Not started",
};

const statusPillPA = (status, label) => {
    const key = STATUS_LABELS_PA[status] ? status : "pending";

    return `<span class="pa-state-pill pa-state-pill--${key}"><span></span>${escapeHtmlPA(label || STATUS_LABELS_PA[key])}</span>`;
};

var employeePolicyAssessmentTable = (function () {
    let tableContent = null;

    const attemptsText = (data) => {
        return data.max_attempts !== null && data.max_attempts !== undefined ? `${data.attempts_count} / ${data.max_attempts}` : `${data.attempts_count}`;
    };

    var _tableGen = function () {
        const employee = $("#employeePolicyAssessmentTable").attr("data-employee");
        const status = $("#status-PA").val() || "";
        const level = $("#level-PA").val() || "";
        const role = $("#role-PA").val() || "";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#employeePolicyAssessmentTable", {
            ajaxURL: route("policy.assessment.assignment.list"),
            ajaxParams: { employee: employee, role: role, level: level, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50],
            layout: "fitColumns",
            /* Policy never collapses; Actions goes last, so on a phone the row keeps the
               buttons beside the title and Status/Due date lead the open detail list.
               download/print: true keep a collapsed column in the export and print-out. */
            responsiveLayout: "collapse",
            responsiveLayoutCollapseStartOpen: window.matchMedia("(max-width: 767px)").matches,
            placeholder: "No policy assessments found",
            ajaxResponse(url, params, response) {
                const total = Number(response.total_rows || 0);
                const summaryEl = document.querySelector("#employeePolicySummary-PA");

                if (summaryEl) {
                    summaryEl.textContent = total > 0
                        ? `${total} ${total === 1 ? "policy" : "policies"} in this list`
                        : "Policies this member of staff must read and meet the target for.";
                }

                return response;
            },
            columns: [
                {
                    title: "Policy",
                    field: "policy_title",
                    headerHozAlign: "left",
                    minWidth: 160,
                    widthGrow: 2,
                    responsive: 0,
                    formatter(cell) {
                        const data = cell.getData();
                        let tags = "";

                        if (data.policy_archived) {
                            tags = ' <em class="pa-tag pa-tag--red">Policy deleted</em>';
                        } else if (data.policy_inactive) {
                            tags = ' <em class="pa-tag pa-tag--muted">Inactive</em>';
                        }

                        /* Category, then the role it was assigned through (nothing for a policy picked by hand). */
                        const meta = `${data.category ? `<small>${escapeHtmlPA(data.category)}</small>` : ""}${roleTagHtml(data)}`;

                        return `<div class="pa-cell-stack"><strong>${escapeHtmlPA(data.policy_title)}${tags}</strong>${meta ? `<span class="pa-cell-meta">${meta}</span>` : ""}</div>`;
                    },
                },
                { title: "Category", field: "category", visible: false, download: true },
                { title: "Role", field: "role_name", visible: false, download: true },
                {
                    title: "Level",
                    field: "level",
                    headerHozAlign: "left",
                    minWidth: 172,
                    responsive: 2,
                    download: true,
                    print: true,
                    formatter(cell) {
                        const data = cell.getData();

                        return `<div class="pa-level-cell">${levelPill(data.level)}${data.has_badge ? badgeSvg(data.level, "sm") : ""}</div>`;
                    },
                    accessorDownload: (value, data) => data.level_label || levelLabel(value),
                },
                { title: "Badge awarded", field: "badge_awarded_at", visible: false, download: true },
                {
                    title: "Assigned",
                    field: "assigned_at",
                    headerHozAlign: "left",
                    minWidth: 110,
                    responsive: 6,
                    download: true,
                    print: true,
                    formatter(cell) {
                        const data = cell.getData();

                        return `<div class="pa-cell-stack"><span>${escapeHtmlPA(data.assigned_at || "—")}</span>${data.assigned_by ? `<small>by ${escapeHtmlPA(data.assigned_by)}</small>` : ""}</div>`;
                    },
                },
                {
                    title: "Due date",
                    field: "due_date",
                    headerHozAlign: "left",
                    minWidth: 136,
                    responsive: 3,
                    download: true,
                    print: true,
                    formatter(cell) {
                        const data = cell.getData();

                        if (!data.due_date) {
                            return '<span class="pa-muted">—</span>';
                        }

                        return data.is_overdue
                            ? `<span class="pa-due is-overdue"><i data-lucide="alarm-clock"></i>${escapeHtmlPA(data.due_date)}</span>`
                            : `<span class="pa-due">${escapeHtmlPA(data.due_date)}</span>`;
                    },
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    minWidth: 212,
                    responsive: 2,
                    download: true,
                    print: true,
                    formatter(cell) {
                        const data = cell.getData();

                        return data.deleted_at ? '<span class="pa-state-pill pa-state-pill--archived"><span></span>Archived</span>' : statusPillPA(data.display_status, data.display_status_label);
                    },
                    accessorDownload: (value, data) => (data.deleted_at ? "Archived" : data.display_status_label),
                },
                {
                    title: "Attempts",
                    field: "attempts_count",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 96,
                    responsive: 4,
                    download: true,
                    print: true,
                    formatter: (cell) => escapeHtmlPA(attemptsText(cell.getData())),
                    accessorDownload: (value, data) => attemptsText(data),
                },
                {
                    title: "Best score",
                    field: "best_score",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 100,
                    responsive: 5,
                    download: true,
                    print: true,
                    formatter: (cell) => escapeHtmlPA(formatScorePA(cell.getValue())),
                },
                {
                    title: "Opened PDF",
                    field: "policy_opened_at",
                    headerHozAlign: "left",
                    minWidth: 118,
                    responsive: 7,
                    download: true,
                    print: true,
                    formatter: (cell) => (cell.getValue() ? escapeHtmlPA(cell.getValue()) : '<span class="pa-muted">—</span>'),
                },
                { title: "Confirmed read", field: "acknowledged_at", visible: false, download: true },
                {
                    title: "Last attempt",
                    field: "last_attempt_at",
                    headerHozAlign: "left",
                    minWidth: 118,
                    responsive: 8,
                    download: true,
                    print: true,
                    formatter: (cell) => (cell.getValue() ? escapeHtmlPA(cell.getValue()) : '<span class="pa-muted">—</span>'),
                },
                /* Export only: times the test tab or window was left, over every attempt of the assignment. */
                { title: "Tab exits", field: "tab_exits", visible: false, download: true },
                { title: "Time away", field: "away_label", visible: false, download: true },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 130,
                    responsive: 1,
                    download: false,
                    formatter(cell) {
                        const data = cell.getData();
                        const id = escapeHtmlPA(data.id);
                        const actions = [];

                        if (data.deleted_at == null) {
                            actions.push(`<button data-id="${id}" type="button" class="attempts_btn ep-doc-action-btn ep-doc-action-btn--view" title="View attempts"><i data-lucide="list-checks" class="w-4 h-4"></i></button>`);

                            if (data.can_retake) {
                                actions.push(`<button data-id="${id}" type="button" class="retake_btn ep-doc-action-btn ep-doc-action-btn--docs" title="Allow one more attempt"><i data-lucide="rotate-ccw" class="w-4 h-4"></i></button>`);
                            }

                            actions.push(`<button data-id="${id}" type="button" class="delete_btn ep-doc-action-btn ep-doc-action-btn--danger" title="Archive assignment"><i data-lucide="trash-2" class="w-4 h-4"></i></button>`);
                        } else {
                            actions.push(`<button data-id="${id}" type="button" class="restore_btn ep-doc-action-btn ep-doc-action-btn--restore" title="Restore assignment"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>`);
                        }

                        return `<div class="ep-doc-action-group">${actions.join("")}</div>`;
                    },
                },
            ],
            renderComplete() {
                renderPolicyIcons();
            },
        });
    };

    return {
        init: function () {
            _tableGen();
        },
        rowData: function (id) {
            const row = tableContent ? tableContent.getRow(Number(id)) : false;

            return row ? row.getData() : null;
        },
        download: function (type, filename, options) {
            if (tableContent) {
                tableContent.download(type, filename, options);
            }
        },
        print: function () {
            if (tableContent) {
                tableContent.print();
            }
        },
        redraw: function () {
            if (tableContent) {
                tableContent.redraw();
            }
        },
    };
})();

(function () {
    if (!$("#employeePolicyAssessmentTable").length) {
        return;
    }

    const employeeId = $("#employeePolicyAssessmentTable").attr("data-employee");
    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const getModal = (selector) => tailwind.Modal.getOrCreateInstance(document.querySelector(selector));
    const assignModal = getModal("#assignPolicyModal-PA");
    const attemptsModal = getModal("#attemptsModal-PA");
    const reviewModal = getModal("#attemptReviewModal-PA");
    const successModal = getModal("#successModal-PA");
    const warningModal = getModal("#warningModal-PA");
    const confirmModal = getModal("#confirmModal-PA");
    let reviewReturnModal = null;

    employeePolicyAssessmentTable.init();

    const showSuccess = (title, description, descriptionHtml = null) => {
        $("#successModal-PA .successModalTitle").text(title);

        if (descriptionHtml !== null) {
            $("#successModal-PA .successModalDesc").html(descriptionHtml);
        } else {
            $("#successModal-PA .successModalDesc").text(description);
        }

        successModal.show();
    };

    const showWarning = (title, description) => {
        $("#warningModal-PA .warningModalTitle").text(title);
        $("#warningModal-PA .warningModalDesc").text(description);
        warningModal.show();
    };

    const errorMessage = (error, fallback) => {
        return error?.response?.data?.message || fallback;
    };

    /* ---------- Badges ---------- */
    const badgeSection = document.getElementById("policyBadges-PA");
    const badgeGrid = badgeSection ? badgeSection.querySelector("[data-badge-grid]") : null;
    const badgeEmpty = badgeSection ? badgeSection.querySelector("[data-badge-empty]") : null;
    const showRevokedBox = document.getElementById("showRevoked-PA");
    let badgeRows = [];

    /* Three tiny medals with the number held at each level (same markup as the Blade view). */
    const badgeCountsInner = (counts) => {
        return LEVELS.map((level) => {
            const count = Number(counts[level] || 0);

            return `<span class="pa-badge-count pa-badge-count--${level}${count > 0 ? "" : " is-zero"}" title="${BADGE_NAMES[level]} (${levelLabel(level)})">${badgeSvg(level, "sm")}<strong>${count}</strong></span>`;
        }).join("");
    };

    const renderBadgeCounts = (counts) => {
        const node = badgeSection ? badgeSection.querySelector("[data-badge-counts]") : null;
        const total = Number(counts.total || 0);

        if (node) {
            node.innerHTML = badgeCountsInner(counts);
            node.setAttribute("aria-label", `${total} ${total == 1 ? "badge" : "badges"}: ${LEVELS.map((level) => `${Number(counts[level] || 0)} ${BADGE_NAMES[level].toLowerCase()}`).join(", ")}`);
        }

        $("#policySummaryChips-PA [data-pa-badge-count]").each(function () {
            $(this).text(total);

            if (this.nextSibling) {
                this.nextSibling.textContent = ` ${total == 1 ? "badge" : "badges"}`;
            }
        });
    };

    /* One tile: the medal with its caption (badgeSvg, as the partial draws it), then the score or who revoked it, then the action. */
    const badgeTileHtml = (badge) => {
        const level = isLevel(badge.level) ? badge.level : "beginner";
        const id = escapeHtmlPA(badge.id);
        const revoked = !!Number(badge.revoked || 0);
        const card = badgeSvg(level, "md", { title: badge.policy_title, date: badge.awarded_at || null, revoked });
        let meta = "Awarded";

        if (revoked) {
            meta = `Revoked${badge.revoked_at ? ` ${badge.revoked_at}` : ""}${badge.revoked_by ? ` by ${badge.revoked_by}` : ""}`;
        } else if (badge.score !== null && badge.score !== undefined && badge.score !== "") {
            meta = `Scored ${formatScorePA(badge.score)}`;
        }

        const button = revoked
            ? `<button type="button" class="pa-ep-badge__btn pa-ep-badge__btn--restore restore_badge_btn" data-id="${id}"><i data-lucide="rotate-ccw" class="w-4 h-4"></i>Restore</button>`
            : `<button type="button" class="pa-ep-badge__btn pa-ep-badge__btn--revoke revoke_badge_btn" data-id="${id}"><i data-lucide="ban" class="w-4 h-4"></i>Revoke</button>`;

        return `<li class="pa-ep-badge pa-ep-badge--${level}${revoked ? " is-revoked" : ""}" data-badge-id="${id}">${card}<span class="pa-ep-badge__meta">${escapeHtmlPA(meta)}</span>${button}</li>`;
    };

    const renderBadges = (rows) => {
        if (!badgeGrid) {
            return;
        }

        badgeRows = rows;
        badgeGrid.innerHTML = rows.map(badgeTileHtml).join("");

        if (badgeEmpty) {
            badgeEmpty.textContent = showRevokedBox && showRevokedBox.checked
                ? "No badges yet, and none revoked."
                : "No badges yet. A badge is awarded the first time they meet the target in a policy test.";
            badgeEmpty.hidden = rows.length > 0;
        }

        renderPolicyIcons();
    };

    /* Live badges, or live then revoked with "Show revoked": gold first, newest first. */
    const loadBadges = () => {
        if (!badgeGrid) {
            return Promise.resolve();
        }

        const query = new URLSearchParams();
        const sorters = [["deleted_at", "asc"], ["level", "desc"], ["awarded_at", "desc"]];

        query.set("employee", employeeId);
        query.set("status", showRevokedBox && showRevokedBox.checked ? "all" : "1");
        query.set("page", "1");
        query.set("size", "true");
        sorters.forEach(([field, dir], index) => {
            query.set(`sorters[${index}][field]`, field);
            query.set(`sorters[${index}][dir]`, dir);
        });
        badgeSection.classList.add("is-loading");

        return axios({
            method: "get",
            url: `${route("policy.assessment.badge.list")}?${query.toString()}`,
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            renderBadges(response.data?.data || []);
        }).catch((error) => {
            showWarning("Could not load the badges", errorMessage(error, "Something went wrong. Please try again."));
        }).finally(() => {
            badgeSection.classList.remove("is-loading");
        });
    };

    const findBadge = (id) => {
        const fromList = badgeRows.find((row) => Number(row.id) === Number(id));

        if (fromList) {
            return fromList;
        }

        /* Tiles drawn by the server before any reload: read the caption. */
        const tile = badgeGrid ? badgeGrid.querySelector(`[data-badge-id="${Number(id)}"]`) : null;

        return tile ? { policy_title: tile.querySelector(".pa-badge-card__title")?.textContent || "", level_label: tile.querySelector(".pa-badge-card__level")?.textContent.replace(/ badge$/, "") || "" } : null;
    };

    showRevokedBox?.addEventListener("change", loadBadges);

    $("#policyBadges-PA").on("click", ".revoke_badge_btn", function () {
        const badge = findBadge($(this).attr("data-id"));
        const what = badge ? `The ${badge.level_label || "level"} badge for ${badge.policy_title}` : "This badge";

        $("#confirmModal-PA .confModTitle").text("Revoke badge?");
        $("#confirmModal-PA .confModDesc").text(`${what} will be taken away. Their results are kept and you can restore it later.`);
        $("#confirmModal-PA .agreeWith").attr("data-id", $(this).attr("data-id")).attr("data-action", "REVOKEBADGEPA");
        confirmModal.show();
    });

    $("#policyBadges-PA").on("click", ".restore_badge_btn", function () {
        const badge = findBadge($(this).attr("data-id"));
        const what = badge ? `The ${badge.level_label || "level"} badge for ${badge.policy_title}` : "This badge";

        $("#confirmModal-PA .confModTitle").text("Restore badge?");
        $("#confirmModal-PA .confModDesc").text(`${what} will be given back.`);
        $("#confirmModal-PA .agreeWith").attr("data-id", $(this).attr("data-id")).attr("data-action", "RESTOREBADGEPA");
        confirmModal.show();
    });

    /* The chips under the card title and the badge counts; reloaded after anything that changes them. */
    const refreshSummary = () => {
        axios({
            method: "get",
            url: route("policy.assessment.results.employee", employeeId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            const summary = response.data?.summary || {};

            $("#policySummaryChips-PA [data-pa-count]").each(function () {
                const key = $(this).attr("data-pa-count");

                $(this).text(Number(summary[key] || 0));
            });

            if (summary.badges) {
                renderBadgeCounts(summary.badges);
            }
        }).catch(() => {
            /* The chips just keep their last values. */
        });
    };

    const reloadAll = () => {
        employeePolicyAssessmentTable.init();
        refreshSummary();
    };

    /* ---------- Filters, export, print ---------- */
    $("#tabulator-html-filter-go-PA").on("click", function () {
        employeePolicyAssessmentTable.init();
    });

    $("#tabulator-html-filter-reset-PA").on("click", function () {
        $("#status-PA").val("");
        $("#level-PA").val("");
        $("#role-PA").val("");
        employeePolicyAssessmentTable.init();
    });

    $("#tabulatorFilterForm-PA").on("keypress", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            employeePolicyAssessmentTable.init();
        }
    });

    $("#tabulator-export-csv-PA").on("click", function () {
        employeePolicyAssessmentTable.download("csv", "policy-assessments.csv");
    });

    $("#tabulator-export-xlsx-PA").on("click", function () {
        window.XLSX = xlsx;
        employeePolicyAssessmentTable.download("xlsx", "policy-assessments.xlsx", {
            sheetName: "Policy Assessments",
        });
    });

    $("#tabulator-print-PA").on("click", function () {
        employeePolicyAssessmentTable.print();
    });

    window.addEventListener("resize", () => {
        employeePolicyAssessmentTable.redraw();
        renderPolicyIcons();
    });

    /* ---------- Assign ---------- */
    const tomMulti = {
        dropdownParent: "body",
        dropdownClass: "ts-dropdown lcc-tom-float pa-tom-dropdown",
        copyClassesToDropdown: false,
        create: false,
        maxOptions: null,
        plugins: {
            dropdown_input: {},
            remove_button: { title: "Remove" },
        },
    };
    const assignPolicies = new TomSelect("#pa_ep_policies", { ...tomMulti, placeholder: "Search policies..." });
    /* Many roles: the role picker is one searchable multi-select instead of cards. */
    const roleSelectNode = document.querySelector("#assignPolicyForm-PA [data-role-select]");
    const assignRoles = roleSelectNode ? new TomSelect(roleSelectNode, { ...tomMulti, placeholder: "Search roles..." }) : null;
    const rolePicker = initRolePicker(document.getElementById("assignPolicyForm-PA"), assignPolicies, assignRoles);
    const levelPicker = initLevelPicker(document.getElementById("assignPolicyForm-PA"), assignPolicies, rolePicker.showLevel);

    const clearErrors = () => {
        $("#assignPolicyForm-PA .acc__input-error").html("");
        $("#assignPolicyForm-PA .border-danger").removeClass("border-danger");
    };

    document.getElementById("assignPolicyModal-PA").addEventListener("hidden.tw.modal", function () {
        clearErrors();
        /* Unticks every role and empties the policy picker. */
        rolePicker.reset();
        $('#assignPolicyForm-PA input[name="due_date"]').val("");
        $('#assignPolicyForm-PA textarea[name="note"]').val("");
        $('#assignPolicyForm-PA input[name="send_email"]').prop("checked", true);
        levelPicker.reset();
    });

    const assignSummaryHtml = (result) => {
        const created = Number(result.created || 0);
        const updated = Number(result.updated || 0);
        const skipped = Number(result.skipped || 0);
        const emailed = Number(result.emailed || 0);
        const lines = [`<li><strong>${created}</strong> new ${created == 1 ? "assignment" : "assignments"} created</li>`];

        if (updated > 0) {
            lines.push(`<li><strong>${updated}</strong> existing ${updated == 1 ? "assignment" : "assignments"} given the new due date</li>`);
        }

        lines.push(`<li><strong>${skipped}</strong> already assigned at this level and left as ${skipped == 1 ? "it was" : "they were"}</li>`);

        if (result.email_requested) {
            lines.push(emailed > 0 ? "<li>Email sent to the member of staff</li>" : "<li>No email sent (no new policies, no email address or no mail settings)</li>");
        }

        return `${assignedLevelHtml(result.level)}${assignedRolesHtml(result.roles)}<ul class="pa-result-counts">${lines.join("")}</ul>`;
    };

    $("#assignPolicyForm-PA").on("submit", function (event) {
        event.preventDefault();

        const form = document.getElementById("assignPolicyForm-PA");
        const saveButton = document.querySelector("#assignPolicySave-PA");

        clearErrors();
        saveButton.setAttribute("disabled", "disabled");
        saveButton.querySelector(".pa-spinner").style.cssText = "display: inline-block;";

        axios({
            method: "post",
            url: route("policy.assessment.assignment.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            saveButton.removeAttribute("disabled");
            saveButton.querySelector(".pa-spinner").style.cssText = "display: none;";

            if (response.status == 200) {
                assignModal.hide();
                showSuccess("Policies assigned", "", assignSummaryHtml(response.data || {}));
                reloadAll();
            }
        }).catch((error) => {
            saveButton.removeAttribute("disabled");
            saveButton.querySelector(".pa-spinner").style.cssText = "display: none;";

            if (error.response && error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors || {})) {
                    const base = String(key).split(".")[0];
                    const $error = $(`#assignPolicyForm-PA .error-${base}`);

                    $(`#assignPolicyForm-PA .${base}`).addClass("border-danger");

                    if ($error.text() === "") {
                        $error.text(Array.isArray(val) ? val[0] : val);
                    }
                }

                return;
            }

            showWarning("Could not assign policies", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    /* ---------- Retake / archive / restore ---------- */
    $("#employeePolicyAssessmentTable").on("click", ".retake_btn", function () {
        const data = employeePolicyAssessmentTable.rowData($(this).attr("data-id"));

        $("#confirmModal-PA .confModTitle").text("Allow a retake?");
        $("#confirmModal-PA .confModDesc").text(data ? `One more attempt at ${data.policy_title} (${data.level_label || levelLabel(data.level)}).` : "One more attempt at this policy.");
        $("#confirmModal-PA .agreeWith").attr("data-id", $(this).attr("data-id")).attr("data-action", "RETAKEPA");
        confirmModal.show();
    });

    $("#employeePolicyAssessmentTable").on("click", ".delete_btn", function () {
        $("#confirmModal-PA .confModTitle").text("Archive assignment?");
        $("#confirmModal-PA .confModDesc").text("They will no longer see this policy at this level. Their attempts and any badge they earned are kept, and you can restore it later.");
        $("#confirmModal-PA .agreeWith").attr("data-id", $(this).attr("data-id")).attr("data-action", "DELETEPA");
        confirmModal.show();
    });

    $("#employeePolicyAssessmentTable").on("click", ".restore_btn", function () {
        $("#confirmModal-PA .confModTitle").text("Restore assignment?");
        $("#confirmModal-PA .confModDesc").text("The policy will appear for them again.");
        $("#confirmModal-PA .agreeWith").attr("data-id", $(this).attr("data-id")).attr("data-action", "RESTOREPA");
        confirmModal.show();
    });

    document.getElementById("confirmModal-PA").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal-PA .agreeWith").attr("data-id", "0").attr("data-action", "none");
        $("#confirmModal-PA button").removeAttr("disabled");
    });

    $("#confirmModal-PA .agreeWith").on("click", function () {
        const recordID = $(this).attr("data-id");
        const action = $(this).attr("data-action");
        const requests = {
            RETAKEPA: { method: "post", url: () => route("policy.assessment.assignment.retake", recordID), title: "Retake allowed" },
            DELETEPA: { method: "delete", url: () => route("policy.assessment.assignment.destroy", recordID), title: "Archived", text: "The assignment has been archived." },
            RESTOREPA: { method: "post", url: () => route("policy.assessment.assignment.restore", recordID), title: "Restored", text: "The assignment has been restored." },
            REVOKEBADGEPA: { method: "delete", url: () => route("policy.assessment.badge.destroy", recordID), title: "Badge revoked", text: "The badge has been taken away. You can restore it from Show revoked.", badge: true },
            RESTOREBADGEPA: { method: "post", url: () => route("policy.assessment.badge.restore", recordID), title: "Badge restored", text: "The badge has been given back.", badge: true },
        };
        const request = requests[action];

        if (!request) {
            return;
        }

        $("#confirmModal-PA button").attr("disabled", "disabled");

        axios({
            method: request.method,
            url: request.url(),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            $("#confirmModal-PA button").removeAttr("disabled");
            confirmModal.hide();

            if (action == "RETAKEPA") {
                const left = response.data?.attempts_left;
                showSuccess(request.title, left !== null && left !== undefined ? `They now have ${left} ${left == 1 ? "attempt" : "attempts"} left.` : "They can take the test again.");
            } else {
                showSuccess(request.title, request.text);
            }

            reloadAll();

            if (request.badge) {
                loadBadges();
            }
        }).catch((error) => {
            $("#confirmModal-PA button").removeAttr("disabled");
            confirmModal.hide();
            showWarning("That did not work", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    /* ---------- Attempts + attempt review ---------- */
    const loadingHtml = '<div class="pa-loading"><span class="pa-loading__dot"></span>Loading...</div>';

    const assignmentMetaHtml = (assignment) => {
        const attempts = assignment.max_attempts !== null && assignment.max_attempts !== undefined ? `${assignment.attempts_count} of ${assignment.max_attempts}` : `${assignment.attempts_count} (unlimited)`;
        const items = [
            ["Due", assignment.due_date ? `<span class="${assignment.is_overdue ? "pa-text-red" : ""}">${escapeHtmlPA(assignment.due_date)}</span>` : "No due date"],
            ["Attempts used", escapeHtmlPA(attempts)],
            ["Best score", escapeHtmlPA(formatScorePA(assignment.best_score))],
            ["Opened PDF", escapeHtmlPA(assignment.policy_opened_at || "Not yet")],
            ["Confirmed read", escapeHtmlPA(assignment.acknowledged_at || "Not yet")],
        ];

        if (assignment.role_name) {
            items.unshift(["Role", roleTagHtml(assignment)]);
        }

        if (assignment.passed_at) {
            items.push(["Target met on", escapeHtmlPA(assignment.passed_at)]);
        }

        if (assignment.note) {
            items.push(["Note", escapeHtmlPA(assignment.note)]);
        }

        return `<dl class="pa-meta-list">${items.map(([label, value]) => `<div><dt>${label}</dt><dd>${value}</dd></div>`).join("")}</dl>`;
    };

    $("#employeePolicyAssessmentTable").on("click", ".attempts_btn", function () {
        const id = Number($(this).attr("data-id"));
        const data = employeePolicyAssessmentTable.rowData(id);

        $("#attemptsModal-PA .attemptsModalTitle").text(data ? data.policy_title : "Attempts");
        $("#attemptsModal-PA .attemptsModalSubtitle").text(data ? [data.category, `${data.level_label || levelLabel(data.level)} exam`].filter((value) => value).join(" · ") : "");
        $("#attemptsModal-PA .attemptsModalBody").html(loadingHtml);
        attemptsModal.show();

        axios({
            method: "get",
            url: route("policy.assessment.results.employee", employeeId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            const assignment = (response.data?.assignments || []).find((item) => Number(item.id) === id);

            if (!assignment) {
                $("#attemptsModal-PA .attemptsModalBody").html('<p class="pa-empty-note">Attempts are not available for this assignment (its policy may have been deleted).</p>');
                return;
            }

            $("#attemptsModal-PA .attemptsModalBody").html(
                `<div class="pa-attempt-summary"><div class="pa-attempt-summary__head">${levelPill(assignment.level)}${statusPillPA(assignment.display_status, assignment.display_status_label)}${assignment.has_badge ? `<span class="pa-badge-note">${badgeSvg(assignment.level, "sm")}<span>Badge awarded${assignment.badge_awarded_at ? ` ${escapeHtmlPA(assignment.badge_awarded_at)}` : ""}</span></span>` : ""}</div>${assignmentMetaHtml(assignment)}</div>${attemptsTableHtml(assignment.attempts, { iconClass: "w-4 h-4" })}`
            );
            renderPolicyIcons();
        }).catch((error) => {
            $("#attemptsModal-PA .attemptsModalBody").html(`<p class="pa-empty-note">${escapeHtmlPA(errorMessage(error, "Could not load the attempts."))}</p>`);
        });
    });

    $("#attemptsModal-PA").on("click", ".review_attempt_btn", function () {
        const attemptId = $(this).attr("data-id");

        reviewReturnModal = attemptsModal;
        attemptsModal.hide();
        $("#attemptReviewModal-PA .attemptReviewTitle").text("Attempt review");
        $("#attemptReviewModal-PA .attemptReviewSubtitle").text("");
        $("#attemptReviewModal-PA .attemptReviewBody").html(loadingHtml);
        reviewModal.show();

        axios({
            method: "get",
            url: route("policy.assessment.attempt.show", attemptId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            const payload = response.data || {};

            $("#attemptReviewModal-PA .attemptReviewTitle").text(`Attempt ${payload.attempt?.attempt_no ?? ""} — ${payload.policy?.title ?? ""}`);
            $("#attemptReviewModal-PA .attemptReviewSubtitle").text(payload.policy?.category ?? "");
            $("#attemptReviewModal-PA .attemptReviewBody").html(attemptReviewHtml(payload));
            renderPolicyIcons();
        }).catch((error) => {
            $("#attemptReviewModal-PA .attemptReviewBody").html(`<p class="pa-empty-note">${escapeHtmlPA(errorMessage(error, "Could not load this attempt."))}</p>`);
        });
    });

    $("#attemptReviewModal-PA .attemptReviewBack").on("click", function () {
        reviewModal.hide();

        if (reviewReturnModal) {
            reviewReturnModal.show();
        }
    });
})();
