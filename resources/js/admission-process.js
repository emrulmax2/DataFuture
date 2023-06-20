import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { each } from "jquery";
import Dropzone from "dropzone";


("use strict");
var processTaskArchiveListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let tableContent = new Tabulator("#processTaskArchiveListTable", {
            ajaxURL: route("admission.archived.process.list"),
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
                    title: "Description",
                    field: "desc",
                    headerHozAlign: "left",
                },
                {
                    title: "Deleted At",
                    field: "deleted_at",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        
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

var processTaskLogTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        var applicantTaskId = ($('#processTaskLogTable').attr('data-applicanttaskid') > 0 ? $('#processTaskLogTable').attr('data-applicanttaskid') : 0);
        let tableContent = new Tabulator("#processTaskLogTable", {
            ajaxURL: route("admission.process.log.list"),
            ajaxParams: { applicantTaskId: applicantTaskId},
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


(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const processDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#processDropdown"));
    const uploadTaskDocumentModal = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#uploadTaskDocumentModal"));
    const updateTaskOutcomeModal = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#updateTaskOutcomeModal"));

    const updateTaskOutcomeModalEl = document.getElementById('updateTaskOutcomeModal')
    updateTaskOutcomeModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#updateTaskOutcomeModal .modal-body").html('');
        $('#updateTaskOutcomeModal input[name="applicant_task_id"]').val('0');
    });
    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
    });

    
    $('#studentProcessListForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('studentProcessListForm');
    
        document.querySelector('#addProcessItemsAdd').setAttribute('disabled', 'disabled');
        document.querySelector("#addProcessItemsAdd svg").style.cssText ="display: inline-block;";

        var task_list_ids = [];
        var applicant_id = $('input[name="applicant_id"]', $form).val();
        $form.find('.task_list_id').each(function(){
            if($(this).prop('checked')){
                task_list_ids.push($(this).val());
            }
        });
        if(task_list_ids.length > 0){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('admission.process.store.task.list'),
                data: {task_list_ids : task_list_ids, applicant_id : applicant_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#addProcessItemsAdd').removeAttribute('disabled');
                    document.querySelector("#addProcessItemsAdd svg").style.cssText = "display: none;";

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html(response.data.message);
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 5000);
                }
            }).catch(error => {
                document.querySelector('#addProcessItemsAdd').removeAttribute('disabled');
                document.querySelector("#addProcessItemsAdd svg").style.cssText = "display: none;";
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
                        }, 5000);
                    } else {
                        console.log('error');
                    }
                }
            });
        }else{
            document.querySelector('#addProcessItemsAdd').removeAttribute('disabled');
            document.querySelector("#addProcessItemsAdd svg").style.cssText = "display: none;";

            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html("Error Found!" );
                $("#warningModal .warningModalDesc").html('You have to select at least one process to continue.');
                $("#warningModal .warningCloser").attr('data-action', 'DISMISS');
            });

            setTimeout(function(){
                warningModal.hide();
            }, 5000);
        }
        
    });

    $('#closeProcessDropdown').on('click', function(e){
        e.preventDefault();
        processDropdown.hide();
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

        drzn.on("maxfilesexceeded", (file) => {
            $('#uploadTaskDocumentModal .modal-content .uploadError').remove();
            $('#uploadTaskDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn.removeFile(file);
            setTimeout(function(){
                $('#uploadTaskDocumentModal .modal-content .uploadError').remove();
            }, 4000)
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
                    $("#successModal .successModalDesc").html('Applicant document successfully uploaded.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 5000);
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!" );
                    $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact administrator.');
                    $("#warningModal .warningCloser").attr('data-action', 'RELOAD');
                });
                setTimeout(function(){
                    warningModal.hide();
                    window.location.reload();
                }, 5000);
            }
        })

        $('#uploadProcessDoc').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadProcessDoc').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadProcessDoc svg").style.cssText ="display: inline-block;";
            drzn.processQueue();
            
        })
    }

    const uploadTaskDocumentModalEl = document.getElementById('uploadTaskDocumentModal')
    uploadTaskDocumentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#uploadTaskDocumentModal input[type="applicant_task_id"]').val('0');
        //drzn.removeAllFiles();
    });

    $('.uploadTaskDoc').on('click', function(e){
        var $btn = $(this);
        var applicanttaskid = $btn.attr('data-applicanttaskid');
        $('#uploadTaskDocumentModal [name="applicant_task_id"]').val(applicanttaskid);
    });

    $('.deleteApplicantTask').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var taskid = $btn.attr('data-taskid');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this task from applicant process? Please click on agree to continue.');
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
        let applicant = $agreeBTN.attr('data-applicant');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETETASK'){
            axios({
                method: 'delete',
                url: route('admission.destory.task'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant assigned task successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'COMPLETEDTASK'){
            axios({
                method: 'post',
                url: route('admission.completed.task'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant task status successfully changed to "COMPLETED".');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'PENDINGTASK'){
            axios({
                method: 'post',
                url: route('admission.pending.task'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant task status successfully changed to "PENDING".');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORETASK'){
            axios({
                method: 'post',
                url: route('admission.resotore.task'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant task successfully resotred under "In Progress" tab.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    });

    if ($("#processTaskArchiveListTable").length) {
        // Init Table
        processTaskArchiveListTable.init();

        $('#processTaskArchiveListTable').on('click', '.restore_btn', function(e){
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

    $('.updateTaskOutcome').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var taskId = $btn.attr('data-applicanttaskid');
        axios({
            method: 'post',
            url: route('admission.show.task.outmoce.statuses'),
            data: {taskId : taskId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                //console.log(response.data.message);
                $('#updateTaskOutcomeModal .modal-body').html(response.data.message.res);
                $('#updateTaskOutcomeModal input[name="applicant_task_id"]').val(taskId);
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
        var applicant_id = $('input[name="applicant_id"]', $form).val();
        var applicant_task_id = $('input[name="applicant_task_id"]', $form).val();
        $form.find('.resultStatus').each(function(){
            if($(this).prop('checked')){
                taskStatusId.push($(this).val());
            }
        });
        if(taskStatusId.length > 0){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('admission.process.task.result.update'),
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
                    }, 5000);
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
                        }, 5000);
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
            }, 5000);
        }
    });

    $('.viewTaskLogBtn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var applicantTaskId = $btn.attr('data-applicanttaskid');

        $('#processTaskLogTable').attr('data-applicanttaskid', applicantTaskId);
        processTaskLogTable.init();
    })

})()