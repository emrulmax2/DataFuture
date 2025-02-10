import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";

("use strict");
var employeeDocumentListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let employeeId = $("#employeeDocumentListTable").attr('data-employee') != "" ? $("#employeeDocumentListTable").attr('data-employee') : "0";
        let queryStr = $("#query-ED").val() != "" ? $("#query-ED").val() : "";
        let status = $("#status-ED").val() != "" ? $("#status-ED").val() : "1";

        let tableContent = new Tabulator("#employeeDocumentListTable", {
            ajaxURL: route("agent-user.documents.uploads.list"),
            ajaxParams: { employeeId: employeeId, queryStr : queryStr, status : status},
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
                        if(cell.getData().url != ''){
                            btns +='<a data-id="' + cell.getData().id + '" target="_blank" href="javascript:void(0);" class="downloadDoc btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-ED").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-ED").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-ED").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Employee Upload Details",
            });
        });

        $("#tabulator-export-html-ED").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-ED").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
var employeeCommunicationDocumentListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let employeeId = $("#employeeCommunicationDocumentListTable").attr('data-employee') != "" ? $("#employeeCommunicationDocumentListTable").attr('data-employee') : "0";
        let queryStr = $("#query-EDC").val() != "" ? $("#query-EDC").val() : "";
        let status = $("#status-EDC").val() != "" ? $("#status-EDC").val() : "1";

        let tableContent = new Tabulator("#employeeCommunicationDocumentListTable", {
            ajaxURL: route("agent-user.documents.communication.list"),
            ajaxParams: { employeeId: employeeId, queryStr : queryStr, status : status},
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
                        if(cell.getData().url != ''){
                            btns +='<a data-id="'+cell.getData().id+'" target="_blank" href="javascript:void(0);" class="downloadDoc btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-EDC").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EDC").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EDC").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Employee Upload Details",
            });
        });

        $("#tabulator-export-html-EDC").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EDC").on("click", function (event) {
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
    if ($("#employeeDocumentListTable").length) {
        // Init Table
        employeeDocumentListTable.init();

        // Filter function
        function filterHTMLFormUP() {
            employeeDocumentListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-ED").on("click", function (event) {
            filterHTMLFormUP();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-ED").on("click", function (event) {
            $("#query-ED").val("");
            $("#status-ED").val("1");
            filterHTMLFormUP();
        });

    }
    if ($("#employeeCommunicationDocumentListTable").length) {
        // Init Table
        employeeCommunicationDocumentListTable.init();

        // Filter function
        function filterHTMLFormEDC() {
            employeeCommunicationDocumentListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-EDC").on("click", function (event) {
            filterHTMLFormEDC();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EDC").on("click", function (event) {
            $("#query-EDC").val("");
            $("#status-EDC").val("1");
            filterHTMLFormEDC();
        });

    }


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const uploadsDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#uploadsDropdown"));
    const uploadEmployeeDocumentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadEmployeeDocumentModal"));

    const uploadEmployeeDocumentModalEl = document.getElementById('uploadEmployeeDocumentModal')
    uploadEmployeeDocumentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#uploadEmployeeDocumentModal input[name="display_file_name"]').val('');
        $('#uploadEmployeeDocumentModal input[name="document_setting_id"]').val('0');
        $('#uploadEmployeeDocumentModal input[name="hard_copy_check"]').val('0');
        $('#uploadEmployeeDocumentModal input[name="hard_copy_check_status"][value="0"]').prop('checked', true);
        document.querySelector('#uploadEmpDocBtn').removeAttribute('disabled', 'disabled');
        document.querySelector("#uploadEmpDocBtn svg").style.cssText ="display: none;";

        Dropzone.forElement('#uploadDocumentForm').removeAllFiles(true);
    });
    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
    });

    $('#closeUploadsDropdown').on('click', function(e){
        e.preventDefault();
        uploadsDropdown.hide();
    });

    $('#confirmModal .disAgreeWith').on('click', function(e){
        e.preventDefault();

        confirmModal.hide();
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
    
    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            warningModal.hide();
            window.location.reload();
        }else{
            warningModal.hide();
        }
    });

    $('#uploadEmployeeDocumentModal [name="doc_name"]').on('keyup', function(){
        $('#uploadEmployeeDocumentModal [name="display_file_name"]').val($(this).val());
    })

    /* Start Dropzone */
    if($("#uploadDocumentForm").length > 0){
        let dzError = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadDocumentForm = {
            autoProcessQueue: false,
            maxFiles: 10,
            maxFilesize: 20,
            parallelUploads: 10,
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


        var drzn1 = new Dropzone('#uploadDocumentForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#uploadEmployeeDocumentModal .modal-content .uploadError').remove();
            $('#uploadEmployeeDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#uploadEmployeeDocumentModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn1.on("error", function(file, response){
            dzError = true;
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn1.on('queuecomplete', function(){
            $('#uploadEmpDocBtn').removeAttr('disabled');
            document.querySelector("#uploadEmpDocBtn svg").style.cssText ="display: none;";

            uploadEmployeeDocumentModal.hide();
            if(!dzError){
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Employee document successfully uploaded.');
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
                    //window.location.reload();
                }, 2000);
            }
        })

        $('#uploadEmpDocBtn').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadEmpDocBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadEmpDocBtn svg").style.cssText ="display: inline-block;";
            
            if($('#uploadEmployeeDocumentModal [name="hard_copy_check_status"]:checked').length > 0){
                var hardCopyChecked = $('#uploadEmployeeDocumentModal [name="hard_copy_check_status"]:checked').val();
                $('#uploadEmployeeDocumentModal input[name="hard_copy_check"]').val(hardCopyChecked)
                drzn1.processQueue();
            }else{
                $('#uploadEmployeeDocumentModal .modal-content .uploadError').remove();
                $('#uploadEmployeeDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the hard copy check status.</div>');
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#uploadEmployeeDocumentModal .modal-content .uploadError').remove();
                    document.querySelector('#uploadEmpDocBtn').removeAttribute('disabled', 'disabled');
                    document.querySelector("#uploadEmpDocBtn svg").style.cssText ="display: none;";
                }, 2000)
            }
            
        });
    }
    /* End Dropzone */

    $('#employeeDocumentUploaders').on('click', function(e){
        e.preventDefault();
        
        if($('.employee_doc_ids:checked').length > 0){
            uploadEmployeeDocumentModal.show();
            var documentSettingId = $('.employee_doc_ids:checked').val();
            $('#uploadEmployeeDocumentModal input[name="document_setting_id"]').val(documentSettingId);

            
            var selectedDocumentID = $('.employee_doc_ids:checked');
            var documentLabelText = selectedDocumentID.attr('data-label').trim();

            $('#documentNameDisplay').text(documentLabelText);

            $('.displayNameInput').on('keyup', function() {
                var displayName = $(this).val();
                var seperator = " ";
                if(displayName.length > 0){
                    seperator = " - ";
                }else{
                    seperator = " ";
                }
                $('#documentNameDisplay').text(documentLabelText + seperator + displayName);
            });


            uploadsDropdown.hide();
            $('.employee_doc_ids').prop('checked', false);
        }else{
            warningModal.show();
            $('#warningModal .warningModalTitle').html('Oops!');
            $('#warningModal .warningModalDesc').html('Please a document type from the list first.');
            $('#warningModal .warningCloser').attr('data-action', 'DISMISS');

            setTimeout(function(){
                warningModal.hide();
            }, 2000);
        }
    });

    $('#employeeDocumentListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var uploadId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this document from employee list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', uploadId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETEDOC');
        });
    });

    $('#employeeDocumentListTable').on('click', '.restore_btn', function(e){
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
        let employee = $agreeBTN.attr('data-employee');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEDOC'){
            axios({
                method: 'delete',
                url: route('agent-user.documents.destory.uploads'),
                data: {employee : employee, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    employeeDocumentListTable.init();
                    employeeCommunicationDocumentListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Employee uploaded document successfully deleted.');
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
                url: route('agent-user.documents.restore.uploads'),
                data: {employee : employee, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    employeeDocumentListTable.init();
                    employeeCommunicationDocumentListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Employee document successfully resotred.');
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


    $('#employeeDocumentListTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('agent-user.documents.download.url'),
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

    $('#employeeCommunicationDocumentListTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('agent-user.documents.download.url'),
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
})();