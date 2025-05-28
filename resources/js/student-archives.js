import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";


("use strict");
var studentArchiveListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let studentId = $("#studentArchiveListTable").attr('data-student') != "" ? $("#studentArchiveListTable").attr('data-student') : "0";
        let queryStrARCV = $("#query-ARCV").val() != "" ? $("#query-ARCV").val() : "";

        let tableContent = new Tabulator("#studentArchiveListTable", {
            ajaxURL: route("student.archives.list"),
            ajaxParams: { studentId: studentId, queryStrARCV : queryStrARCV},
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
                    minWidth: 50,
                },
                {
                    title: "Field",
                    field: "field_name",
                    headerHozAlign: "left",
                    minWidth: 100,
                },
                {
                    title: "Old Value",
                    field: "old_value",
                    headerHozAlign: "left",
                    minWidth: 100,
                },
                {
                    title: "New Value",
                    field: "new_value",
                    headerHozAlign: "left",
                    minWidth: 100,
                },
                {
                    title: "Issued By",
                    field: "created_by",
                    headerHozAlign: "left",
                    minWidth: 100,
                    width: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                        html += '</div>';

                        return html;
                    }
                }
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
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    if ($("#studentArchiveListTable").length) {
        // Init Table
        studentArchiveListTable.init();

        // Filter function
        function filterHTMLFormARCV() {
            studentArchiveListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-ARCV").on("click", function (event) {
            filterHTMLFormARCV();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-ARCV").on("click", function (event) {
            $("#query-ARCV").val("");
            $("#status-ARCV").val("1");
            filterHTMLFormARCV();
        });

    }


})();