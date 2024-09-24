import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var accountsDueReportTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let semester_ids = $("#due_semester_id").val() != "" ? $("#due_semester_id").val() : "";
        let course_ids = $("#due_course_id").val() != "" ? $("#due_course_id").val() : "";
        let status_ids = $("#due_status_id").val() != "" ? $("#due_status_id").val() : "";
        let due_date = $("#due_date").val() != "" ? $("#due_date").val() : "";

        let tableContent = new Tabulator("#accountsDueReportTable", {
            ajaxURL: route("statuses.list"),
            ajaxParams: { semester_ids: semester_ids, course_ids: course_ids, status_ids : status_ids, due_date : due_date },
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
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                },
                {
                    title: "Process",
                    field: "process",
                    headerHozAlign: "left",
                    headerSort: false,
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
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){

    let dueTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let dueTomOptionsMul = {
        ...dueTomOptions,
        plugins: {
            ...dueTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var due_semester_id = new TomSelect('#due_semester_id', dueTomOptionsMul);
    var due_course_id = new TomSelect('#due_course_id', dueTomOptionsMul);
        due_course_id.clear(true);
        due_course_id.clearOptions();
        due_course_id.disable;
    var due_status_id = new TomSelect('#due_status_id', dueTomOptionsMul);
        due_status_id.clear(true);
        due_status_id.clearOptions();
        due_status_id.disable;

    $('#due_semester_id').on('change', function(){
        var $theSemester = $(this);
        var theSemesters = $theSemester.val();

        if(theSemesters.length > 0){
            axios({
                method: "post",
                url: route('reports.account.due.get.course.status'),
                data: {theSemesters : theSemesters},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    var courses = response.data.courses;
                    var statuses = response.data.statuses;
                    due_course_id.enable();
                    $.each(courses, function(index, row) {
                        due_course_id.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    due_course_id.refreshOptions();

                    due_status_id.enable();
                    $.each(statuses, function(index, row) {
                        due_status_id.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    due_status_id.refreshOptions();
                }

            }).catch(error => {
                if (error.response) {
                    due_course_id.clear(true);
                    due_course_id.clearOptions();
                    due_course_id.disable();

                    due_status_id.clear(true);
                    due_status_id.clearOptions();
                    due_status_id.disable();
                }

            });
        }else{
            due_course_id.clear(true);
            due_course_id.clearOptions();
            due_course_id.disable();

            due_status_id.clear(true);
            due_status_id.clearOptions();
            due_status_id.disable();
        }
    });
    
    
    $('#due_course_id').on('change', function(){
        var $theSemester = $('#due_semester_id');
        var theSemesters = $theSemester.val();
        var $theCourse = $(this);
        var theCourses = $theCourse.val();

        if(theCourses.length > 0){
            axios({
                method: "post",
                url: route('reports.account.due.get.statuses'),
                data: {theSemesters : theSemesters, theCourses : theCourses},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    var statuses = response.data.statuses;
                    due_status_id.enable();
                    $.each(statuses, function(index, row) {
                        due_status_id.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    due_status_id.refreshOptions();
                }

            }).catch(error => {
                if (error.response) {
                    due_status_id.clear(true);
                    due_status_id.clearOptions();
                    due_status_id.disable();
                }

            });
        }else{
            due_status_id.clear(true);
            due_status_id.clearOptions();
            due_status_id.disable();
        }
    });

})()