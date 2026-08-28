import xlsx from "xlsx";
import Tabulator from "tabulator-tables";
import { createIcons, icons } from "lucide";

("use strict");

function conversionRenderLucide() {
    createIcons({
        icons,
        "stroke-width": 1.5,
        nameAttr: "data-lucide",
    });
}

// Inline-styled pills rather than btn-* classes: the admission redesign
// re-themes button colors per course palette, which turned "Completed" red.
// These hexes match the ss-status-pill palette on the settings pages.
const conversionPill = (label, color, bg, border) =>
    '<span style="display:inline-flex;align-items:center;gap:6px;padding:3px 12px;border-radius:999px;font-size:12px;font-weight:700;line-height:1.4;border:1px solid ' + border + ';background:' + bg + ';color:' + color + ';">' +
    '<span style="width:6px;height:6px;border-radius:999px;background:' + color + ';"></span>' + label + '</span>';

const conversionStatusBadges = {
    queued: conversionPill('Queued', '#64748b', '#f1f5f9', '#e2e8f0'),
    processing: conversionPill('Processing', '#b45309', '#fef3c7', '#fde68a'),
    completed: conversionPill('Completed', '#0f8278', '#e5f7f3', '#b9e4db'),
    failed: conversionPill('Failed', '#d64545', '#fde7e7', '#f4cbcb'),
    cancelled: conversionPill('Cancelled', '#d64545', '#fff5f5', '#f4cbcb'),
};

var conversionLogListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#conversionLogListTable").attr('data-applicant') != "" ? $("#conversionLogListTable").attr('data-applicant') : "0";
        let queryStr = $("#query-CL").val() != "" ? $("#query-CL").val() : "";
        let status = $("#status-CL").val() != "" ? $("#status-CL").val() : "";

        let tableContent = new Tabulator("#conversionLogListTable", {
            ajaxURL: route("admission.conversion.log.list"),
            ajaxParams: { applicantId: applicantId, queryStr: queryStr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 25,
            paginationSizeSelector: [true, 10, 25, 50],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No conversion has been run for this applicant yet",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: "80",
                },
                {
                    title: "Batch",
                    field: "batch_ref",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: "110",
                    formatter(cell, formatterParams) {
                        let data = cell.getData();
                        return '<span title="' + data.batch_id + '">' + data.batch_ref + '</span>';
                    }
                },
                {
                    title: "Step",
                    field: "job_name",
                    headerHozAlign: "left",
                    minWidth: 220,
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    width: "140",
                    formatter(cell, formatterParams) {
                        let status = cell.getValue();
                        return conversionStatusBadges[status] ? conversionStatusBadges[status] : status;
                    }
                },
                {
                    title: "Message",
                    field: "message",
                    headerHozAlign: "left",
                    minWidth: 260,
                    formatter(cell, formatterParams) {
                        return '<div style="white-space: normal; word-break: break-word;">' + (cell.getValue() ? cell.getValue() : '') + '</div>';
                    }
                },
                {
                    title: "Attempts",
                    field: "attempts",
                    headerHozAlign: "left",
                    width: "100",
                },
                {
                    title: "Started",
                    field: "started_at",
                    headerHozAlign: "left",
                    width: "160",
                },
                {
                    title: "Finished",
                    field: "finished_at",
                    headerHozAlign: "left",
                    width: "160",
                },
                {
                    title: "Error",
                    field: "exception",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: "100",
                    formatter(cell, formatterParams) {
                        let data = cell.getData();
                        return data.exception != "" ? '<button type="button" class="btn btn-danger py-0 px-2 text-white">View</button>' : '';
                    },
                    cellClick(e, cell) {
                        let data = cell.getData();
                        if (data.exception != "") {
                            $('#conversionErrorTitle').text(data.job_name);
                            $('#conversionErrorContent').text(data.exception);
                            tailwind.Modal.getOrCreateInstance(document.querySelector("#viewConversionErrorModal")).show();
                        }
                    }
                },
            ],
            renderComplete() {
                conversionRenderLucide();
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
            }
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            conversionRenderLucide();
        });

        // Export
        $("#tabulator-export-csv-CL").on("click", function (event) {
            tableContent.download("csv", "conversion-log.csv");
        });
        $("#tabulator-export-xlsx-CL").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "conversion-log.xlsx", {
                sheetName: "Conversion Log",
            });
        });

        // Print
        $("#tabulator-print-CL").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    if ($("#conversionLogListTable").length) {
        conversionLogListTable.init();

        function filterHTMLFormCL() {
            conversionLogListTable.init();
        }
        $("#tabulator-html-filter-go-CL").on("click", function (event) {
            filterHTMLFormCL();
        });
        $("#tabulator-html-filter-reset-CL").on("click", function (event) {
            $("#query-CL").val("");
            $("#status-CL").val("");
            filterHTMLFormCL();
        });
    }
})();
