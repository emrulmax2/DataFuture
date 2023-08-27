import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { each } from "jquery";
import Dropzone from "dropzone";

("use strict");
var letterHeaderListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let queryStr = $("#query-HEADER").val() != "" ? $("#query-HEADER").val() : "";
        let status = $("#status-HEADER").val() != "" ? $("#status-HEADER").val() : "1";

        let tableContent = new Tabulator("#letterHeaderListTable", {
            ajaxURL: route("letterheader.list"),
            ajaxParams: {queryStr : queryStr, status : status},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 5,
            paginationSizeSelector: [5, 10, 20, 30, 40],
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
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Letter",
                    field: "for_letter",
                    headerHozAlign: "left",
                },
                {
                    title: "Email",
                    field: "for_email",
                    headerHozAlign: "left",
                },
                {
                    title: "File",
                    field: "url",
                    headerHozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                       if(cell.getData().url != ''){
                            return '<img style="max-with: 100%; height: 30px;" src="'+cell.getData().url+'" alt="'+cell.getData().name+'"/>';
                       } 
                    }
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
                        btns +='<a target="_blank" href="'+cell.getData().url+'" download class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-HEADER").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-HEADER").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-HEADER").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Letter Header Templates",
            });
        });

        $("#tabulator-export-html-HEADER").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-HEADER").on("click", function (event) {
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
    if ($("#letterHeaderListTable").length) {
        // Init Table
        letterHeaderListTable.init();

        // Filter function
        function filterHTMLFormUP() {
            letterHeaderListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-HEADER").on("click", function (event) {
            filterHTMLFormUP();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-HEADER").on("click", function (event) {
            $("#query-HEADER").val("");
            $("#status-HEADER").val("1");
            filterHTMLFormUP();
        });

    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#letterheadConfirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const uploadLetterHeaderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadLetterHeaderModal"));

    
    // To get value of name field
    
    const confirmModalEl = document.getElementById('letterheadConfirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#letterheadConfirmModal .confModDesc").html('');
        $("#letterheadConfirmModal .agreeWith").attr('data-recordid', '0');
        $("#letterheadConfirmModal .agreeWith").attr('data-status', 'none');
        $('#letterheadConfirmModal button').removeAttr('disabled');
    });

    $('#letterheadConfirmModal .disAgreeWith').on('click', function(e){
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

    /* Start Dropzone */
    if($("#uploadLetterHeadForm").length > 0){
        let dzError = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadLetterHeadForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 20,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
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

        var drzn1 = new Dropzone('#uploadLetterHeadForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#uploadLetterHeadForm .modal-content .uploadError').remove();
            $('#uploadLetterHeadForm .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 1 file at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#uploadLetterHeadForm .modal-content .uploadError').remove();
            }, 4000)
        });

        drzn1.on("error", function(file, response){
            dzError = true;
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on('queuecomplete', function(){
            $('#uploadHeaderBtn').removeAttr('disabled');
            document.querySelector("#uploadHeaderBtn svg").style.cssText ="display: none;";

            uploadLetterHeaderModal.hide();
            if(!dzError){
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Successfully uploaded.');
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
                    $("#warningModal .warningCloser").attr('data-action', 'DISMISS');
                });
                setTimeout(function(){
                    warningModal.hide();
                    window.location.reload();
                }, 5000);
            }
        });

        const uploadLetterHeaderModalEl = document.getElementById('uploadLetterHeaderModal')
        uploadLetterHeaderModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#uploadLetterHeaderModal .acc__input-error').html('');
            $('#uploadLetterHeaderModal input[name="display_name"]').val('');
            $('#uploadLetterHeaderModal input[name="name"]').val('');
            $('#uploadLetterHeaderModal input[name="for_letter"]').val('No');
            $('#uploadLetterHeaderModal input[name="for_email"]').val('No');
            $('#uploadLetterHeaderModal input.letter_for_options').prop('checked', false);
            drzn1.removeAllFiles();
        });

        $('#uploadLetterHeaderModal [name="display_name"]').on('keyup paste change', function(e){
            $('#uploadLetterHeaderModal [name="name"]').val($('#uploadLetterHeaderModal [name="display_name"]').val());
        })

        /*$('#uploadLetterHeaderModal [name="dispaly_for"]').on('change', function(e){
            $('#uploadLetterHeaderModal [name="for"]').val($('#uploadLetterHeaderModal [name="dispaly_for"]').val());
        })*/

        $('#uploadLetterHeaderModal .letter_for_options').on('change', function(e){
            $('#uploadLetterHeaderModal .letter_for_options').each(function(){
                var inputVal = $(this).val();
                if($(this).prop('checked')){
                    $('#uploadLetterHeaderModal input[name="'+inputVal+'"]').val('Yes');
                }else{
                    $('#uploadLetterHeaderModal input[name="'+inputVal+'"]').val('No');
                }
            });
        })

        $('#uploadHeaderBtn').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadHeaderBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadHeaderBtn svg").style.cssText ="display: inline-block;";

            var header_for = $('#uploadLetterHeaderModal .letter_for_options:checked').length;
            alert(header_for);
            return false;
       
            if($('#uploadLetterHeaderModal [name="name"]').val() != "" && header_for > 0){
                $('#uploadLetterHeaderModal .modal-content .uploadError').remove();
                drzn1.processQueue();
            }else{
                $('#uploadLetterHeaderModal .modal-content .uploadError').remove();
                $('#uploadLetterHeaderModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please fill out all required fields.</div>');
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#uploadLetterHeaderModal .modal-content .uploadError').remove();
                }, 5000);

                document.querySelector('#uploadHeaderBtn').removeAttribute('disabled', 'disabled');
                document.querySelector("#uploadHeaderBtn svg").style.cssText ="display: none;";
            }
            
        });
    }
    /* End Dropzone */

    $('#letterHeaderListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var uploadId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("letterheadConfirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#letterheadConfirmModal .confModTitle").html("Are you sure?" );
            $("#letterheadConfirmModal .confModDesc").html('Want to delete this file? Please click on agree to continue.');
            $("#letterheadConfirmModal .agreeWith").attr('data-recordid', uploadId);
            $("#letterheadConfirmModal .agreeWith").attr('data-status', 'DELETEDOC');
        });
    });

    $('#letterHeaderListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var uploadId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("letterheadConfirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#letterheadConfirmModal .confModTitle").html("Are you sure?" );
            $("#letterheadConfirmModal .confModDesc").html('Want to restore this file from the trash? Please click on agree to continue.');
            $("#letterheadConfirmModal .agreeWith").attr('data-recordid', uploadId);
            $("#letterheadConfirmModal .agreeWith").attr('data-status', 'RESTOREDOC');
        });
    });

    $('#letterheadConfirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');

        $('#letterheadConfirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEDOC'){
            axios({
                method: 'delete',
                url: route('letterheaderfooter.destory.uploads'),
                data: {recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#letterheadConfirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    letterHeaderListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Uploaded file successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTOREDOC'){
            axios({
                method: 'post',
                url: route('letterheaderfooter.resotore.uploads'),
                data: {recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#letterheadConfirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    letterHeaderListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('File successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    });
})();