import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var assetsRegisterListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let type = $("#acc_asset_type_id").val() != "" ? $("#acc_asset_type_id").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#assetsRegisterListTable", {
            ajaxURL: route("accounts.assets.register.list"),
            ajaxParams: { querystr: querystr, status: status, type: type },
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
                /*{
                    title: "#ID",
                    field: "id",
                },*/
                {
                    title: "Purchase",
                    field: "id",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="block relative">';
                                html += '<div class="font-medium whitespace-nowrap">';
                                    html += '<span class="text-success">'+cell.getData().transaction_date_2+'</span>';
                                html += '</div>';
                                html += '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5 flex justify-start items-center">';
                                    html += cell.getData().transaction_code;
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Supplier",
                    field: "detail",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="relative whitespace-normal">';
                                html += cell.getData().detail;
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Price",
                    field: "transaction_amount",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "acc_asset_type_id",
                    headerHozAlign: "left",
                },
                {
                    title: "Description",
                    field: "description",
                    headerHozAlign: "left",
                },
                {
                    title: "Location",
                    field: "location",
                    headerHozAlign: "left",
                },
                {
                    title: "Serial",
                    field: "serial",
                    headerHozAlign: "left",
                },
                {
                    title: "Barcode",
                    field: "barcode",
                    headerHozAlign: "left",
                },
                {
                    title: "Life Span",
                    field: "life",
                    headerHozAlign: "left",
                },
                /*{
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().active == 1 ? 'Checked' : '')+' value="'+cell.getData().active+'" type="checkbox" class="status_updater form-check-input"> </div>';
                    }
                },*/
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "120",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editTitleModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
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
        $("#tabulator-export-csv-TITLE").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-TITLE").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-TITLE").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html-TITLE").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-TITLE").on("click", function (event) {
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
    assetsRegisterListTable.init();

    // Filter function
    function filterHTMLForm() {
        assetsRegisterListTable.init();
    }

    // On submit filter form
    $("#tabulatorFilterForm")[0].addEventListener(
        "keypress",
        function (event) {
            let keycode = event.keyCode ? event.keyCode : event.which;
            if (keycode == "13") {
                event.preventDefault();
                filterHTMLForm();
            }
        }
    );

    // On click go button
    $("#tabulator-html-filter-go").on("click", function (event) {
        filterHTMLForm();
    });

    // On reset filter form
    $("#tabulator-html-filter-reset").on("click", function (event) {
        $("#query").val("");
        $("#acc_asset_type_id").val("");
        $("#status").val("1");

        filterHTMLForm();
    });
})()