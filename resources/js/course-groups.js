/*
 * Groups list for the redesigned Course Management module.
 *
 * A like-for-like port of the legacy `groups.js` — same endpoints, same
 * payloads, same add / edit / delete / restore / bulk flows — rebuilt against
 * the new markup with the shared plumbing from `course-table-kit.js`.
 *
 * Two things the legacy page could not do are fixed by construction:
 *
 *   - The blade shipped duplicate ids (`term`, `course_id` appeared in both the
 *     filter row and the modals), so TomSelect bound to whichever came first
 *     and the modal's own course box was left unmanaged. Every control here has
 *     its own id.
 *   - The Status filter had no "All", so a list mixing active, inactive and
 *     archived groups could not be seen in one place.
 */

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
    wireExports,
    wireResize,
} from "./course-table-kit";

const TABLE_ID = "#groupsTableId";

/** Copy for each bulk action, keyed by the value the endpoint expects. */
const BULK_ACTIONS = {
    ACTIVEALL: {
        title: "Mark as active?",
        message: (n) => `${n} will be available for planning again.`,
        confirmLabel: "Mark active",
        tone: "safe",
        done: ["Groups updated", "The selected groups are now active."],
    },
    INACTIVEALL: {
        title: "Mark as inactive?",
        message: (n) => `${n} will be hidden from planning until reactivated.`,
        confirmLabel: "Mark inactive",
        tone: "safe",
        done: ["Groups updated", "The selected groups are now inactive."],
    },
    DELETEALL: {
        title: "Move to archive?",
        message: (n) => `${n} will be moved to the archive and hidden from this list.`,
        confirmLabel: "Archive",
        tone: "danger",
        done: ["Groups archived", "The selected groups have been moved to the archive."],
    },
    RESTOREALL: {
        title: "Restore these groups?",
        message: (n) => `${n} will come out of the archive with the status they had before.`,
        confirmLabel: "Restore",
        tone: "safe",
        // Restoring clears the archive flag but leaves `active` alone, so a
        // group archived while inactive comes back under Inactive, not Active.
        done: ["Groups restored", "The selected groups are back in the list with their previous status."],
    },
};

const countLabel = (n) => `${n} ${n === 1 ? "group" : "groups"}`;

