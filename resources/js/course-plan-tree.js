/*
 * Class Plan Tree View.
 *
 * The tree is lazy — four levels, each fetched when its parent opens:
 *   academic year -> term -> course -> group
 * Picking a group replaces the right-hand panel with server-rendered markup
 * from `plans.tree.get.module`, which brings the detail table's host with it.
 *
 * Every endpoint, parameter name and CSS hook the server depends on is
 * unchanged from the legacy `plan-tree.js`; only the markup around them is new.
 */

import IMask from "imask";
import Litepicker from "litepicker";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {
    PAGINATION_LANGS,
    PAGINATION_SIZES,
    clearErrors,
    createRangePainter,
    csrfHeaders,
    hideConfirm,
    openConfirm,
    paintCount,
    paintErrors,
    refreshIcons,
    setBusy,
    showSuccess,
    wireConfirmReset,
    wireResize,
} from "./course-table-kit";

const TABLE_ID = "#classPlanTreeListTable";

(function () {
    const layout = document.querySelector(".cm-treelayout");
    if (!layout) return;

    const result = layout.querySelector("[data-cm-tree-result]");
    const notice = layout.querySelector("[data-cm-tree-notice]");

    let table = null;
    let lastTotal = null;

    const getTable = () => table;
    const paintRange = createRangePainter(TABLE_ID, getTable, () => lastTotal);

    /* ------------------------------------------------------------------ *
     * Tree
     * ------------------------------------------------------------------ */

    const spin = (row, on) => {
        const el = row.querySelector("[data-cm-tree-spin]");
        if (el) el.hidden = !on;
    };

    const mark = (row, open) => {
        const el = row.querySelector("[data-cm-mark]");
        if (el && !el.classList.contains("cm-tree__mark--leaf")) el.textContent = open ? "−" : "+";
    };

    /** Closing a branch drops its children, so reopening always refetches. */
    function collapse(item) {
        const child = item.querySelector(":scope > .cm-tree__child");
        if (child) child.remove();

        item.classList.remove("is-open");
        mark(item.querySelector(":scope > .cm-tree__row"), false);
    }

    /**
     * Opens one branch. `payload` is the parameter set that level's endpoint
     * expects — the names differ between them, so each caller passes its own.
     */
    function expand(row, routeName, payload) {
        const item = row.closest(".cm-tree__item");

        if (item.classList.contains("is-open")) {
            collapse(item);

            return;
        }

        spin(row, true);
        axios({ method: "post", url: route(routeName), data: payload, headers: csrfHeaders() })
            .then((response) => {
                spin(row, false);
                item.insertAdjacentHTML("beforeend", response.data.htm || "");
                item.classList.add("is-open");
                mark(row, true);
                refreshIcons();
            })
            .catch(() => {
                spin(row, false);
                showSuccess("Something went wrong", "That branch could not be opened. Please try again.", "warn");
            });
    }

    $(layout).on("click", ".academicYear", function () {
        expand(this, "plans.tree.get.semester", { academicyear: this.getAttribute("data-yearid") });
    });

    $(layout).on("click", ".theTerm", function () {
        expand(this, "plans.tree.get.courses", {
            academicYearId: this.getAttribute("data-yearid"),
            attendanceSemester: this.getAttribute("data-attendanceSemester"),
        });
    });

    $(layout).on("click", ".theCourse", function () {
        expand(this, "plans.tree.get.groups", {
            academicYearId: this.getAttribute("data-yearid"),
            attendanceSemester: this.getAttribute("data-attendanceSemester"),
            courseId: this.getAttribute("data-courseid"),
        });
    });

    // The leaf. Loads the right-hand panel rather than another tree level.
    $(layout).on("click", ".theGroup", function () {
        const row = this;

        layout.querySelectorAll(".cm-tree__line.is-active").forEach((el) => el.classList.remove("is-active"));
        layout.querySelectorAll(".theGroup").forEach((el) => el.classList.remove("is-active"));
        row.classList.add("is-active");
        // The fill lives on the line so it covers the tools as well as the row.
        row.closest(".cm-tree__line").classList.add("is-active");
        spin(row, true);

        axios({
            method: "post",
            url: route("plans.tree.get.module"),
            data: {
                academicYearId: row.getAttribute("data-yearid"),
                attendancesemester: row.getAttribute("data-attendanceSemester"),
                courseId: row.getAttribute("data-courseid"),
                groupId: row.getAttribute("data-groupid"),
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                spin(row, false);
                result.innerHTML = response.data.htm || "";
                result.hidden = false;
                if (notice) notice.hidden = true;
                refreshIcons();
                buildTable();
            })
            .catch(() => {
                spin(row, false);
                showSuccess("Something went wrong", "That group could not be opened. Please try again.", "warn");
            });
    });

    /* The rail button hides the module menu, giving the tree and the detail the
     * full width. The choice sticks, because anyone working through a deep tree
     * wants it to stay that way between groups. */
    const NAV_KEY = "cm-tree-nav-hidden";

    function paintNav() {
        const hidden = layout.classList.contains("is-navhidden");
        const icon = layout.querySelector("[data-cm-rail-icon]");
        const btn = layout.querySelector("[data-cm-nav-toggle]");

        if (icon) {
            icon.innerHTML = hidden ? '<path d="M9 18l6-6-6-6"></path>' : '<path d="M15 18l-6-6 6-6"></path>';
        }
        if (btn) btn.setAttribute("title", hidden ? "Show the menu" : "Hide the menu");
    }

    if (window.localStorage && window.localStorage.getItem(NAV_KEY) === "1") {
        layout.classList.add("is-navhidden");
    }
    paintNav();

    $(layout).on("click", "[data-cm-nav-toggle]", function () {
        layout.classList.toggle("is-navhidden");
        paintNav();

        if (window.localStorage) {
            window.localStorage.setItem(NAV_KEY, layout.classList.contains("is-navhidden") ? "1" : "0");
        }

        // Tabulator sizes to its container, so it has to be told.
        if (table) table.redraw(true);
    });

    /* ------------------------------------------------------------------ *
     * Visibility — per term, course or group
     * ------------------------------------------------------------------ */

    $(layout).on("click", ".visibilityBtn", function (event) {
        event.stopPropagation();

        const btn = this;
        const next = btn.getAttribute("data-visibility");

        btn.setAttribute("disabled", "disabled");
        axios({
            method: "post",
            url: route("plans.update.visibility"),
            data: {
                academicYearId: btn.getAttribute("data-yearid"),
                attendanceSemester: btn.getAttribute("data-attendanceSemester"),
                courseId: btn.getAttribute("data-courseid"),
                groupId: btn.getAttribute("data-groupid"),
                visibility: next,
            },
            headers: csrfHeaders(),
        })
            .then(() => {
                btn.removeAttribute("disabled");

                // The class is the state the server and the next render read.
                const on = String(next) === "1";
                btn.classList.toggle("visibility_1", on);
                btn.classList.toggle("visibility_0", !on);
                btn.setAttribute("data-visibility", on ? 0 : 1);
                btn.setAttribute("title", on ? "Visible to students" : "Hidden from students");

                // The glyph has to follow the state — only the colour was
                // changing, so a hidden branch kept showing an open eye.
                const svg = btn.querySelector("svg");
                if (svg) {
                    svg.innerHTML = on
                        ? '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle>'
                        : '<path d="M9.9 4.24A9.1 9.1 0 0 1 12 4c7 0 10 8 10 8a18 18 0 0 1-2.16 3.19M6.61 6.61A18 18 0 0 0 2 12s3 8 10 8a9 9 0 0 0 5.39-1.61M2 2l20 20M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>';
                }
            })
            .catch(() => {
                btn.removeAttribute("disabled");
                showSuccess("Something went wrong", "The visibility could not be changed.", "warn");
            });
    });

    /* ------------------------------------------------------------------ *
     * Detail table
     * ------------------------------------------------------------------ */

    function scope() {
        const host = document.querySelector(TABLE_ID);

        return host
            ? {
                  // `courses`, not `course` — the name the endpoint reads.
                  courses: host.getAttribute("data-course"),
                  attendanceSemester: host.getAttribute("data-attendanceSemester"),
                  group: host.getAttribute("data-group"),
                  year: host.getAttribute("data-year"),
              }
            : {};
    }

    function syncBulkButtons() {
        const n = table ? table.getSelectedRows().length : 0;

        ["#generateDaysBtn", "#bulkCommunication"].forEach((sel) => {
            const btn = document.querySelector(sel);
            if (btn) btn.hidden = n === 0;
        });
    }

    /**
     * The generated-days count, as an obvious link out to the dates page. It
     * was a bare number before — correct, but nothing said it could be opened.
     */
    function daysLink(count, planId) {
        const n = Number(count) || 0;

        if (!n) return '<span class="cm-treedays__zero">No days</span>';

        return (
            `<a class="cm-treedays__link" href="${route("plan.dates", planId)}" title="View the generated class days">` +
            '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect></svg>' +
            `${n} ${n === 1 ? "day" : "days"}` +
            '<svg class="cm-treedays__go" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M7 7h10v10"></path></svg>' +
            "</a>"
        );
    }

    /** One side of the row: days count, day, time and tutor, stacked. */
    function stack(daysLabel, day, time, tutor) {
        return (
            '<span class="cm-treedays">' +
            `<span class="cm-treedays__count">${daysLabel}</span>` +
            `<span class="cm-treedays__day">${day || ""}</span>` +
            `<span class="cm-treedays__time">${time || ""}</span>` +
            `<span class="cm-treedays__tutor">${tutor || ""}</span>` +
            "</span>"
        );
    }

    function buildTable() {
        if (!document.querySelector(TABLE_ID)) return;

        table = new Tabulator(TABLE_ID, {
            ajaxURL: route("plans.tree.list"),
            ajaxParams: scope(),
            ajaxFiltering: true,
            ajaxSorting: false,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: PAGINATION_SIZES,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No class plans found",
            langs: PAGINATION_LANGS,
            selectable: true,
            selectableCheck: (row) => row.getData().id > 0,
            ajaxResponse(url, params, response) {
                lastTotal = typeof response.total === "number" ? response.total : null;
                paintCount(lastTotal);

                return response;
            },
            columns: [
                {
                    formatter: "rowSelection",
                    titleFormatter: "rowSelection",
                    hozAlign: "left",
                    headerHozAlign: "left",
                    width: 58,
                    headerSort: false,
                    cssClass: "cm-cell--check",
                    cellClick(e, cell) {
                        cell.getRow().toggleSelect();
                    },
                },
                {
                    // A theory plan and its tutorial are one row, so the id cell
                    // shows the pair.
                    title: "ID",
                    field: "id",
                    headerSort: false,
                    width: 100,
                    cssClass: "cm-cell--nowrap",
                    formatter(cell) {
                        const d = cell.getData();

                        return d.child_id > 0 ? `${d.id}&nbsp;-&nbsp;${d.child_id}` : String(d.id);
                    },
                },
                {
                    // Grows at twice the rate of the others rather than taking
                    // every spare pixel, which is what left it enormous while
                    // the columns beside it stayed cramped.
                    title: "Module",
                    field: "module",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 128,
                    variableHeight: true,
                    cssClass: "cm-cell--wrap",
                    formatter(cell) {
                        const d = cell.getData();

                        return (
                            '<span class="cm-treemod">' +
                            (d.class_type
                                ? `<span class="cm-treemod__type" data-cm-type="${String(d.class_type).toLowerCase()}"><span class="cm-treemod__dot"></span>${d.class_type}</span>`
                                : "") +
                            `<span class="cm-planlink">${d.module || ""}</span>` +
                            "</span>"
                        );
                    },
                },
                {
                    title: "No of Student",
                    field: "on_of_student",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 80,
                    formatter(cell) {
                        return `<button type="button" class="cm-linkbtn assignedStudents" data-id="${cell.getData().id}">${cell.getValue() || ""}</button>`;
                    },
                },
                {
                    title: "Theory / Seminer Days",
                    field: "dates",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 138,
                    variableHeight: true,
                    cssClass: "cm-cell--stack",
                    formatter(cell) {
                        const d = cell.getData();

                        return (
                            stack(daysLink(d.dates, d.id), d.day, d.time, d.tutor) +
                            `<button type="button" class="cm-pillbtn cm-treedays__edit editPlanBtn" data-id="${d.id}"><i data-lucide="pencil"></i>Edit Plan</button>`
                        );
                    },
                },
                {
                    title: "Tutorial Days",
                    field: "tutorial",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 138,
                    variableHeight: true,
                    cssClass: "cm-cell--stack",
                    formatter(cell) {
                        const d = cell.getData();
                        const t = d.tutorial;

                        // Only a theory plan that is itself a parent and has no
                        // tutorial yet can gain one — a seminar never does, and a
                        // row that is already a tutorial cannot own another.
                        if (!t || !d.child_id) {
                            if (d.class_type === "Theory" && d.parent_id == 0) {
                                return (
                                    `<button type="button" class="cm-pillbtn cm-treedays__edit cm-treedays__add editTutorialBtn" data-theory="${d.id}" data-tutorial="0">` +
                                    '<i data-lucide="plus"></i>Add Tutorial</button>'
                                );
                            }

                            return '<span class="cm-treedays__none">—</span>';
                        }

                        return (
                            stack(daysLink(t.dates, d.child_id), t.day, t.time, d.personalTutor) +
                            `<button type="button" class="cm-pillbtn cm-treedays__edit editTutorialBtn" data-theory="${d.id}" data-tutorial="${d.child_id}"><i data-lucide="pencil"></i>Edit Tutorial</button>`
                        );
                    },
                },
                {
                    title: "Results",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    widthGrow: 1,
                    minWidth: 150,
                    formatter(cell) {
                        const d = cell.getData();
                        if (d.deleted_at) return "";

                        const done = d.submissionDone === "Yes";
                        let out = "";

                        // Both of these are links out to the results area — the
                        // label turns on whether the submission is in, not on
                        // whether the row has a tutorial.
                        if (d.uploadAssesment == 1) {
                            out +=
                                `<a class="cm-pillbtn ${done ? "" : "cm-pillbtn--gold"}" href="${route("results-staff-submission.show", d.id)}">` +
                                `<i data-lucide="${done ? "eye" : "upload"}"></i>${done ? "View Result" : "Upload Submission"}</a>`;
                        }

                        if (d.submissionAvailable == 1 && !done) {
                            out += `<a class="cm-pillbtn cm-pillbtn--view" href="${route("result.comparison", d.id)}"><i data-lucide="file-text"></i>View Submission</a>`;
                        }

                        return `<span class="cm-treeresults">${out}</span>`;
                    },
                },
            ],
            rowSelectionChanged: syncBulkButtons,
            renderComplete() {
                refreshIcons();
                paintRange();
                // fitColumns leaves a sub-pixel overflow that shows as a
                // horizontal scrollbar; every table in this module trims the
                // last column by 1px to absorb it.
                const cols = this.getColumns();
                if (cols.length > 0) {
                    const last = cols[cols.length - 1];
                    last.setWidth(last.getWidth() - 1);
                }
            },
        });

        wireResize(getTable);
    }

    /* ------------------------------------------------------------------ *
     * Generate days / bulk communication
     * ------------------------------------------------------------------ */

    /** The ids of the ticked rows — what both buttons act on. */
    const selectedPlanIds = () => (table ? table.getSelectedData().map((r) => r.id) : []);

    const noSelection = () =>
        showSuccess(
            "Nothing selected",
            "Tick at least one class plan in the table first.",
            "warn"
        );

    $(layout).on("click", "#generateDaysBtn", function () {
        const ids = selectedPlanIds();
        if (!ids.length) return noSelection();

        openConfirm({
            id: ids.join(","),
            action: "GENERATE",
            title: "Generate class days?",
            message: `Class days will be generated across the term for ${ids.length} selected ${ids.length === 1 ? "plan" : "plans"}.`,
            confirmLabel: "Yes, generate",
            tone: "restore",
        });
    });

    /* This one leaves the tree. `bulk.communication` is
     * `bulk-communication/communication/{classplans}` — the ids are a required
     * route segment, not a query string, and `BulkCommunicationController::list`
     * splits them on "-". A query string against a parameterless route() call
     * could never have resolved. */
    $(layout).on("click", "#bulkCommunication", function () {
        const ids = selectedPlanIds();
        if (!ids.length) return noSelection();

        window.location.href = route("bulk.communication", ids.join("-"));
    });

    wireConfirmReset();

    $("#confirmModal .agreeWith").on("click", function () {
        if ($(this).attr("data-action") !== "GENERATE") return;

        const ids = String($(this).attr("data-id")).split(",");

        setBusy("#generateDaysBtn", true);
        axios({
            method: "post",
            url: route("plan.dates.generate"),
            data: { classPlansIds: ids },
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#generateDaysBtn", false);
                hideConfirm();
                showSuccess(response.data.title || "Done", response.data.Message || "The class days were generated.");
                buildTable();
            })
            .catch((error) => {
                setBusy("#generateDaysBtn", false);
                hideConfirm();

                const payload = error.response ? error.response.data : null;
                showSuccess(
                    (payload && payload.title) || "Something went wrong",
                    (payload && payload.Message) || "The class days could not be generated.",
                    "warn"
                );
            });
    });

    /* ------------------------------------------------------------------ *
     * Modals
     * ------------------------------------------------------------------ */

    const modal = (id) => tailwind.Modal.getOrCreateInstance(document.querySelector(`#${id}`));

    // Masks and pickers are bound once; the fields live in the page, not in the
    // panel the tree replaces.
    $(".treeTimeField").each(function () {
        IMask(this, {
            overwrite: true,
            autofix: true,
            mask: "HH:MM",
            blocks: {
                HH: { mask: IMask.MaskedRange, placeholderChar: "HH", from: 0, to: 23, maxLength: 2 },
                MM: { mask: IMask.MaskedRange, placeholderChar: "MM", from: 0, to: 59, maxLength: 2 },
            },
        });
    });

    document.querySelectorAll(".treeDateField").forEach((input) => {
        new Litepicker({
            element: input,
            autoApply: true,
            singleMode: true,
            numberOfColumns: 1,
            numberOfMonths: 1,
            format: "DD-MM-YYYY",
            dropdowns: { minYear: 1900, maxYear: 2050, months: true, years: true },
        });
    });

    /** The day chip rows write into the hidden input the endpoints read. */
    function setPick(name, value) {
        const group = document.querySelector(`[data-cm-optpick="${name}"]`);
        const field = document.querySelector(`#${name}`);
        if (!group || !field) return;

        field.value = value || "";
        group.querySelectorAll("[data-cm-opt]").forEach((btn) => {
            btn.classList.toggle("is-on", btn.getAttribute("data-cm-opt") === value);
        });
    }

    $("[data-cm-optpick]").on("click", "[data-cm-opt]", function () {
        const group = this.closest("[data-cm-optpick]");

        setPick(group.getAttribute("data-cm-optpick"), this.getAttribute("data-cm-opt"));
    });

    /** Which of the seven day columns is set on a plan. */
    const dayOf = (plan) => ["sat", "sun", "mon", "tue", "wed", "thu", "fri"].find((d) => plan[d] == 1) || "";

    /** `class.plan.edit` and friends return dates as stored. */
    function toPickerDate(value) {
        const iso = String(value || "").match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!iso || iso[1] === "0000") return "";

        return `${iso[3]}-${iso[2]}-${iso[1]}`;
    }

    // Theory is led by the tutor, everything else by the personal tutor.
    function syncTutorFields() {
        const type = $("#tp_class_type").val();
        const isTheory = type === "Theory";

        $(".tpTutorWrap").toggle(isTheory);
        $(".tpPTutorWrap").toggle(!isTheory);
    }

    $("#tp_class_type").on("change", syncTutorFields);

    /* ---- Edit plan --------------------------------------------------- */

    $(layout).on("click", ".editPlanBtn", function () {
        const id = this.getAttribute("data-id");

        axios({ method: "get", url: route("plans.tree.edit", id), headers: csrfHeaders() }).then((response) => {
            const p = response.data.plan;

            clearErrors("#editPlanForm");
            $("#editPlanModal .termName").text(p.term || "—");
            $("#editPlanModal .courseName").text(p.course || "—");
            $("#editPlanModal .groupName").text(p.group || "—");

            $("#tp_module_creation_id").html(p.modules || "");
            $("#tp_rooms_id").val(p.rooms_id || "");
            $("#tp_class_type").val(p.class_type || "");
            $("#tp_tutor_id").val(p.tutor_id || "");
            $("#tp_personal_tutor_id").val(p.personal_tutor_id || "");
            $("#tp_start_time").val(p.start_time || "");
            $("#tp_end_time").val(p.end_time || "");
            $("#tp_submission_date").val(toPickerDate(p.submission_date));
            $("#tp_virtual_room").val(p.virtual_room || "");
            $("#tp_note").val(p.note || "");
            setPick("tp_class_day", dayOf(p));

            $('#editPlanForm input[name="id"]').val(id);
            $('#editPlanForm input[name="group_id"]').val(p.group_id || "");
            $('#editPlanForm input[name="module_enrollment_key"]').val(p.module_enrollment_key || "");

            syncTutorFields();
            modal("editPlanModal").show();
        });
    });

    $("#editPlanForm").on("submit", function (event) {
        event.preventDefault();
        clearErrors("#editPlanForm");
        setBusy("#updateTreePlan", true);

        axios({
            method: "post",
            url: route("plans.tree.update"),
            data: new FormData(this),
            headers: csrfHeaders(),
        })
            .then(() => {
                setBusy("#updateTreePlan", false);
                modal("editPlanModal").hide();
                showSuccess("Congratulations!", "The class plan was updated.");
                buildTable();
            })
            .catch((error) => {
                setBusy("#updateTreePlan", false);
                if (error.response && error.response.status === 422) {
                    paintErrors("#editPlanForm", error.response.data.errors || {});
                }
            });
    });

    /* ---- Tutorial ---------------------------------------------------- */

    /* The same dialog adds and edits. `data-tutorial` is 0 on the Add Tutorial
     * button a theory row grows when it has no tutorial yet, and the tutorial's
     * own plan id when there is one — which is also what tells `storeTutorial`
     * whether to insert or update. */
    $(layout).on("click", ".editTutorialBtn", function () {
        // `getTutorial` reads `theory_id` and `tutorial_id`; sending `id` meant
        // it resolved neither and the dialog never opened.
        const theoryId = this.getAttribute("data-theory");
        const tutorialId = this.getAttribute("data-tutorial");
        const isEdit = Number(tutorialId) > 0;

        axios({
            method: "post",
            url: route("plans.tree.get.tutorial"),
            data: { theory_id: theoryId, tutorial_id: tutorialId },
            headers: csrfHeaders(),
        }).then((response) => {
            const p = response.data.plan;

            clearErrors("#tutorialDetailsForm");
            $("[data-cm-tutorial-title]").text(isEdit ? "Edit Tutorial" : "Add Tutorial");
            $("#tutorialDetailsModal .tuTermName").text(p.term || "—");
            $("#tutorialDetailsModal .tuCourseName").text(p.course || "—");
            $("#tutorialDetailsModal .tuGroupName").text(p.group || "—");
            $("#tutorialDetailsModal .tuModuleName").text(p.module || "—");
            $("#tutorialDetailsModal .tuVenueName").text(p.venue || "—");

            $("#tu_rooms_id").val(p.rooms_id || "");
            $("#tu_start_time").val(p.start_time || "");
            $("#tu_end_time").val(p.end_time || "");
            $("#tu_virtual_room").val(p.virtual_room || "");
            $("#tu_note").val(p.note || "");
            setPick("tu_class_day", dayOf(p));

            // `personal_tutor_id` is the tutorial's own; `pt_id` is the parent
            // theory's, which seeds a tutorial that does not have one yet.
            $("#tu_personal_tutor_id").val(p.personal_tutor_id || p.pt_id || "");

            // `tutorial_id` decides insert vs update, so it is the button's own
            // attribute and nothing else. It used to fall back to `pt_id`, which
            // is the personal tutor's *user* id — on Add that made `storeTutorial`
            // run `Plan::where('id', <user id>)->update(...)`, overwriting an
            // unrelated plan (or silently matching nothing) instead of inserting.
            $('#tutorialDetailsForm input[name="theory_id"]').val(theoryId);
            $('#tutorialDetailsForm input[name="tutorial_id"]').val(isEdit ? tutorialId : 0);

            modal("tutorialDetailsModal").show();
        });
    });

    $("#tutorialDetailsForm").on("submit", function (event) {
        event.preventDefault();
        clearErrors("#tutorialDetailsForm");
        setBusy("#storeTreeTutorial", true);

        axios({
            method: "post",
            url: route("plans.tree.store.tutorial"),
            data: new FormData(this),
            headers: csrfHeaders(),
        })
            .then(() => {
                setBusy("#storeTreeTutorial", false);
                modal("tutorialDetailsModal").hide();
                showSuccess("Congratulations!", "The tutorial was saved.");
                buildTable();
            })
            .catch((error) => {
                setBusy("#storeTreeTutorial", false);
                if (error.response && error.response.status === 422) {
                    paintErrors("#tutorialDetailsForm", error.response.data.errors || {});
                }
            });
    });

    /* ---- Sync tutorial ----------------------------------------------- */

    $(layout).on("click", ".syncTutorialBtn", function () {
        const id = this.getAttribute("data-id");

        axios({
            method: "post",
            url: route("plans.tree.get.theories"),
            data: { plan_id: id },
            headers: csrfHeaders(),
        }).then((response) => {
            $("#sync_plan_id").html(response.data.htm || "");
            $('#syncTutorialForm input[name="id"]').val(id);
            modal("syncTutorialModal").show();
        });
    });

    $("#syncTutorialForm").on("submit", function (event) {
        event.preventDefault();
        clearErrors("#syncTutorialForm");
        setBusy("#syncTutorialBtn", true);

        axios({
            method: "post",
            url: route("plans.tree.sync.tutorial"),
            data: new FormData(this),
            headers: csrfHeaders(),
        })
            .then(() => {
                setBusy("#syncTutorialBtn", false);
                modal("syncTutorialModal").hide();
                showSuccess("Congratulations!", "The tutorial was synced.");
                buildTable();
            })
            .catch((error) => {
                setBusy("#syncTutorialBtn", false);
                if (error.response && error.response.status === 422) {
                    paintErrors("#syncTutorialForm", error.response.data.errors || {});
                }
            });
    });

    /* ---- Assigned students ------------------------------------------- */

    let studentsTable = null;

    $(layout).on("click", ".assignedStudents", function () {
        const planId = this.getAttribute("data-id");

        modal("viewAssignedStudentModal").show();
        studentsTable = new Tabulator("#assignedStudentModalListTable", {
            ajaxURL: route("plans.tree.assigned.list"),
            ajaxParams: { plan_id: planId },
            ajaxFiltering: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: PAGINATION_SIZES,
            layout: "fitColumns",
            placeholder: "No students assigned",
            langs: PAGINATION_LANGS,
            columns: [
                {
                    // Photo and registration number share a cell, as they do in
                    // the existing list.
                    title: "Reg. No",
                    field: "registration_no",
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 178,
                    formatter(cell) {
                        const d = cell.getData();
                        const initials = `${(d.first_name || "")[0] || ""}${(d.last_name || "")[0] || ""}`.toUpperCase();
                        const avatar = d.photo_url
                            ? `<span class="cm-avatar"><img src="${d.photo_url}" alt=""></span>`
                            // Tone keyed off the id, so a student keeps the same
                            // colour every time the list is opened.
                            : `<span class="cm-avatar" data-cm-tone="${(Number(d.id) || 0) % 6}">${initials}</span>`;

                        return (
                            '<span class="cm-studentcell">' +
                            avatar +
                            `<a class="cm-studentlink" href="${d.url}">${cell.getValue() || ""}</a>` +
                            "</span>"
                        );
                    },
                },
                { title: "First Name", field: "first_name", headerHozAlign: "left", widthGrow: 1, minWidth: 110 },
                { title: "Last Name", field: "last_name", headerHozAlign: "left", widthGrow: 1, minWidth: 110 },
                {
                    // Untitled in the existing list too: a full-time student
                    // shows the sunset glyph, part-time the sun.
                    title: "",
                    field: "full_time",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: 58,
                    formatter(cell) {
                        const full = cell.getValue() == 1;

                        return (
                            `<span class="cm-fulltime ${full ? "is-full" : ""}" title="${full ? "Full time" : "Part time"}">` +
                            (full
                                ? '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 10V2M4.93 10.93l1.41 1.41M2 18h2M20 18h2M19.07 10.93l-1.41 1.41M22 22H2M16 6l-4 4-4-4M16 18a4 4 0 0 0-8 0"></path></svg>'
                                : '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>') +
                            "</span>"
                        );
                    },
                },
                { title: "Semester", field: "semester", headerHozAlign: "left", widthGrow: 1, minWidth: 112 },
                {
                    // Wraps rather than ellipsising — the course name is the one
                    // value here long enough to be cut, and it is worth reading.
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                    widthGrow: 2,
                    minWidth: 190,
                    variableHeight: true,
                    cssClass: "cm-cell--wrap",
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 150,
                    formatter(cell) {
                        const v = String(cell.getValue() || "");
                        if (!v) return "";

                        // Anything that reads as a problem is flagged; the rest
                        // is treated as a normal standing.
                        const bad = /suspend|withdraw|discard|defer|terminat|fail/i.test(v);

                        return `<span class="cm-statusbadge ${bad ? "is-bad" : ""}">${v}</span>`;
                    },
                },
            ],
            renderComplete() {
                refreshIcons();
                // fitColumns leaves a sub-pixel overflow that shows as a
                // horizontal scrollbar; every table in this module trims the
                // last column by 1px to absorb it.
                const cols = this.getColumns();
                if (cols.length > 0) {
                    const last = cols[cols.length - 1];
                    last.setWidth(last.getWidth() - 1);
                }
            },
        });
    });

    /* ---- Assign manager / audit user ---------------------------------- */

    /* `plan_participants.type` holds exactly these two words. They are what
     * `getAssignDetails` filters the existing assignments by and what
     * `assignParticipants` writes, and `getAssignDetails` compares the title
     * against 'Auditor' case-sensitively — so the casing is part of the
     * contract, not a label. */
    const ASSIGN_MANAGER = "Manager";
    const ASSIGN_AUDITOR = "Auditor";

    /* One searchable list of people, built the first time the modal opens. */
    let assignedUsers = null;

    function assignedUsersControl() {
        const select = document.querySelector("#assigned_user_ids");
        if (!select) return null;

        if (!assignedUsers) {
            assignedUsers = new TomSelect(select, {
                placeholder: "Search people…",
                // The modal clips its own overflow, so the list has to hang
                // off <body> to stay whole.
                dropdownParent: "body",
                dropdownClass: "ts-dropdown cm-tom-dropdown",
                plugins: ["remove_button"],
                maxOptions: 500,
            });
        }

        return assignedUsers;
    }

    function openAssign(btn, type, title) {
        const users = assignedUsersControl();

        axios({
            method: "post",
            url: route("plans.get.assign.details"),
            data: {
                yearid: btn.getAttribute("data-yearid"),
                termid: btn.getAttribute("data-attendanceSemester"),
                courseid: btn.getAttribute("data-courseid"),
                groupid: btn.getAttribute("data-groupid"),
                type,
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                clearErrors("#assignManagerOrCoOrdinatorForm");

                // The server sends the whole trail —
                // "<u>year</u> > <u>term</u> > <u>course</u> > <u>group</u> >
                // Assign <u>Manager</u>". The heading already names the role,
                // so only the trail goes in the eyebrow.
                const trail = String(response.data.title || "").split(/\s>\sAssign\s/)[0];
                $("[data-cm-assign-eyebrow]").html(trail || "Group");
                $("[data-cm-assign-title]").text(title);

                $('#assignManagerOrCoOrdinatorForm input[name="type"]').val(type);

                // `assignParticipants` explodes this on commas, so it has to
                // travel as a string — an array is a 500.
                $('#assignManagerOrCoOrdinatorForm input[name="plan_ids"]').val((response.data.plans || []).join(","));

                // Whoever is already assigned comes back as `participants`;
                // they must show as selected so a save does not drop them.
                if (users) {
                    users.clear(true);
                    (response.data.participants || []).forEach((id) => users.addItem(String(id), true));
                }

                modal("assignManagerOrCoOrdinatorModal").show();
            })
            .catch(() => {
                showSuccess("Something went wrong", "The assignment details could not be loaded.", "warn");
            });
    }

    /* Delegated from `document`, not from the tree: tw-starter's dropdown
     * `appendTo("body")`s the open menu and puts it back on close, so by the
     * time either of these is clicked it is no longer a descendant of
     * `.cm-treelayout` and a handler bound there would never fire. Nothing
     * else on this screen sits inside a dropdown, which is why the rest stay
     * delegated from the layout.
     *
     * `preventDefault` only — stopping propagation would also stop the
     * library's own body-click handler, leaving the menu open behind the
     * modal. */
    $(document).on("click", ".assignManager", function (event) {
        event.preventDefault();
        openAssign(this, ASSIGN_MANAGER, "Assign Manager");
    });

    $(document).on("click", ".assignCoOrdinator", function (event) {
        event.preventDefault();
        openAssign(this, ASSIGN_AUDITOR, "Audit User");
    });

    $("#assignManagerOrCoOrdinatorForm").on("submit", function (event) {
        event.preventDefault();
        clearErrors("#assignManagerOrCoOrdinatorForm");

        const form = this;
        const planIds = String($('input[name="plan_ids"]', form).val() || "");

        // A group with no plans yet has nothing to assign anyone to. The
        // endpoint would answer 422 with a bare message and no field to hang
        // it on, so say it here instead.
        if (planIds === "") {
            paintErrors("#assignManagerOrCoOrdinatorForm", {
                assigned_user_ids: "This group has no class plans yet. Add a plan before assigning people.",
            });

            return;
        }

        setBusy("#assignParticipantsBtn", true);

        const payload = {
            type: $('input[name="type"]', form).val() || ASSIGN_MANAGER,
            plan_ids: planIds,
            assigned_user_ids: assignedUsers ? assignedUsers.getValue() : $("#assigned_user_ids").val() || [],
        };

        axios({ method: "post", url: route("plans.assign.participants"), data: payload, headers: csrfHeaders() })
            .then(() => {
                setBusy("#assignParticipantsBtn", false);
                modal("assignManagerOrCoOrdinatorModal").hide();
                showSuccess("Congratulations!", "The people were assigned.");
            })
            .catch((error) => {
                setBusy("#assignParticipantsBtn", false);
                if (error.response && error.response.status === 422) {
                    const data = error.response.data || {};
                    paintErrors(
                        "#assignManagerOrCoOrdinatorForm",
                        data.errors || { assigned_user_ids: data.message || "The people could not be assigned." }
                    );
                }
            });
    });
})();
