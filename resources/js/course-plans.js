/*
 * Class Plans list for the redesigned Course Management module.
 *
 * A like-for-like port of the legacy `plan.js` list screen — same endpoints and
 * payloads — rebuilt against the new markup with the shared plumbing from
 * `course-table-kit.js`.
 *
 * Two legacy defects are fixed by construction here:
 *
 *   - The Status filter never worked. The blade rendered `id="statusCPL"` while
 *     the script read `$("#status-CPL")`, so `status` was always `undefined`,
 *     was dropped from the request, and the controller fell back to Active.
 *     Archived plans were unreachable from this screen.
 *   - The calendar (grid) view wrote its server-rendered HTML over the
 *     Tabulator element, so switching back to the list left a dead table until
 *     a full page reload. The two views now have separate hosts.
 */

import IMask from "imask";
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

const TABLE_ID = "#classPlansListTable";

const VIEW_LIST = "1";
const VIEW_CALENDAR = "2";
const VIEW_TREE = "3";

/* Endpoints on this controller build their JSON from a 1-based PHP counter, so
 * the payload is an object (`{"1":{…}}`), not an array. */
function toRows(res) {
    if (Array.isArray(res)) return res;
    if (res && typeof res === "object") return Object.values(res);

    return [];
}

/* `class.plan.edit` returns dates as they sit in the database. The field is a
 * DD-MM-YYYY picker, and the update endpoint runs everything through strtotime,
 * so reformatting here is display-only. */
function toPickerDate(value) {
    if (!value) return "";

    const iso = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!iso) return String(value);
    if (iso[1] === "0000") return "";

    return `${iso[3]}-${iso[2]}-${iso[1]}`;
}

/** Table display for a stored date: DD-MM-YYYY, or an em dash when unset. */
function toListDate(value) {
    if (!value) return "—";

    const iso = String(value).match(/^(-?\d{4})-(\d{2})-(\d{2})/);
    if (!iso) return String(value);

    const year = parseInt(iso[1], 10);
    if (!(year > 1900 && year < 2200)) return "—";

    return `${iso[3]}-${iso[2]}-${iso[1]}`;
}

