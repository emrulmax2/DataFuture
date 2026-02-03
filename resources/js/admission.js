import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {createApp} from 'vue'

import IMask from 'imask';
import { set } from "lodash";

("use strict");
var admissionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let semesters = $("#semesters-ADM").val() != "" ? $("#semesters-ADM").val() : "";
        let courses = $("#courses-ADM").val() != "" ? $("#courses-ADM").val() : "";
        let statuses = $("#statuses-ADM").val() != "" ? $("#statuses-ADM").val() : "";
        let refno = $("#refno-ADM").val() != "" ? $("#refno-ADM").val() : "";
        let firstname = $("#firstname-ADM").val() != "" ? $("#firstname-ADM").val() : "";
        let lastname = $("#lastname-ADM").val() != "" ? $("#lastname-ADM").val() : "";
        let dob = $("#dob-ADM").val() != "" ? $("#dob-ADM").val() : "";
        let agents = $("#agents-ADM").val() != "" ? $("#agents-ADM").val() : "";
        let email = $("#email-ADM").val() != "" ? $("#email-ADM").val() : "";
        let phone = $("#phone-ADM").val() != "" ? $("#phone-ADM").val() : "";

        let tableContent = new Tabulator("#admissionListTable", {
            ajaxURL: route("admission.list"),
            ajaxParams: { semesters: semesters, courses: courses, statuses: statuses, refno: refno, firstname: firstname, lastname: lastname, dob: dob, agents: agents,email:email,phone:phone},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 50,100,200,500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Ref. No",
                    field: "application_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                                    html += '<img alt="'+cell.getData().first_name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -5px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().application_no+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().full_name+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
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
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) { 
                        let status = cell.getData().status_id;
                        let btns = status;
                        if (cell.getData().create_account == true) {
                            if(cell.getData().apply_ready!=false) {
                                let url = route('impersonate', { id: cell.getData().apply_ready, guardName: 'applicant' });
                                btns = '<a target="__blank" href="' + url + '" title="Login As Applicant" class="btn btn-warning min-w-max mr-1 tooltip"><i data-lucide="log-in" class="w-5 h-5"></i></a>';
                                btns += '<button title="Send Mail" data-id="' + cell.getData().apply_ready + '" class="sendMail tooltip btn btn-success text-white min-w-max"><i data-lucide="send" class="w-5 h-5 "></i></button>';

                            } else {
                                btns = '<button  data-id="' + cell.getData().id + '" data-tw-toggle="modal" data-tw-target="#addNewAccountConfirm" type="button" class="create_new_account btn-rounded-md btn btn-primary text-white  ml-1"><i data-lucide="plus" class="w-4 h-4"></i> Create Account</button>';

                            }
                        }

                        return btns;
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

                $(".sendMail").on("click", function (e) {
                    e.preventDefault();
                    let $tthis =$(this);
                    let id = $tthis.data("id");
                    // Call your send mail function here
                    $tthis.html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
                    tailwind.svgLoader()
                    $tthis.attr("disabled", "disabled");
                    axios({
                        method: "get",
                        url: route("admission.applicant.password.change",id),
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        },
                    })
                    .then((response) => {
                            $tthis.removeAttr("disabled");
                            $tthis.html('<i data-lucide="send" class="w-5 h-5 "></i>')
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                            const successModalStart = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
                            successModalStart.show();
                            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                                $("#successModal .successModalTitle").html("Email Sent!" );
                                $("#successModal .successModalDesc").html('An Password Change Email Sent to your personal email');
                            }); 

                            setTimeout(() => {
                                successModalStart.hide();
                            }, 3000);
                    })
                    .catch((error) => {
                            console.log(error);
                            $tthis.removeAttr("disabled");
                    });
                });
            
                
            },
            rowClick:function(e, row){
                // check if cell has status has button then below code will not work
                if (row.getData().create_account==false) {
                    window.open(row.getData().url, '_blank');
                }

                //set the student_id
                const studentIdInput = document.getElementById("student_id");
                if (studentIdInput) {
                    studentIdInput.value = row.getData().id;
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

        // Export
        $("#tabulator-export-csv-ADM").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-ADM").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-ADM").on("click", function (event) {
            // window.XLSX = xlsx;
            // tableContent.download("xlsx", "data.xlsx", {
            //     sheetName: "Admission Details",
            // });
            event.preventDefault();

            document.querySelector('#tabulator-export-xlsx-ADM').setAttribute('disabled', 'disabled');
            document.querySelector("#excelExportBtn").style.cssText ="display: inline-block;";


            let semesters = $("#semesters-ADM").val() != "" ? $("#semesters-ADM").val() : "";
            let courses = $("#courses-ADM").val() != "" ? $("#courses-ADM").val() : "";
            let statuses = $("#statuses-ADM").val() != "" ? $("#statuses-ADM").val() : "";
            let refno = $("#refno-ADM").val() != "" ? $("#refno-ADM").val() : "";
            let firstname = $("#firstname-ADM").val() != "" ? $("#firstname-ADM").val() : "";
            let lastname = $("#lastname-ADM").val() != "" ? $("#lastname-ADM").val() : "";
            let dob = $("#dob-ADM").val() != "" ? $("#dob-ADM").val() : "";
            let agents = $("#agents-ADM").val() != "" ? $("#agents-ADM").val() : "";
            let email = $("#email-ADM").val() != "" ? $("#email-ADM").val() : "";
            let phone = $("#phone-ADM").val() != "" ? $("#phone-ADM").val() : "";
            
            axios({
                method: "get",
                url: route("admission.export"),
                params:{ semesters: semesters, courses: courses, statuses: statuses, refno: refno, firstname: firstname, lastname: lastname, dob: dob, agents: agents,email:email,phone:phone},
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                responseType: 'blob',
            })
            .then((response) => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'admission_download.xlsx'); 
                    document.body.appendChild(link);
                    link.click();
                    document.querySelector('#tabulator-export-xlsx-ADM').removeAttribute('disabled', 'disabled');
                    document.querySelector("#excelExportBtn").style.cssText ="display: none;";
            })
            .catch((error) => {
                    console.log(error);
                    document.querySelector('#tabulator-export-xlsx-ADM').removeAttribute('disabled', 'disabled');
                    document.querySelector("#excelExportBtn").style.cssText ="display: none;";
            });
        });

        $("#tabulator-export-html-ADM").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-ADM").on("click", function (event) {
            tableContent.print();
        });
        //comment for uploader
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var educationQualTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#educationQualTable").attr('data-applicant') != "" ? $("#educationQualTable").attr('data-applicant') : "0";
        let querystr = $("#query-EQ").val() != "" ? $("#query-EQ").val() : "";
        let status = $("#status-EQ").val() != "" ? $("#status-EQ").val() : "";

        let tableContent = new Tabulator("#educationQualTable", {
            ajaxURL: route("qualification.list"),
            ajaxParams: { applicantId: applicantId, querystr: querystr, status: status},
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
                    title: "#SL",
                    field: "sl",
                    width: "110",
                },
                {
                    title: "Awarding Body",
                    field: "awarding_body",
                    headerHozAlign: "left",
                },
                {
                    title: "Highest Academic Qualification",
                    field: "highest_academic",
                    headerHozAlign: "left",
                },
                {
                    title: "Subjects",
                    field: "subjects",
                    headerHozAlign: "left",
                },
                {
                    title: "Result",
                    field: "result",
                    headerHozAlign: "left",
                },
                {
                    title: "Award Date",
                    field: "degree_award_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "150",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editQualificationModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
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
        $("#tabulator-export-csv-EQ").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EQ").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EQ").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Education Qualification Details",
            });
        });

        $("#tabulator-export-html-EQ").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EQ").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var employmentHistoryTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#employmentHistoryTable").attr('data-applicant') != "" ? $("#employmentHistoryTable").attr('data-applicant') : "0";
        let querystr = $("#query-EH").val() != "" ? $("#query-EH").val() : "";
        let status = $("#status-EH").val() != "" ? $("#status-EH").val() : "";

        let tableContent = new Tabulator("#employmentHistoryTable", {
            ajaxURL: route("employment.list"),
            ajaxParams: { applicantId: applicantId, querystr: querystr, status: status},
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
                    title: "#SL",
                    field: "sl",
                    width: "80",
                },
                {
                    title: "Organization",
                    field: "company_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "company_phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Position",
                    field: "position",
                    headerHozAlign: "left",
                },
                {
                    title: "Start",
                    field: "start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "End",
                    field: "end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Address",
                    field: "address",
                    headerHozAlign: "left",
                    width: "180",
                    formatter(cell, formatterParams) {   
                        return '<div class="whitespace-nowrap">'+cell.getData().address+'</div>';
                    }
                },
                {
                    title: "Contact Person",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Position",
                    field: "contact_position",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "contact_phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "150",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editEmployementHistoryModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
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
        $("#tabulator-export-csv-EH").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EH").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EH").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Employment History Details",
            });
        });

        $("#tabulator-export-html-EH").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EH").on("click", function (event) {
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
    let admissionDatepickerOpt = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        maxDate: new Date(),
        dropdowns: {
            minYear: 1900,
            maxYear: null,
            months: true,
            years: true,
        },
    };

    $('.admissionDatepicker').each(function(){
        new Litepicker({
            element: this,
            ...admissionDatepickerOpt,
        });
    })

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    if($('.phoneMask').length > 0){
        $('.phoneMask').each(function(){
            IMask(
                this, {
                  mask: '00000000000'
                }
            )
        })
    }
    
    //var employment_status = new TomSelect('#employment_status', tomOptions);

    $('.addmissionLccTom').each(function(){
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
    });

    if($('#admissionListTable').length > 0){
        let multiTomOpt = {
            ...tomOptions,
            plugins: {
                ...tomOptions.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };
        var semestersADM = new TomSelect('#semesters-ADM', multiTomOpt);
        var coursesADM = new TomSelect('#courses-ADM', multiTomOpt);
        var statusesADM = new TomSelect('#statuses-ADM', multiTomOpt);
        var agentADM = new TomSelect('#agents-ADM', multiTomOpt);

        // Init Table
        admissionListTable.init();

        // Filter function
        function filterHTMLFormADM() {
            admissionListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-ADM")[0].addEventListener(
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
        $("#tabulator-html-filter-go-ADM").on("click", function (event) {
            filterHTMLFormADM();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-ADM").on("click", function (event) {
            semestersADM.clear(true);
            coursesADM.clear(true);
            statusesADM.clear(true);
            agentADM.clear(true);

            $("#refno-ADM").val('');
            $("#firstname-ADM").val('');
            $("#lastname-ADM").val('');
            $("#dob-ADM").val('');

            filterHTMLFormADM();
        });

        semestersADM.on('change',function(event){
            
            axios({
                method: "get",
                url: route("course.creation.coursesbysemester"),
                params:{ semesters:event },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                responseType: 'json',
            })
            .then((response) => {
                let courseList = response.data
                $(courseList).each(function(index,course) {
                    coursesADM.addOption({value:course.id,text:course.name})
                  });
            })
            .catch((error) => {
                    console.log(error);
            });
        })
    }

    if($('#educationQualTable').length > 0){
        if($('#educationQualTable').hasClass('activeTable')){
            educationQualTable.init();
        }
        // Filter function
        function filterHTMLFormEQ() {
            educationQualTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-EQ").on("click", function (event) {
            filterHTMLFormEQ();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EQ").on("click", function (event) {
            $("#query-EQ").val("");
            $("#status-EQ").val("1");
            filterHTMLFormEQ();
        });

        const addQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addQualificationModal"));
        const editQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editQualificationModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addQualificationModalEl = document.getElementById('addQualificationModal')
        addQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addQualificationModal .acc__input-error').html('');
            $('#addQualificationModal .modal-body input').val('');
            $('#addQualificationModal .modal-body select').val('');
        });

        const editQualificationModalEl = document.getElementById('editQualificationModal')
        editQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editQualificationModal .acc__input-error').html('');
            $('#editQualificationModal .modal-body input').val('');
            $('#editQualificationModal .modal-body select').val('');
            $('#editQualificationModal .modal-footer input[name="id"]').val('0');
        });

        $('#addQualificationForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('addQualificationForm');
        
            document.querySelector('#saveEducationQualification').setAttribute('disabled', 'disabled');
            document.querySelector("#saveEducationQualification svg").style.cssText ="display: inline-block;";
    
            let form_data = new FormData(form);
            let applicantId = $('[name="applicant_id"]', $form).val();
            axios({
                method: "post",
                url: route('qualification.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#saveEducationQualification').removeAttribute('disabled');
                    document.querySelector("#saveEducationQualification svg").style.cssText = "display: none;";
    
                    addQualificationModal.hide();
    
                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Eucation Qualification date successfully added.');
                    });                
                        
                }
                educationQualTable.init();
            }).catch(error => {
                document.querySelector('#saveEducationQualification').removeAttribute('disabled');
                document.querySelector("#saveEducationQualification svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addQualificationForm .${key}`).addClass('border-danger');
                            $(`#addQualificationForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });
    
        $("#educationQualTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");
    
            axios({
                method: "get",
                url: route("qualification.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editQualificationModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        $('#editQualificationModal input[name="highest_academic"]').val(dataset.highest_academic ? dataset.highest_academic : '');
                        $('#editQualificationModal input[name="awarding_body"]').val(dataset.awarding_body ? dataset.awarding_body : '');
                        $('#editQualificationModal input[name="subjects"]').val(dataset.subjects ? dataset.subjects : '');
                        $('#editQualificationModal input[name="result"]').val(dataset.result ? dataset.result : '');
                        $('#editQualificationModal input[name="degree_award_date"]').val(dataset.degree_award_date ? dataset.degree_award_date : '');
                        
                        $('#editQualificationModal input[name="id"]').val(editId);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });
    
        $("#editQualificationForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editQualificationForm input[name="id"]').val();
            const form = document.getElementById("editQualificationForm");
    
            document.querySelector('#updateEducationQualification').setAttribute('disabled', 'disabled');
            document.querySelector('#updateEducationQualification svg').style.cssText = 'display: inline-block;';
    
            let form_data = new FormData(form);
    
            axios({
                method: "post",
                url: route("qualification.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateEducationQualification").removeAttribute("disabled");
                    document.querySelector("#updateEducationQualification svg").style.cssText = "display: none;";
                    editQualificationModal.hide();
    
                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Education Qualification data successfully updated.');
                    });
                }
                educationQualTable.init();
            }).catch((error) => {
                document.querySelector("#updateEducationQualification").removeAttribute("disabled");
                document.querySelector("#updateEducationQualification svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editQualificationForm .${key}`).addClass('border-danger')
                            $(`#editQualificationForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editQualificationModal.hide();
    
                        errorModal.show();
                        document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                            $("#errorModal .errorModalTitle").html("Oops!");
                            $("#errorModal .errorModalDesc").html('No data change found!');
                        });
                    } else {
                        console.log("error");
                    }
                }
            });
        });
    
        // Delete Course
        $('#educationQualTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');
    
            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });
    
        // Restore Course
        $('#educationQualTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');
    
            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    
        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');
    
            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('qualification.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();
    
                        successModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                        });
                    }
                    educationQualTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('qualification.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();
    
                        successModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record Successfully Restored!');
                        });
                    }
                    educationQualTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })
    }

    if($('#employmentHistoryTable').length > 0){
        if($('#employmentHistoryTable').hasClass('activeTable')){
            employmentHistoryTable.init();
        }

        // Filter function
        function filterHTMLFormEH() {
            employmentHistoryTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-EH").on("click", function (event) {
            filterHTMLFormEH();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EH").on("click", function (event) {
            $("#query-EH").val("");
            $("#status-EH").val("1");
            filterHTMLFormEH();
        });

        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';
        const addEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmployementHistoryModal"));
        const editEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmployementHistoryModal"));
        const confirmEmploymentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmEmploymentModal"));
        const confirmEmploymentModalEl = document.getElementById('confirmEmploymentModal')
        confirmEmploymentModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#confirmEmploymentModal .confModTitle').html('');
            $('#confirmEmploymentModal .confModDesc').html('');
            $('#confirmEmploymentModal .agreeWith').attr('data-status', 'none').removeAttr('disabled');
        });

        
        var employment_status = new TomSelect('#employment_status', tomOptions);


        $('#employment_status').on('change', function(){
            var employmentStatus = $(this).val();
            var applicantId = $(this).attr('data-applicant');
            
            confirmEmploymentModal.show();
            if($(this).val() == ''){
                $('#confirmEmploymentModal .confModTitle').html('Oops!');
                $('#confirmEmploymentModal .confModDesc').html('Employment status can not be empty. Please select a valid one.');
                $('#confirmEmploymentModal .agreeWith').attr('data-status', 'none').attr('disabled', 'disabled');
            }else if($(this).val() == 'Unemployed' || $(this).val() == 'Contractor' || $(this).val() == 'Consultant' || $(this).val() == 'Office Holder'){
                $('#confirmEmploymentModal .confModTitle').html('Are you sure?');
                $('#confirmEmploymentModal .confModDesc').html('You want to change students employment status? All existing employment history will be removed.');
                $('#confirmEmploymentModal .agreeWith').attr('data-status', employmentStatus);
            }else{
                $('#confirmEmploymentModal .confModTitle').html('Are you sure?');
                $('#confirmEmploymentModal .confModDesc').html('You want to change students employment status?  All existing employment will found under Archive status.');
                $('#confirmEmploymentModal .agreeWith').attr('data-status', employmentStatus);
            }
        });

        $('#confirmEmploymentModal .disAgreeWith').on('click', function(e){
            e.preventDefault();
            confirmEmploymentModal.hide();
            window.location.reload();
        });

        $('#confirmEmploymentModal .agreeWith').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);
            var applicant = $btn.attr('data-applicant');
            var status = $btn.attr('data-status');

            $btn.attr('disabled', 'disabled');
            $btn.siblings('.disAgreeWith').attr('disabled', 'disabled');

            axios({
                method: "post",
                url: route('admission.update.employment.status'),
                data: {applicant : applicant, status : status},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $btn.removeAttr('disabled');
                    $btn.siblings('.disAgreeWith').removeAttr('disabled');

                    confirmEmploymentModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student employment status successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                $btn.removeAttr('disabled');
                $btn.siblings('.disAgreeWith').removeAttr('disabled');

                if (error.response) {
                    console.log('error');
                }
            });
        });

        $('#successModal .successCloser').on('click', function(e){
            e.preventDefault();
            if($(this).attr('data-action') == 'RELOAD'){
                successModal.hide();
                window.location.reload();
            }else{
                successModal.hide();
            }
        });

        $('#addEmployementHistoryForm input[name="continuing"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addEmployementHistoryForm input[name="end_date"]').val('').attr('disabled', 'disabled');
            }else{
                $('#addEmployementHistoryForm input[name="end_date"]').val('').removeAttr('disabled');
            }
        })
    
        $('#editEmployementHistoryModal input[name="continuing"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editEmployementHistoryModal input[name="end_date"]').val('').attr('disabled', 'disabled');
            }else{
                $('#editEmployementHistoryModal input[name="end_date"]').val('').removeAttr('disabled');
            }
        })
    
    
        $('#addEmployementHistoryForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('addEmployementHistoryForm');
        
            document.querySelector('#saveEmpHistory').setAttribute('disabled', 'disabled');
            document.querySelector("#saveEmpHistory svg").style.cssText ="display: inline-block;";
    
            let form_data = new FormData(form);
            let applicantId = $('[name="applicant_id"]', $form).val();
            axios({
                method: "post",
                url: route('employment.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => { 
                if (response.status == 200) {
                    document.querySelector('#saveEmpHistory').removeAttribute('disabled');
                    document.querySelector("#saveEmpHistory svg").style.cssText = "display: none;";
    
                    addEmployementHistoryModal.hide();
    
                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Employment History date successfully added.');
                    });                
                        
                }
                employmentHistoryTable.init();
            }).catch(error => {
                document.querySelector('#saveEmpHistory').removeAttribute('disabled');
                document.querySelector("#saveEmpHistory svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addEmployementHistoryForm .${key}`).addClass('border-danger');
                            $(`#addEmployementHistoryForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });
    
        $("#employmentHistoryTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");
    
            axios({
                method: "get",
                url: route("employment.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
    
                    $('#editEmployementHistoryModal input[name="company_name"]').val(dataset.company_name ? dataset.company_name : '');
                    $('#editEmployementHistoryModal input[name="company_phone"]').val(dataset.company_phone ? dataset.company_phone : '');
                    $('#editEmployementHistoryModal input[name="position"]').val(dataset.position ? dataset.position : '');
                    $('#editEmployementHistoryModal input[name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                    if(dataset.continuing && dataset.continuing == 1){
                        $('#editEmployementHistoryModal input[name="continuing"]').prop('checked', true);
                        $('#editEmployementHistoryModal input[name="end_date"]').val('').attr('disabled', 'disabled');
                    }else{
                        $('#editEmployementHistoryModal input[name="continuing"]').prop('checked', false);
                        $('#editEmployementHistoryModal input[name="end_date"]').val(dataset.end_date ? dataset.end_date : '').removeAttr('disabled');
                    }
    
                    if(dataset.address_line_1 != '' || dataset.city != '' || dataset.post_code != '' || dataset.country != ''){
                        var htmls = '';
                        htmls += '<span class="text-slate-600 font-medium">'+dataset.address_line_1+'</span><br/>';
                        if(dataset.address_line_2 != ''){
                            htmls += '<span class="text-slate-600 font-medium">'+dataset.address_line_2+'</span><br/>';
                        }
                        htmls += '<span class="text-slate-600 font-medium">'+dataset.city+'</span>, ';
                        if(dataset.state != ''){
                            htmls += '<span class="text-slate-600 font-medium">'+dataset.state+'</span>, <br/>';
                        }else{
                            htmls += '<br/>';
                        }
                        htmls += '<span class="text-slate-600 font-medium">'+dataset.post_code+'</span>,<br/>';
                        htmls += '<span class="text-slate-600 font-medium">'+dataset.country+'</span><br/>';
    
                        htmls += '<input type="hidden" name="employment_address" value="'+dataset.address_line_1+'"/>';
                        htmls += '<input type="hidden" name="employment_address_line_1" value="'+(dataset.address_line_1 != '' ? dataset.address_line_1 : '')+'"/>';
                        htmls += '<input type="hidden" name="employment_address_line_2" value="'+(dataset.address_line_2 != '' ? dataset.address_line_2 : '')+'"/>';
                        htmls += '<input type="hidden" name="employment_address_city" value="'+(dataset.city != '' ? dataset.city : '')+'"/>';
                        htmls += '<input type="hidden" name="employment_address_state" value="'+(dataset.state != '' ? dataset.state : '')+'"/>';
                        htmls += '<input type="hidden" name="employment_address_postal_zip_code" value="'+(dataset.post_code != '' ? dataset.post_code : '')+'"/>';
                        htmls += '<input type="hidden" name="employment_address_country" value="'+(dataset.country != '' ? dataset.country : '')+'"/>';
    
                        $('#editEmpHistoryAddress').fadeIn().html(htmls).addClass('active');
                        $('#editEmployementHistoryModal .addressPopupToggler span').html('Update Address');
                    }else{
                        $('#editEmpHistoryAddress').fadeOut().html('').removeClass('active');
                        $('#editEmployementHistoryModal .addressPopupToggler span').html('Add Address');
                    }
    
                    $('#editEmployementHistoryModal input[name="contact_name"]').val(dataset.reference[0].name ? dataset.reference[0].name : '');
                    $('#editEmployementHistoryModal input[name="contact_position"]').val(dataset.reference[0].position ? dataset.reference[0].position : '');
                    $('#editEmployementHistoryModal input[name="contact_phone"]').val(dataset.reference[0].phone ? dataset.reference[0].phone : '');
                    $('#editEmployementHistoryModal input[name="contact_email"]').val(dataset.reference[0].email ? dataset.reference[0].email : '');
                    $('#editEmployementHistoryModal input[name="ref_id"]').val(dataset.reference[0].id ? dataset.reference[0].id : '');
                    
                    $('#editEmployementHistoryModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });
    
        $("#editEmployementHistoryForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editEmployementHistoryForm input[name="id"]').val();
            const form = document.getElementById("editEmployementHistoryForm");
    
            document.querySelector('#updateEmpHistory').setAttribute('disabled', 'disabled');
            document.querySelector('#updateEmpHistory svg').style.cssText = 'display: inline-block;';
    
            let form_data = new FormData(form);
    
            axios({
                method: "post",
                url: route("employment.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateEmpHistory").removeAttribute("disabled");
                    document.querySelector("#updateEmpHistory svg").style.cssText = "display: none;";
                    editEmployementHistoryModal.hide();
    
                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Employment History data successfully updated.');
                    });
                }
                employmentHistoryTable.init();
            }).catch((error) => {
                document.querySelector("#updateEmpHistory").removeAttribute("disabled");
                document.querySelector("#updateEmpHistory svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editEmployementHistoryForm .${key}`).addClass('border-danger')
                            $(`#editEmployementHistoryForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editEmployementHistoryModal.hide();
    
                        errorModal.show();
                        document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                            $("#errorModal .errorModalTitle").html("Oops!");
                            $("#errorModal .errorModalDesc").html('No data change found!');
                        });
                    } else {
                        console.log("error");
                    }
                }
            });
        });
    
        // Delete Course
        $('#employmentHistoryTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');
    
            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEEH');
            });
        });
    
        // Restore Course
        $('#employmentHistoryTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');
    
            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREEH');
            });
        });
    
        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');
    
            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETEEH'){
                axios({
                    method: 'delete',
                    url: route('employment.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();
    
                        successModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                        });
                    }
                    employmentHistoryTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREEH'){
                axios({
                    method: 'post',
                    url: route('employment.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();
    
                        successModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record Successfully Restored!');
                        });
                    }
                    employmentHistoryTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })
    }


    /*Address Modal*/
    if($('#addressModal').length > 0){
        const addressModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addressModal"));

        const addressModalEl = document.getElementById('addressModal')
        addressModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addressModal .acc__input-error').html('');
            $('#addressModal input').val('');
        });
        $('.addressPopupToggler').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);
            var wrapid = $btn.attr('data-address-wrap');
            var prefix = $btn.attr('data-prefix');

            $('#addressModal input[name="place"]').val(wrapid);
            $('#addressModal input[name="prefix"]').val(prefix);
            if($(wrapid).hasClass('active')){
                $('#addressModal #student_address_address_line_1').val($(wrapid+' input[name="'+prefix+'_address_line_1"]').val());
                $('#addressModal #student_address_address_line_2').val($(wrapid+' input[name="'+prefix+'_address_line_2"]').val());
                $('#addressModal #student_address_city').val($(wrapid+' input[name="'+prefix+'_address_city"]').val());
                $('#addressModal #student_address_state_province_region').val($(wrapid+' input[name="'+prefix+'_address_state"]').val());
                $('#addressModal #student_address_postal_zip_code').val($(wrapid+' input[name="'+prefix+'_address_postal_zip_code"]').val());
                $('#addressModal #student_address_country').val($(wrapid+' input[name="'+prefix+'_address_country"]').val());
            }
        });

        $('#addressForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var wrapid = $('input[name="place"]', $form).val();
            var prefix = $('input[name="prefix"]', $form).val();

            document.querySelector('#insertAddress').setAttribute('disabled', 'disabled');
            document.querySelector('#insertAddress svg').style.cssText = 'display: inline-block;';

            var err = 0;
            $('input.required', $form).each(function(){
                if($(this).val() == ''){
                    $(this).siblings('.acc__input-error').html('This field is required.');
                    err += 1;
                }else{
                    $(this).siblings('.acc__input-error').html('');
                }
            });

            if(err > 0){
                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';
            }else{
                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';

                var htmls = '';
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_1', $form).val()+'</span><br/>';
                if($('#student_address_address_line_2', $form).val() != ''){
                    htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_2', $form).val()+'</span><br/>';
                }
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_city', $form).val()+'</span>, ';
                if($('#student_address_state_province_region', $form).val() != ''){
                    htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_state_province_region', $form).val()+'</span>, <br/>';
                }else{
                    htmls += '<br/>';
                }
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_postal_zip_code', $form).val()+'</span>,<br/>';
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_country', $form).val()+'</span><br/>';

                htmls += '<input type="hidden" name="'+prefix+'_address" value="'+$('#student_address_address_line_1', $form).val()+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_line_1" value="'+($('#student_address_address_line_1', $form).val() != '' ? $('#student_address_address_line_1', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_line_2" value="'+($('#student_address_address_line_2', $form).val() != '' ? $('#student_address_address_line_2', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_city" value="'+($('#student_address_city', $form).val() != '' ? $('#student_address_city', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_state" value="'+($('#student_address_state_province_region', $form).val() != '' ? $('#student_address_state_province_region', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_postal_zip_code" value="'+($('#student_address_postal_zip_code', $form).val() != '' ? $('#student_address_postal_zip_code', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_country" value="'+($('#student_address_country', $form).val() != '' ? $('#student_address_country', $form).val() : '')+'"/>';

                addressModal.hide();
                $(wrapid).fadeIn().html(htmls).addClass('active');
                $('button[data-address-wrap="'+wrapid+'"] span').html('Update Address')
            }
        });
    }
    /*Address Modal*/

    /* Edit Personal Details */
    if($('#editAdmissionPersonalDetailsForm').length > 0){
        $('#disability_status').on('change', function(){
            if($('#disability_status').prop('checked')){
                $('.disabilityItems').fadeIn('fast', function(){
                    $('.disabilityItems input[type="checkbox"]').prop('checked', false);
                    $('.disabilityAllowance').fadeOut();
                    $('.disabilityAllowance input[type="checkbox"]').prop('checked', false);
                });
            }else{
                $('.disabilityItems').fadeOut('fast', function(){
                    $('.disabilityItems input[type="checkbox"]').prop('checked', false);
                    $('.disabilityAllowance').fadeOut();
                    $('.disabilityAllowance input[type="checkbox"]').prop('checked', false);
                });
            }
        });
    
        $('.disabilityItems input[type="checkbox"]').on('change', function(){
            if($('.disabilityItems input[type="checkbox"]:checked').length > 0){
                if(!$('.disabilityAllowance').is(':visible')){
                    $('.disabilityAllowance').fadeIn('fast', function(){
                        $('input[type="checkbox"]', this).prop('checked', false);
                    });
                }
            }else{
                $('.disabilityAllowance').fadeOut('fast', function(){
                    $('input[type="checkbox"]', this).prop('checked', false);
                });
            }
        });

        const editAdmissionPersonalDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionPersonalDetailsModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        $('#editAdmissionPersonalDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionPersonalDetailsForm');
        
            document.querySelector('#savePD').setAttribute('disabled', 'disabled');
            document.querySelector("#savePD svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            let applicantId = $('[name="applicant_id"]', $form).val();
            axios({
                method: "post",
                url: route('admission.update.personal.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#savePD').removeAttribute('disabled');
                    document.querySelector("#savePD svg").style.cssText = "display: none;";

                    editAdmissionPersonalDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Personal Data successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#savePD').removeAttribute('disabled');
                document.querySelector("#savePD svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionPersonalDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionPersonalDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#successModal .successCloser').on('click', function(e){
            e.preventDefault();
            if($(this).attr('data-action') == 'RELOAD'){
                successModal.hide();
                window.location.reload();
            }else{
                successModal.hide();
            }
        })
    }
    /* Edit Personal Details*/

    /* Edit Contact Details */
    if($('#editAdmissionContactDetailsForm').length > 0){
        const editAdmissionContactDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionContactDetailsModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        $('#editAdmissionContactDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionContactDetailsForm');
        
            document.querySelector('#saveCD').setAttribute('disabled', 'disabled');
            document.querySelector("#saveCD svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('admission.update.contact.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#saveCD').removeAttribute('disabled');
                    document.querySelector("#saveCD svg").style.cssText = "display: none;";

                    editAdmissionContactDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Contact Details Data successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#saveCD').removeAttribute('disabled');
                document.querySelector("#saveCD svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionContactDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionContactDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#successModal .successCloser').on('click', function(e){
            e.preventDefault();
            if($(this).attr('data-action') == 'RELOAD'){
                successModal.hide();
                window.location.reload();
            }else{
                successModal.hide();
            }
        });


        $('#editAdmissionContactDetailsForm #mobile').on('keyup paste', function(){
            var $input = $(this);
            var $btn = $(this).siblings('#sendMobileVerifiCode');
            var $orgStatusInput = $(this).siblings('.mobile_verification');
    
            var orgCode = $input.attr('data-org');
            var code = $input.val();
            var orgStatus = $orgStatusInput.attr('data-org');
            var status = $orgStatusInput.val();
            if(code != '' && code != orgCode){
                $btn.css({'display': 'inline-flex'}).removeClass('btn-primary verified').addClass('btn-danger').html('<i data-lucide="link" class="w-4 h-4 mr-1"></i> Send Code');
                $input.css({'border-color': 'red'});
                $orgStatusInput.val('0');
                status = 0;

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }else if(code == orgCode){
                if(orgStatus == 1){
                    $btn.css({'display': 'inline-flex'}).removeClass('btn-danger').addClass('btn-primary verified').html('<i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified');
                    $input.css({'border-color': 'rgba(226, 232, 240, 1)'});
                
                    $orgStatusInput.val(orgStatus);
                    status = orgStatus;
                }else{
                    $btn.css({'display': 'inline-flex'}).removeClass('btn-primary verified').addClass('btn-danger').html('<i data-lucide="link" class="w-4 h-4 mr-1"></i> Send Code');
                    $input.css({'border-color': 'red'});
                    $orgStatusInput.val('0');
                    status = 0;
                }

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }else{
                $btn.fadeOut();
                $input.css({'border-color': 'rgba(226, 232, 240, 1)'});
                $orgStatusInput.val(orgStatus);
                status = orgStatus;
            }
        });

        $('#sendMobileVerifiCode').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var $theInput = $theBtn.siblings('input[name="mobile"]');
            if(!$theBtn.hasClass('.verified')){
                var applicant_id = $theBtn.attr('data-applicant-id');
                var mobileNo = $theInput.val();

                $theBtn.attr('disabled', 'disabled');
                $theInput.attr('readonly', 'readonly');

                 
                axios({
                    method: "post",
                    url: route('admission.send.mobile.verification.code'),
                    data: {applicant_id : applicant_id, mobileNo : mobileNo},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#editAdmissionContactDetailsForm .verifyCodeGroup').fadeIn(function(){
                            $('#verification_code', this).val('').removeAttr('readonly');
                            $('#verifyMobile', this).removeAttr('disabled');
                        })
                    }
                }).catch(error => {
                    $theBtn.removeAttr('disabled');
                    $theInput.removeAttr('readonly');

                    if (error.response) {
                        console.log('error');
                    }
                });
            }
        });

        $('#verifyMobile').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var $theInput = $theBtn.siblings('input[name="verification_code"]');
            var $orgStatusInput = $('#editAdmissionContactDetailsForm .mobile_verification');

            $theBtn.attr('disabled', 'disabled');
            $theInput.attr('readonly', 'readonly');

            if($theInput.val() != '' && $theInput.val().length == 6){
                $('.error-mobile_verification_error').html('');

                var applicant_id = $theBtn.attr('data-applicant-id');
                var code = $theInput.val();
                var mobile = $('#editAdmissionContactDetailsForm input[name="mobile"]').val();

                axios({
                    method: "post",
                    url: route('admission.mobile.verify.code'),
                    data: {applicant_id : applicant_id, code : code, mobile : mobile},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {

                        if(response.data.suc == 1){
                            $('#editAdmissionContactDetailsForm .verifyCodeGroup').fadeOut(function(){
                                $('#verification_code', this).val('').removeAttr('readonly');
                                $('#verifyMobile', this).removeAttr('disabled');
                            });

                            $('#editAdmissionContactDetailsForm #sendMobileVerifiCode').css({'display': 'inline-flex'}).removeClass('btn-danger').addClass('btn-primary verified').html('<i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified');
                            $('#editAdmissionContactDetailsForm input[name="mobile"]').css({'border-color': 'rgba(226, 232, 240, 1)'});
                        
                            $orgStatusInput.val(1);
                        }else{
                            $theBtn.removeAttr('disabled');
                            $theInput.removeAttr('readonly');

                            $('.error-mobile_verification_error').html('Verification code does not found. Please insert a valid one.')
                        }

                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $theBtn.removeAttr('disabled');
                $theInput.removeAttr('readonly');

                $('.error-mobile_verification_error').html('Verification code can not be empty and code length should be 6 digit.')
            }
        });
    }
    /* Edit Contact Details*/

    /* Edit Residency Status & Criminal Convictions */
    if($('#editAdmissionResidencyCriminalForm').length > 0){
        const editAdmissionResidencyCriminalModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionResidencyCriminalModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

        const toggleConvictionDetails = () => {
            const selected = $('#editAdmissionResidencyCriminalForm input[name="have_you_been_convicted"]:checked').val();
            if (selected === "1") {
                $('#editAdmissionResidencyCriminalForm .criminalConvictionDetailsWrap').fadeIn('fast');
            } else {
                $('#editAdmissionResidencyCriminalForm .criminalConvictionDetailsWrap').fadeOut('fast', function(){
                    $('#editAdmissionResidencyCriminalForm #criminal_conviction_details').val('');
                });
            }
        };

        toggleConvictionDetails();
        $(document).on('change', '#editAdmissionResidencyCriminalForm input[name="have_you_been_convicted"]', toggleConvictionDetails);

        $('#editAdmissionResidencyCriminalForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionResidencyCriminalForm');

            document.querySelector('#saveResidencyCriminal').setAttribute('disabled', 'disabled');
            document.querySelector('#saveResidencyCriminal svg').style.cssText = "display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('admission.update.residency.criminal'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#saveResidencyCriminal').removeAttribute('disabled');
                    document.querySelector('#saveResidencyCriminal svg').style.cssText = "display: none;";

                    editAdmissionResidencyCriminalModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Residency and Criminal Conviction details successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#saveResidencyCriminal').removeAttribute('disabled');
                document.querySelector('#saveResidencyCriminal svg').style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionResidencyCriminalForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionResidencyCriminalForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#successModal .successCloser').on('click', function(e){
            e.preventDefault();
            if($(this).attr('data-action') == 'RELOAD'){
                successModal.hide();
                window.location.reload();
            }else{
                successModal.hide();
            }
        })
    }
    /* Edit Residency Status & Criminal Convictions */

    /* Edit Kin Details */
    if($('#editAdmissionKinDetailsForm').length > 0) {
        
        const editAdmissionKinDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionKinDetailsModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        $('#editAdmissionKinDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionKinDetailsForm');
        
            document.querySelector('#saveNOK').setAttribute('disabled', 'disabled');
            document.querySelector("#saveNOK svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('admission.update.kin.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#saveNOK').removeAttribute('disabled');
                    document.querySelector("#saveNOK svg").style.cssText = "display: none;";

                    editAdmissionKinDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Next of Kin Data successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#saveNOK').removeAttribute('disabled');
                document.querySelector("#saveNOK svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionKinDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionKinDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#successModal .successCloser').on('click', function(e){
            e.preventDefault();
            if($(this).attr('data-action') == 'RELOAD'){
                successModal.hide();
                window.location.reload();
            }else{
                successModal.hide();
            }
        })
    }
    /* Edit Kin Details*/

    /* Edit Course Details*/
    $('#student_loan').on('change', function(){
        var $this = $(this);
        if($this.val() == 'Others'){
            $('.studentLoanEnglandFunding').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanFundReceipt').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanApplied').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.otherFundings').fadeIn('fast', function(){
                $('input', this).val('');
            })
        }else if($this.val() == 'Student Loan'){
            $('.studentLoanEnglandFunding').fadeIn('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanFundReceipt').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanApplied').fadeIn('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.otherFundings').fadeOut('fast', function(){
                $('input', this).val('');
            })
        }else{
            $('.studentLoanEnglandFunding').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanFundReceipt').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanApplied').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.otherFundings').fadeOut('fast', function(){
                $('input', this).val('');
            })
        }
    })

    $('#student_finance_england').on('change', function(){
        if($(this).prop('checked')){
            $('.studentLoanFundReceipt').fadeIn('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
        }else{
            $('.studentLoanFundReceipt').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
        }
    });

    if($('#editAdmissionCourseDetailsForm').length > 0){
        var course_creation_id = new TomSelect('#course_creation_id', tomOptions);
        var venue_id = new TomSelect('#venue_id', tomOptions);
        var student_loan = new TomSelect('#student_loan', tomOptions);

        $('#course_creation_id').on('change', function(e){

            /*var has_ew = $('option:selected', this).attr('data-ew');
            if(has_ew == 1){
                $('.eveningWeekendWrap').fadeIn('fast', function(){
                    $('[name="full_time"]', this).prop('checked', false);
                })
            }else{
                $('.eveningWeekendWrap').fadeOut('fast', function(){
                    $('[name="full_time"]', this).prop('checked', false);
                })
            }*/
            $('.courseLoading').show();
            let SelectedValue = $(this).val();
            //woorking all here get the venues
            if(SelectedValue=="") {
                $('#selectVenue').fadeOut('fast', function(){
                    $('.courseLoading').hide();
                })
            }else{
                axios({
                    method: "get",
                    url: route("course.creation.edit", $('#course_creation_id').val()),
                    headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
                }).then((response) => {
                    if (response.status == 200) {
                        let venues = response.data.venues;
                            venue_id.clear();
                            venue_id.clearOptions();
                        if(venues.length>1) {
                            venue_id.addOption({value:'',text:"Please Select"});
                            venue_id.addItem('');
                        }
                        venues.forEach((e, i) => {
                            if(e.pivot.deleted_at==null) {
                                if(venues.length==1) {
                                    venue_id.removeOption('');
                                    venue_id.addOption({value:e.id,text:e.name});
                                    venue_id.addItem(e.id);
                                } else {
                                    venue_id.removeItem(e.id);
                                    venue_id.addOption({value:e.id,text:e.name});
                                }
                            }
                        });
                        $('.courseLoading').hide();
                        
                    }
                }).catch((error) => {
                    console.log(error);
                });
            }
        
        });

        $('#venue_id').on('change', function(e){
            let $theVenue = $(this);
            let $theCourseCreation = $('#course_creation_id');

            let venue_id = $theVenue.val();
            let course_creation_id = $theCourseCreation.val();

            if(venue_id > 0 && course_creation_id > 0){
                axios({
                    method: "post",
                    url: route("admission.get.evening.weekend.status"),
                    data: {course_creation_id : course_creation_id, venue_id : venue_id},
                    headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
                }).then((response) => {
                    if (response.status == 200) {
                        if(response.data.weekends == 1){
                            $('.eveningWeekendWrap').fadeIn('fast', function(){
                                $('[name="full_time"]', this).prop('checked', false).removeClass('onlyWeekends');
                            })
                        }else if(response.data.weekends == 2){
                            $('.eveningWeekendWrap').fadeIn('fast', function(){
                                $('[name="full_time"]', this).prop('checked', true).addClass('onlyWeekends');
                            })
                        }else{
                            $('.eveningWeekendWrap').fadeOut('fast', function(){
                                $('[name="full_time"]', this).prop('checked', false).removeClass('onlyWeekends');
                            })
                        }
                    }
                }).catch((error) => {
                    $('.eveningWeekendWrap').fadeOut('fast', function(){
                        $('[name="full_time"]', this).prop('checked', false).removeClass('onlyWeekends');
                    })
                    console.log(error);
                });
            }else{
                $('.eveningWeekendWrap').fadeOut('fast', function(){
                    $('[name="full_time"]', this).prop('checked', false).removeClass('onlyWeekends');
                })
            }
        })

        $('#full_time').on('click', function(e){
            if($(this).hasClass('onlyWeekends')){
                e.preventDefault();
                e.stopPropagation();
            }
        })
        

        const editAdmissionCourseDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionCourseDetailsModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        $('#editAdmissionCourseDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionCourseDetailsForm');
        
            document.querySelector('#savePCP').setAttribute('disabled', 'disabled');
            document.querySelector("#savePCP svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('admission.update.course.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#savePCP').removeAttribute('disabled');
                    document.querySelector("#savePCP svg").style.cssText = "display: none;";

                    editAdmissionCourseDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Course & Programme Details successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#savePCP').removeAttribute('disabled');
                document.querySelector("#savePCP svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionCourseDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionCourseDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#successModal .successCloser').on('click', function(e){
            e.preventDefault();
            if($(this).attr('data-action') == 'RELOAD'){
                successModal.hide();
                window.location.reload();
            }else{
                successModal.hide();
            }
        })
    }
    /* Edit Course Details*/

    /* Edit Education Qualification Details*/
    if($('#applicantQualification').length > 0){
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmEducationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmEducationModal"));
        const confirmEducationModalEl = document.getElementById('confirmEducationModal')
        confirmEducationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#confirmEducationModal .confModTitle').html('');
            $('#confirmEducationModal .confModDesc').html('');
            $('#confirmEducationModal .agreeWith').attr('data-action', 'none');
        });
        $('#is_edication_qualification').on('change', function(e){
            var applicantId = $(this).attr('data-applicant');
            
            confirmEducationModal.show();
            if($(this).prop('checked')){
                $('#confirmEducationModal .confModTitle').html('Are you sure?');
                $('#confirmEducationModal .confModDesc').html('You want to enabled students education qualification status?  All existing qualification will found under Archive status.');
                $('#confirmEducationModal .agreeWith').attr('data-action', 1);
            }else{
                $('#confirmEducationModal .confModTitle').html('Are you sure?');
                $('#confirmEducationModal .confModDesc').html('You want to disabled students education qualification status? All existing qualification will be removed.');
                $('#confirmEducationModal .agreeWith').attr('data-action', 0);
            }
        });

        $('#confirmEducationModal .disAgreeWith').on('click', function(e){
            e.preventDefault();
            confirmEducationModal.hide();
            window.location.reload();
        });

        $('#confirmEducationModal .agreeWith').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);
            var applicant = $btn.attr('data-applicant');
            var status = $btn.attr('data-action');

            $btn.attr('disabled', 'disabled');
            $btn.siblings('.disAgreeWith').attr('disabled', 'disabled');

            axios({
                method: "post",
                url: route('admission.update.qualification.status'),
                data: {applicant : applicant, status : status},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $btn.removeAttr('disabled');
                    $btn.siblings('.disAgreeWith').removeAttr('disabled');

                    confirmEducationModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student education qualification status successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                $btn.removeAttr('disabled');
                $btn.siblings('.disAgreeWith').removeAttr('disabled');

                if (error.response) {
                    console.log('error');
                }
            });
        });
    }
    
    // 10011500 
    if($('#addNewAccountConfirm').length > 0){
        
        const addNewAccountConfirm = tailwind.Modal.getOrCreateInstance(document.querySelector("#addNewAccountConfirm"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
        
         const addNewAccountConfirmEl = document.getElementById('addNewAccountConfirm');
        $(".create_new_account").on("click", function() {
            var $$this = $(this);
            var studentId = $$this.data("id");
            $('#student_id').val(studentId);
        });
        addNewAccountConfirmEl.addEventListener('hide.tw.modal', function(event) {
            $('#student_id').val('');
        });


        $('#addNewAccountConfirm .agreeWith').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);
            var student_id = $('#student_id').val();
           
            $('svg',$btn).show();
            $btn.attr('disabled', 'disabled');
            $btn.siblings('.disAgreeWith').attr('disabled', 'disabled');

            axios({
                method: "post",
                url: route('admission.create.account.from.student'),
                data: {student_id : student_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $btn.removeAttr('disabled');
                    $btn.siblings('.disAgreeWith').removeAttr('disabled');
                    $('svg',$btn).hide();
                    addNewAccountConfirm.hide();
                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Account Created!" );
                        $("#successModal .successModalDesc").html('Please check the applicant personal email for password.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                $btn.removeAttr('disabled');
                $btn.siblings('.disAgreeWith').removeAttr('disabled');

                if (error.response) {
                    console.log('error');
                }
            });
        });

        
    }




    
})();