(function () {
    if (!$(TABLE_ID).length) return;

    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));

    const bulkBox = document.querySelector("[data-cm-bulk]");
    const selLabel = document.querySelector("[data-cm-selcount]");

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

    // The full course list as rendered, kept before TomSelect takes the element
    // over: the Term filter narrows the Course options to that term's courses,
    // and clearing the term has to put every course back.
    const allCourses = $("#group-course option")
        .map(function () {
            return { value: this.value, text: this.textContent };
        })
        .get()
        .filter((option) => option.value !== "");

    const termTom = new TomSelect("#group-term", tomBase);
    const courseTom = new TomSelect("#group-course", { ...tomBase, placeholder: "All courses" });
    const addCourseTom = new TomSelect("#add_course_id", tomBase);
    const editCourseTom = new TomSelect("#edit_course_id", tomBase);

    const val = (selector) => {
        const value = $(selector).val();

        return value === null || value === undefined ? "" : value;
    };

    /** The parameter names the list endpoint expects. */
    function listParams() {
        return {
            querystr: val("#group-query"),
            term: val("#group-term"),
            course_id: val("#group-course"),
            status: val("#group-status"),
        };
    }

    /** Repaints the Course filter with `options`, keeping "All courses" first. */
    function fillCourseFilter(options) {
        courseTom.clear(true);
        courseTom.clearOptions();
        courseTom.addOption({ value: "", text: "All courses" });
        options.forEach((option) => courseTom.addOption(option));
        courseTom.refreshOptions(false);
    }

    // Only the courses that actually have a group in the chosen term are worth
    // offering — the same narrowing the legacy screen did.
    $("#group-term").on("change", function () {
        const term = val("#group-term");

        if (!(term > 0)) {
            fillCourseFilter(allCourses);
            return;
        }

        courseTom.disable();

        axios({ method: "get", url: route("group.courselist.by.term", term), headers: csrfHeaders() })
            .then((response) => {
                courseTom.enable();

                if (response.status == 200) {
                    fillCourseFilter(
                        (response.data || []).map((row) => ({ value: String(row.id), text: row.name })),
                    );
                }
            })
            .catch((error) => {
                courseTom.enable();
                console.log(error);
            });
    });

    $("#groupFilterForm").on("submit", function (e) {
        e.preventDefault();
        buildTable();
    });

    $("#groupFilterReset").on("click", function () {
        $("#group-query").val("");
        $("#group-status").val("1");
        termTom.clear(true);
        fillCourseFilter(allCourses);
        buildTable();
    });

    /* ------------------------------------------------------------------ *
     * Selection — reveals the Group Actions menu
     * ------------------------------------------------------------------ */

    /** Ids of the selected rows, in the order the table holds them. */
    function selectedIds() {
        return tableContent ? tableContent.getSelectedData().map((row) => row.id) : [];
    }

    function paintSelection() {
        const picked = selectedIds().length;

        if (bulkBox) bulkBox.hidden = picked === 0;
        if (selLabel) {
            selLabel.hidden = picked === 0;
            selLabel.textContent = picked === 0 ? "" : `${picked} selected`;
        }

        // A menu left open over a cleared selection would act on nothing.
        if (picked === 0) {
            tailwind.Dropdown.getOrCreateInstance(document.querySelector("#groupActionDropdown")).hide();
        }
    }

    /* ------------------------------------------------------------------ *
     * Table
     * ------------------------------------------------------------------ */

    function buildTable() {
        tableContent = new Tabulator(TABLE_ID, {
            ajaxURL: route("groups.list"),
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
                { title: "#ID", field: "id", width: 84 },
                {
                    // Term and course are relation values, not columns, so the
                    // endpoint cannot sort on them.
                    title: "Term",
                    field: "term",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 120,
                    cssClass: "cm-cell--clamp",
                },
                {
                    title: "Course",
                    field: "course",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 2,
                    minWidth: 180,
                    cssClass: "cm-cell--clamp",
                },
                {
                    title: "Group Name",
                    field: "name",
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 140,
                    cssClass: "cm-cell--primary",
                },
                {
                    title: "Evening & Weekend",
                    field: "evening_and_weekend",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 158,
                    formatter(cell) {
                        const isEve = cell.getValue() === "Yes";

                        return (
                            `<span class="cm-daychip ${isEve ? "is-eve" : ""}" title="${isEve ? "Evening &amp; weekend" : "Weekday only"}">` +
                            (isEve
                                ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 10V2M4.93 10.93l1.41 1.41M2 18h2M20 18h2M19.07 10.93l-1.41 1.41M22 22H2M16 6l-4 4-4-4M16 18a4 4 0 0 0-8 0"></path></svg>'
                                : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>') +
                            "</span>"
                        );
                    },
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    width: 128,
                    formatter(cell) {
                        const data = cell.getData();
                        // Archived is a state of its own: an archived group can
                        // still carry `active = 1` underneath.
                        const state = data.deleted_at
                            ? { tone: "is-archived", label: "Archived" }
                            : data.active == 1
                              ? { tone: "is-ok", label: "Active" }
                              : { tone: "is-off", label: "Inactive" };

                        return `<span class="cm-statusline ${state.tone}"><span class="cm-statusline__dot"></span>${state.label}</span>`;
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 120,
                    download: false,
                    formatter(cell) {
                        // Only the row id is interpolated — an integer from the
                        // list endpoint, never free text.
                        const data = cell.getData();
                        let btns = '<span class="cm-rowactions">';

                        if (data.deleted_at == null) {
                            btns += `<button type="button" data-id="${data.id}" data-tw-toggle="modal" data-tw-target="#editModal" title="Edit group" class="edit_btn cm-rowbtn cm-rowbtn--edit"><i data-lucide="pencil"></i></button>`;
                            btns += `<button type="button" data-id="${data.id}" title="Delete group" class="delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>`;
                        } else {
                            btns += `<button type="button" data-id="${data.id}" title="Restore group" class="restore_btn cm-rowbtn cm-rowbtn--restore"><i data-lucide="rotate-ccw"></i></button>`;
                        }

                        return btns + "</span>";
                    },
                },
            ],
            rowSelectionChanged: paintSelection,
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

        // A rebuild drops the old selection with the old rows.
        paintSelection();
        wireExports(getTable, "groups", "Groups");
    }

    buildTable();
    wireResize(getTable);
    wireConfirmReset();

    /* ------------------------------------------------------------------ *
     * Create / update — unchanged endpoints and payloads
     * ------------------------------------------------------------------ */

    /** Puts a modal back to its opening state. */
    function resetForm(prefix, tom) {
        clearErrors(`#${prefix}Form`);
        $(`#${prefix}Modal input[name="name"]`).val("");
        $(`#${prefix}Modal select[name="term_declaration_id"]`).val("");
        $(`#${prefix}Modal input[name="evening_and_weekend"]`).prop("checked", false);
        $(`#${prefix}Modal input[name="active"]`).prop("checked", true);
        tom.clear(true);
    }

    // Native listeners, not jQuery: `.on("hide.tw.modal")` would be read as the
    // event "hide" in the "tw.modal" namespace and never fire, because the theme
    // dispatches a CustomEvent whose *type* contains the dots.
    document.querySelector("#addModal").addEventListener("hide.tw.modal", function () {
        resetForm("add", addCourseTom);
    });

    document.querySelector("#editModal").addEventListener("hide.tw.modal", function () {
        resetForm("edit", editCourseTom);
        $('#editModal input[name="id"]').val("0");
    });

    $("#addForm").on("submit", function (e) {
        e.preventDefault();

        clearErrors("#addForm");
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("groups.store"),
            data: new FormData(document.getElementById("addForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#save", false);

                if (response.status == 200) {
                    addModal.hide();
                    showSuccess("Group added", "The group has been created successfully.");
                }
                buildTable();
            })
            .catch((error) => {
                setBusy("#save", false);
                if (error.response && error.response.status == 422) {
                    paintErrors("#addForm", error.response.data.errors);
                }
            });
    });

    $(TABLE_ID).on("click", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        clearErrors("#editForm");

        axios({ method: "get", url: route("groups.edit", editId), headers: csrfHeaders() })
            .then((response) => {
                if (response.status != 200) return;

                const dataset = response.data;

                $('#editModal select[name="term_declaration_id"]').val(
                    dataset.term_declaration_id ? dataset.term_declaration_id : "",
                );
                $('#editModal input[name="name"]').val(dataset.name ? dataset.name : "");

                // `.val()` alone would not repaint a TomSelect control.
                if (dataset.course_id > 0) editCourseTom.addItem(dataset.course_id, true);
                else editCourseTom.clear(true);

                $('#editModal input[name="evening_and_weekend"]').prop("checked", dataset.evening_and_weekend == 1);
                $('#editModal input[name="active"]').prop("checked", dataset.active == 1);
                $('#editModal input[name="id"]').val(editId);
            })
            .catch((error) => {
                console.log(error);
            });
    });

    $("#editForm").on("submit", function (e) {
        e.preventDefault();
        const editId = $('#editModal input[name="id"]').val();

        clearErrors("#editForm");
        setBusy("#update", true);

        axios({
            method: "post",
            url: route("groups.update", editId),
            data: new FormData(document.getElementById("editForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#update", false);

                if (response.status == 200) {
                    editModal.hide();
                    showSuccess("Group updated", "The changes have been saved successfully.");
                }
                buildTable();
            })
            .catch((error) => {
                setBusy("#update", false);

                if (error.response) {
                    if (error.response.status == 422) {
                        paintErrors("#editForm", error.response.data.errors);
                    } else if (error.response.status == 304) {
                        editModal.hide();
                        showSuccess("No change", "Nothing was modified on this record.");
                    }
                }
            });
    });

    /* ------------------------------------------------------------------ *
     * Row actions and bulk actions — one confirm dialog for all of them
     * ------------------------------------------------------------------ */

    $(TABLE_ID).on("click", ".delete_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "DELETE",
            title: "Delete this group?",
            message: "The group will be moved to the archive and hidden from this list.",
            confirmLabel: "Delete",
            tone: "danger",
        });
    });

    $(TABLE_ID).on("click", ".restore_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "RESTORE",
            title: "Restore this group?",
            message: "The group will come out of the archive with the status it had before.",
            confirmLabel: "Restore",
            tone: "safe",
        });
    });

    $(".groupActionBTN").on("click", function (e) {
        e.preventDefault();

        const action = $(this).attr("data-action");
        const ids = selectedIds();
        const copy = BULK_ACTIONS[action];
        if (!copy || ids.length === 0) return;

        tailwind.Dropdown.getOrCreateInstance(document.querySelector("#groupActionDropdown")).hide();

        openConfirm({
            id: ids.join(","),
            action,
            title: copy.title,
            message: copy.message(countLabel(ids.length)),
            confirmLabel: copy.confirmLabel,
            tone: copy.tone,
        });
    });

    $("#confirmModal .agreeWith").on("click", function () {
        const $agree = $(this);
        const recordID = $agree.attr("data-id");
        const action = $agree.attr("data-action");

        const bulk = BULK_ACTIONS[action];
        if (!bulk && action !== "DELETE" && action !== "RESTORE") return;

        $("#confirmModal button").attr("disabled", "disabled");

        const request = bulk
            ? {
                  method: "post",
                  url: route("groups.bulk.action"),
                  data: { ids: recordID, action },
              }
            : action === "DELETE"
              ? { method: "delete", url: route("groups.destory", recordID) }
              : { method: "post", url: route("groups.restore", recordID) };

        axios({ ...request, headers: csrfHeaders() })
            .then((response) => {
                $("#confirmModal button").removeAttr("disabled");

                if (response.status == 200) {
                    hideConfirm();

                    if (bulk) showSuccess(bulk.done[0], bulk.done[1]);
                    else if (action === "DELETE")
                        showSuccess("Group deleted", "The group has been moved to the archive.");
                    else showSuccess("Group restored", "The group is back in the list with its previous status.");
                }
                buildTable();
            })
            .catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
    });
})();
