import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import html2canvas from "html2canvas";
import { saveAs } from 'file-saver';

("use strict");
var taskAssignedStudentTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let task_id = $("#taskAssignedStudentTable").attr('data-taskid');
        let phase = $("#taskAssignedStudentTable").attr('data-phase');
        
        let org_email = ($("#taskAssignedStudentTable").attr('data-email') != 'undefined' ? $("#taskAssignedStudentTable").attr('data-email') : 'No');
        let id_card = ($("#taskAssignedStudentTable").attr('data-idcard') != 'undefined' ? $("#taskAssignedStudentTable").attr('data-idcard') : 'No');
        let interview = ($("#taskAssignedStudentTable").attr('data-interview') != 'undefined' ? $("#taskAssignedStudentTable").attr('data-interview') : 'No');
        
        
        let tableContent = new Tabulator("#taskAssignedStudentTable", {
            ajaxURL: route("task.manager.list"),
            ajaxParams: { status : status, task_id : task_id, phase : phase},
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
                    formatter(cell, formatterParams) {  
                        var html = '<a href="'+cell.getData().url+'" class="whitespace-normal font-medium text-primary">';
                                html += (phase == 'Applicant' ? cell.getData().application_no : cell.getData().registration_no),
                            html += '</a>';
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
                        $('#exportTaskStudentListBtn').fadeIn();
                        $('#commonActionBtns').fadeIn();
                    }
                }else{
                    $('#exportTaskStudentsBtn').fadeOut();
                    $('#completeEmailTaskStudentsBtn').fadeOut();
                    $('#exportTaskStudentListBtn').fadeOut();
                    $('#commonActionBtns').fadeOut();
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
            $("#query").val('');

            filterHTMLFormADM();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const downloadIDCard = tailwind.Modal.getOrCreateInstance(document.querySelector("#downloadIDCard"));
    const canceledReasonModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#canceledReasonModal"));
    const callLockModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#callLockModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));

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

})();