/*
 * Shared behaviour for the simple "id + name" list screens in the redesigned
 * Course Management module (Semesters, Module Levels, …).
 *
 * These pages are the same screen with different labels and endpoints, so the
 * markup lives in `partials/simple-list.blade.php` and the behaviour lives
 * here; each page ships a small config file naming its routes. Anything shared
 * with the richer lists (Courses, …) lives in `course-table-kit.js`.
 *
 * Behaviour is a like-for-like port of the legacy per-page scripts — same
 * endpoints, same payloads, same add / edit / delete / restore flows.
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

/**
 * @param {object} config
 * @param {string} config.tableId      DOM id of the table host, without the `#`.
 * @param {string} config.noun         Singular label, e.g. "Semester".
 * @param {string} config.nounPlural   Plural label, used for export filenames.
 * @param {string} config.columnTitle  Header for the name column.
 * @param {object} config.routes       {list, store, edit, update, destroy, restore} route names.
 */
export function initCourseSimpleList(config) {
    const TABLE_ID = `#${config.tableId}`;
    if (!$(TABLE_ID).length) return;

    const noun = config.noun;
    const nounLower = noun.toLowerCase();
    const fileBase = (config.nounPlural || `${noun}s`).toLowerCase().replace(/\s+/g, "-");

    // Set from each list response so the header count and the footer range can
    // report the *filtered* total rather than the rows on this page.
    let lastTotal = null;
    let tableContent = null;

    const getTable = () => tableContent;
    const paintRange = createRangePainter(TABLE_ID, getTable, () => lastTotal);

    function buildTable() {

        tableContent = new Tabulator(TABLE_ID, {
            ajaxURL: route(config.routes.list),
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
                // `total` is sent by the list endpoints; responses without it
                // simply fall back to a range with no total.
                lastTotal = typeof response.total === "number" ? response.total : null;
                paintCount(lastTotal);

                return response;
            },
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 120,
                },
                {
                    title: config.columnTitle,
                    field: "name",
                    headerHozAlign: "left",
                    // Only lands on `.tabulator-cell`, so the header keeps its
                    // own uppercase styling.
                    cssClass: "cm-cell--primary",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 150,
                    download: false,
                    formatter(cell) {
                        // Only the row id is interpolated — an integer from the
                        // list endpoint, never free text.
                        const id = cell.getData().id;
                        let btns = '<span class="cm-rowactions">';

                        if (cell.getData().deleted_at == null) {
                            btns +=
                                '<button type="button" data-id="' +
                                id +
                                '" data-tw-toggle="modal" data-tw-target="#editModal" title="Edit ' +
                                nounLower +
                                '" class="edit_btn cm-rowbtn cm-rowbtn--edit"><i data-lucide="pencil"></i></button>';
                            btns +=
                                '<button type="button" data-id="' +
                                id +
                                '" title="Delete ' +
                                nounLower +
                                '" class="delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>';
                        } else {
                            btns +=
                                '<button type="button" data-id="' +
                                id +
                                '" title="Restore ' +
                                nounLower +
                                '" class="restore_btn cm-rowbtn cm-rowbtn--restore"><i data-lucide="rotate-ccw"></i></button>';
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

        wireExports(getTable, fileBase, config.nounPlural);
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
        $("#addForm #add_name").val("").trigger("focus");
    });

    $("#addForm").on("submit", function (e) {
        e.preventDefault();
        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));

        clearErrors("#addForm");
        setBusy("#save", true);

        axios({
            method: "post",
            url: route(config.routes.store),
            data: new FormData(document.getElementById("addForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#save", false);

                if (response.status == 200) {
                    $("#addForm #add_name").val("");
                    addModal.hide();
                    showSuccess(`${noun} added`, `The ${nounLower} has been created successfully.`);
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

        axios({ method: "get", url: route(config.routes.edit, editId), headers: csrfHeaders() })
            .then((response) => {
                if (response.status == 200) {
                    const dataset = response.data;
                    $('#editModal input[name="name"]').val(dataset.name ? dataset.name : "");
                    $('#editModal input[name="id"]').val(editId);
                }
            })
            .catch((error) => {
                console.log(error);
            });
    });

    $("#editForm").on("submit", function (e) {
        e.preventDefault();
        const editId = $('#editModal input[name="id"]').val();
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));

        clearErrors("#editForm");
        setBusy("#update", true);

        axios({
            method: "post",
            url: route(config.routes.update, editId),
            data: new FormData(document.getElementById("editForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#update", false);

                if (response.status == 200) {
                    editModal.hide();
                    showSuccess(`${noun} updated`, "The changes have been saved successfully.");
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
            title: "Delete this record?",
            message: `“${rowName(this)}” will be moved to the archive and hidden from this list.`,
            confirmLabel: "Delete",
            tone: "danger",
        });
    });

    $(TABLE_ID).on("click", ".restore_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "RESTORE",
            title: "Restore this record?",
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
                ? { method: "delete", url: route(config.routes.destroy, recordID) }
                : { method: "post", url: route(config.routes.restore, recordID) };

        axios({ method: request.method, url: request.url, headers: csrfHeaders() })
            .then((response) => {
                $("#confirmModal button").removeAttr("disabled");

                if (response.status == 200) {
                    hideConfirm();
                    showSuccess(
                        action === "DELETE" ? `${noun} deleted` : `${noun} restored`,
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
}
