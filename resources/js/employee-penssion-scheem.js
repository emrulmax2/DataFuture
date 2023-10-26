import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var employeePenssionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-PNS").val() != "" ? $("#query-PNS").val() : "";
        let status = $("#status-PNS").val() != "" ? $("#status-PNS").val() : "";
        let employee_id = $("#employeePenssionListTable").attr('data-employee');

        let tableContent = new Tabulator("#employeePenssionListTable", {
            ajaxURL: route("employee.penssion.list"),
            ajaxParams: { employee_id: employee_id, querystr: querystr, status: status },
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
                    width: "180",
                },
                {
                    title: "Scheme",
                    field: "penssion",
                    headerHozAlign: "left",
                },
                {
                    title: "Date Joined",
                    field: "joining_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Date Left",
                    field: "date_left",
                    headerHozAlign: "left",
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
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editBankModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +='<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-PNS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-PNS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-PNS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Groups Details",
            });
        });

        $("#tabulator-export-html-PNS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-PNS").on("click", function (event) {
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
    if ($("#employeePenssionListTable").length) {
        // Init Table
        employeePenssionListTable.init();

        // Filter function
        function filterHTMLFormPNS() {
            employeePenssionListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PNS")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormPNS();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-PNS").on("click", function (event) {
            filterHTMLFormPNS();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PNS").on("click", function (event) {
            $("#query-PNS").val("");
            $("#status-PNS").val("1");
            filterHTMLFormPNS();
        });
    }


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const addBankModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBankModal"));
    //const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    let confModalDelTitle = 'Are you sure?';

    const addBankModalEl = document.getElementById('addBankModal')
    addBankModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addModal .acc__input-error').html('');
        $('#addModal .modal-body input').val('');
        $('#addModal .modal-body select').val('');
        $('#addModal input[name="active"]').prop('checked', false);
    });
})();