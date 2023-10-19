import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { each } from "jquery";
import Dropzone from "dropzone";

("use strict");
var signatoryListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let queryStr = $("#query-SG").val() != "" ? $("#query-SG").val() : "";
        let status = $("#status-SG").val() != "" ? $("#status-SG").val() : "1";

        let tableContent = new Tabulator("#signatoryListTable", {
            ajaxURL: route("signatory.list"),
            ajaxParams: { queryStr : queryStr, status : status},
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
                    title: "Signature",
                    field: "url",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        if(cell.getData().url != ''){
                            html += '<div class="flex lg:justify-start">\
                                    <div class="intro-x">\
                                        <img alt="'+cell.getData().signatory_name+'" class="rounded-0 h-10 w-auto relative" src="'+cell.getData().url+'">\
                                    </div>\
                                </div>';
                        }
                        return html;
                    }
                },
                {
                    title: "Name",
                    field: "signatory_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += cell.getData().signatory_name;
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Designation",
                    field: "signatory_post",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += cell.getData().signatory_post;
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "230",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if(cell.getData().url != ''){
                            btns +='<a target="_blank" href="'+cell.getData().url+'" download class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                        }
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" data-tw-toggle="modal" data-tw-target="#editSignatoryModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' + cell.getData().id + '" class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }else if(cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-SG").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-SG").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-SG").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Signatory Details",
            });
        });

        $("#tabulator-export-html-SG").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-SG").on("click", function (event) {
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
    if ($("#signatoryListTable").length) {
        // Init Table
        signatoryListTable.init();

        // Filter function
        function filterHTMLFormAN() {
            signatoryListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-SG").on("click", function (event) {
            filterHTMLFormAN();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-SG").on("click", function (event) {
            $("#query-SG").val("");
            $("#status-SG").val("1");
            filterHTMLFormAN();
        });

    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addSignatoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSignatoryModal"));
    const editSignatoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSignatoryModal"));

    const addSignatoryModalEl = document.getElementById('addSignatoryModal')
    addSignatoryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addSignatoryModal .acc__input-error').html('');
        $('#addSignatoryModal input').val('');
        $('#addSignatoryModal #addSignatoryDocumentName').html('');
    });

    const editSignatoryModalEl = document.getElementById('editSignatoryModal')
    editSignatoryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editSignatoryModal .acc__input-error').html('');
        $('#editSignatoryModal .modal-body input').val('');
        $('#editSignatoryModal input[name="id"]').val('0');
        $('#editSignatoryModal #editSignatoryDocumentName').html('');
        $('#editSignatoryModal .downloadExistAttachment').fadeOut().attr('href', '#');
    });

    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
    });
    
    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            warningModal.hide();
            window.location.reload();
        }else{
            warningModal.hide();
        }
    });
    
    $('#addSignatoryForm').on('change', '#addSignatoryDocument', function(){
        showFileName('addSignatoryDocument', 'addSignatoryDocumentName');
    });
    
    $('#editSignatoryForm').on('change', '#editSignatoryDocument', function(){
        showFileName('editSignatoryDocument', 'editSignatoryDocumentName');
    });

    function showFileName(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = fileInput.files[0].name;
        namePreview.innerText = fileName;
        return false;
    };


    $('#addSignatoryForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addSignatoryForm');
    
        document.querySelector('#saveSignatorySet').setAttribute('disabled', 'disabled');
        document.querySelector("#saveSignatorySet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addSignatoryForm input[name="signatory"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('signatory.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveSignatorySet').removeAttribute('disabled');
            document.querySelector("#saveSignatorySet svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addSignatoryModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Staff signatory set successfully inserted.');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 5000);
            }
            signatoryListTable.init();
        }).catch(error => {
            document.querySelector('#saveSignatorySet').removeAttribute('disabled');
            document.querySelector("#saveSignatorySet svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addSignatoryForm .${key}`).addClass('border-danger');
                        $(`#addSignatoryForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#signatoryListTable').on('click', '.edit_btn', function(e){
        var $btn = $(this);
        var signatoryID = $btn.attr('data-id');
        axios({
            method: "post",
            url: route('signatory.edit'),
            data: {signatoryID : signatoryID},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            let dataset = response.data.message;
            $('#editSignatoryForm input[name="signatory_name"]').val(dataset.signatory_name ? dataset.signatory_name : '');
            $('#editSignatoryForm input[name="signatory_post"]').val(dataset.signatory_post ? dataset.signatory_post : '');
            $('#editSignatoryForm input[name="id"]').val(signatoryID);
            if(dataset.signature != ''){
                $('#editSignatoryForm .downloadExistAttachment').fadeIn().attr('href', dataset.signature);
            }else{
                $('#editSignatoryForm .downloadExistAttachment').fadeOut().attr('href', '#');
            }
        }).catch(error => {
            console.log('error');
        });
    });

    $('#editSignatoryForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editSignatoryForm');
    
        document.querySelector('#updateSignatorySet').setAttribute('disabled', 'disabled');
        document.querySelector("#updateSignatorySet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#editSignatoryForm input[name="signatory"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('signatory.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateSignatorySet').removeAttribute('disabled');
            document.querySelector("#updateSignatorySet svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editSignatoryModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Signatory set successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 5000);
            }
            signatoryListTable.init();
        }).catch(error => {
            document.querySelector('#updateSignatorySet').removeAttribute('disabled');
            document.querySelector("#updateSignatorySet svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editSignatoryForm .${key}`).addClass('border-danger');
                        $(`#editSignatoryForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });


    $('#signatoryListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Signatory from applicant list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETE');
        });
    });

    $('#signatoryListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this Signatory from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTORE');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('signatory.destory', recordid),
                data: {recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    signatoryListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Signatory set successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('signatory.restore', recordid),
                data: {recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    signatoryListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Signatory set successfully resotred.');
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