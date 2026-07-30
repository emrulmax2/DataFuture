/*
 * Courses list for the redesigned Course Management module.
 *
 * A like-for-like port of the legacy `courses.js` — same endpoints, same
 * payloads, same add / edit / delete / restore / status-toggle flows — rebuilt
 * against the new markup. Richer than the simple lists (seven columns, a row
 * status toggle and a view action), so it drives Tabulator itself and takes
 * the shared plumbing from `course-table-kit.js`.
 */

import Tabulator from "tabulator-tables";
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
    readFilters,
    refreshIcons,
    rowName,
    setBusy,
    showSuccess,
    wireConfirmReset,
    wireExports,
    wireFilters,
    wireResize,
} from "./course-table-kit";

const TABLE_ID = "#courseTableId";

(function () {
    if (!$(TABLE_ID).length) return;

    let lastTotal = null;
    let tableContent = null;

    const getTable = () => tableContent;
    const paintRange = createRangePainter(TABLE_ID, getTable, () => lastTotal);

    function buildTable() {

        tableContent = new Tabulator(TABLE_ID, {
            ajaxURL: route("courses.list"),
            ajaxParams: readFilters(),
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
            ajaxResponse(url, params, response) {
                lastTotal = typeof response.total === "number" ? response.total : null;
                paintCount(lastTotal);

                return response;
            },
            columns: [
                { title: "#ID", field: "id", width: 84 },
                {
                    title: "Course Name",
                    field: "name",
                    headerHozAlign: "left",
                    widthGrow: 3,
                    minWidth: 220,
                    // Only lands on `.tabulator-cell`, so the header keeps its
                    // own uppercase styling.
                    cssClass: "cm-cell--primary",
                },
                { title: "Awarding Body", field: "bodies", headerHozAlign: "left", widthGrow: 1, minWidth: 130 },
                { title: "Source of Tution", field: "fees", headerHozAlign: "left", widthGrow: 1, minWidth: 150, cssClass: "cm-cell--clamp" },
                { title: "Degree Offered", field: "degree_offered", headerHozAlign: "left", widthGrow: 1, minWidth: 150, cssClass: "cm-cell--clamp" },
                { title: "Pre Qualification", field: "pre_qualification", headerHozAlign: "left", widthGrow: 1, minWidth: 130 },
                {
                    title: "Status",
                    field: "active",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 92,
                    download: false,
                    formatter(cell) {
                        // A button, not a checkbox: the change is only committed
                        // after the confirm dialog, so the control must not flip
                        // itself on click. Only the row id is interpolated.
                        const data = cell.getData();
                        const on = data.active == 1;
                        const glyph = on
                            ? '<i data-lucide="check"></i>'
                            : '<i data-lucide="x"></i>';

                        return (
                            '<button type="button" data-id="' +
                            data.id +
                            '" title="' +
                            (on ? "Set inactive" : "Set active") +
                            '" class="status_updater cm-switch ' +
                            (on ? "cm-switch--on" : "cm-switch--off") +
                            '"><span class="cm-switch__knob">' +
                            glyph +
                            "</span></button>"
                        );
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 140,
                    download: false,
                    formatter(cell) {
                        const data = cell.getData();
                        let btns = '<span class="cm-rowactions">';

                        if (data.deleted_at == null) {
                            btns +=
                                '<a href="' +
                                route("courses.show", data.id) +
                                '" title="View course" class="cm-rowbtn cm-rowbtn--view"><i data-lucide="eye"></i></a>';
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" data-tw-toggle="modal" data-tw-target="#editModal" title="Edit course" class="edit_btn cm-rowbtn cm-rowbtn--edit"><i data-lucide="pencil"></i></button>';
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" title="Delete course" class="delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>';
                        } else {
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" title="Restore course" class="restore_btn cm-rowbtn cm-rowbtn--restore"><i data-lucide="rotate-ccw"></i></button>';
                        }

                        return btns + "</span>";
                    },
                },
            ],
            renderComplete() {
                refreshIcons();
                paintRange();

                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    lastColumn.setWidth(lastColumn.getWidth() - 1);
                }
            },
        });

        wireExports(getTable, "courses", "Courses");
    }

    buildTable();
    wireResize(getTable);
    wireFilters(buildTable);
    wireConfirmReset();

    /* ------------------------------------------------------------------ *
     * Create / update — unchanged endpoints and payloads
     * ------------------------------------------------------------------ */

    // Native listener, not jQuery: `.on("shown.tw.modal")` would be read as the
    // event "shown" in the "tw.modal" namespace and never fire, because the
    // theme dispatches a CustomEvent whose *type* contains the dots.
    document.querySelector("#addModal").addEventListener("shown.tw.modal", function () {
        clearErrors("#addForm");
        document.getElementById("addForm").reset();
        $("#addForm #add_name").trigger("focus");
    });

    $("#addForm").on("submit", function (e) {
        e.preventDefault();
        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));

        clearErrors("#addForm");
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("courses.store"),
            data: new FormData(document.getElementById("addForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#save", false);

                if (response.status == 200) {
                    document.getElementById("addForm").reset();
                    addModal.hide();
                    showSuccess("Course added", "The course has been created successfully.");
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

        axios({ method: "get", url: route("courses.edit", editId), headers: csrfHeaders() })
            .then((response) => {
                if (response.status != 200) return;

                const row = response.data;
                $("#editForm input[name='name']").val(row.name ?? "");
                $("#editForm input[name='degree_offered']").val(row.degree_offered ?? "");
                $("#editForm input[name='pre_qualification']").val(row.pre_qualification ?? "");
                $("#editForm select[name='awarding_body_id']").val(row.awarding_body_id ?? "");
                $("#editForm select[name='source_tuition_fee_id']").val(row.source_tuition_fee_id ?? "");
                $("#editForm select[name='color_theme']").val(row.color_theme ?? "");
                // The column stores the literal strings "Yes"/"No".
                $("#editForm input[name='franchise_course']").prop("checked", row.franchise_course === "Yes");
                $("#editForm input[name='active']").prop("checked", row.active == 1);
                $("#editForm input[name='id']").val(editId);
            })
            .catch((error) => {
                console.log(error);
            });
    });

    $("#editForm").on("submit", function (e) {
        e.preventDefault();
        const editId = $("#editForm input[name='id']").val();
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));

        clearErrors("#editForm");
        setBusy("#update", true);

        axios({
            method: "post",
            url: route("courses.update", editId),
            data: new FormData(document.getElementById("editForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#update", false);

                if (response.status == 200) {
                    editModal.hide();
                    showSuccess("Course updated", "The changes have been saved successfully.");
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
     * Confirm (delete / restore / status change)
     * ------------------------------------------------------------------ */

    $(TABLE_ID).on("click", ".delete_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "DELETE",
            title: "Delete this course?",
            message: `“${rowName(this)}” will be moved to the archive and hidden from this list.`,
            confirmLabel: "Delete",
            tone: "danger",
        });
    });

    $(TABLE_ID).on("click", ".restore_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "RESTORE",
            title: "Restore this course?",
            message: `“${rowName(this)}” will be returned to the active list.`,
            confirmLabel: "Restore",
            tone: "safe",
        });
    });

    $(TABLE_ID).on("click", ".status_updater", function () {
        const turningOff = $(this).hasClass("cm-switch--on");

        openConfirm({
            id: $(this).attr("data-id"),
            action: "CHANGESTAT",
            title: turningOff ? "Set this course inactive?" : "Set this course active?",
            message: turningOff
                ? `“${rowName(this)}” will stop appearing where active courses are offered.`
                : `“${rowName(this)}” will become available again wherever active courses are offered.`,
            confirmLabel: turningOff ? "Set inactive" : "Set active",
            tone: "safe",
        });
    });

    $("#confirmModal .agreeWith").on("click", function () {
        const $agree = $(this);
        const recordID = $agree.attr("data-id");
        const action = $agree.attr("data-action");

        const requests = {
            DELETE: { method: "delete", url: () => route("courses.destory", recordID) },
            RESTORE: { method: "post", url: () => route("courses.restore", recordID) },
            CHANGESTAT: { method: "post", url: () => route("courses.update.status", recordID) },
        };

        const request = requests[action];
        if (!request) return;

        $("#confirmModal button").attr("disabled", "disabled");

        axios({ method: request.method, url: request.url(), headers: csrfHeaders() })
            .then((response) => {
                $("#confirmModal button").removeAttr("disabled");

                if (response.status == 200) {
                    hideConfirm();

                    if (action === "DELETE") {
                        showSuccess("Course deleted", "The record has been moved to the archive.");
                    } else if (action === "RESTORE") {
                        showSuccess("Course restored", "The record has been returned to the active list.");
                    } else {
                        showSuccess("Status updated", "The course status has been changed.");
                    }
                }
                buildTable();
            })
            .catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
    });
})();
