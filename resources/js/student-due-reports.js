import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var studentDueReportList = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let semester_ids = $("#due_semester_id").val() != "" ? $("#due_semester_id").val() : "";
        let course_ids = $("#due_semester_id").val() != "" ? $("#due_course_id").val() : "";
        let status_ids = $("#due_status_id").val() != "" ? $("#due_status_id").val() : "";
        let due_date = $("#due_date").val() != "" ? $("#due_date").val() : "";

        let tableContent = new Tabulator("#studentDueReportList", {
            ajaxURL: route("report.student.due.list"),
            ajaxParams: { semester_ids: semester_ids, course_ids: course_ids, status_ids: status_ids, due_date: due_date },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 50, 100, 200, 300, 400],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                // {
                //     title: "#SL",
                //     field: "sl",
                //     width: "60",
                //     headerSort: false,
                // },
                {
                    title: "Student ID",
                    field: "student_id",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().student_id+'</div>';
                    },
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return '<div class="whitespace-normal text-xs">'+cell.getData().course+'</div>';
                    },
                },
                {
                    title: "Intake",
                    field: "semester",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                /*{
                    title: "Start Date",
                    field: "start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "End Date",
                    field: "end_date",
                    headerHozAlign: "left",
                },*/
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "No of Agreement",
                    field: "no_of_agreement",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return '<div class="font-medium"><span class="text-danger">'+cell.getData().no_of_agreement+'</span>/<span class="text-success">'+cell.getData().no_of_agreement_all+'</span></div>';
                    },
                },
                {
                    title: "Claim Total",
                    field: "claim_total",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Received total",
                    field: "received_total",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Due",
                    field: "due",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Due Date",
                    field: "due_date",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return '<div class="whitespace-normal break-words text-xs">'+cell.getData().due_date+'</div>';
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
    if ($("#studentDueReportList").length) {
        studentDueReportList.init();

        // Filter function
        function filterDueReportHTMLForm() {
            studentDueReportList.init();
        }

        // On click go button
        $("#accDueSubmitBtn").on("click", function (event) {
            filterDueReportHTMLForm();
        });
    }


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

    $(document).on('click', '#downloadXl', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var $theLoader = $theBtn.find('.theLoader');

        let semester_ids = $("#due_semester_id").val() != "" ? $("#due_semester_id").val() : "";
        let course_ids = $("#due_course_id").val() != "" ? $("#due_course_id").val() : "";
        let statuses = $("#due_status_id").val() != "" ? $("#due_status_id").val() : "";

        $theBtn.addClass('disabled');
        $theLoader.fadeIn();

        axios({
            method: "post",
            url: route("report.student.due.xl.download"),
            params:{ semester_ids: semester_ids, course_ids: course_ids, statuses: statuses },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            responseType: 'blob',
        }).then((response) => {
            $theBtn.removeClass('disabled');
            $theLoader.fadeOut();

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'Student_due_reports.xlsx'); 
            document.body.appendChild(link);
            link.click();
            link.remove();
        }).catch((error) => {
            $theBtn.removeClass('disabled');
            $theLoader.fadeOut();
            console.log(error);
        });
    })
})()