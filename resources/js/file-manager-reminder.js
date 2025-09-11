import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var expiredDocumentList = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let tableContent = new Tabulator("#expiredDocumentList", {
            ajaxURL: route("file.manager.reminder.list"),
            ajaxParams: { querystr: querystr },
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
                    field: "display_file_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Expired At",
                    field: "expire_at",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div>';
                                html += '<div class="font-medium whitespace-nowrap text-'+cell.getData().expire_color+'">'+cell.getData().expire_at+'</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Created",
                    field: "created_at",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div>';
                                html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_at+'</div>';
                                html += '<div class="text-slate-500 text-xs whitespace-nowrap"> By '+cell.getData().created_by+'</div>';
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
                        let attachments = cell.getData().attachments;  
                        console.log(attachments)             
                        var btns = "";
                            if(typeof attachments === 'object' || attachments !== null){
                                btns += '<div class="dropdown inline-flex ml-1">';
                                    btns += '<button class="dropdown-toggle btn btn-facebook text-white btn-rounded ml-1 p-0 w-7 h-7" aria-expanded="false" data-tw-toggle="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip-icon lucide-paperclip w-3 h-3"><path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"/></svg></button>';
                                    btns += '<div class="dropdown-menu w-64">';
                                        btns += '<ul class="dropdown-content">';
                                            $.each(attachments, function(index, attachment){
                                                btns += '<li>';
                                                    btns += '<a href="'+attachment.url+'" class="dropdown-item break-all whitespace-normal" style="align-items: flex-start;"><i style="flex: 0 0 1rem;" data-lucide="download-cloud" class="w-4 h-4 mr-2 text-success"></i> '+attachment.name+'</a>';
                                                btns += '</li>';
                                            });
                                        btns += '</div>';
                                    btns += '</div>';
                                btns += '</div>';
                            }
                            btns += '<a href="'+cell.getData().download_url+'" download class="downloadDoc relative btn btn-success text-white btn-rounded ml-1 p-0 w-7 h-7"><i data-lucide="cloud-lightning" class="w-3 h-3"></i></a>';
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
            rowClick:function(e, row){
                window.open(row.getData().url, '_self');
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
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();


(function () {
    // Tabulator
    if ($("#expiredDocumentList").length) {
        expiredDocumentList.init();

        // Filter function
        function filterTitleHTMLForm() {
            expiredDocumentList.init();
        }

        // On submit filter form
        $("#query")[0].addEventListener(
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
            filterTitleHTMLForm();
        });
    }
})()