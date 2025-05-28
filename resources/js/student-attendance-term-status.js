import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var stdAtnTermStatusHistoryTable = (function () {
    var _tableGen = function (student_id, term_id) {
        let tableContent = new Tabulator("#stdAtnTermStatusHistoryTable", {
            ajaxURL: route("student.attendance.term.status.list"),
            ajaxParams: { term_id: term_id, student_id : student_id},
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
                    title: "Term",
                    field: "term",
                    headerHozAlign: "left"
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left"
                },
                {
                    title: "Reason",
                    field: "status_change_reason",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal break-all">'+cell.getData().status_change_reason+'</div>';
                    }
                },
                {
                    title: "By",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: 200,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().status_change_date != '' ? cell.getData().status_change_date : (cell.getData().created_at != '' ? cell.getData().created_at : ''))+'</div>';
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
    };
    return {
        init: function (student_id, term_id) {
            _tableGen(student_id, term_id);
        },
    };
})();

(function(){
    if ($("#stdAtnTermStatusHistoryModal").length) {
        const stdAtnTermStatusHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#stdAtnTermStatusHistoryModal"));

        const stdAtnTermStatusHistoryModalEl = document.getElementById('stdAtnTermStatusHistoryModal')
        stdAtnTermStatusHistoryModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#stdAtnTermStatusHistoryTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role').attr('data-term', 0);
        });

        $(document).on('click', '.sts_history_btn', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var term_id = $theBtn.attr('data-term');
            var student_id = $theBtn.attr('data-student');

            stdAtnTermStatusHistoryTable.init(student_id, term_id);
        })
    }
})()