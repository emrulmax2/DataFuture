/*
 * Term Declarations list for the redesigned Course Management module.
 *
 * A like-for-like port of the legacy `term-declaration.js` — same endpoints and
 * payloads — rebuilt against the new markup and taking the shared plumbing from
 * `course-table-kit.js`.
 *
 * Note the `IMask` import. The legacy file used IMask for the two time fields
 * but never imported it, so `$('.theTimeField').each(...)` threw a
 * ReferenceError at init and every handler registered after that line — edit,
 * update, delete and restore — was never bound.
 */

import IMask from "imask";
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

const TABLE_ID = "#termTableId";

// Every field the add/edit forms carry, in payload order. Used to copy an edit
// response onto the form without naming each input twice.
const FIELDS = [
    "name",
    "academic_year_id",
    "term_type_id",
    "start_date",
    "end_date",
    "total_teaching_weeks",
    "teaching_start_date",
    "teaching_end_date",
    "revision_start_date",
    "revision_end_date",
    "exam_publish_date",
    "exam_publish_time",
    "exam_resubmission_publish_date",
    "exam_resubmission_publish_time",
    "stuload",
];

(function () {
    if (!$(TABLE_ID).length) return;

    let lastTotal = null;
    let tableContent = null;

    const getTable = () => tableContent;
    const paintRange = createRangePainter(TABLE_ID, getTable, () => lastTotal);

    function buildTable() {
        tableContent = new Tabulator(TABLE_ID, {
            ajaxURL: route("term-declaration.list"),
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
                { title: "#ID", field: "id", width: 96 },
                { title: "Academic Year", field: "academic_year", headerHozAlign: "left", widthGrow: 1, minWidth: 140 },
                {
                    title: "Declared Term",
                    field: "name",
                    headerHozAlign: "left",
                    widthGrow: 2,
                    minWidth: 180,
                    // Only lands on `.tabulator-cell`, so the header keeps its
                    // own uppercase styling.
                    cssClass: "cm-cell--primary",
                },
                { title: "Term Type", field: "type", headerHozAlign: "left", widthGrow: 1, minWidth: 140 },
                { title: "Start Date", field: "start_date", headerHozAlign: "left", widthGrow: 1, minWidth: 120 },
                { title: "End Date", field: "end_date", headerHozAlign: "left", widthGrow: 1, minWidth: 120 },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 140,
                    download: false,
                    formatter(cell) {
                        // Only the row id is interpolated — an integer from the
                        // list endpoint, never free text.
                        const data = cell.getData();
                        let btns = '<span class="cm-rowactions">';

                        if (data.deleted_at == null) {
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" data-tw-toggle="modal" data-tw-target="#editModal" title="Edit term" class="edit_btn cm-rowbtn cm-rowbtn--edit"><i data-lucide="pencil"></i></button>';
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" title="Delete term" class="delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>';
                        } else {
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" title="Restore term" class="restore_btn cm-rowbtn cm-rowbtn--restore"><i data-lucide="rotate-ccw"></i></button>';
                        }

                        return btns + "</span>";
                    },
                },
            ],
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

        wireExports(getTable, "term-declarations", "Term Declarations");
    }

    buildTable();
    wireResize(getTable);
    wireFilters(buildTable);
    wireConfirmReset();

    /* ------------------------------------------------------------------ *
     * HH:MM masks on the two time fields
     * ------------------------------------------------------------------ */

    document.querySelectorAll(".theTimeField").forEach((field) => {
        IMask(field, {
            overwrite: true,
            autofix: true,
            mask: "HH:MM",
            blocks: {
                HH: { mask: IMask.MaskedRange, placeholderChar: "HH", from: 0, to: 23, maxLength: 2 },
                MM: { mask: IMask.MaskedRange, placeholderChar: "MM", from: 0, to: 59, maxLength: 2 },
            },
        });
    });

    /* ------------------------------------------------------------------ *
     * Create / update — unchanged endpoints and payloads
     * ------------------------------------------------------------------ */

    // Native listener, not jQuery: `.on("shown.tw.modal")` would be read as the
    // event "shown" in the "tw.modal" namespace and never fire, because the
    // theme dispatches a CustomEvent whose *type* contains the dots.
    document.querySelector("#addModal").addEventListener("shown.tw.modal", function () {
        clearErrors("#addForm");
        document.getElementById("addForm").reset();
        $("#addForm #td_add_name").trigger("focus");
    });

    $("#addForm").on("submit", function (e) {
        e.preventDefault();
        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));

        clearErrors("#addForm");
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("term-declaration.store"),
            data: new FormData(document.getElementById("addForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#save", false);

                if (response.status == 200) {
                    document.getElementById("addForm").reset();
                    addModal.hide();
                    showSuccess("Term added", "The term declaration has been created successfully.");
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

        axios({ method: "get", url: route("term-declaration.edit", editId), headers: csrfHeaders() })
            .then((response) => {
                if (response.status != 200) return;

                const row = response.data;
                FIELDS.forEach((field) => {
                    const value = row[field] === null || row[field] === undefined ? "" : row[field];
                    $(`#editForm [name="${field}"]`).val(value);
                });
                // Stored as 0 when unset, which should read as an empty box.
                if (!row.stuload || row.stuload <= 0) $('#editForm [name="stuload"]').val("");

                $('#editForm input[name="id"]').val(editId);
            })
            .catch((error) => {
                console.log(error);
            });
    });

    $("#editForm").on("submit", function (e) {
        e.preventDefault();
        const editId = $('#editForm input[name="id"]').val();
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));

        clearErrors("#editForm");
        setBusy("#update", true);

        axios({
            method: "post",
            // This endpoint takes the record in the URL: `{term}/update`.
            url: route("term-declaration.update", editId),
            data: new FormData(document.getElementById("editForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#update", false);

                if (response.status == 200) {
                    editModal.hide();
                    showSuccess("Term updated", "The changes have been saved successfully.");
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
     * Confirm (delete / restore)
     * ------------------------------------------------------------------ */

    $(TABLE_ID).on("click", ".delete_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "DELETE",
            title: "Delete this term?",
            message: `“${rowName(this)}” will be moved to the archive and hidden from this list.`,
            confirmLabel: "Delete",
            tone: "danger",
        });
    });

    $(TABLE_ID).on("click", ".restore_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "RESTORE",
            title: "Restore this term?",
            message: `“${rowName(this)}” will be returned to the active list.`,
            confirmLabel: "Restore",
            tone: "safe",
        });
    });

    $("#confirmModal .agreeWith").on("click", function () {
        const $agree = $(this);
        const recordID = $agree.attr("data-id");
        const action = $agree.attr("data-action");

        if (action !== "DELETE" && action !== "RESTORE") return;

        $("#confirmModal button").attr("disabled", "disabled");

        const request =
            action === "DELETE"
                ? { method: "delete", url: route("term-declaration.destroy", recordID) }
                : { method: "post", url: route("term-declaration.restore", recordID) };

        axios({ method: request.method, url: request.url, headers: csrfHeaders() })
            .then((response) => {
                $("#confirmModal button").removeAttr("disabled");

                if (response.status == 200) {
                    hideConfirm();
                    showSuccess(
                        action === "DELETE" ? "Term deleted" : "Term restored",
                        action === "DELETE"
                            ? "The record has been moved to the archive."
                            : "The record has been returned to the active list.",
                    );
                }
                buildTable();
            })
            .catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
    });
})();
