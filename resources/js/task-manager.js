import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var taskAssignedStudentTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let task_id = $("#taskAssignedStudentTable").attr('data-taskid');

        let tableContent = new Tabulator("#taskAssignedStudentTable", {
            ajaxURL: route("task.manager.list"),
            ajaxParams: { querystr : querystr, task_id : task_id},
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
                    title: "Ref. No",
                    field: "application_no",
                    headerHozAlign: "left",
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                },
                {
                    title: "DOB",
                    field: "date_of_birth",
                    headerHozAlign: "left",
                },
                {
                    title: "Gender",
                    field: "sex_identifier_id",
                    headerHozAlign: "left",
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status_id",
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

        // Export
        $("#tabulator-export-csv-LSD").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-LSD").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-LSD").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Students Details",
            });
        });

        $("#tabulator-export-html-LSD").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-LSD").on("click", function (event) {
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
    if($('#taskAssignedStudentTable').length > 0){
        // Init Table
        taskAssignedStudentTable.init();

        // Filter function
        function filterHTMLFormADM() {
            taskAssignedStudentTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormADM();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLFormADM();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val('');

            filterHTMLFormADM();
        });
    }
})();