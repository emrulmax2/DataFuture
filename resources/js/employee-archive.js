import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const renderLucideIcons = () => {
    createIcons({
        icons,
        "stroke-width": 1.5,
        nameAttr: "data-lucide",
    });
};

const archiveValue = (value) => {
    return (value !== null && value !== undefined && String(value).trim() !== "") ? value : "&mdash;";
};

var employeeArchiveListTable = (function () {
    let currentTotalRows = 0;

    var _tableGen = function () {
        // Setup Tabulator
        let employeeId = $("#employeeArchiveListTable").attr('data-employee') != "" ? $("#employeeArchiveListTable").attr('data-employee') : "0";
        let queryStr = $("#query-ARC").val() != "" ? $("#query-ARC").val() : "";

        let tableContent = new Tabulator("#employeeArchiveListTable", {
            ajaxURL: route("employee.archive.list"),
            ajaxParams: { employeeId: employeeId, queryStr : queryStr},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            ajaxResponse(url, params, response) {
                currentTotalRows = Number(response.total_rows || 0);
                const summaryEl = document.querySelector("#employeeArchiveSummary");
                if (summaryEl) {
                    summaryEl.textContent = currentTotalRows > 0
                        ? `${currentTotalRows} change${currentTotalRows === 1 ? "" : "s"} on record`
                        : "Audit trail of changes recorded against this employee.";
                }
                return response;
            },
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: 92,
                },
                {
                    title: "Table Name",
                    field: "table",
                    headerHozAlign: "left",
                    width: 220,
                    formatter(cell, formatterParams){
                        const data = cell.getData();
                        const rowId = (data.row_id !== null && data.row_id !== undefined && String(data.row_id).trim() !== "")
                            ? `<span class="ep-doc-arccell__row">#${data.row_id}</span>` : "";
                        return `<div class="ep-doc-arccell"><span class="ep-doc-arccell__table">${data.table}</span>${rowId}</div>`;
                    }
                },
                {
                    title: "Field Name",
                    field: "field_name",
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<span class="ep-doc-arccell__field">${archiveValue(cell.getData().field_name)}</span>`;
                    }
                },
                {
                    title: "Previous Value",
                    field: "field_value",
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<span class="ep-doc-arcval ep-doc-arcval--old">${archiveValue(cell.getData().field_value)}</span>`;
                    }
                },
                {
                    title: "New Value",
                    field: "field_new_value",
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<span class="ep-doc-arcval ep-doc-arcval--new">${archiveValue(cell.getData().field_new_value)}</span>`;
                    }
                },
                {
                    title: "Created By",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: 200,
                    formatter(cell, formatterParams){
                        const data = cell.getData();
                        return `
                            <div class="ep-doc-usercell">
                                <div class="ep-doc-usercell__name">${data.created_by}</div>
                                <div class="ep-doc-usercell__meta">${data.created_at}</div>
                            </div>
                        `;
                    }
                },
            ],
            renderComplete() {
                renderLucideIcons();
            }
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        // Export
        $("#tabulator-export-csv-ARC").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx-ARC").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Employee Note Details",
            });
        });

        // Print
        $("#tabulator-print-ARC").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();


(function(){
    if ($("#employeeArchiveListTable").length) {
        // Init Table
        employeeArchiveListTable.init();

        // Filter function
        function filterHTMLFormARC() {
            employeeArchiveListTable.init();
        }

        $("#query-ARC").on('keypress', function(e){
            var key = e.keyCode || e.which;
            if(key === 13){
                e.preventDefault(); 
                filterHTMLFormARC();
            }
        })


        // On click go button
        $("#tabulator-html-filter-go-ARC").on("click", function (event) {
            filterHTMLFormARC();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-ARC").on("click", function (event) {
            $("#query-ARC").val("");
            filterHTMLFormARC();
        });
    }

})()