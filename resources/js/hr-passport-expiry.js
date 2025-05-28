import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
 
("use strict");
var passportExpiryListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        let tableContent = new Tabulator("#passportExpiryListTable", {
            ajaxURL: route("hr.portal.passport.expiry.list"),
            ajaxParams: {},
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
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<a href="'+cell.getData().url+'" class="flex justify-start items-center">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5">';
                                    html += '<img alt="'+cell.getData().name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div>';
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().name+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().designation != '' ? cell.getData().designation : 'Unknown')+'</div>';
                                html += '</div>';
                            html += '</a>';
                        return html;
                    }
                },
                {
                    title: "Work Permit",
                    field: "doc_number",
                    headerHozAlign: "left",
                },
                {
                    title: "Work Permit Exp. Date",
                    field: "doc_expire",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "days",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    formatter(cell, formatterParams){
                        return '<span class="btn inline-flex '+cell.getData().class+' w-auto px-1 text-white py-0 rounded-0">'+cell.getData().days+(cell.getData().days == 1 ? ' Day' : ' Days')+'</span>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "180",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";

                        btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#sendPassportExpireMailModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="mail-check" class="w-4 h-4"></i></button>';
                        btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#viewPassportExpireMailsModal" class="delete_btn btn btn-facebook text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="list-ordered" class="w-4 h-4"></i></button>';
                        
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
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
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
    if ($("#passportExpiryListTable").length) {
        passportExpiryListTable.init();
    }
})();