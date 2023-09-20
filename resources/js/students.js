import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";


("use strict");
var liveStudentsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let semesters = $("#semesters-LSD").val() != "" ? $("#semesters-LSD").val() : "";
        let courses = $("#courses-LSD").val() != "" ? $("#courses-LSD").val() : "";
        let statuses = $("#courses-LSD").val() != "" ? $("#statuses-LSD").val() : "";
        let refno = $("#refno-LSD").val() != "" ? $("#refno-LSD").val() : "";
        let firstname = $("#firstname-LSD").val() != "" ? $("#firstname-LSD").val() : "";
        let lastname = $("#lastname-LSD").val() != "" ? $("#lastname-LSD").val() : "";
        let dob = $("#dob-LSD").val() != "" ? $("#dob-LSD").val() : "";

        let tableContent = new Tabulator("#liveStudentsListTable", {
            ajaxURL: route("student.list"),
            ajaxParams: { semesters: semesters, courses: courses, statuses: statuses, refno: refno, firstname: firstname, lastname: lastname, dob: dob},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [5, 10, 20, 30, 40],
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
                    field: "gender",
                    headerHozAlign: "left",
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
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
                sheetName: "Venues Details",
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
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };


    $('.lccTom').each(function(){
        if ($(this).attr("multiple") !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: "Remove this item",
                    },
                }
            };
        }
        new TomSelect(this, tomOptions);
    })

    if($('#liveStudentsListTable').length > 0){
        var semestersLSD = new TomSelect('#semesters-LSD', tomOptions);
        var coursesLSD = new TomSelect('#courses-LSD', tomOptions);
        var statusesLSD = new TomSelect('#statuses-LSD', tomOptions);

        // Init Table
        liveStudentsListTable.init();

        // Filter function
        function filterHTMLFormLSD() {
            liveStudentsListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-LSD")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormLSD();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-LSD").on("click", function (event) {
            filterHTMLFormLSD();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-LSD").on("click", function (event) {
            semestersLSD.clear(true);
            coursesLSD.clear(true);
            statusesLSD.clear(true);

            $("#refno-LSD").val('');
            $("#firstname-LSD").val('');
            $("#lastname-LSD").val('');
            $("#dob-LSD").val('');

            filterHTMLFormLSD();
        });
    }
})();