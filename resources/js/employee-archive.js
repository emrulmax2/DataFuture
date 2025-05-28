import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var employeeArchiveListTable = (function () {
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
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: "120",
                },
                {
                    title: "Table Name",
                    field: "table",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += cell.getData().table;
                            if(cell.getData().row_id != ''){
                                html += '&nbsp;'+cell.getData().row_id;
                            }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Field Name",
                    field: "field_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Previous Value",
                    field: "field_value",
                    headerHozAlign: "left",
                },
                {
                    title: "New Value",
                    field: "field_new_value",
                    headerHozAlign: "left",
                },
                {
                    title: "Created By",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: "200",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
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