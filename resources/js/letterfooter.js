import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { each } from "jquery";
import Dropzone from "dropzone";

("use strict");

var letterFooterListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let queryStr = $("#query-FOOTER").val() != "" ? $("#query-FOOTER").val() : "";
        let status = $("#status-FOOTER").val() != "" ? $("#status-FOOTER").val() : "1";

        let tableContent = new Tabulator("#letterFooterListTable", {
            ajaxURL: route("letterfooter.list"),
            ajaxParams: {queryStr : queryStr, status : status},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 5,
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
                    title: "Staff",
                    field: "for_staff",
                    headerHozAlign: "left",
                },
                {
                    title: "File",
                    field: "url",
                    headerHozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                       if(cell.getData().url != ''){
                            return '<img style="max-with: 100%; width: auto; height: 30px;" src="'+cell.getData().url+'" alt="'+cell.getData().name+'"/>';
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
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        btns +='<a target="_blank" href="'+cell.getData().url+'" download class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-FOOTER").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-FOOTER").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-FOOTER").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Letter Footer Templates",
            });
        });

        $("#tabulator-export-html-FOOTER").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-FOOTER").on("click", function (event) {
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
    if ($("#letterFooterListTable").length) {
        // Init Table
        letterFooterListTable.init();

        // Filter function
        function filterHTMLFormUP() {
            letterFooterListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-FOOTER").on("click", function (event) {
            filterHTMLFormUP();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-FOOTER").on("click", function (event) {
            $("#query-FOOTER").val("");
            $("#status-FOOTER").val("1");
            filterHTMLFormUP();
        });

    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const uploadLetterFooterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadLetterFooterModal"));

    

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
    if($("#uploadLetterFootForm").length > 0){
        let dzErrorFooter = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadLetterFootForm = {
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

        var drznfooter = new Dropzone('#uploadLetterFootForm', options);

        drznfooter.on("maxfilesexceeded", (file) => {
            $('#uploadLetterFootForm .modal-content .uploadError').remove();
            $('#uploadLetterFootForm .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 1 file at a time.</div>');
            drznfooter.removeFile(file);
            setTimeout(function(){
                $('#uploadLetterFootForm .modal-content .uploadError').remove();
            }, 2000)
        });

        drznfooter.on("error", function(file, response){
            //console.log(response);
            dzErrorFooter = true;
        });

        drznfooter.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drznfooter.on('queuecomplete', function(){
            $('#uploadFooterBtn').removeAttr('disabled');
            document.querySelector("#uploadFooterBtn svg").style.cssText ="display: none;";

            uploadLetterFooterModal.hide();
            
            if(!dzErrorFooter){
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Successfully uploaded.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            } else{
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
        });

        const uploadLetterFooterModalEl = document.getElementById('uploadLetterFooterModal')
        uploadLetterFooterModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#uploadLetterFooterModal .acc__input-error').html('');
            $('#uploadLetterFooterModal input[name="footer_display_name"]').val('');
            $('#uploadLetterFooterModal input[name="name"]').val('');
            $('#uploadLetterFooterModal input[name="for_letter"]').val('No');
            $('#uploadLetterFooterModal input[name="for_email"]').val('No');
            $('#uploadLetterFooterModal input[name="for_staff"]').val('No');
            $('#uploadLetterFooterModal input.letter_for_options').prop('checked', false);
            drznfooter.removeAllFiles();
        });

        $('#uploadLetterFooterModal [name="footer_display_name"]').on('keyup paste change', function(e){
            $('#uploadLetterFooterModal [name="name"]').val($('#uploadLetterFooterModal [name="footer_display_name"]').val());
        })

        /*$('#uploadLetterFooterModal [name="footer_dispaly_for"]').on('change', function(e){
            $('#uploadLetterFooterModal [name="for"]').val($('#uploadLetterFooterModal [name="footer_dispaly_for"]').val());
        })*/

        $('#uploadLetterFooterModal .letter_for_options').on('change', function(e){
            $('#uploadLetterFooterModal .letter_for_options').each(function(){
                var inputVal = $(this).val();
                if($(this).prop('checked')){
                    $('#uploadLetterFooterModal input[name="'+inputVal+'"]').val('Yes');
                }else{
                    $('#uploadLetterFooterModal input[name="'+inputVal+'"]').val('No');
                }
            });
        })

        $('#uploadFooterBtn').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadFooterBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadFooterBtn svg").style.cssText ="display: inline-block;";
            
            if($('#uploadLetterFooterModal [name="footer_display_name"]').val() != "" && $('#uploadLetterFooterModal [name="footer_dispaly_for"]').val() != ''){
                $('#uploadLetterFooterModal .modal-content .uploadError').remove();
                drznfooter.processQueue();
            }else{    
                $('#uploadLetterFooterModal .modal-content .uploadError').remove();
                $('#uploadLetterFooterModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Please fill out all required fields</div>');
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                document.querySelector('#uploadFooterBtn').removeAttribute('disabled', 'disabled');
                document.querySelector("#uploadFooterBtn svg").style.cssText ="display: none;";

                setTimeout(function(){
                    $('#uploadLetterFooterModal .modal-content .uploadError').remove();
                }, 2000)
            }
            
        });
    }
    /* End Dropzone */

    $('#letterFooterListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var uploadId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this file? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', uploadId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETEDOC');
        });
    });

    $('#letterFooterListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var uploadId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this file from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', uploadId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTOREDOC');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEDOC'){
            axios({
                method: 'delete',
                url: route('letterheaderfooter.destory.uploads'),
                data: {recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    letterFooterListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Uploaded file successfully deleted.');
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
                url: route('letterheaderfooter.resotore.uploads'),
                data: {recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    letterFooterListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('File successfully resotred.');
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
})();