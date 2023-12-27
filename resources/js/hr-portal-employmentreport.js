import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var employmentReportListTable = (function () {
    var _tableGen = function () {
        let tableContent = new Tabulator("#employmentReportListTable", {
            ajaxURL: route("hr.portal.employment.reports.list"),
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
                    title: "SL No",
                    field: "sl",
                },
                {
                    title: "Description",
                    field: "report_description",
                    headerHozAlign: "left",
                },
                {
                    title: "File Name",
                    field: "file_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Run",
                    field: "last_run",
                    headerHozAlign: "left",
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
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
    if ($("#employmentReportListTable").length) {
        employmentReportListTable.init();
    }
})();