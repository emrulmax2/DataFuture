import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");

var amazonBookInfoListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let location = $("#searc_location").val();

        let tableContent = new Tabulator("#amazonBookInfoListTable", {
            ajaxURL: route("library.management.books.list"),
            ajaxParams: { querystr: querystr, location: location },
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
                    title: "Book",
                    field: "title",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="flex justify-start items-center">';
                                html += '<div class="intro-x mr-3" style="flex: 0 0 48px;">';
                                    html += '<img alt="'+cell.getData().title+'" class="w-auto h-12 shadow intro-x mr-5" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div>';
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().title+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().author != '' ? cell.getData().author : '')+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Publisher",
                    field: "publisher",
                    headerHozAlign: "left",
                },
                {
                    title: "ISBN13",
                    field: "isbn13",
                    headerHozAlign: "left",
                },
                {
                    title: "ISBN10",
                    field: "isbn10",
                    headerHozAlign: "left",
                },
                {
                    title: "Edition",
                    field: "edition",
                    headerHozAlign: "left",
                },
                {
                    title: "Published",
                    field: "publication_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Price",
                    field: "price",
                    headerHozAlign: "left",
                },
                {
                    title: "Qty",
                    field: "quantity",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "70",
                    formatter(cell, formatterParams) { 
                        return '<a data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#viewQtyModal" href="javascript:void(0);" class="viewQtyBtn text-primary font-medium underline">'+cell.getData().quantity+'</a>';
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
        });

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Roles Details",
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


var libraryBookLocationList = (function () {
    var _tableGen = function (amazonBookId) {
        // Setup Tabulator

        let tableContent = new Tabulator("#libraryBookLocationList", {
            ajaxURL: route("library.management.books.location.list"),
            ajaxParams: { amazonBookId: amazonBookId },
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
                    width: "80",
                },
                {
                    title: "Book",
                    field: "title",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) { 
                        var html = '<div class="flex justify-start items-center">';
                                html += '<div class="intro-x mr-3" style="flex: 0 0 48px;">';
                                    html += '<img alt="'+cell.getData().title+'" class="w-auto h-12 shadow intro-x mr-5" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div>';
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().title+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().author != '' ? cell.getData().author : '')+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "ISBN13",
                    field: "isbn13",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "ISBN10",
                    field: "isbn10",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Venue",
                    field: "venue",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Location",
                    field: "location",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Barcode",
                    field: "book_barcode",
                    headerHozAlign: "left",
                    width: 180,
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
    };
    return {
        init: function (amazonBookId) {
            _tableGen(amazonBookId);
        },
    };
})();

(function (){
    let lbmTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: false,
        maxItems: null,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let lbmTomOptionsMul = {
        ...lbmTomOptions,
        plugins: {
            ...lbmTomOptions.plugins,
            remove_button: {
                title: 'Remove this item',
            },
        },
    };

    let searc_location = new TomSelect('#searc_location', lbmTomOptionsMul);

    if ($("#amazonBookInfoListTable").length) {
        amazonBookInfoListTable.init();

        function filterHTMLForm() {
            amazonBookInfoListTable.init();
        }

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

        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLForm();
        });

        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            searc_location.clear(true);
            filterHTMLForm();
        });
    }

    
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const viewQtyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewQtyModal"));

    $('#amazonBookInfoListTable').on('click', '.viewQtyBtn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var abi_id = $theBtn.attr('data-id');

        libraryBookLocationList.init(abi_id);
    })

})();