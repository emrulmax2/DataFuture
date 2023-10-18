import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";


("use strict");
var liveStudentsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        const form = document.getElementById('studentSearchForm');
        let form_data = new FormData(form);

        let tableContent = new Tabulator("#liveStudentsListTable", {
            ajaxURL: route("student.list"),
            ajaxParams: { form_data: form_data},
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
        let tomOptionsMul = {
            ...tomOptions,
            plugins: {
                ...tomOptions.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };
        var student_status = new TomSelect('#student_status', tomOptionsMul);
        var academic_year = new TomSelect('#academic_year', tomOptionsMul);
        var intake_semester = new TomSelect('#intake_semester', tomOptionsMul);
        var attendance_semester = new TomSelect('#attendance_semester', tomOptionsMul);
        var instance_year = new TomSelect('#instance_year', tomOptionsMul);
        var course = new TomSelect('#course', tomOptionsMul);
        var group = new TomSelect('#group', tomOptionsMul);
        var module = new TomSelect('#module', tomOptionsMul);
        var term_status = new TomSelect('#term_status', tomOptionsMul);
        var student_type = new TomSelect('#student_type', tomOptionsMul);
        var group_student_status = new TomSelect('#group_student_status', tomOptionsMul);

        // Reset Tom Select
        function resetStudentIDSearch(){
            $('#registration_no').val('');
        }

        function resetStudentSearch(){
            student_status.clear(true);
            $('#studentSearchStatus').val('0');
            $('#student_id, #student_name, #student_dob #student_abr, #student_ssn, #student_uhn, #student_mobile, #student_email, #student_post_code').val('');
        }

        function resetGroupSearch(){
            academic_year.clear(true);
            intake_semester.clear(true);
            attendance_semester.clear(true);
            instance_year.clear(true);
            course.clear(true);
            group.clear(true);
            module.clear(true);
            term_status.clear(true);
            student_type.clear(true);
            group_student_status.clear(true);
            $('#evening_weekend').val('');
            $('#groupSearchStatus').val('0');
        }

        /* Start List Table Inits */
            liveStudentsListTable.init();

            function filterStudentListTable() {
                liveStudentsListTable.init();
            }

            $("#studentIDSearchBtn, #studentSearchBtn, #studentGroupSearchBtn").on("click", function (event) {
                filterStudentListTable();
            });

            $("#resetStudentSearch").on("click", function (event) {
                resetStudentSearch();
                resetGroupSearch();
                resetStudentIDSearch();

                filterStudentListTable();
            });
        /* End List Table Inits */


        
        const studentSearchAccordion = tailwind.Accordion.getOrCreateInstance(document.querySelector("#studentSearchAccordion"));
        $('#advanceSearchToggle').on('click', function(e){
            e.preventDefault();
            $('#studentSearchAccordionWrap').slideToggle();
            $('#studentIDSearchBtn').fadeToggle();
            studentSearchAccordion.toggle();
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            filterStudentListTable();
        });

        $('#studentSearchBtn').on('click', function(){
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            if($(this).hasClass('collapsed')){
                $('#studentSearchStatus').val(1);
                $('#groupSearchStatus').val(0);
            }else{
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(0);
            }

            filterStudentListTable();
        });

        $('#studentGroupSearchBtn').on('click', function(){
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            if($(this).hasClass('collapsed')){
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(1);
            }else{
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(0);
            }

            filterStudentListTable();
        });

        $('.registration_no').on('keyup', function(){
            var $theInput = $(this);
            var SearchVal = $theInput.val();

            if(SearchVal.length >= 3){
                axios({
                    method: "post",
                    url: route('student.filter.id'),
                    data: {SearchVal : SearchVal},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $theInput.siblings('.autoFillDropdown').html(response.data.htm).fadeIn();
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                        $theInput.siblings('.autoFillDropdown').html('').fadeOut();
                    }
                });
            }else{
                $theInput.siblings('.autoFillDropdown').html('').fadeOut();
            }
        });

        $('.autoFillDropdown').on('click', 'li a:not(".disable")', function(e){
            e.preventDefault();
            var registration_no = $(this).attr('href');
            $(this).parent('li').parent('ul.autoFillDropdown').siblings('.registration_no').val(registration_no);
            $(this).parent('li').parent('.autoFillDropdown').html('').fadeOut();
        });
    }
})();