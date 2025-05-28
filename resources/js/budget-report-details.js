import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select"; 
import tippy, { roundArrow } from "tippy.js";
import Litepicker from "litepicker";

("use strict");
var budgetReqListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        let year_id = $("#budgetReqListTable").attr('data-year') != "" && $("#budgetReqListTable").attr('data-year') != 'undefined' ? $("#budgetReqListTable").attr('data-year') : "0";
        let set_id = $("#budgetReqListTable").attr('data-set') != "" && $("#budgetReqListTable").attr('data-set') != 'undefined' ? $("#budgetReqListTable").attr('data-set') : "0";
        let details_id = $("#budgetReqListTable").attr('data-details') != "" && $("#budgetReqListTable").attr('data-details') != 'undefined' ? $("#budgetReqListTable").attr('data-details') : "0";

        let tableContent = new Tabulator("#budgetReqListTable", {
            ajaxURL: route("budget.management.reports.details.list"),
            ajaxParams: { year_id: year_id, set_id: set_id, details_id: details_id },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 20, 30, 50, 100, 150],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Year",
                    field: "year",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: '90'
                },
                {
                    title: "Vendor",
                    field: "vendor",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().vendor+'</div>';
                    }
                },
                {
                    title: "Date",
                    field: "date",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: '115'
                },
                {
                    title: "By",
                    field: "required_by",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: '115'
                },
                {
                    title: "Requisitioner",
                    field: "requisitioners",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().requisitioners+'</div>';
                    }
                },
                /*{
                    title: "Budget Source",
                    field: "budget",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().budget+'</div>';
                    }
                },*/
                {
                    title: "Venue",
                    field: "venue",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().venue+'</div>';
                    }
                },
                {
                    title: "Status",
                    field: "active",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        if(cell.getData().active == 4){
                            return '<span class="btn btn-sm btn-success text-white px-2 py-1">Completed</span>';
                        }else if(cell.getData().active == 3){
                            return '<span class="btn btn-sm btn-primary text-white px-2 py-1">Approved</span>';
                        }else if(cell.getData().active == 2){
                            return '<span class="btn btn-sm btn-pending text-white px-2 py-1">First Approval</span>';
                        }else if(cell.getData().active == 1){
                            return '<span class="btn btn-sm btn-warning text-white px-2 py-1">Active</span>';
                        }else if(cell.getData().active == 0){
                            return '<span class="btn btn-sm btn-danger text-white px-2 py-1">Cancelled</span>';
                        }
                    }
                },
                {
                    title: "Total",
                    field: "total",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: '115',
                    formatter(cell, formatterParams){
                        return '<div class="font-medium">'+cell.getData().total+'</div>';
                    }
                },
                {
                    title: "Paid",
                    field: "paid",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: '115',
                    formatter(cell, formatterParams){
                        var html;
                        if(cell.getData().active == 4){
                            html = '<div class="font-medium">'+cell.getData().paid+'</div>';
                        }
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
            },
            rowFormatter:function(row){
                var data = row.getData();
            },
            rowClick: function (e, row) {
                window.open(row.getData().url, '_blank');
            },
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
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
    budgetReqListTable.init();


    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#successModal .successCloser').attr('data-action', 'NONE');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            succModal.hide();
        }
    });
})();