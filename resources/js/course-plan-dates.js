/*
 * Class Plan Dates — the generated class days for one plan.
 *
 * A port of the legacy `plans-date-list.js`, hitting the same endpoints with the
 * same parameter names (`planid`, `dates`, `status`) and the same store, delete
 * and restore contracts. Only the markup around them is new.
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
    refreshIcons,
    setBusy,
    showSuccess,
    wireConfirmReset,
    wireResize,
} from "./course-table-kit";

const TABLE_ID = "#planDateListTable";

(function () {
    const host = document.querySelector(TABLE_ID);
    if (!host) return;

    const planId = host.getAttribute("data-planid");

    let table = null;
    let lastTotal = null;

    const getTable = () => table;
    const paintRange = createRangePainter(TABLE_ID, getTable, () => lastTotal);

    /** The names `list()` reads; `planid` is fixed for this screen. */
    const params = () => ({
        planid: planId,
        dates: $("#pd-date").val() || "",
        status: $("#pd-status").val() || "1",
    });

    function buildTable() {
        table = new Tabulator(TABLE_ID, {
            ajaxURL: route("plan.dates.list"),
            ajaxParams: params(),
            ajaxFiltering: true,
            ajaxSorting: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: PAGINATION_SIZES,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No dates found for this plan",
            langs: PAGINATION_LANGS,
            ajaxResponse(url, params, response) {
                lastTotal = typeof response.total === "number" ? response.total : null;
                paintCount(lastTotal);

                return response;
            },
            columns: [
                { title: "#ID", field: "id", width: 84 },
                {
                    title: "Date",
                    field: "date",
                    headerHozAlign: "left",
                    widthGrow: 2,
                    minWidth: 170,
                    cssClass: "cm-cell--primary",
                },
                { title: "Type", field: "name", headerHozAlign: "left", widthGrow: 1, minWidth: 140 },
                { title: "Room", field: "room", headerHozAlign: "left", widthGrow: 1, minWidth: 100 },
                { title: "Time", field: "time", headerHozAlign: "left", widthGrow: 1, minWidth: 110 },
                {
                    title: "Status",
                    field: "upcomming_status",
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 110,
                    formatter(cell) {
                        const v = String(cell.getValue() || "");

                        // "Upcomming" is still to happen, anything else has been
                        // and gone — worth telling apart at a glance.
                        const tone = /upcom/i.test(v) ? "is-soon" : /unknown/i.test(v) ? "is-muted" : "";

                        return `<span class="cm-datestate ${tone}">${v}</span>`;
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 92,
                    download: false,
                    formatter(cell) {
                        const d = cell.getData();

                        // A trashed date offers restore instead of delete.
                        if (d.deleted_at) {
                            return `<span class="cm-rowactions"><button type="button" data-id="${d.id}" title="Restore this date" class="restoreDate cm-rowbtn cm-rowbtn--restore"><i data-lucide="rotate-cw"></i></button></span>`;
                        }

                        return `<span class="cm-rowactions"><button type="button" data-id="${d.id}" title="Delete this date" class="deleteDate cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button></span>`;
                    },
                },
            ],
            renderComplete() {
                refreshIcons();
                paintRange();

                // fitColumns leaves a sub-pixel overflow that shows as a
                // horizontal scrollbar.
                const cols = this.getColumns();
                if (cols.length > 0) {
                    const last = cols[cols.length - 1];
                    last.setWidth(last.getWidth() - 1);
                }
            },
        });
    }

    buildTable();
    wireResize(getTable);

    /* ------------------------------------------------------------------ *
     * Filters
     * ------------------------------------------------------------------ */

    $("#planDateFilterForm").on("submit", function (event) {
        event.preventDefault();
        buildTable();
    });

    $("#planDateReset").on("click", function () {
        $("#pd-date").val("");
        $("#pd-status").val("1");
        buildTable();
    });

    /* ------------------------------------------------------------------ *
     * Add a date
     * ------------------------------------------------------------------ */

    const addModal = () => tailwind.Modal.getOrCreateInstance(document.querySelector("#addPlanDateModal"));

    document.querySelector("#addPlanDateModal").addEventListener("hide.tw.modal", function () {
        clearErrors("#addPlanDateForm");
        $("#pd_name").val("");
        $("#pd_date").val("");
    });

    $("#addPlanDateForm").on("submit", function (event) {
        event.preventDefault();
        clearErrors("#addPlanDateForm");
        setBusy("#savePlanDate", true);

        axios({
            method: "post",
            url: route("plan.dates.store"),
            data: new FormData(this),
            headers: csrfHeaders(),
        })
            .then(() => {
                setBusy("#savePlanDate", false);
                addModal().hide();
                showSuccess("Congratulations!", "The date was added to this plan.");
                buildTable();
            })
            .catch((error) => {
                setBusy("#savePlanDate", false);
                if (error.response && error.response.status === 422) {
                    paintErrors("#addPlanDateForm", error.response.data.errors || {});
                }
            });
    });

    /* ------------------------------------------------------------------ *
     * Delete / restore
     * ------------------------------------------------------------------ */

    $(TABLE_ID).on("click", ".deleteDate", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "DELETE",
            title: "Delete this date?",
            message: "This class day will be removed from the plan.",
            confirmLabel: "Delete",
        });
    });

    $(TABLE_ID).on("click", ".restoreDate", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "RESTORE",
            title: "Restore this date?",
            message: "This class day will be returned to the plan.",
            confirmLabel: "Restore",
            tone: "restore",
        });
    });

    wireConfirmReset();

    $("#confirmModal .agreeWith").on("click", function () {
        const id = $(this).attr("data-id");
        const action = $(this).attr("data-action");
        if (action !== "DELETE" && action !== "RESTORE") return;

        const isDelete = action === "DELETE";

        axios({
            method: isDelete ? "delete" : "post",
            url: isDelete ? route("plan.dates.destory", id) : route("plan.dates.restore", id),
            headers: csrfHeaders(),
        })
            .then(() => {
                hideConfirm();
                showSuccess("Done", isDelete ? "The date was deleted." : "The date was restored.");
                buildTable();
            })
            .catch(() => {
                hideConfirm();
                showSuccess("Something went wrong", "The action could not be completed.", "warn");
            });
    });
})();