(function () {
    if (!$(TABLE_ID).length) return;

    const editPlanModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPlanModal"));

    const listPane = document.querySelector('[data-cm-pane="list"]');
    const calendarPane = document.querySelector('[data-cm-pane="grid"]');
    const generateBtn = document.querySelector("#generateDaysBtn");
    const viewSelect = document.querySelector("#plan-view");

    let lastTotal = null;
    let tableContent = null;

    const getTable = () => tableContent;
    const paintRange = createRangePainter(TABLE_ID, getTable, () => lastTotal);

    /* ------------------------------------------------------------------ *
     * Filter controls
     * ------------------------------------------------------------------ */

    const tomBase = {
        plugins: { dropdown_input: {} },
        placeholder: "Please Select",
        dropdownParent: "body",
        dropdownClass: "ts-dropdown cm-tom-dropdown",
        allowEmptyOption: true,
    };
    const tomMultiple = {
        ...tomBase,
        plugins: { ...tomBase.plugins, remove_button: { title: "Remove this item" } },
    };

    const coursesTom = new TomSelect("#plan-courses", tomBase);
    const termsTom = new TomSelect("#plan-term", tomBase);
    const groupsTom = new TomSelect("#plan-group", tomBase);
    const tutorsTom = new TomSelect("#plan-tutor", tomMultiple);
    const ptutorsTom = new TomSelect("#plan-ptutor", tomMultiple);
    const roomsTom = new TomSelect("#plan-room", tomMultiple);
    const daysTom = new TomSelect("#plan-days", tomMultiple);

    // The two tutor fields list every active user, so they keep the searchable
    // control the legacy modal had. The other modal selects are short enough to
    // stay native. Both are re-parented to <body>, hence the modal z-index in
    // the `cm-tom-dropdown` skin.
    const tutorTom = new TomSelect("#tutor_id", tomBase);
    const personalTutorTom = new TomSelect("#personal_tutor_id", tomBase);

    groupsTom.disable();

    const val = (selector) => {
        const value = $(selector).val();

        return value === null || value === undefined || value === "" ? "" : value;
    };

    /** The parameter names the list endpoint expects, which are not the form's. */
    function listParams() {
        return {
            courses: val("#plan-courses"),
            term_declarations: val("#plan-term"),
            group: val("#plan-group"),
            room: val("#plan-room"),
            tutor: val("#plan-tutor"),
            ptutor: val("#plan-ptutor"),
            days: val("#plan-days"),
            status: val("#plan-status"),
            dates: val("#plan-date"),
        };
    }

    /** …and the grid endpoint's, which differ again (`term_declaration`). */
    function calendarParams() {
        return {
            courses: val("#plan-courses"),
            term_declaration: val("#plan-term"),
            group: val("#plan-group"),
            room: val("#plan-room"),
            tutor: val("#plan-tutor"),
            ptutor: val("#plan-ptutor"),
            days: val("#plan-days"),
            status: val("#plan-status"),
            dates: val("#plan-date"),
        };
    }

    // Groups are only meaningful for one course within one term, so the field
    // stays disabled until both are chosen.
    $("#plan-courses, #plan-term").on("change", function () {
        const course = val("#plan-courses");
        const term = val("#plan-term");

        groupsTom.clear(true);
        groupsTom.clearOptions();

        if (!(course > 0 && term > 0)) {
            groupsTom.disable();

            return;
        }

        axios({
            method: "post",
            url: route("class.plan.get.group.filter"),
            data: { course, term },
            headers: csrfHeaders(),
        })
            .then((response) => {
                groupsTom.enable();
                toRows(response.data.res).forEach((row) => {
                    groupsTom.addOption({ value: row.id, text: row.name });
                });
                groupsTom.refreshOptions(false);
            })
            .catch(() => {
                groupsTom.enable();
            });
    });

    /* ------------------------------------------------------------------ *
     * List view
     * ------------------------------------------------------------------ */

    function syncGenerateButton() {
        if (!generateBtn) return;

        const selected = tableContent ? tableContent.getSelectedRows().length : 0;
        generateBtn.hidden = selected === 0;
    }

    function buildTable() {
        tableContent = new Tabulator(TABLE_ID, {
            ajaxURL: route("class.plan.list"),
            ajaxParams: listParams(),
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: PAGINATION_SIZES,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records",
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
                    cssClass: "cm-cell--check",
                    headerSort: false,
                    download: false,
                    cellClick(e, cell) {
                        cell.getRow().toggleSelect();
                    },
                },
                { title: "#ID", field: "id", width: 58 },
                {
                    // Module and course read as one thing, and both are long
                    // enough that side-by-side columns forced either an ellipsis
                    // or a four-line wrap. Stacked, the pair costs one column.
                    title: "Module",
                    field: "module",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 2,
                    minWidth: 160,
                    variableHeight: true,
                    cssClass: "cm-cell--wrap",
                    formatter(cell) {
                        const data = cell.getData();
                        const href = route("tutor-dashboard.plan.module.show", data.id);

                        return (
                            '<span class="cm-stack">' +
                            `<a class="cm-planlink" href="${href}">${data.module || ""}</a>` +
                            `<span class="cm-stack__sub">${data.course || ""}</span>` +
                            "</span>"
                        );
                    },
                },
                {
                    title: "Group",
                    field: "group",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 96,
                    cssClass: "cm-cell--clamp",
                },
                {
                    title: "Room",
                    field: "room",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 110,
                    variableHeight: true,
                    cssClass: "cm-cell--wrap",
                    formatter(cell) {
                        const data = cell.getData();

                        return (
                            '<span class="cm-stack">' +
                            `<span class="cm-stack__name">${data.room_name || ""}</span>` +
                            `<span class="cm-stack__sub">${data.venue_name || ""}</span>` +
                            "</span>"
                        );
                    },
                },
                {
                    title: "Day - Time",
                    field: "day",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 88,
                    formatter(cell) {
                        const data = cell.getData();

                        return (
                            '<span class="cm-stack">' +
                            `<span class="cm-stack__name">${data.day || ""}</span>` +
                            `<span class="cm-stack__sub">${data.time || ""}</span>` +
                            "</span>"
                        );
                    },
                },
                {
                    title: "Submission",
                    field: "submission_date",
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 88,
                    formatter(cell) {
                        // Plans with no submission date carry a zero date, which
                        // the legacy list printed raw as "30-11--0001" / "01-01-1970".
                        return toListDate(cell.getValue());
                    },
                },
                // 106, not 84: the uppercase header plus its sort caret is wider
                // than any value in the column, and a fixed column cannot grow.
                { title: "Class Type", field: "class_type", headerHozAlign: "left", width: 106 },
                {
                    // Tutorial and Seminar classes are led by the personal tutor;
                    // everything else by the module tutor.
                    title: "Tutor / P. Tutor",
                    field: "tutor",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 104,
                    cssClass: "cm-cell--clamp",
                    formatter(cell) {
                        const data = cell.getData();
                        const isTutorial = data.class_type === "Tutorial" || data.class_type === "Seminar";

                        return (isTutorial ? data.personalTutor : data.tutor) || "";
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 136,
                    download: false,
                    formatter(cell) {
                        const data = cell.getData();

                        if (data.deleted_at) {
                            return (
                                '<span class="cm-rowactions">' +
                                `<button type="button" data-id="${data.id}" title="Restore plan" class="restore_btn cm-rowbtn cm-rowbtn--restore"><i data-lucide="rotate-cw"></i></button>` +
                                "</span>"
                            );
                        }

                        let btns = '<span class="cm-rowactions">';

                        // Only plans whose days have been generated have days to view.
                        if (data.dates == 1) {
                            btns += `<a href="${route("plan.dates", data.id)}" title="View days" class="cm-rowbtn cm-rowbtn--view"><i data-lucide="calendar"></i></a>`;
                        }

                        btns += `<button type="button" data-id="${data.id}" data-tw-toggle="modal" data-tw-target="#editPlanModal" title="Edit plan" class="edit_btn cm-rowbtn cm-rowbtn--edit"><i data-lucide="pencil"></i></button>`;
                        btns += `<button type="button" data-id="${data.id}" title="Delete plan" class="delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>`;

                        return btns + "</span>";
                    },
                },
            ],
            rowSelectionChanged: syncGenerateButton,
            renderComplete() {
                refreshIcons();
                paintRange();

                const cols = this.getColumns();
                if (cols.length > 0) {
                    const last = cols[cols.length - 1];
                    last.setWidth(last.getWidth() - 1);
                }
            },
        });
    }

    /* ------------------------------------------------------------------ *
     * Calendar view — a server-rendered room x day timetable
     * ------------------------------------------------------------------ */

    function showPane(which) {
        if (listPane) listPane.hidden = which !== "list";
        if (calendarPane) calendarPane.hidden = which !== "grid";
        if (generateBtn && which !== "list") generateBtn.hidden = true;
    }

    function loadCalendar() {
        if (!calendarPane) return;

        calendarPane.innerHTML = '<div class="cm-planscal__loading">Loading timetable…</div>';
        showPane("grid");

        axios({
            method: "post",
            url: route("class.plan.grid"),
            data: calendarParams(),
            headers: csrfHeaders(),
        })
            .then((response) => {
                calendarPane.innerHTML = response.data.htm || "";
                refreshIcons();
            })
            .catch(() => {
                calendarPane.innerHTML = '<div class="cm-planscal__loading">The timetable could not be loaded.</div>';
            });
    }

    function applyView() {
        const view = viewSelect ? viewSelect.value : VIEW_LIST;

        if (view === VIEW_TREE) {
            window.location.href = route("plans.tree");

            return;
        }

        if (view === VIEW_CALENDAR) {
            loadCalendar();

            return;
        }

        showPane("list");
        buildTable();
    }

    buildTable();
    wireResize(getTable);

    $("#planFilterForm").on("submit", function (event) {
        event.preventDefault();
        applyView();
    });

    $("#planFilterReset").on("click", function () {
        [coursesTom, termsTom, groupsTom, tutorsTom, ptutorsTom, roomsTom, daysTom].forEach((instance) => {
            instance.clear(true);
        });
        groupsTom.clearOptions();
        groupsTom.disable();

        $("#plan-date").val("");
        $("#plan-status").val("1");
        if (viewSelect) viewSelect.value = VIEW_LIST;

        showPane("list");
        buildTable();
    });

    /* ------------------------------------------------------------------ *
     * Generate days
     * ------------------------------------------------------------------ */

    if (generateBtn) {
        generateBtn.addEventListener("click", function () {
            const ids = tableContent ? tableContent.getSelectedData().map((row) => row.id) : [];
            if (!ids.length) return;

            openConfirm({
                id: ids.join(","),
                action: "GENERATE",
                title: "Generate class days?",
                message: `Class days will be generated across the term for ${ids.length} selected ${ids.length === 1 ? "plan" : "plans"}.`,
                confirmLabel: "Yes, generate",
                tone: "restore",
            });
        });
    }

    function generateDays(ids) {
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
                showSuccess(response.data.title, response.data.Message);
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
                buildTable();
            });
    }

    /* ------------------------------------------------------------------ *
     * Edit plan
     * ------------------------------------------------------------------ */

    $(".theTimeField").each(function () {
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

    /** The Class Day chip row stands in for the legacy radio group; the value
     *  still reaches the server through the hidden input beside it. */
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

    $("#class_type").on("change", syncTutorField);

    // Tutorial and Seminar classes are led by the personal tutor, so the Tutor
    // field is withdrawn — and cleared, so a stale id is never posted.
    function syncTutorField() {
        const type = $("#class_type").val();
        const isTutorial = type === "Tutorial" || type === "Seminar";

        $("#editPlanModal .tutorWrap").toggle(!isTutorial);
        if (isTutorial) tutorTom.clear(true);
    }

    document.getElementById("editPlanModal").addEventListener("hide.tw.modal", function () {
        clearErrors("#editPlanForm");
        $("#editPlanForm select").val("");
        $("#editPlanForm textarea").val("");
        $('#editPlanForm input[type="text"]').val("");
        $('#editPlanForm input[name="id"]').val("0");
        tutorTom.clear(true);
        personalTutorTom.clear(true);
        setPick("class_day", "");
        $("#editPlanModal .tutorWrap").show();
    });

    $(TABLE_ID).on("click", ".edit_btn", function () {
        const planId = $(this).attr("data-id");

        axios({
            method: "get",
            url: route("class.plan.edit", planId),
            headers: csrfHeaders(),
        }).then((response) => {
            const plan = response.data.plan;

            $("#editPlanModal .termName").text(plan.term || "—");
            $("#editPlanModal .courseName").text(plan.course || "—");
            $("#editPlanModal .groupName").text(plan.group || "—");

            $("#module_creation_id").html(plan.modules || "");
            $("#rooms_id").val(plan.rooms_id || "");

            // `.val()` alone would not repaint a TomSelect control.
            tutorTom.clear(true);
            personalTutorTom.clear(true);
            if (plan.tutor_id) tutorTom.addItem(String(plan.tutor_id), true);
            if (plan.personal_tutor_id) personalTutorTom.addItem(String(plan.personal_tutor_id), true);
            $("#start_time").val(plan.start_time || "");
            $("#end_time").val(plan.end_time || "");
            $("#submission_date").val(toPickerDate(plan.submission_date));
            $("#virtual_room").val(plan.virtual_room || "");
            $("#note").val(plan.note || "");

            $("#class_type").val(plan.class_type || "");
            syncTutorField();

            const day = ["sat", "sun", "mon", "tue", "wed", "thu", "fri"].find((name) => plan[name] == 1);
            setPick("class_day", day || "");

            $('#editPlanForm input[name="id"]').val(planId);
        });
    });

    $("#editPlanForm").on("submit", function (event) {
        event.preventDefault();

        clearErrors("#editPlanForm");
        setBusy("#updatePlans", true);

        axios({
            method: "post",
            url: route("class.plan.update"),
            data: new FormData(document.getElementById("editPlanForm")),
            headers: csrfHeaders(),
        })
            .then(() => {
                setBusy("#updatePlans", false);
                editPlanModal.hide();
                showSuccess("Congratulations!", "The class plan was updated.");
                buildTable();
            })
            .catch((error) => {
                setBusy("#updatePlans", false);
                if (error.response && error.response.status === 422) {
                    paintErrors("#editPlanForm", error.response.data.errors);
                }
            });
    });

    /* ------------------------------------------------------------------ *
     * Delete / restore
     * ------------------------------------------------------------------ */

    $(TABLE_ID).on("click", ".delete_btn", function () {
        const id = $(this).attr("data-id");

        openConfirm({
            id,
            action: "DELETE",
            title: "Delete this plan?",
            message: "This class plan will be removed from the list.",
            confirmLabel: "Delete",
        });
    });

    $(TABLE_ID).on("click", ".restore_btn", function () {
        const id = $(this).attr("data-id");

        openConfirm({
            id,
            action: "RESTORE",
            title: "Restore this plan?",
            message: "This class plan will be returned to the active list.",
            confirmLabel: "Restore",
            tone: "restore",
        });
    });

    wireConfirmReset();

    $("#confirmModal .agreeWith").on("click", function () {
        const id = $(this).attr("data-id");
        const action = $(this).attr("data-action");

        if (action === "GENERATE") {
            generateDays(String(id).split(","));

            return;
        }

        if (action !== "DELETE" && action !== "RESTORE") return;

        const isDelete = action === "DELETE";

        axios({
            method: isDelete ? "delete" : "post",
            url: isDelete ? route("class.plan.delete", id) : route("class.plan.restore", id),
            headers: csrfHeaders(),
        })
            .then(() => {
                hideConfirm();
                showSuccess(
                    "Congratulations!",
                    isDelete ? "The class plan was deleted." : "The class plan was restored."
                );
                buildTable();
            })
            .catch(() => {
                hideConfirm();
                showSuccess("Something went wrong", "The action could not be completed.", "warn");
            });
    });

    /* ------------------------------------------------------------------ *
     * Export
     * ------------------------------------------------------------------ */

    $("#exportPlansXLSX").on("click", function () {
        setBusy("#exportPlansXLSX", true);

        axios({
            method: "post",
            url: route("class.plan.export"),
            data: new FormData(document.getElementById("planFilterForm")),
            headers: csrfHeaders(),
            responseType: "blob",
        })
            .then((response) => {
                setBusy("#exportPlansXLSX", false);

                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", "Plan_List.xlsx");
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            })
            .catch(() => {
                setBusy("#exportPlansXLSX", false);
                showSuccess("Export failed", "The plan list could not be exported.", "warn");
            });
    });
})();
