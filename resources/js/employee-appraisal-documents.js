import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";

("use strict");
var employeeAppraisalDocListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let employee_id = $("#employeeAppraisalDocListTable").attr('data-employee') != "" ? $("#employeeAppraisalDocListTable").attr('data-employee') : "0";
        let appraisal_id = $("#employeeAppraisalDocListTable").attr('data-appraisal') != "" ? $("#employeeAppraisalDocListTable").attr('data-appraisal') : "0";
        let queryStr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "1";

        let tableContent = new Tabulator("#employeeAppraisalDocListTable", {
            ajaxURL: route('employee.appraisal.documents.list'),
            ajaxParams: { employee_id: employee_id, appraisal_id: appraisal_id, queryStr : queryStr, status : status},
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
                    headerHozAlign: "left",
                    width: "120",
                },
                {
                    title: "Name",
                    field: "display_file_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Checked",
                    field: "hard_copy_check",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        if(cell.getData().hard_copy_check == 1){
                            return '<span class="btn btn-success-soft px-1 py-0 rounded-0">Yes</span>';
                        }else{
                            return '<span class="btn btn-pending-soft px-1 py-0 rounded-0">No</span>';
                        }
                    }
                },
                {
                    title: "Uploaded By",
                    field: "created_by",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if(cell.getData().url == 1 && cell.getData().document_id > 0){
                            btns +='<a data-id="' + cell.getData().document_id + '" href="javascript:void(0);" class="downloadDoc btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                        }
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }else if(cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
                const actionColumn = this.getColumn("id");
                if (actionColumn) {
                    const currentWidth = actionColumn.getWidth();
                    actionColumn.setWidth(currentWidth - 1);
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
        $("#tabulator-export-csv-UP").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-UP").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-UP").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Student Upload Details",
            });
        });

        $("#tabulator-export-html-UP").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-UP").on("click", function (event) {
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
    if ($("#employeeAppraisalDocListTable").length) {
        // Init Table
        employeeAppraisalDocListTable.init();

        // Filter function
        function filterHTMLFormUP() {
            employeeAppraisalDocListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLFormUP();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterHTMLFormUP();
        });

    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addAppraisalDocModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAppraisalDocModal"));

    const addAppraisalDocModalEl = document.getElementById('addAppraisalDocModal')
    addAppraisalDocModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addAppraisalDocModal input[name="hard_copy_check"]').val('0');
        $('#addAppraisalDocModal input[name="display_file_name"]').val('');
        $('#addAppraisalDocModal input[name="document_name"]').val('');
        $('#addAppraisalDocModal input[name="hard_copy_check_status"][value="0"]').prop('checked', false);

        document.querySelector('#uploadDocBtn').removeAttribute('disabled', 'disabled');
        document.querySelector("#uploadDocBtn svg").style.cssText ="display: none;";
    });

    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
    });

    $('#confirmModal .disAgreeWith').on('click', function(e){
        e.preventDefault();

        confirmModal.hide();
    });

    $('#successModal .successCloser').on('click', function(e) {
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


    /* Start Dropzone */
    if($("#addAppraisalDocForm").length > 0){
        let dzError = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.addAppraisalDocForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 20,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn1 = new Dropzone('#addAppraisalDocForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#addAppraisalDocModal .modal-content .uploadError').remove();
            $('#addAppraisalDocModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#addAppraisalDocModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn1.on("error", function(file, response){
            dzError = true;
        });

        drzn1.on("success", function(file, response){
            console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn1.on('queuecomplete', function(){
            $('#uploadDocBtn').removeAttr('disabled');
            document.querySelector("#uploadDocBtn svg").style.cssText ="display: none;";

            addAppraisalDocModal.hide();
            if(!dzError){
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Employee appraisal document successfully uploaded.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!" );
                    $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact administrator.');
                    $("#warningModal .warningCloser").attr('data-action', 'DISMISS');
                });
                setTimeout(function(){
                    warningModal.hide();
                    window.location.reload();
                }, 2000);
            }
        })

        $('#uploadDocBtn').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadDocBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadDocBtn svg").style.cssText ="display: inline-block;";
            
            if($('#addAppraisalDocModal [name="hard_copy_check_status"]:checked').length > 0 && $('#addAppraisalDocModal [name="document_name"]').val() != ''){
                var hardCopyChecked = $('#addAppraisalDocModal [name="hard_copy_check_status"]:checked').val();
                $('#addAppraisalDocModal input[name="hard_copy_check"]').val(hardCopyChecked)
                var document_name = $('#addAppraisalDocModal [name="document_name"]').val();
                $('#addAppraisalDocModal input[name="display_file_name"]').val(document_name)
                drzn1.processQueue();
            }else{
                $('#addAppraisalDocModal .modal-content .uploadError').remove();
                $('#addAppraisalDocModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the hard copy check status and fill out the document name field..</div>');
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#addAppraisalDocModal .modal-content .uploadError').remove();
                    document.querySelector('#uploadDocBtn').removeAttribute('disabled', 'disabled');
                    document.querySelector("#uploadDocBtn svg").style.cssText ="display: none;";
                }, 2000)
            }
            
        });
    }
    /* End Dropzone */


    $('#employeeAppraisalDocListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var uploadId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this document from student list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', uploadId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETEDOC');
        });
    });

    $('#employeeAppraisalDocListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var uploadId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this document from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', uploadId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTOREDOC');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let apprisal = $agreeBTN.attr('data-apprisal');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEDOC'){
            axios({
                method: 'delete',
                url: route('employee.appraisal.document.destory'),
                data: {apprisal : apprisal, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    employeeAppraisalDocListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Employee appraisal uploaded document successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTOREDOC'){
            axios({
                method: 'post',
                url: route('employee.appraisal.document.restore'),
                data: {apprisal : apprisal, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    employeeAppraisalDocListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Employee Appraisal document successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    });

    $('#employeeAppraisalDocListTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('employee.documents.download.url'),
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
})()