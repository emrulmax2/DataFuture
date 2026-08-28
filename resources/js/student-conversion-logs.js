import xlsx from "xlsx";
import { createElement, createIcons, icons, ExternalLink } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const tableNode = document.querySelector("#conversionLogsTableId");

    if (!tableNode) {
        return;
    }

    let tableContent = null;

    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.7,
            nameAttr: "data-lucide",
        });
    };

    const escapeHtml = (value) => {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    const iconSvg = (Icon) => {
        const icon = createElement(Icon);

        icon.setAttribute("stroke-width", "1.9");
        icon.setAttribute("aria-hidden", "true");

        return icon.outerHTML;
    };

    const applicantFormatter = (cell) => {
        const data = cell.getData();
        const refNo = data.application_no ? `<small>Ref: ${escapeHtml(data.application_no)}</small>` : "";

        return `<span class="ss-conversion-applicant"><strong>${escapeHtml(data.applicant_name)}</strong>${refNo}</span>`;
    };

    const stateFormatter = (cell) => {
        const state = cell.getValue();

        if (state === "failed") {
            return '<span class="ss-status-pill is-inactive"><span></span>Failed</span>';
        }
        if (state === "inprogress") {
            // No amber variant exists for ss-status-pill, so color it inline.
            return '<span class="ss-status-pill" style="border-color:#fde68a;background:#fef3c7;color:#b45309;"><span style="background:#b45309;"></span>In Progress</span>';
        }

        return '<span class="ss-status-pill is-active"><span></span>Completed</span>';
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (!data.detail_url) {
            return "";
        }

        return `<a href="${escapeHtml(data.detail_url)}" class="ss-row-action" aria-label="View conversion log detail" title="View step-by-step detail">${iconSvg(ExternalLink)}</a>`;
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#conversionLogsTableId", {
            ajaxURL: route("student.conversion.logs.list"),
            ajaxParams: { querystr, status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No conversion has been run yet",
            columns: [
                {
                    title: "SL",
                    field: "sl",
                    width: 70,
                    headerSort: false,
                },
                {
                    title: "Applicant",
                    field: "applicant_name",
                    minWidth: 220,
                    headerSort: false,
                    formatter: applicantFormatter,
                },
                {
                    title: "Student Reg No",
                    field: "registration_no",
                    width: 150,
                    headerSort: false,
                },
                {
                    title: "Batch",
                    field: "batch_ref",
                    width: 110,
                    headerSort: false,
                    formatter(cell) {
                        const data = cell.getData();
                        return `<span title="${escapeHtml(data.batch_id)}">${escapeHtml(data.batch_ref)}</span>`;
                    },
                },
                {
                    title: "Steps",
                    field: "steps",
                    width: 100,
                    headerSort: false,
                },
                {
                    title: "Outcome",
                    field: "state",
                    width: 130,
                    headerSort: false,
                    formatter: stateFormatter,
                },
                {
                    title: "Failed Steps",
                    field: "failed_steps",
                    minWidth: 200,
                    headerSort: false,
                    formatter(cell) {
                        const value = cell.getValue();
                        return value ? `<div class="text-danger" style="white-space: normal; word-break: break-word;">${escapeHtml(value)}</div>` : "";
                    },
                },
                {
                    title: "Dispatched",
                    field: "dispatched_at",
                    width: 160,
                    headerSort: false,
                },
                {
                    title: "Finished",
                    field: "finished_at",
                    width: 160,
                    headerSort: false,
                },
                {
                    title: "By",
                    field: "created_by",
                    width: 140,
                    headerSort: false,
                },
                {
                    title: "Detail",
                    field: "detail_url",
                    width: 90,
                    headerSort: false,
                    hozAlign: "center",
                    formatter: actionFormatter,
                },
            ],
            renderComplete() {
                refreshIcons();
            },
        });
    };

    buildTable();

    window.addEventListener("resize", () => {
        if (tableContent) {
            tableContent.redraw();
        }
        refreshIcons();
    });

    $("#tabulator-html-filter-go").on("click", function () {
        buildTable();
    });
    $("#tabulator-html-filter-reset").on("click", function () {
        $("#query").val("");
        $("#status").val("");
        buildTable();
    });

    $("#tabulator-print").on("click", function () {
        if (tableContent) {
            tableContent.print();
        }
    });
    $("#tabulator-export-csv").on("click", function () {
        if (tableContent) {
            tableContent.download("csv", "student-conversion-logs.csv");
        }
    });
    $("#tabulator-export-xlsx").on("click", function () {
        if (tableContent) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "student-conversion-logs.xlsx", {
                sheetName: "Conversion Logs",
            });
        }
    });
})();
