import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import html2canvas from "html2canvas";
import { saveAs } from 'file-saver';
import Dropzone from "dropzone";
import TomSelect from "tom-select";
import { data } from "jquery";

("use strict");
var taskAssignedStudentTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let reg_or_ref = $("#reg_or_ref").val() != "" ? $("#reg_or_ref").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let courses = $("#courses").val() != "" ? $("#courses").val() : "";
        let task_id = $("#taskAssignedStudentTable").attr('data-taskid');
        let phase = $("#taskAssignedStudentTable").attr('data-phase');
        
        let org_email = ($("#taskAssignedStudentTable").attr('data-email') != 'undefined' ? $("#taskAssignedStudentTable").attr('data-email') : 'No');
        let id_card = ($("#taskAssignedStudentTable").attr('data-idcard') != 'undefined' ? $("#taskAssignedStudentTable").attr('data-idcard') : 'No');
        let interview = ($("#taskAssignedStudentTable").attr('data-interview') != 'undefined' ? $("#taskAssignedStudentTable").attr('data-interview') : 'No');
        let excuse = ($("#taskAssignedStudentTable").attr('data-excuse') != 'undefined' ? $("#taskAssignedStudentTable").attr('data-excuse') : 'No');
        let pearsonreg = ($("#taskAssignedStudentTable").attr('data-pearsonreg') != 'undefined' ? $("#taskAssignedStudentTable").attr('data-pearsonreg') : 'No');
        
        
        let tableContent = new Tabulator("#taskAssignedStudentTable", {
            ajaxURL: route("task.manager.list"),
            ajaxParams: { status : status, task_id : task_id, phase : phase, courses : courses, reg_or_ref : reg_or_ref},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 100,
            paginationSizeSelector: [true, 50, 100, 200, 300, 500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            
            selectable: true,
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "left", 
                    headerHozAlign: "left",
                    width: "60",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, cell){
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: (phase == 'Applicant' ? 'Ref. No' : 'Reg. No'),
                    field: (phase == 'Applicant' ? "application_no" : 'registration_no'),
                    headerHozAlign: "left",
                    width: "150",
                    formatter(cell, formatterParams) {  
                        var html = '<a href="'+cell.getData().url+'" class="whitespace-normal font-medium text-primary mr-1">';
                                html += (phase == 'Applicant' ? cell.getData().application_no : cell.getData().registration_no),
                            html += '</a>';
                        if(cell.getData().outcome != ''){
                            html += ' <span class="font-medium underline text-primary">(Outcome: '+cell.getData().outcome+')</span>';
                        }
                        return html;
                    }
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                    width: "180",
                    formatter(cell, formatterParams) {
                        var first_name = cell.getData().first_name;
                        if(phase != 'Applicant' && first_name.length > 20){
                            return '<span class="text-danger">'+first_name+'</span>';
                        }else{
                            return first_name;
                        }
                    }
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                    width: "180",
                    formatter(cell, formatterParams) {
                        var last_name = cell.getData().last_name;
                        if(phase != 'Applicant' && last_name.length > 20){
                            return '<span class="text-danger">'+last_name+'</span>';
                        }else{
                            return last_name;
                        }
                    }
                },
                {
                    title: "Course",
                    field: "course",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="whitespace-normal">';
                                html += '<span>'+cell.getData().course+'</span><br/>';
                                html += '<span>'+cell.getData().semester+'</span><br/>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                    width: "120",
                },
                {
                    title: "Interview Details",
                    field: "task_id",
                    headerSort: false,
                    headerHozAlign: "left",
                    visible: (interview == 'Yes' && (status == 'In Progress' || status == 'Completed') ? true : false),
                    formatter(cell, formatterParams) {  
                        var html = '<div class="flex justify-start items-center">';
                                html += '<div>';
                                    if(cell.getData().interview.date){
                                        html += '<span class="font-medium"> Date: '+cell.getData().interview.date+'</span><br/>';
                                    }
                                    if(cell.getData().interview.time){
                                        html += '<span class="font-medium"> Time: '+cell.getData().interview.time+'</span><br/>';
                                    }
                                    if(cell.getData().interview.interviewer){
                                        html += '<span class="font-medium"> Interviewer: '+cell.getData().interview.interviewer+'</span><br/>';
                                    }
                                    if(cell.getData().interview.result){
                                        html += '<span class="font-medium"> Result: '+cell.getData().interview.result+'</span><br/>';
                                    }
                                    if(cell.getData().interview.interview_id && cell.getData().interview.interview_id > 0){
                                        html += '<a data-id="'+cell.getData().interview.interview_id+'" href="javascript:void(0);" class="applicantprofile-lock__button inline-flex justify-start font-medium text-primary pt-2 underline"><i data-lucide="eye-off" class="w-4 h-4 mr-2"></i>\
                                            View Profile\
                                            <svg width="25" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" stroke="rgb(100,116,139)" class="loading invisible w-4 h-4 ml-2">\
                                                <g fill="none" fill-rule="evenodd" stroke-width="4">\
                                                    <circle cx="22" cy="22" r="1">\
                                                        <animate attributeName="r" begin="0s" dur="1.8s" values="1; 20" calcMode="spline" keyTimes="0; 1" keySplines="0.165, 0.84, 0.44, 1" repeatCount="indefinite" />\
                                                        <animate attributeName="stroke-opacity" begin="0s" dur="1.8s" values="1; 0" calcMode="spline" keyTimes="0; 1" keySplines="0.3, 0.61, 0.355, 1" repeatCount="indefinite" />\
                                                    </circle>\
                                                    <circle cx="22" cy="22" r="1">\
                                                        <animate attributeName="r" begin="-0.9s" dur="1.8s" values="1; 20" calcMode="spline" keyTimes="0; 1" keySplines="0.165, 0.84, 0.44, 1" repeatCount="indefinite" />\
                                                        <animate attributeName="stroke-opacity" begin="-0.9s" dur="1.8s" values="1; 0" calcMode="spline" keyTimes="0; 1" keySplines="0.3, 0.61, 0.355, 1" repeatCount="indefinite" />\
                                                    </circle>\
                                                </g>\
                                            </svg>\
                                        </a>';
                                    }
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Task Status",
                    field: "task_status",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="flex justify-start items-center">';
                                html += '<div>';
                                    html += '<span class="font-medium">'+cell.getData().task_status+'</span><br/>';
                                    if(cell.getData().task_created_by != ''){
                                        html += '<span class="font-medium"> By: '+cell.getData().task_created_by+'</span><br/>';
                                    }
                                    html += '<span>'+cell.getData().task_created+'</span><br/>';
                                    if(cell.getData().canceled_reason != ''){
                                        html += '<span class="font-medium"> Reason: </span><span>'+cell.getData().canceled_reason+'</span>';
                                    }
                                html += '</div>';
                                if(id_card == 'Yes'){
                                    html += '<button data-taskid="'+cell.getData().task_id+'" data-studentid="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#downloadIDCard" type="button" class="downloadIDCardBtn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-4"><i data-lucide="download-cloud" class="w-4 h-4"></i></button>';
                                }else if(interview == 'Yes' && cell.getData().task_status == 'Pending'){
                                    html += '<button data-task="'+cell.getData().task_id+'" data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#callLockModal" type="button" class="unlockApplicantInterview btn-rounded btn btn-warning text-white p-0 w-9 h-9 ml-4"><i data-lucide="lock" class="w-4 h-4"></i></button>';
                                }
                            html += '</div>';
                            html += '<input type="hidden" name="phase" class="phase" value="'+cell.getData().phase+'"/>';
                            html += '<input type="hidden" name="ids" class="ids" value="'+cell.getData().ids+'"/>';
                        return html;
                    }
                },
                {
                    title: "Task Type",
                    field: "student_document_request_form_id",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        let html = '';
                            if(cell.getData().student_document_request_form_id != null ){
                                //insert data into local storage
                                let student_documentRequest = cell.getData().student_document_request_form_id
                                
                                html += '<div>';
                                        html += '<span class="font-medium">'+student_documentRequest.name+'</span>';
                                html += '</div>';
                            }
                        return html;
                    }
                },
                {
                    title: "&nbsp;",
                    field: "has_task_status",
                    headerSort: false,
                    headerHozAlign: "right",
                    hozAlign: "right", 
                    visible: (interview != 'Yes' ? true : false),
                    formatter(cell, formatterParams) {  
                        var html = '';
                        if(cell.getData().has_task_status == 'Yes' || cell.getData().has_task_upload == 'Yes' || cell.getData().is_completable == 1 || cell.getData().downloads != ''){
                            html += '<div class="flex justify-end items-center">';
                                if(cell.getData().downloads != ''){
                                    html += cell.getData().downloads;
                                }
                                if(cell.getData().task_status == 'Pending'){
                                    html += '<div class="flex justify-end ml-3">';
                                        html += '<div class="dropdown">';
                                            html += '<a class="dropdown-toggle w-5 h-5" href="javascript:void(0);" aria-expanded="false" data-tw-toggle="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="more-vertical" class="lucide lucide-more-vertical w-5 h-5 text-slate-500"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg></a>';
                                            html += '<div class="dropdown-menu w-64">';
                                                html += '<ul class="dropdown-content">';
                                                    if(cell.getData().has_task_status == 'Yes'){
                                                        html += '<li>';
                                                            html += '<a data-phase="'+cell.getData().phase+'" data-taskid="'+cell.getData().task_id+'" data-studentid="'+cell.getData().id +'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#updateTaskOutcomeModal" class="updateTaskOutcome dropdown-item">';
                                                                html += '<i data-lucide="award" class="w-4 h-4 mr-2"></i> Update Outcome';
                                                            html += '</a>';
                                                        html += '</li>';
                                                    }
                                                    if(cell.getData().has_task_upload == 'Yes'){
                                                        html += '<li>';
                                                            html += '<a data-phase="'+cell.getData().phase+'" data-taskid="'+cell.getData().task_id+'" data-studentid="'+cell.getData().id +'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#uploadTaskDocumentModal" class="uploadTaskDoc dropdown-item">';
                                                                html += '<i data-lucide="cloud-lightning" class="w-4 h-4 mr-2"></i> Upload Documents';
                                                            html += '</a>';
                                                        html += '</li>';
                                                    }
                                                    console.log(cell.getData().student_document_request_form_id);
                                                    if(cell.getData().student_document_request_form_id != null ){
                                                        //insert data into local storage
                                                        localStorage.setItem('student_document_request_form'+cell.getData().student_task_id, JSON.stringify(cell.getData().student_document_request_form_id));
                                                        
                                                        
                                                        html += '<li>';
                                                            html += '<a data-studenttaskid="'+cell.getData().student_task_id+'" data-phase="'+cell.getData().phase+'" data-taskid="'+cell.getData().task_id+'" data-studentid="'+cell.getData().id +'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#addLetterModal" class="sendLetterToStudent dropdown-item">';
                                                                html += '<i data-lucide="mail" class="w-4 h-4 mr-2"></i> Generate Requested Document';
                                                            html += '</a>';
                                                        html += '</li>';
                                                        html += '<li>';
                                                            html += '<a data-studenttaskid="'+cell.getData().student_task_id+'" data-phase="'+cell.getData().phase+'" data-taskid="'+cell.getData().task_id+'" data-studentid="'+cell.getData().id +'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#updateTaskDocumentRequestOutcomeModal" class="updateTaskDocRequestForm dropdown-item">';
                                                                html += '<i data-lucide="award" class="w-4 h-4 mr-2"></i> Update task outcome';
                                                            html += '</a>';
                                                        html += '</li>';
                                                    }
                                                    if(cell.getData().is_completable == 1 && cell.getData().task_excuse == 'No'){
                                                        html += '<li>';
                                                            html += '<a data-phase="'+cell.getData().phase+'" data-taskid="'+cell.getData().task_id+'" data-studentid="'+cell.getData().id +'" href="javascript:void(0);" class="markAsSingleComplete dropdown-item">';
                                                                html += '<i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Mark as Complete';
                                                                html += '<svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"\
                                                                    stroke="rgb(100, 116, 139)" class="w-4 h-4 ml-2 theLoaderSvg">\
                                                                    <g fill="none" fill-rule="evenodd">\
                                                                        <g transform="translate(1 1)" stroke-width="4">\
                                                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>\
                                                                            <path d="M36 18c0-9.94-8.06-18-18-18">\
                                                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18"\
                                                                                    to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>\
                                                                            </path>\
                                                                        </g>\
                                                                    </g>\
                                                                </svg>';
                                                            html += '</a>';
                                                        html += '</li>';
                                                    }
                                                    if(cell.getData().is_completable == 1 && cell.getData().task_excuse == 'Yes'){
                                                        html += '<li>';
                                                            html += '<a data-recordid="'+cell.getData().student_task_id+'" data-phase="'+cell.getData().phase+'" data-taskid="'+cell.getData().task_id+'" data-studentid="'+cell.getData().id +'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#viewAttendanceExcuseModal" class="viewExcuse dropdown-item">';
                                                                html += '<i data-lucide="eye-off" class="w-4 h-4 mr-2"></i> View Excuse';
                                                            html += '</a>';
                                                        html += '</li>';
                                                    }
                                                html += '</ul>';
                                            html += '</div>';
                                        html += '</div>';
                                    html += '</div>';
                                }
                            html += '</div>';
                        } 

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
            },
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    if(org_email == 'Yes'){
                        $('#exportTaskStudentsBtn').fadeIn();
                        $('#completeEmailTaskStudentsBtn').fadeIn();
                    }else{
                        if(pearsonreg == 'Yes'){
                            $('#exportPearsonRegStudentList').fadeIn();
                        }
                        if(excuse == 'No'){
                            $('#exportTaskStudentListBtn').fadeIn();
                            $('#commonActionBtns').fadeIn();
                        }
                    }
                }else{
                    $('#exportTaskStudentsBtn').fadeOut();
                    $('#completeEmailTaskStudentsBtn').fadeOut();
                    $('#exportTaskStudentListBtn').fadeOut();
                    $('#commonActionBtns').fadeOut();
                    $('#exportPearsonRegStudentList').fadeOut();
                }
            },
            selectableCheck:function(row){
                return row.getData().task_id > 0;
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
            $("#status").val('Pending');
            $("#courses").val('');
            $("#reg_or_ref").val('');

            filterHTMLFormADM();
        });
    }

    let tomOptionsTasManager = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        maxOptions: null,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let change_status_id = new TomSelect('#change_status_id', tomOptionsTasManager);
    let term_declaration_id = new TomSelect('#term_declaration_id', tomOptionsTasManager);

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const downloadIDCard = tailwind.Modal.getOrCreateInstance(document.querySelector("#downloadIDCard"));
    const canceledReasonModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#canceledReasonModal"));
    const callLockModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#callLockModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
    const viewAttendanceExcuseModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewAttendanceExcuseModal"));

    const uploadTaskDocumentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadTaskDocumentModal"));
    const updateTaskOutcomeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#updateTaskOutcomeModal"));
    const updateTaskDocumentRequestOutcomeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#updateTaskDocumentRequestOutcomeModal"));

    const uploadPearsonRegConfModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadPearsonRegConfModal"));

    const downloadIDCardEl = document.getElementById('downloadIDCard')
    downloadIDCardEl.addEventListener('hide.tw.modal', function(event) {
        $('#downloadIDCard .idContent').html('').fadeOut('fast');
        $('#downloadIDCard .idLoader').fadeIn('fast');
    });

    const canceledReasonModalEl = document.getElementById('canceledReasonModal')
    canceledReasonModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#canceledReasonModal .acc__input-error').html('');
        $('#canceledReasonModal textarea, #canceledReasonModal input').val('');
    });

    const callLockModalEl = document.getElementById('callLockModal')
    callLockModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#callLockModal .acc__input-error').html('');
        $('#callLockModal textarea, #canceledReasonModal input').val('');
    });
    
    const updateTaskOutcomeModalEl = document.getElementById('updateTaskOutcomeModal')
    updateTaskOutcomeModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#updateTaskOutcomeModal .modal-body").html('');
        $('#updateTaskOutcomeModal input[name="student_id"]').val('0');
        $('#updateTaskOutcomeModal input[name="task_id"]').val('0');
        $('#updateTaskOutcomeModal input[name="phase"]').val('');
    });

    const viewAttendanceExcuseModalEl = document.getElementById('viewAttendanceExcuseModal')
    viewAttendanceExcuseModalEl.addEventListener('hide.tw.modal', function(event) {
        var loaderHtml = '<div class="loaderWrap flex justify-center items-center py-5">\
                            <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="rgb(30, 41, 59)" class="w-8 h-8">\
                                <g fill="none" fill-rule="evenodd">\
                                    <g transform="translate(1 1)" stroke-width="4">\
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>\
                                        <path d="M36 18c0-9.94-8.06-18-18-18">\
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>\
                                        </path>\
                                    </g>\
                                </g>\
                            </svg>\
                        </div>';
        $("#viewAttendanceExcuseModal .modal-body").html(loaderHtml);
        $('#viewAttendanceExcuseModal input[name="student_id"]').val('0');
        $('#viewAttendanceExcuseModal input[name="student_task_id"]').val('0');
        $('#viewAttendanceExcuseModal input[name="attendance_excuse_id"]').val('0');
    });
    
    const uploadPearsonRegConfModalEl = document.getElementById('uploadPearsonRegConfModal')
    uploadPearsonRegConfModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#uploadPearsonRegConfModal [name="document"]').val('');
        $('#uploadPearsonRegConfModal .documentPearRegName').html('');
        $('#uploadPearsonRegConfModal textarea').html('');
        change_status_id.clear(true);
        term_declaration_id.clear(true);
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

    $('#exportTaskStudentsBtn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var ids = [];

        $btn.attr('disabled', 'disabled');
        $btn.siblings('#completeEmailTaskStudentsBtn').attr('disabled', 'disabled');
        $('#taskAssignedStudentTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.ids').val());
        });

        if(ids.length > 0){
            $.ajax({
                type: 'GET',
                url: route('task.manager.students.email.excel'),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                data: {
                    ids: ids
                },
                xhrFields:{
                    responseType: 'blob'
                },
                beforeSend: function() {},
                success: function(data) {
                    $btn.removeAttr('disabled').fadeOut();
                    $btn.siblings('#completeEmailTaskStudentsBtn').removeAttr('disabled').fadeOut();
                    taskAssignedStudentTable.init();

                    var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(data);
                        link.download = 'New_Student_Email_Id_Create_Task.xlsx';
                        link.click();

                        link.remove();

                    /*var url = window.URL || window.webkitURL;
                    var objectUrl = url.createObjectURL(data);
                    var newWindow = window.open(objectUrl);
                    newWindow.document.title = 'New_Student_Email_Id_Create_Task';*/
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }else{
            $btn.removeAttr('disabled').fadeOut();
            $btn.siblings('#completeEmailTaskStudentsBtn').removeAttr('disabled').fadeOut();
            taskAssignedStudentTable.init();
        }
    });

    $('#exportPearsonRegStudentList').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var ids = [];

        $btn.attr('disabled', 'disabled');
        $('#taskAssignedStudentTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.ids').val());
        });

        if(ids.length > 0){
            $.ajax({
                type: 'GET',
                url: route('task.manager.pearson.registration.excel'),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                data: {
                    ids: ids
                },
                xhrFields:{
                    responseType: 'blob'
                },
                beforeSend: function() {},
                success: function(data) {
                    $btn.removeAttr('disabled').fadeOut();
                    $btn.siblings('#completeEmailTaskStudentsBtn').removeAttr('disabled').fadeOut();
                    taskAssignedStudentTable.init();

                    var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(data);
                        link.download = 'BTECRTypeSA1.xlsx';
                        link.click();

                        link.remove();

                    /*var url = window.URL || window.webkitURL;
                    var objectUrl = url.createObjectURL(data);
                    var newWindow = window.open(objectUrl);
                    newWindow.document.title = 'New_Student_Email_Id_Create_Task';*/
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }else{
            $btn.removeAttr('disabled').fadeOut();
            taskAssignedStudentTable.init();
        }
    });

    $('#completeEmailTaskStudentsBtn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var ids = [];

        $btn.attr('disabled', 'disabled');
        $btn.siblings('#exportTaskStudentsBtn').attr('disabled', 'disabled')
        $('#taskAssignedStudentTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.ids').val());
        });

        if(ids.length > 0){
            axios({
                method: "post",
                url: route('task.manager.comlete.students.email.id.task'),
                data: {ids : ids},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $btn.removeAttr('disabled').fadeOut();
                    $btn.siblings('#exportTaskStudentsBtn').removeAttr('disabled').fadeOut()
                    taskAssignedStudentTable.init();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html('Congratulations!');
                        $("#successModal .successModalDesc").html('Student New Email ID task successfully completed and welcome message has been sent.');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error => {
                if(error.response){
                    console.log('error');
                }
            });
        }else{
            $btn.removeAttr('disabled').fadeOut();
            $btn.siblings('#exportTaskStudentsBtn').removeAttr('disabled').fadeOut()
            taskAssignedStudentTable.init();
        }
    });

    $('#taskAssignedStudentTable').on('click', '.downloadIDCardBtn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var task_id = $btn.attr('data-taskid');
        var student_id = $btn.attr('data-studentid');

        axios({
            method: "post",
            url: route('task.manager.download.id.card'),
            data: {student_id : student_id, task_id : task_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#downloadIDCard .idLoader').fadeOut('fast');
                $('#downloadIDCard .idContent').fadeIn('fast').html(response.data.res);
            }
        }).catch(error => {
            if(error.response){
                console.log('error');
            }
        });
    })

    $('#downloadIDCard').on('click', '.thePrintBtn', function(){
        var $currentBtn = $(this);
        var currentIdAttr = $currentBtn.attr('data-id');
        var currentId = '#theIDCard_'+currentIdAttr;
        var $currentIDCard = $('#theIDCard_'+currentIdAttr);

        html2canvas(document.querySelector(currentId), { useCORS: true, allowTaint : true }).then(canvas => {
            canvas.toBlob(function(blob) {
                window.saveAs(blob, currentIdAttr+'.jpg');

                setTimeout(function(){
                    downloadIDCard.hide();
                }, 2000);
            });
        });
    });

    
    $('.updateSelectedStudentTaskStatusBtn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);

        if(!$btn.hasClass('disabled')){

            $btn.addClass('disabled');
            $btn.find('svg.theLoaderSvg').fadeIn();
            $btn.closest('.updateSelectedStudentTaskStatusBtn').addClass('disabled');

            var task_id = $btn.attr('data-taskid');
            var status = $btn.attr('data-status');
            var phase = $btn.attr('data-phase');
            var student_ids = [];
            $('#taskAssignedStudentTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                student_ids.push($row.find('.ids').val());
            });

            if(student_ids.length > 0){
                axios({
                    method: "post",
                    url: route('task.manager.update.task.status'),
                    data: {student_ids : student_ids, task_id : task_id, status : status, phase : phase},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $btn.removeClass('disabled');
                        $btn.find('svg.theLoaderSvg').fadeOut();
                        $btn.closest('.updateSelectedStudentTaskStatusBtn').removeClass('disabled');

                        taskAssignedStudentTable.init();

                        successModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html('Congratulations!');
                            $("#successModal .successModalDesc").html('Selected students task status successfully updated.');
                        });

                        setTimeout(function(){
                            successModal.hide();
                        }, 2000);
                    }
                }).catch(error => {
                    if(error.response){
                        console.log('error');
                    }
                });
            }else{
                $btn.removeClass('disabled');
                $btn.find('svg.theLoaderSvg').fadeOut();
                $btn.closest('.updateSelectedStudentTaskStatusBtn').removeClass('disabled');

                taskAssignedStudentTable.init();
            }
        }
    });

    
    $(document).on('click', '.markAsSingleComplete', function(e){
        e.preventDefault();
        var $btn = $(this);

        if(!$btn.hasClass('disabled')){

            $btn.addClass('disabled');
            $btn.find('svg.theLoaderSvg').fadeIn();

            var task_id = $btn.attr('data-taskid');
            var status = 'Completed';
            var phase = $btn.attr('data-phase');
            var studentid = $btn.attr('data-studentid');
            var student_ids = [studentid];

            if(student_ids.length > 0){
                axios({
                    method: "post",
                    url: route('task.manager.update.task.status'),
                    data: {student_ids : student_ids, task_id : task_id, status : status, phase : phase},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $btn.removeClass('disabled');
                        $btn.find('svg.theLoaderSvg').fadeOut();

                        taskAssignedStudentTable.init();

                        successModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html('Congratulations!');
                            $("#successModal .successModalDesc").html('Students task status successfully updated.');
                        });

                        setTimeout(function(){
                            successModal.hide();
                        }, 2000);
                    }
                }).catch(error => {
                    if(error.response){
                        console.log('error');
                    }
                });
            }else{
                $btn.removeClass('disabled');
                $btn.find('svg.theLoaderSvg').fadeOut();

                taskAssignedStudentTable.init();
            }
        }
    });

    $('#exportTaskStudentListBtn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var task_id = $btn.attr('data-taskid');
        var phase = $btn.attr('data-phase');
        var task_name = $('.theTaskName').text();
        var ids = [];

        $btn.attr('disabled', 'disabled');
        $btn.find('svg.theLoaderSvg').fadeIn();

        $('#taskAssignedStudentTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.ids').val());
        });

        if(ids.length > 0){
            $.ajax({
                type: 'GET',
                url: route('task.manager.students.list.excel'),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                data: {
                    ids: ids,
                    task_id : task_id,
                    phase : phase
                },
                xhrFields:{
                    responseType: 'blob'
                },
                beforeSend: function() {},
                success: function(data) {
                    $btn.removeAttr('disabled');
                    $btn.find('svg.theLoaderSvg').fadeOut();
                    taskAssignedStudentTable.init();

                    var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(data);
                        link.download = task_name.replace(' ', '_')+'_Assigned_Student_List.xlsx';
                        link.click();

                        link.remove();

                    /*var url = window.URL || window.webkitURL;
                    var objectUrl = url.createObjectURL(data);
                    var newWindow = window.open(objectUrl);
                    newWindow.document.title = 'New_Student_Email_Id_Create_Task';*/
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }else{
            $btn.removeAttr('disabled');
            $btn.find('svg.theLoaderSvg').fadeOut();
            taskAssignedStudentTable.init();
        }
    });


    $('.markAsCanceled').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var task_id = $btn.attr('data-taskid');
        var phase = $btn.attr('data-phase');
        var ids = [];
        $('#taskAssignedStudentTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.ids').val());
        });

        if(ids.length > 0){
            canceledReasonModal.show();
            document.getElementById("canceledReasonModal").addEventListener("shown.tw.modal", function (event) {
                $('#canceledReasonModal input[name="phase"]').val(phase);
                $('#canceledReasonModal input[name="task_id"]').val(task_id);
                $('#canceledReasonModal input[name="ids"]').val(ids.join());
            });
        }
    });

    $('#canceledReasonForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('canceledReasonForm');
    
        document.querySelector('#updateReason').setAttribute('disabled', 'disabled');
        document.querySelector("#updateReason svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('task.manager.canceled.task'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateReason').removeAttribute('disabled');
            document.querySelector("#updateReason svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                canceledReasonModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Selected student task successfully canceled.');
                });     
            }
            taskAssignedStudentTable.init();
        }).catch(error => {
            document.querySelector('#updateReason').removeAttribute('disabled');
            document.querySelector("#updateReason svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#canceledReasonForm .${key}`).addClass('border-danger');
                        $(`#canceledReasonForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#updateTaskDocumentRequestOutcomeForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('updateTaskDocumentRequestOutcomeForm');
    
        document.querySelector('#updateRequestBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#updateRequestBtn .loading").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('task.manager.document_request.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateRequestBtn').removeAttribute('disabled');
            document.querySelector("#updateRequestBtn .loading").style.cssText = "display: none;";
            
            if (response.status == 200) {
                //second axios post here
                let formDataJson = response.data.data;
                // Create a new FormData object
                let formData = new FormData();
                // Ensure letter_body is a string
                // if (formDataJson.hasOwnProperty('letter_body')) {
                //     formDataJson.letter_body = String(formDataJson.letter_body);
                // }
                // Loop through the JSON object and append each key-value pair to the FormData object
                for (var key in formDataJson) {
                    if (formDataJson.hasOwnProperty(key)) {
                        formData.append(key, formDataJson[key]);
                    }
                }
                axios({
                    method: "post",
                    url: route('student.send.letter'),
                    data: formData,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    document.querySelector('#updateRequestBtn').removeAttribute('disabled');
                    document.querySelector("#updateRequestBtn .loading").style.cssText = "display: none;";
                    
                    if (response.status == 200) {
                        
                        updateTaskDocumentRequestOutcomeModal.hide();

                        successModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Selected student task successfully completed.');
                        });     
                        
                        setTimeout(function(){
                            successModal.hide();
                        }, 2000);
                    }
                }).catch(error => {
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(error.response.data.errors)) {
                                $(`#addLetterForm .${key}`).addClass('border-danger');
                                $(`#addLetterForm  .error-${key}`).html(val);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });

            }
            taskAssignedStudentTable.init();
        }).catch(error => {
            document.querySelector('#updateRequestBtn').removeAttribute('disabled');
            document.querySelector("#updateRequestBtn .loading").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#updateTaskDocumentRequestOutcomeForm .${key}`).addClass('border-danger');
                        $(`#updateTaskDocumentRequestOutcomeForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#taskAssignedStudentTable').on('click', '.unlockApplicantInterview', function(e){
        e.preventDefault();
        var $btn = $(this);
        var task_id = $btn.attr('data-task');
        var id = $btn.attr('data-id');

        $('#callLockModal input[name="applicantId"]').val(id);
        $('#callLockModal input[name="taskListId"]').val(task_id);
    });

    
    $('#callLockModalForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('callLockModalForm');
    
        document.querySelector('#unlock').setAttribute('disabled', 'disabled');
        document.querySelector("#unlock svg.loading").style.cssText ="display: inline-block;";
    
        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('applicant.interview.unlock.only'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#unlock').removeAttribute('disabled');
            document.querySelector("#unlock svg.loading").style.cssText = "display: none;";
            
            if (response.status == 200) {
                callLockModal.hide();
    
                successModal.show();
                let Data = response.data.ref;
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Profile Unlocked.');
                });   
                
                location.href= Data;  
            }
        }).catch(error => {
            document.querySelector('#unlock').removeAttribute('disabled');
            document.querySelector("#unlock svg.loading").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#callLockModalForm .${key}`).addClass('border-danger');
                        $(`#callLockModalForm  .error-${key}`).html(val);
                    }
                } else if (error.response.status == 404) {
                    successModal.hide();
                    callLockModal.hide();
                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html('Wrong Date of Birth!');
                        $("#errorModal .errorModalDesc").html('Please enter the correct DOB. If you further issue  please contact the Admission Office.');
                    });     
                } else {
                    console.log('error')
                }
            }
        });
    });

    $('#taskAssignedStudentTable').on('click', '.applicantprofile-lock__button', function (e) { 
        e.preventDefault();
        document.querySelector(".applicantprofile-lock__button svg.loading").classList.remove('invisible')
        const data = {
            interviewId : $(this).attr("data-id")
        }
        axios({
            method: "post",
            url: route('applicant.interview.unlock'),
            data: data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector(".applicantprofile-lock__button svg.loading").classList.add('invisible')
            if (response.status == 200) {
                successModal.show();
                let Data = response.data.ref;
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Success!" );
                    $("#successModal .successModalDesc").html('Profile Matched.');
                });   
                
                location.href= Data;  
            }
        }).catch(error => {
            document.querySelector(".applicantprofile-lock__button svg.loading").classList.add('invisible')
            if (error.response) {
                if (error.response.status == 422) {
                    successModal.hide();
                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html('Invalid Profile!');
                        $("#errorModal .errorModalDesc").html('Something went wrong. Please try later.');
                    });
                } else if (error.response.status == 404) {
                    successModal.hide();
                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html('Invalid Profile!');
                        $("#errorModal .errorModalDesc").html('Interviewer didn\'t match');
                    });  
                } else {
                    console.log('error')
                }
            }
        });
    
    });


    if($("#uploadTaskDocumentForm").length > 0){
        let dzError = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadTaskDocumentForm = {
            autoProcessQueue: false,
            maxFiles: 10,
            maxFilesize: 20,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn = new Dropzone('#uploadTaskDocumentForm', options);

        drzn.on('addedfile', function(file){
            if(file.name.match(/[`!@#$%^&*+\=\[\]{};':"\\|,<>\/?~]/)){
                $('#uploadTaskDocumentModal .modal-content .uploadError').remove();
                $('#uploadTaskDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! One of your selected file name contain validation error & that file has been removed.</div>');
                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
                drzn.removeFile(file);

                setTimeout(function(){
                    $('#uploadTaskDocumentModal .modal-content .uploadError').remove();
                }, 5000)
            }
        });

        drzn.on("maxfilesexceeded", (file) => {
            $('#uploadTaskDocumentModal .modal-content .uploadError').remove();
            $('#uploadTaskDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn.removeFile(file);
            setTimeout(function(){
                $('#uploadTaskDocumentModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn.on("error", function(file, response){
            dzError = true;
        });

        drzn.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn.on("complete", function(file) {
            //drzn.removeFile(file);
        }); 

        drzn.on('queuecomplete', function(){
            $('#uploadProcessDoc').removeAttr('disabled');
            document.querySelector("#uploadProcessDoc svg").style.cssText ="display: none;";

            uploadTaskDocumentModal.hide();
            if(!dzError){
                uploadTaskDocumentModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('student document successfully uploaded.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }else{
                $('#uploadProcessDoc').removeAttr('disabled');
                document.querySelector("#uploadProcessDoc svg").style.cssText ="display: none;";

                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!" );
                    $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact administrator.');
                    $("#warningModal .warningCloser").attr('data-action', 'RELOAD');
                });
                setTimeout(function(){
                    warningModal.hide();
                    window.location.reload();
                }, 2000);
            }
        })

        $('#uploadProcessDoc').on('click', function(e){
            e.preventDefault();
            var acceptedFiles = drzn.getAcceptedFiles().length;
            if(acceptedFiles > 0){
                document.querySelector('#uploadProcessDoc').setAttribute('disabled', 'disabled');
                document.querySelector("#uploadProcessDoc svg").style.cssText ="display: inline-block;";
                drzn.processQueue();
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!" );
                    $("#warningModal .warningModalDesc").html('Empty submission are not accepted. Please upload some valid files.');
                    $("#warningModal .warningCloser").attr('data-action', 'NONE');
                });
                
                setTimeout(function(){
                    warningModal.hide();
                }, 2000);
            }
        });

        const uploadTaskDocumentModalEl = document.getElementById('uploadTaskDocumentModal')
        uploadTaskDocumentModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#uploadTaskDocumentModal input[name="student_id"]').val('0');
            $('#uploadTaskDocumentModal input[name="task_id"]').val('0');
            $('#uploadTaskDocumentModal input[name="phase"]').val('');
            $('#uploadTaskDocumentModal input[name="display_file_name"]').val('');
            $('#uploadTaskDocumentModal input[name="hard_copy_check"]').val('0');
            $('#uploadTaskDocumentModal #hard_copy_check-2').prop('checked', true);
            drzn.removeAllFiles(true);
        });
    }

    $(document).on('click', '.uploadTaskDoc', function(e){
        var $btn = $(this); 
        var phase = $btn.attr('data-phase');
        var taskid = $btn.attr('data-taskid');
        var studentid = $btn.attr('data-studentid');

        $('#uploadTaskDocumentModal [name="student_id"]').val(studentid);
        $('#uploadTaskDocumentModal [name="task_id"]').val(taskid);
        $('#uploadTaskDocumentModal [name="phase"]').val(phase);
    });

    $('#uploadTaskDocumentModal #process_doc_name').on('keyup', function(){
        $('#uploadTaskDocumentModal input[name="display_file_name"]').val($('#uploadTaskDocumentModal #process_doc_name').val());
    });

    $('#uploadTaskDocumentModal [name="hard_copy_check_status"]').on('change', function(){
        $('#uploadTaskDocumentModal input[name="hard_copy_check"]').val($('#uploadTaskDocumentModal [name="hard_copy_check_status"]:checked').val());
    });


    $(document).on('click', '.updateTaskDocRequestForm', function(e){
        var $btn = $(this); 
        var studentTaskId = $btn.attr('data-studenttaskid');
        //get data from local storage
        var dataset = localStorage.getItem('student_document_request_form'+studentTaskId);
                          
        const dataSetRequest = JSON.parse(dataset);                      
        
        console.log(dataSetRequest);  
        // insert data into modal body
        //$('#updateTaskDocumentRequestOutcomeModal .modal-body').html(cell.getData().student_document_request_form_id);
        $('#updateTaskDocumentRequestOutcomeModal #letter_set_id').html('');
        $('#updateTaskDocumentRequestOutcomeModal #description').html('');

        $('#updateTaskDocumentRequestOutcomeModal #letter_set_id').html('<option value="'+dataSetRequest.letter_set.id+'">'+dataSetRequest.letter_set.letter_title+'</option>');
        $('#updateTaskDocumentRequestOutcomeModal #description').html(dataSetRequest.description);
        $('#updateTaskDocumentRequestOutcomeModal input[name=student_task_id]').val(studentTaskId);
        //this should be checked data
        if(dataSetRequest.service_type == 'Same Day (cost £10.00)')
            $('#updateTaskDocumentRequestOutcomeModal #service_type1').prop('checked',true);
        else
            $('#updateTaskDocumentRequestOutcomeModal #service_type2').prop('checked',true);
            

    });

    $('#uploadTaskDocumentModal #process_doc_name').on('keyup', function(){
        $('#uploadTaskDocumentModal input[name="display_file_name"]').val($('#uploadTaskDocumentModal #process_doc_name').val());
    });

    $('#uploadTaskDocumentModal [name="hard_copy_check_status"]').on('change', function(){
        $('#uploadTaskDocumentModal input[name="hard_copy_check"]').val($('#uploadTaskDocumentModal [name="hard_copy_check_status"]:checked').val());
    });

    $(document).on('click', '.updateTaskOutcome', function(e){
        e.preventDefault();
        var $btn = $(this);

        var phase = $btn.attr('data-phase');
        var taskid = $btn.attr('data-taskid');
        var studentid = $btn.attr('data-studentid');

        axios({
            method: 'post',
            url: route('task.manager.outcome.statuses'),
            data: {phase : phase, taskid : taskid, studentid : studentid},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#updateTaskOutcomeModal .modal-body').html(response.data.message.res);
                $('#updateTaskOutcomeModal input[name="student_id"]').val(studentid);
                $('#updateTaskOutcomeModal input[name="task_id"]').val(taskid);
                $('#updateTaskOutcomeModal input[name="phase"]').val(phase);
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error =>{
            console.log(error)
        });
    });

    $("#updateTaskOutcomeForm").on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('updateTaskOutcomeForm');
    
        document.querySelector('#updateOutcomeBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#updateOutcomeBtn svg").style.cssText ="display: inline-block;";

        var taskStatusId = [];
        $form.find('.resultStatus').each(function(){
            if($(this).prop('checked')){
                taskStatusId.push($(this).val());
            }
        });
        if(taskStatusId.length > 0){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('task.manager.update.outcome'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#updateOutcomeBtn').removeAttribute('disabled');
                    document.querySelector("#updateOutcomeBtn svg").style.cssText = "display: none;";
                    updateTaskOutcomeModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Process Task result successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#updateOutcomeBtn').removeAttribute('disabled');
                document.querySelector("#updateOutcomeBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html("Error Found!" );
                            $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact administrator.');
                            $("#warningModal .warningCloser").attr('data-action', 'RELOAD');
                        });
                        setTimeout(function(){
                            warningModal.hide();
                            window.location.reload();
                        }, 2000);
                    } else {
                        console.log('error');
                    }
                }
            });
        }else{
            document.querySelector('#updateOutcomeBtn').removeAttribute('disabled');
            document.querySelector("#updateOutcomeBtn svg").style.cssText = "display: none;";

            $('#updateTaskOutcomeModal .taskUoutComeAlert').remove();
            $('#updateTaskOutcomeModal .modal-content').prepend('<div class="alert taskUoutComeAlert alert-pending-soft show flex items-start mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> Result can not be empty.</div>')
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
            setTimeout(function(){
                $('#updateTaskOutcomeModal .taskUoutComeAlert').remove();
            }, 2000);
        }
    });

    $(document).on('click', '.downloadTaskDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var phase = $theLink.attr('data-phase');
        var id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('task.manage.document.download'), 
            data: {phase : phase, id : id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    })

    /* Attendance Excuse Start */
    $(document).on('click', '.viewExcuse', function(e){
        e.preventDefault();
        let $theLink = $(this);
        var student_task_id = $theLink.attr('data-recordid');
        var student_id = $theLink.attr('data-studentid');

        axios({
            method: "post",
            url: route('student.process.task.view.excuse'), 
            data: {student_task_id : student_task_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                $('#viewAttendanceExcuseModal .modal-body').html(response.data.htm);
                $('#viewAttendanceExcuseModal [name="student_id"]').val(student_id);
                $('#viewAttendanceExcuseModal [name="student_task_id"]').val(student_task_id);
                $('#viewAttendanceExcuseModal [name="attendance_excuse_id"]').val(response.data.excuse);

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            } 
        }).catch(error => {
            if(error.response){
                console.log('error');
            }
        });
    });

    $('#viewAttendanceExcuseForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('viewAttendanceExcuseForm');
    
        document.querySelector('#updateAttnExcuseBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#updateAttnExcuseBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.process.update.task.and.excuse'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateAttnExcuseBtn').removeAttribute('disabled');
            document.querySelector("#updateAttnExcuseBtn svg").style.cssText = "display: none;";
            if (response.status == 200) {
                viewAttendanceExcuseModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Attendance excuse status successfully updated');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });    

                setTimeout(() => {
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateAttnExcuseBtn').removeAttribute('disabled');
            document.querySelector("#updateAttnExcuseBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#viewAttendanceExcuseForm .${key}`).addClass('border-danger');
                        $(`#viewAttendanceExcuseForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    })
    /* Attendance Excuse End */

    /* Pearson Reg. Conf Start */
    $('#uploadPearsonRegConfForm').on('change', '#editPearRegDocument', function(){
        showFileName('editPearRegDocument', 'editPearRegDocumentName');
    });

    function showFileName(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = fileInput.files[0].name;
        namePreview.innerText = fileName;
        return false;
    };

    $('#uploadPearsonRegConfForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('uploadPearsonRegConfForm');
    
        document.querySelector('#upPRegConfBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#upPRegConfBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#uploadPearsonRegConfForm input[name="document"]')[0].files[0]); 
        axios({
            method: "POST",
            url: route('student.process.upload.registration.confirmations'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#upPRegConfBtn').removeAttribute('disabled');
            document.querySelector("#upPRegConfBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                uploadPearsonRegConfModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.msg);
                });  
                
                setTimeout(function(){
                    //successModal.hide();
                }, 2000);
            }
            taskAssignedStudentTable.init();
        }).catch(error => {
            document.querySelector('#upPRegConfBtn').removeAttribute('disabled');
            document.querySelector("#upPRegConfBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#uploadPearsonRegConfForm .${key}`).addClass('border-danger');
                        $(`#uploadPearsonRegConfForm  .error-${key}`).html(val);
                    }
                } else if(error.response.status == 405){
                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Error!" );
                        $("#errorModal .errorModalDesc").html(error.response.data.msg);
                    }); 

                    setTimeout(() => {
                        //errorModal.hide();
                    }, 2000);
                } else {
                    console.log('error');
                }
            }
        });
    })

    /* Pearson Reg. Conf End */
})();