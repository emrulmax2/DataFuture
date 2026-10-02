import xlsx from "xlsx";
import { createElement, createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import { LEVELS, escapeHtml, levelLabel, levelPill } from "./policy-assessment/levels";

("use strict");

/*
 * Policy Assessments › Question Bank: the policies list (HR).
 *
 * An exam never draws from one level alone: it takes a set share of Beginner,
 * Intermediate and Expert questions. So the Questions column shows each
 * level's active / total ("B 18/20") and one pill for the exams: "All exams
 * ready", or which exam cannot start yet ("Expert exam not ready"). The server
 * works out and words each exam's readiness (row.exams), using the policy's
 * own questions per test; nothing about the pattern is spelt out here.
 */
(function () {
    const tableNode = document.querySelector("#policyDocumentTable");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let tableContent = null;

    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    };

    /*
     * Inline SVG for markup built in JS. Tabulator can re-run formatters on a
     * redraw (e.g. a window resize) without firing renderComplete, so table
     * cells must not rely on createIcons() swapping <i data-lucide> later.
     */
    const iconSvg = (name, extraClass = "") => {
        const key = String(name).split("-").map((part) => part.charAt(0).toUpperCase() + part.slice(1)).join("");
        const node = icons[key];

        if (!node) {
            return "";
        }

        const svg = createElement(node);

        svg.setAttribute("class", `lucide lucide-${name}${extraClass ? " " + extraClass : ""}`);
        svg.setAttribute("stroke-width", "1.7");
        svg.setAttribute("aria-hidden", "true");

        return svg.outerHTML;
    };

    /* Only http(s) links are ever rendered as links. */
    const safeUrl = (value) => {
        const url = String(value ?? "").trim();

        return /^https?:\/\//i.test(url) ? url : "";
    };

    const clearErrors = ($form) => {
        $form.find(".acc__input-error").text("");
        $form.find(".border-danger").removeClass("border-danger");
    };

    const showErrors = ($form, errors) => {
        clearErrors($form);

        Object.entries(errors || {}).forEach(([key, value]) => {
            const message = Array.isArray(value) ? value.join(" ") : value;

            $form.find(`.${key}`).addClass("border-danger");
            $form.find(`.error-${key}`).text(message);
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

    const showSuccess = (title, description) => {
        $("#successModal .successModalTitle").text(title);
        $("#successModal .successModalDesc").text(description);
        successModal.show();
    };

    const showWarning = (title, description) => {
        $("#warningModal .successModalTitle").text(title);
        $("#warningModal .successModalDesc").text(description);
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

    const syncToggleCopy = ($form) => {
        $form.find(".pa-active-toggle").each(function () {
            const checked = $(this).find("input").is(":checked");

            $(this).find("[data-on]").each(function () {
                $(this).text(checked ? $(this).attr("data-on") : $(this).attr("data-off"));
            });
        });
    };

    const resetForm = ($form, activeByDefault) => {
        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[type="text"], input[type="url"], textarea').val("");
        $form.find('input[name="sort_order"], input[name="max_attempts"], input[name="time_limit_minutes"]').val("");
        $form.find('input[name="pass_mark"]').val("80");
        $form.find('input[name="questions_per_attempt"]').val("10");
        $form.find("option[data-archived-category]").remove();
        $form.find('select[name="policy_category_id"]').val($("#category").val() || "");
        $form.find('input[name="id"]').val("0");
        $form.find('input[name="is_active"]').prop("checked", activeByDefault);
        syncToggleCopy($form);
    };

    const plural = (count, one, many) => `${count} ${count === 1 ? one : many}`;

    const titleFormatter = (cell) => {
        const data = cell.getData();
        const meta = [];

        if (data.category) {
            meta.push(`<span>${escapeHtml(data.category)}${data.category_archived == 1 ? " (archived)" : ""}</span>`);
        }

        if (data.version) {
            meta.push(`<span>${escapeHtml(data.version)}</span>`);
        }

        const pdf = safeUrl(data.pdf_url);

        if (pdf) {
            meta.push(`<a href="${escapeHtml(pdf)}" target="_blank" rel="noopener noreferrer" class="pa-inline-link" title="Open the PDF in a new tab">${iconSvg("file-text")}PDF</a>`);
        } else {
            meta.push(`<span class="pa-muted-warn">No PDF link</span>`);
        }

        const title = data.questions_url
            ? `<a href="${escapeHtml(data.questions_url)}" class="pa-name-cell__link">${escapeHtml(data.title)}</a>`
            : `<strong>${escapeHtml(data.title)}</strong>`;

        return `<span class="pa-name-cell"><span class="pa-name-cell__icon">${iconSvg("file-check-2")}</span><span class="pa-name-cell__body">${title}<small class="pa-meta">${meta.join('<span class="pa-meta__dot">·</span>')}</small></span></span>`;
    };

    /** One level's numbers from a list row: { active, total, drawable }. */
    const levelCounts = (data, level) => {
        const row = (data.levels && data.levels[level]) || {};

        return { active: Number(row.active || 0), total: Number(row.total || 0), drawable: Number(row.drawable || 0) };
    };

    /** "B" / "I" / "E". */
    const levelInitial = (level) => levelLabel(level).charAt(0);

    /** A questions-page link, optionally narrowed (e.g. { level: "expert" }). */
    const questionsLink = (data, params) => {
        if (!data.questions_url) {
            return "";
        }

        const query = new URLSearchParams(params).toString();

        return `${data.questions_url}${query ? `?${query}` : ""}`;
    };

    /*
     * The three mini level pills, "B 18/20 · I 17/19 · E 0/18" (active/total).
     * A level with nothing active is flagged: every exam that draws from it
     * is held back. Each opens the questions page at that level.
     */
    const levelMinis = (data) => {
        const minis = LEVELS.map((level) => {
            const counts = levelCounts(data, level);
            const gap = counts.active === 0;
            const label = levelLabel(level);
            const spoken = gap ? `${label}: none of ${counts.total} active.` : `${label}: ${counts.active} of ${counts.total} active.`;
            const inner = `<span aria-hidden="true">${levelPill(level, `${levelInitial(level)} ${counts.active}/${counts.total}`)}</span><span class="pa-visually-hidden">${escapeHtml(spoken)}</span>`;
            const href = questionsLink(data, { level });
            const className = `pa-lvl-mini pa-lvl-mini--${level}${gap ? " is-gap" : ""}`;
            const title = escapeHtml(`${label} questions: ${counts.active} active of ${counts.total}${gap ? " — none active, so exams that draw from this level cannot start" : ""}`);

            return href
                ? `<a href="${escapeHtml(href)}" class="${className}" title="${title}">${inner}</a>`
                : `<span class="${className}" title="${title}">${inner}</span>`;
        });

        return `<span class="pa-lvl-row">${minis.join("")}</span>`;
    };

    /** One exam of a list row, as the server worded it: { level, name, ready, action, needs_text, summary }. */
    const examInfo = (data, level) => {
        const row = (data.exams && data.exams[level]) || {};

        return {
            level,
            name: row.name || `${levelLabel(level)} exam`,
            ready: row.ready === true || row.ready == 1,
            action: row.action || "",
            needs_text: row.needs_text || "",
            summary: row.summary || "",
        };
    };

    /*
     * One pill for the three exams: "All exams ready", or what cannot start
     * yet — "Expert exam not ready", "2 exams not ready", "No exam ready". The
     * tooltip gives each exam's needs against what is active.
     */
    const examPill = (data) => {
        const exams = LEVELS.map((level) => examInfo(data, level));
        const notReady = exams.filter((exam) => !exam.ready);
        const title = escapeHtml(exams.map((exam) => exam.summary).filter(Boolean).join("\n"));

        if (!notReady.length) {
            return `<span class="pa-pill pa-pill--success" data-exams="ready" title="${title}">${iconSvg("check-circle")}All exams ready</span>`;
        }

        const text = notReady.length === exams.length
            ? "No exam ready"
            : notReady.length === 1
                ? `${notReady[0].name} not ready`
                : `${notReady.length} exams not ready`;

        return `<span class="pa-pill pa-pill--danger" data-exams="not-ready" title="${title}">${iconSvg("alert-triangle")}${escapeHtml(text)}</span>`;
    };

    const questionsFormatter = (cell) => {
        const data = cell.getData();
        const active = Number(data.questions || 0);
        const total = Number(data.questions_total || 0);
        const drafts = Number(data.drafts || 0);
        const drawable = Number(data.drawable || 0);
        const target = Number(data.bank_target || 0);
        const pills = [examPill(data)];

        /* Switched on, but none complete enough to draw: a different fix from activating drafts. */
        if (active > 0 && drawable === 0) {
            pills.push(`<span class="pa-pill pa-pill--danger" title="Its active questions are incomplete. Edit them first.">${iconSvg("alert-triangle")}No usable questions</span>`);
        }

        if (drafts > 0) {
            const label = `${plural(drafts, "draft", "drafts")} to review`;
            const href = questionsLink(data, { status: "drafts" });

            pills.push(href
                ? `<a href="${escapeHtml(href)}" class="pa-pill pa-pill--amber" title="Review drafts">${iconSvg("sparkles")}${label}</a>`
                : `<span class="pa-pill pa-pill--amber">${iconSvg("sparkles")}${label}</span>`);
        }

        if (data.below_target == 1 && target > 0) {
            pills.push(`<span class="pa-pill pa-pill--muted" title="${escapeHtml(`${plural(total, "question", "questions")} in the bank. Aim for at least ${target}.`)}">${iconSvg("library")}Below ${target}</span>`);
        }

        return `<span class="pa-stack pa-qcount"><span class="pa-count"><strong>${active}</strong> active <span class="pa-count__sep">/</span> ${total} total</span>${levelMinis(data)}${pills.length ? `<span class="pa-pill-row">${pills.join("")}</span>` : ""}</span>`;
    };

    const isBlank = (value) => value === null || value === undefined || value === "";

    /*
     * The test rules in one cell, so the table still fits a laptop screen:
     *   10 questions · 80% to pass
     *   15 min                       (or "No time limit")
     *   3 attempts                   (or "Unlimited attempts")
     * The rules apply to every exam. The tooltip shows how the questions per
     * test are shared between the levels in each exam ("Beginner exam:
     * 7 Beginner · 2 Intermediate · 1 Expert"), as the server worked it out.
     */
    const rulesFormatter = (cell) => {
        const data = cell.getData();
        const perTest = Number(data.questions_per_attempt || 0);
        const timed = !isBlank(data.time_limit_minutes);
        const time = timed ? (data.time_limit_label || `${data.time_limit_minutes} min`) : "No time limit";
        const attempts = isBlank(data.max_attempts) ? "Unlimited attempts" : plural(Number(data.max_attempts), "attempt", "attempts");
        const shares = LEVELS.map((level) => examInfo(data, level)).filter((exam) => exam.needs_text).map((exam) => `${exam.name}: ${exam.needs_text}`).join("\n");
        const dot = `<span class="pa-meta__dot" aria-hidden="true">·</span>`;

        return `<span class="pa-stack pa-rules" title="${escapeHtml(shares)}">`
            + `<strong>${plural(perTest, "question", "questions")} ${dot} ${escapeHtml(data.pass_mark)}% to pass</strong>`
            + `<small class="pa-rules__line${timed ? " is-timed" : ""}" data-rule="time">${iconSvg("timer")}${escapeHtml(time)}</small>`
            + `<small class="pa-rules__line" data-rule="attempts">${iconSvg("rotate-cw")}${escapeHtml(attempts)}</small>`
            + `</span>`;
    };

    /** Export text for one exam: "Ready" or "Not ready: activate 5 more Expert". */
    const examDownload = (level) => (value, data) => {
        const exam = examInfo(data, level);

        return exam.ready ? "Ready" : `Not ready${exam.action ? `: ${exam.action}` : ""}`;
    };

    /** Export text for one level: "18 active / 20 total". */
    const levelDownload = (level) => (value, data) => {
        const counts = levelCounts(data, level);

        return `${counts.active} active / ${counts.total} total`;
    };

    const assignedFormatter = (cell) => {
        const data = cell.getData();
        const assigned = Number(data.assigned || 0);
        const passed = Number(data.passed || 0);

        if (assigned === 0) {
            return `<span class="pa-bank-num pa-bank-num--muted" title="Not assigned to anyone yet">None</span>`;
        }

        return `<span class="pa-stack" title="${plural(assigned, "member of staff", "staff")} assigned, ${passed} passed"><strong>${assigned} assigned</strong><small>${passed} passed</small></span>`;
    };

    const statusLabel = (data) => {
        if (data.deleted_at != null) {
            return "Archived";
        }

        return data.is_active == 1 ? "Active" : "Inactive";
    };

    const statusFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<span class="ss-status-pill pa-pill--archived"><span></span>Archived</span>`;
        }

        const on = data.is_active == 1;

        return `<button type="button" role="switch" aria-checked="${on ? "true" : "false"}" data-id="${escapeHtml(data.id)}" class="status_toggle pa-switch ${on ? "is-on" : ""}" title="${on ? "Active: click to make inactive" : "Inactive: click to make active"}"><span class="pa-switch__track"><span class="pa-switch__thumb"></span></span><span class="pa-switch__label">${on ? "Active" : "Inactive"}</span></button>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();
        const id = escapeHtml(data.id);

        if (data.deleted_at != null) {
            return `<button data-id="${id}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore policy" title="Restore">${iconSvg("rotate-cw")}</button>`;
        }

        return [
            `<a href="${escapeHtml(data.questions_url)}" class="ss-row-action ss-row-action--view" aria-label="Manage questions" title="Manage questions">${iconSvg("list-checks")}</a>`,
            `<button data-id="${id}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit policy" title="Edit">${iconSvg("pencil")}</button>`,
            `<button data-id="${id}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive policy" title="Archive">${iconSvg("trash-2")}</button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "3";
        const category = $("#category").val() || "";
        const review = $("#review").val() || "";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#policyDocumentTable", {
            ajaxURL: route("policy.assessment.policy.list"),
            ajaxParams: { querystr, status, category, review },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 25,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: false,
            index: "id",
            placeholder: "No policies found",
            columns: [
                {
                    title: "Policy",
                    field: "title",
                    headerHozAlign: "left",
                    minWidth: 178,
                    widthGrow: 2,
                    formatter: titleFormatter,
                },
                {
                    title: "Category",
                    field: "category",
                    visible: false,
                    download: true,
                },
                {
                    title: "Version",
                    field: "version",
                    visible: false,
                    download: true,
                },
                {
                    title: "PDF link",
                    field: "pdf_url",
                    visible: false,
                    download: true,
                },
                {
                    title: "Questions",
                    field: "questions",
                    headerHozAlign: "left",
                    minWidth: 226,
                    widthGrow: 1,
                    formatter: questionsFormatter,
                    accessorDownload: (value, data) => `${data.questions} active / ${data.questions_total} total`,
                },
                ...LEVELS.map((level) => ({
                    title: levelLabel(level),
                    field: `level_${level}`,
                    visible: false,
                    download: true,
                    accessorDownload: levelDownload(level),
                })),
                ...LEVELS.map((level) => ({
                    title: `${levelLabel(level)} exam`,
                    field: `exam_${level}`,
                    visible: false,
                    download: true,
                    accessorDownload: examDownload(level),
                })),
                {
                    title: "Drafts",
                    field: "drafts",
                    visible: false,
                    download: true,
                },
                {
                    /* rules_summary is the server's one-line wording ("10 questions · 80% to pass · 15 min"): what the export shows. */
                    title: "Test rules",
                    field: "rules_summary",
                    headerHozAlign: "left",
                    headerTooltip: "Questions per test, pass mark, time limit and attempts, for every exam. Sorts by questions per test.",
                    width: 200,
                    formatter: rulesFormatter,
                },
                {
                    title: "Questions per test",
                    field: "questions_per_attempt",
                    visible: false,
                    download: true,
                },
                {
                    title: "Pass mark (%)",
                    field: "pass_mark",
                    visible: false,
                    download: true,
                },
                {
                    title: "Time limit (minutes)",
                    field: "time_limit_minutes",
                    visible: false,
                    download: true,
                    accessorDownload: (value) => (isBlank(value) ? "No time limit" : value),
                },
                {
                    title: "Max attempts",
                    field: "max_attempts",
                    visible: false,
                    download: true,
                    accessorDownload: (value) => (isBlank(value) ? "Unlimited" : value),
                },
                {
                    title: "Assigned",
                    field: "assigned",
                    headerHozAlign: "left",
                    width: 104,
                    formatter: assignedFormatter,
                    accessorDownload: (value, data) => `${data.assigned} assigned / ${data.passed} passed`,
                },
                {
                    title: "Status",
                    field: "is_active",
                    headerHozAlign: "left",
                    width: 120,
                    formatter: statusFormatter,
                    accessorDownload: (value, data) => statusLabel(data),
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 128,
                    minWidth: 128,
                    download: false,
                    print: false,
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

    $("#tabulatorFilterForm").on("keypress.pa-policies", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#category, #status, #review").on("change.pa-policies", buildTable);
    $("#tabulator-html-filter-go").on("click.pa-policies", buildTable);

    $("#tabulator-html-filter-reset").on("click.pa-policies", function () {
        $("#query").val("");
        $("#category").val("");
        $("#review").val("");
        $("#status").val("3");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.pa-policies", function () {
        tableContent?.download("csv", "policy-question-bank.csv");
    });

    $("#tabulator-export-xlsx").on("click.pa-policies", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "policy-question-bank.xlsx", {
            sheetName: "Question Bank",
        });
    });

    $("#tabulator-print").on("click.pa-policies", function () {
        tableContent?.print();
    });

    $("#addForm, #editForm").on("change.pa-policies", ".pa-active-toggle input", function () {
        syncToggleCopy($(this).closest("form"));
    });

    document.getElementById("addModal")?.addEventListener("show.tw.modal", function () {
        resetForm($("#addForm"), true);
    });

    document.getElementById("addModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#addForm"), true);
    });

    document.getElementById("editModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#editForm"), false);
    });

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addForm").on("submit.pa-policies", function (event) {
        event.preventDefault();

        const $form = $("#addForm");

        clearErrors($form);
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("policy.assessment.policy.store"),
            data: new FormData(this),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#save", false);

            if (response.status == 200) {
                addModal.hide();
                showSuccess("Saved", "The policy has been added. Next, add its questions.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#save", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            showWarning("Not saved", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    $("#policyDocumentTable").on("click.pa-policies", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editForm");

        resetForm($form, false);

        axios({
            method: "get",
            url: route("policy.assessment.policy.edit", editId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data || {};
                const $category = $form.find('select[name="policy_category_id"]');
                const categoryId = String(dataset.policy_category_id ?? "");
                const listed = $category.find("option").filter(function () {
                    return this.value === categoryId;
                }).length > 0;

                /*
                 * The select only lists the live categories from page load. A
                 * policy whose category is archived (or was added since) keeps
                 * it: offer it, marked "(archived)" when it is, and preselect
                 * it, so saving without moving the policy works.
                 */
                if (categoryId !== "" && !listed && (dataset.category_archived == 1 || dataset.category_name)) {
                    const option = document.createElement("option");
                    const name = dataset.category_name || "Current category";

                    option.value = categoryId;
                    option.textContent = dataset.category_archived == 1 ? `${name} (archived)` : name;
                    option.setAttribute("data-archived-category", "");
                    $category.append(option);
                }

                $category.val(categoryId);
                $form.find('input[name="title"]').val(dataset.title || "");
                $form.find('input[name="version"]').val(dataset.version || "");
                $form.find('input[name="pdf_url"]').val(dataset.pdf_url || "");
                $form.find('input[name="page_url"]').val(dataset.page_url || "");
                $form.find('textarea[name="description"]').val(dataset.description || "");
                $form.find('input[name="pass_mark"]').val(dataset.pass_mark ?? "");
                $form.find('input[name="questions_per_attempt"]').val(dataset.questions_per_attempt ?? "");
                $form.find('input[name="max_attempts"]').val(dataset.max_attempts ?? "");
                $form.find('input[name="time_limit_minutes"]').val(dataset.time_limit_minutes ?? "");
                $form.find('input[name="sort_order"]').val(dataset.sort_order ?? "");
                $form.find('input[name="is_active"]').prop("checked", dataset.is_active == 1);
                $form.find('input[name="id"]').val(editId);
                syncToggleCopy($form);
                editModal.show();
                refreshIcons();
            }
        }).catch((error) => {
            showWarning("Could not open", errorMessage(error, "This policy could not be loaded."));
        });
    });

    $("#editForm").on("submit.pa-policies", function (event) {
        event.preventDefault();

        const $form = $("#editForm");
        const editId = $form.find('input[name="id"]').val();

        clearErrors($form);
        setBusy("#update", true);

        axios({
            method: "post",
            url: route("policy.assessment.policy.update", editId),
            data: new FormData(this),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#update", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Saved", "The policy has been updated.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#update", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            if (error.response?.status == 304) {
                editModal.hide();
                showSuccess("No changes", "The policy was already up to date.");
                return;
            }

            showWarning("Not saved", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    const setStatus = (recordID, target) => {
        const row = tableContent?.getRow(recordID);
        const $button = $(`#policyDocumentTable .status_toggle[data-id="${recordID}"]`);

        $button.prop("disabled", true);

        axios({
            method: "post",
            url: route("policy.assessment.policy.status", recordID),
            data: { is_active: target ? 1 : 0 },
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200 && row) {
                row.update({ is_active: response.data.is_active });
                refreshIcons();
            }
        }).catch((error) => {
            $button.prop("disabled", false);
            showWarning("Status not changed", errorMessage(error, "Something went wrong. Please try again."));
        });
    };

    $("#policyDocumentTable").on("click.pa-policies", ".status_toggle", function () {
        const recordID = $(this).attr("data-id");
        const data = tableContent?.getRow(recordID)?.getData() || {};
        const turnOn = data.is_active != 1;

        if (!turnOn && Number(data.assigned || 0) > 0) {
            showConfirm(
                "Make this policy inactive?",
                `It is assigned to ${plural(Number(data.assigned), "member of staff", "staff")}. While it is inactive they cannot start its test.`,
                "DEACTIVATE",
                recordID
            );
            return;
        }

        setStatus(recordID, turnOn);
    });

    $("#policyDocumentTable").on("click.pa-policies", ".delete_btn", function () {
        const recordID = $(this).attr("data-id");
        const data = tableContent?.getRow(recordID)?.getData() || {};
        const assigned = Number(data.assigned || 0);
        const detail = assigned > 0
            ? `It is assigned to ${plural(assigned, "member of staff", "staff")}. Their results are kept, but nobody can take its test until you restore it.`
            : "Its questions are kept. You can restore it later from Archived.";

        showConfirm("Archive this policy?", detail, "DELETE", recordID);
    });

    $("#policyDocumentTable").on("click.pa-policies", ".restore_btn", function () {
        showConfirm("Restore this policy?", "It will return to the question bank with its questions.", "RESTORE", $(this).attr("data-id"));
    });

    $("#confirmModal .agreeWith").on("click.pa-policies", function () {
        const recordID = $(this).attr("data-id");
        const action = $(this).attr("data-action");

        if (action == "DEACTIVATE") {
            confirmModal.hide();
            setStatus(recordID, false);
            return;
        }

        if (action != "DELETE" && action != "RESTORE") {
            return;
        }

        $("#confirmModal button").attr("disabled", "disabled");

        axios({
            method: action == "DELETE" ? "delete" : "post",
            url: action == "DELETE" ? route("policy.assessment.policy.destroy", recordID) : route("policy.assessment.policy.restore", recordID),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            $("#confirmModal button").removeAttr("disabled");

            if (response.status == 200) {
                confirmModal.hide();
                showSuccess("Done", action == "DELETE" ? "The policy has been archived." : "The policy has been restored.");
                buildTable();
            }
        }).catch((error) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showWarning("Not done", errorMessage(error, "Something went wrong. Please try again."));
        });
    });
})();
