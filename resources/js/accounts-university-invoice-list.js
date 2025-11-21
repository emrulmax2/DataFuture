import IMask from 'imask';
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Litepicker from "litepicker";


("use strict");
var universityInvoiceListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let queryStr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#universityInvoiceListTable", {
            ajaxURL: route("university.claims.invoices.list"),
            ajaxParams: { queryStr: queryStr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 50, 100, 150, 200, 500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Proforma / Invoice No",
                    field: "proforma_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-block relative">';
                                html += '<div class="font-medium whitespace-nowrap uppercase">';
                                    html += cell.getData().proforma_no;
                                    html += cell.getData().invoice_no != '' ? ' / '+cell.getData().invoice_no : '';
                                html += '</div>';
                                html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap">' +cell.getData().created_at +'</div>';
                            html += '</div>';
                        return html;
                    },
                },
                {
                    title: "Course & Semester",
                    field: "semester",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-block relative">';
                                html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap uppercase">' +cell.getData().course +'</div>';
                                html += '<div class="font-medium whitespace-nowrap uppercase">' +cell.getData().semester +'</div>';
                            html += '</div>';
                        return html;
                    },
                },
                {
                    title: "Invoice To",
                    field: "vendor_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-block relative">';
                                html += '<div class="font-medium whitespace-nowrap uppercase">' +cell.getData().vendor_name +'</div>';
                                if(cell.getData().vendor_email != ''){
                                    html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap uppercase">' +cell.getData().vendor_email +'</div>';
                                }
                                if(cell.getData().vendor_phone != ''){
                                    html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap uppercase">' +cell.getData().vendor_phone +'</div>';
                                }
                            html += '</div>';
                        return html;
                    },
                },
                {
                    title: "Remit To",
                    field: "bank_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-block relative">';
                                html += '<div class="font-medium whitespace-nowrap uppercase">' +cell.getData().bank_name +'</div>';
                                if(cell.getData().ac_name != ''){
                                    html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap">' +cell.getData().ac_name +'</div>';
                                }
                                if(cell.getData().sort_code != ''){
                                    html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap">' +cell.getData().sort_code +'</div>';
                                }
                                if(cell.getData().ac_number != ''){
                                    html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap">' +cell.getData().ac_number +'</div>';
                                }
                            html += '</div>';
                        return html;
                    },
                },
                {
                    title: "No of Students",
                    field: "no_of_students",
                    headerHozAlign: "left",
                },
                {
                    title: "Amount",
                    field: "invoice_total",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-flex items-center relative">';
                                if(cell.getData().status == 2){
                                    html += '<span class="font-medium text-danger line-through whitespace-nowrap uppercase mr-2">' +cell.getData().proforma_total +'</span>';
                                    html += '<br/><span class="font-medium text-success whitespace-nowrap">' +cell.getData().invoice_total +'</span>';
                                }else if(cell.getData().status == 1){
                                    html += '<span class="font-medium whitespace-nowrap uppercase">'+cell.getData().proforma_total+'</span>';
                                }
                            html += '</div>';
                        return html;
                    },
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-block relative">';
                                if(cell.getData().status == 2){
                                    html += '<div class="font-medium whitespace-nowrap uppercase">Invoiced By: ' +cell.getData().invoiced_by +'</div>';
                                    html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap">' +cell.getData().invoiced_at +'</div>';
                                }else if(cell.getData().status == 1){
                                    html += '<div class="font-medium whitespace-nowrap uppercase">New</div>';
                                }
                            html += '</div>';
                        return html;
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "170",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        btns += '<div class="dropdown inline-flex ml-1">';
                                    btns += '<button class="dropdown-toggle btn-rounded btn btn-success text-white p-0 w-9 h-9" aria-expanded="false" data-tw-toggle="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="cloud-lightning" class="lucide lucide-cloud-lightning w-4 h-4"><path d="M6 16.326A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 .5 8.973"></path><path d="m13 12-3 5h4l-3 5"></path></svg></button>';
                                    btns += '<div class="dropdown-menu w-48">';
                                        btns += '<ul class="dropdown-content">';
                                            btns += '<li>';
                                                btns += '<a href="'+route('university.claims.proforma.download', cell.getData().id)+'" class="dropdown-item"><i data-lucide="printer" class="w-4 h-4 mr-2 text-success"></i> Proforma Download</a>';
                                            btns += '</li>';
                                            if(cell.getData().status == 2){
                                                btns += '<li>';
                                                    btns += '<a href="'+route('university.claims.invoices.download', cell.getData().id)+'" class="dropdown-item"><i data-lucide="printer" class="w-4 h-4 mr-2 text-success"></i> Invoice Download</a>';
                                                btns += '</li>';
                                            }
                                        btns += '</ul>';
                                    btns += '</div>';
                                btns += '</div>';
                        //btns += '<a href="'+route('university.claims.invoices.download', cell.getData().id)+'" class="btn-rounded btn btn-facebook text-white p-0 w-9 h-9 ml-1"><i data-lucide="printer" class="w-4 h-4"></i></a>';
                        if (cell.getData().deleted_at == null) {
                            btns += '<a href="'+route('university.claims.invoices.show', cell.getData().id)+'" class="btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
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
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 5];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                } 
            },
            // rowClick:function(e, row){
            //     window.open(row.getData().url, '_blank');
            // }
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
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();


(function () {
    // Tabulator
    if ($("#universityInvoiceListTable").length) {
        universityInvoiceListTable.init();
        
        // Filter function
        function filterTitleHTMLForm() {
            universityInvoiceListTable.init();
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
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterTitleHTMLForm();
        });
    }

})()