import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";


("use strict");
var processTaskArchiveListTable = (function () {
    var _tableGen = function (tableId, studentId, processId ) {
        // Setup Tabulator
        let tableContent = new Tabulator(tableId, {
            ajaxURL: route("student.archived.process.list"),
            ajaxParams: { studentId: studentId, processId: processId },
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
                    minWidth: 50,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 120,
                },
                {
                    title: "Description",
                    field: "desc",
                    headerHozAlign: "left",
                    minWidth: 120,
                },
                {
                    title: "Deleted At",
                    field: "deleted_at",
                    headerHozAlign: "left",
                    minWidth: 120,
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    minWidth: 120,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        btns += '<button data-id="' +cell.getData().id +'" style="top: -7px;"  class="restore_btn relative btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9">\
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="rotate-cw" data-lucide="rotate-cw" class="lucide lucide-rotate-cw block mx-auto"><path d="M21 2v6h-6"></path><path d="M21 13a9 9 0 11-3-7.7L21 8"></path></svg>\
                        </button>';
                        
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
                const actionColumn = this.getColumn("id");
                if (actionColumn) {
                    const currentWidth = actionColumn.getWidth();
                    actionColumn.setWidth(currentWidth - 1);
                }
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
        init: function ( tableId, studentId, processId ) {
            _tableGen( tableId, studentId, processId  );
        },
    };
})();

var processTaskLogTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        var studentTaskId = ($('#processTaskLogTable').attr('data-studenttaskid') > 0 ? $('#processTaskLogTable').attr('data-studenttaskid') : 0);
        let tableContent = new Tabulator("#processTaskLogTable", {
            ajaxURL: route("student.process.log.list"),
            ajaxParams: { studentTaskId: studentTaskId},
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
                    width: "100",
                },
                {
                    title: "Action",
                    field: "actions",
                    headerHozAlign: "left",
                },
                {
                    title: "Field",
                    field: "field_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Prev Value",
                    field: "prev_field_value",
                    headerHozAlign: "left",
                },
                {
                    title: "New Value",
                    field: "current_field_value",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        return '<div>'+cell.getData().current_field_value+'</div>';
                    }
                },
                {
                    title: "Created By",
                    field: "id",
                    headerSort: false,
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {                        
                        var htms = "";
                            htms += '<div>';
                                if(cell.getData().created_by != ''){
                                    htms += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                                }
                                if(cell.getData().created_at != ''){
                                    htms += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                                }
                            htms += '</div>';
                        return htms;
                    },
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

var studentInterviewLogTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        var studentTaskId = ($('#processTaskLogTable').attr('data-studenttaskid') > 0 ? $('#processTaskLogTable').attr('data-studenttaskid') : 0);
        var studentId = ($('#processTaskLogTable').attr('data-studentid') > 0 ? $('#processTaskLogTable').attr('data-studentid') : 0);

        let tableContent = new Tabulator("#processTaskLogTable", {
            ajaxURL: route("student.student.interview.log"),
            ajaxParams: { studentTaskId: studentTaskId, studentId: studentId },
            ajaxFiltering: false,
            ajaxSorting: false,
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
                    title: "Serial",
                    field: "sl",
                    width: "180",
                },
                {
                    title: "Interview Date",
                    field: "date",
                    headerHozAlign: "left",
                    
                    headerSort:false,
                },
                {
                    title: "Sart Time - End Time",
                    field: "time",
                    headerHozAlign: "left",
                    
                    headerSort:false,
                },
                {
                    title: "Result",
                    field: "result",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    
                    headerSort:false,
                },
                {
                    title: "Interviewer",
                    field: "interviewer",
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
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();


(function(){
    if ($(".processTaskArchiveListTable").length) {
        // Init Table
        $(".processTaskArchiveListTable").each(function(){
            var $table = $(this);
            var processId = $table.attr('data-process');
            var studentId = $table.attr('data-student');
            var tableId = '#'+$table.attr('id');
            
            processTaskArchiveListTable.init(tableId, studentId, processId);
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        })

        $('.processTaskArchiveListTable').on('click', '.restore_btn', function(e){
            e.preventDefault();
            var $btn = $(this);
            var rowid = $btn.attr('data-id');

            confirmModal.show();
            document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                $("#confirmModal .confModTitle").html("Are you sure?");
                $("#confirmModal .confModDesc").html('Do you want to restore this process task? Please click on agree to continue.');
                $("#confirmModal .agreeWith").attr('data-recordid', rowid);
                $("#confirmModal .agreeWith").attr('data-status', 'RESTORETASK');
            });
        })
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const processDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#processDropdown"));
    const uploadTaskDocumentModal = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#uploadTaskDocumentModal"));
    const updateTaskOutcomeModal = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#updateTaskOutcomeModal"));
    const processListAccordion = tailwind.Accordion.getOrCreateInstance(document.querySelector("#processListAccordion"));
    const viewAttendanceExcuseModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewAttendanceExcuseModal"));

    const taskUserModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#taskUserModal"));
    document.getElementById('taskUserModal').addEventListener('hidden.tw.modal', function(event){
        $('#taskUserModal .taskUserModalContent').fadeOut('fast', function(){
            $('table tbody', this).html('');
        });
        $('#taskUserModal .taskUserModalLoader').fadeIn();
    });

    const updateTaskOutcomeModalEl = document.getElementById('updateTaskOutcomeModal')
    updateTaskOutcomeModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#updateTaskOutcomeModal .modal-body").html('');
        $('#updateTaskOutcomeModal input[name="student_task_id"]').val('0');
    });
    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
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
        $('#viewAttendanceExcuseModal input[name="student_task_id"]').val('0');
        $('#viewAttendanceExcuseModal input[name="attendance_excuse_id"]').val('0');
    });

    
    $('#studentProcessListForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('studentProcessListForm');
    
        document.querySelector('#addProcessItemsAdd').setAttribute('disabled', 'disabled');
        document.querySelector("#addProcessItemsAdd svg.theLoader").style.cssText ="display: inline-block;";

        var task_list_ids = [];
        var student_id = $('input[name="student_id"]', $form).val();
        $form.find('.task_list_id').each(function(){
            if($(this).prop('checked')){
                task_list_ids.push($(this).val());
            }
        });
        if(task_list_ids.length > 0){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('student.process.store.task.list'),
                data: {task_list_ids : task_list_ids, student_id : student_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#addProcessItemsAdd').removeAttribute('disabled');
                    document.querySelector("#addProcessItemsAdd svg.theLoader").style.cssText = "display: none;";

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html(response.data.message);
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#addProcessItemsAdd').removeAttribute('disabled');
                document.querySelector("#addProcessItemsAdd svg.theLoader").style.cssText = "display: none;";
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
            document.querySelector('#addProcessItemsAdd').removeAttribute('disabled');
            document.querySelector("#addProcessItemsAdd svg.theLoader").style.cssText = "display: none;";

            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html("Error Found!" );
                $("#warningModal .warningModalDesc").html('You have to select at least one process to continue.');
                $("#warningModal .warningCloser").attr('data-action', 'DISMISS');
            });

            setTimeout(function(){
                warningModal.hide();
            }, 2000);
        }
        
    });

    $('#closeProcessDropdown').on('click', function(e){
        e.preventDefault();
        processDropdown.hide();
        processListAccordion.hide();
    })

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })
    
    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            warningModal.hide();
            window.location.reload();
        }else{
            warningModal.hide();
        }
    });


    // Dropzone
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
        })
    }

    const uploadTaskDocumentModalEl = document.getElementById('uploadTaskDocumentModal')
    uploadTaskDocumentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#uploadTaskDocumentModal input[name="student_task_id"]').val('0');
        //drzn.removeAllFiles();
    });

    $('.uploadTaskDoc').on('click', function(e){
        var $btn = $(this);
        var studenttaskid = $btn.attr('data-studenttaskid');
        $('#uploadTaskDocumentModal [name="student_task_id"]').val(studenttaskid);
    });

    $('.deletestudentTask').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var taskid = $btn.attr('data-taskid');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this task from student process? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', taskid);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETETASK');
        });
    });

    $('#confirmModal .disAgreeWith').on('click', function(e){
        e.preventDefault();

        confirmModal.hide();
    });

    $('.markAsCompleted').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        let recordid = $btn.attr('data-recordid');
        
        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to mark this task as Completed? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordid);
            $("#confirmModal .agreeWith").attr('data-status', 'COMPLETEDTASK');
        });
    });

    $('.markAsPending').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        let recordid = $btn.attr('data-recordid');
        
        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to mark this task as Completed? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordid);
            $("#confirmModal .agreeWith").attr('data-status', 'PENDINGTASK');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETETASK'){
            axios({
                method: 'delete',
                url: route('student.destory.task'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('student assigned task successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'COMPLETEDTASK'){
            axios({
                method: 'post',
                url: route('student.completed.task'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('student task status successfully changed to "COMPLETED".');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'PENDINGTASK'){
            axios({
                method: 'post',
                url: route('student.pending.task'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('student task status successfully changed to "PENDING".');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORETASK'){
            axios({
                method: 'post',
                url: route('student.resotore.task'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('student task successfully resotred under "In Progress" tab.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    });

    $('.updateTaskOutcome').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var taskId = $btn.attr('data-studenttaskid');
        axios({
            method: 'post',
            url: route('student.show.task.outmoce.statuses'),
            data: {taskId : taskId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                //console.log(response.data.message);
                $('#updateTaskOutcomeModal .modal-body').html(response.data.message.res);
                $('#updateTaskOutcomeModal input[name="student_task_id"]').val(taskId);
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
        var student_id = $('input[name="student_id"]', $form).val();
        var student_task_id = $('input[name="student_task_id"]', $form).val();
        $form.find('.resultStatus').each(function(){
            if($(this).prop('checked')){
                taskStatusId.push($(this).val());
            }
        });
        if(taskStatusId.length > 0){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('student.process.task.result.update'),
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

    $('.viewTaskLogBtn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var studentTaskId = $btn.attr('data-studenttaskid');
        var studentid = $btn.attr('data-studentid');
        var interview = $btn.attr('data-interview');

        $('#processTaskLogTable').attr('data-studenttaskid', studentTaskId);
        $('#processTaskLogTable').attr('data-interview', interview);
        if(interview == 1){
            studentInterviewLogTable.init();
        }else{
            processTaskLogTable.init();
        }
        
    })

    $('.taskUserLoader').on('click', function(){
        var task_id = $(this).attr('data-taskid');
        taskUserModal.show();

        axios({
            method: 'post',
            url: route('student.process.task.users'),
            data: {task_id : task_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#taskUserModal .taskUserModalLoader').fadeOut('fast');
                $('#taskUserModal .taskUserModalContent').fadeIn('fast', function(){
                    $('table tbody', this).html(response.data.res);
                });

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


    $('#studentProcessAccordion, #processTaskLogTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('student.document.download'), 
            data: {row_id : row_id},
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
    });


    /* Attendance Excuse Start */
    $(document).on('click', '.viewExcuse', function(e){
        e.preventDefault();
        let $theLink = $(this);
        var student_task_id = $theLink.attr('data-recordid');

        axios({
            method: "post",
            url: route('student.process.task.view.excuse'), 
            data: {student_task_id : student_task_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                $('#viewAttendanceExcuseModal .modal-body').html(response.data.htm);
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

})()