import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var myVacancyListTable = (function () {
    var tableContent = null;
    var resizeBound = false;
    var _tableGen = function () {
        if (tableContent) {
            tableContent.destroy();
        }

        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = 1;

        tableContent = new Tabulator("#myVacancyListTable", {
            ajaxURL: route("user.account.vacancy.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, true],
            paginationCounter: "rows",
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: "90",
                },
                {
                    title: "Title",
                    field: "title",
                    headerHozAlign: "left",
                    minWidth: 240,
                    formatter(cell, formatterParams){
                        return '<span class="myhr-vacancies-title">'+cell.getValue()+'</span>';
                    },
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        let label = cell.getValue() ? cell.getValue() : "Not Set";
                        return '<span class="myhr-groups-type-pill myhr-vacancies-type-pill">'+label+'</span>';
                    },
                },
                {
                    title: "Date",
                    field: "date",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        return '<span class="myhr-vacancies-date-pill">'+(cell.getValue() ? cell.getValue() : '-')+'</span>';
                    },
                },
                {
                    title: "Created By",
                    field: "created_by",
                    headerHozAlign: "left",
                    minWidth: 180,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div class="myhr-vacancies-created">';
                            html += '<strong>'+cell.getData().created_by+'</strong>';
                            html += '<span>'+cell.getData().created_at+'</span>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            if(cell.getData().link != ''){
                                btns +='<a href="'+cell.getData().link+'" target="_blank" rel="noopener" class="myhr-groups-action-btn myhr-vacancies-action-btn--link" title="Open link"><i data-lucide="external-link" class="w-4 h-4"></i></a>';
                            }
                            if(cell.getData().document_url != ''){
                                btns +='<a href="'+cell.getData().document_url+'" target="_blank" rel="noopener" class="myhr-groups-action-btn myhr-vacancies-action-btn--document" title="Open document"><i data-lucide="file-down" class="w-4 h-4"></i></a>';
                            }
                        }

                        return btns != "" ? btns : '<span class="myhr-vacancies-empty-action">-</span>';
                    },
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
            },
        });

        // Redraw table on resize
        if (!resizeBound) {
            window.addEventListener("resize", () => {
                if (tableContent) {
                    tableContent.redraw();
                }
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            });
            resizeBound = true;
        }

        // Export
        $("#tabulator-export-csv").off("click.myVacanciesExport").on("click.myVacanciesExport", function (event) {
            tableContent.download("csv", "my-vacancies.csv");
        });

        $("#tabulator-export-xlsx").off("click.myVacanciesExport").on("click.myVacanciesExport", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "my-vacancies.xlsx", {
                sheetName: "My Vacancies",
            });
        });

        // Print
        $("#tabulator-print").off("click.myVacanciesPrint").on("click.myVacanciesPrint", function (event) {
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
    if ($("#myVacancyListTable").length) {
        myVacancyListTable.init();

        function filterTitleHTMLForm() {
            myVacancyListTable.init();
        }

        $("#tabulator-html-filter-go").on("click", function (event) {
            filterTitleHTMLForm();
        });

        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            filterTitleHTMLForm();
        });

        $("#query").on("keypress", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                filterTitleHTMLForm();
            }
        });
    }
})()
