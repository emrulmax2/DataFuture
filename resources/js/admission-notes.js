import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { each } from "jquery";
import Dropzone from "dropzone";

("use strict");
var applicantNotesListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#applicantNotesListTable").attr('data-applicant') != "" ? $("#applicantNotesListTable").attr('data-applicant') : "0";
        let queryStr = $("#query-AN").val() != "" ? $("#query-AN").val() : "";
        let status = $("#status-AN").val() != "" ? $("#status-AN").val() : "1";

        let tableContent = new Tabulator("#applicantNotesListTable", {
            ajaxURL: route("admission.note.list"),
            ajaxParams: { applicantId: applicantId, queryStr : queryStr, status : status},
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
                    title: "Note",
                    field: "note",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += cell.getData().note;
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Created By",
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
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "230",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if(cell.getData().applicant_document_id > 0){
                            btns +='<a  href="javascript:void(0);" data-id="'+cell.getData().applicant_document_id+'" class="downloadDoc btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                        }
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" data-tw-toggle="modal" data-tw-target="#viewNoteModal"  class="view_btn btn btn-twitter text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="eye-off" class="w-4 h-4"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" data-tw-toggle="modal" data-tw-target="#editNoteModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-AN").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-AN").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-AN").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Applicant Notes Details",
            });
        });

        $("#tabulator-export-html-AN").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-AN").on("click", function (event) {
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
    if ($("#applicantNotesListTable").length) {
        // Init Table
        applicantNotesListTable.init();

        // Filter function
        function filterHTMLFormAN() {
            applicantNotesListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-AN").on("click", function (event) {
            filterHTMLFormAN();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-AN").on("click", function (event) {
            $("#query-AN").val("");
            $("#status-AN").val("1");
            filterHTMLFormAN();
        });

    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addNoteModal"));
    const viewNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewNoteModal"));
    const editNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editNoteModal"));

    let addEditor;
    if($("#addEditor").length > 0){
        const el = document.getElementById('addEditor');
        ClassicEditor.create(el).then((editor) => {
            addEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    let editEditor;
    if($("#editEditor").length > 0){
        const el = document.getElementById('editEditor');
        ClassicEditor.create(el).then((editor) => {
            editEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    const addNoteModalEl = document.getElementById('addNoteModal')
    addNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addNoteModal .acc__input-error').html('');
        $('#addNoteModal input[name="document"]').val('');
        $('#addNoteModal #addNoteDocumentName').html('');
        addEditor.setData('');
    });

    const editNoteModalEl = document.getElementById('editNoteModal')
    editNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editNoteModal .acc__input-error').html('');
        $('#editNoteModal input[name="document"]').val('');
        $('#editNoteModal #editNoteDocumentName').html('');
        $('#editNoteModal input[name="id"]').val('0');
        $('#editNoteModal .downloadExistAttachment').attr('href', '#').fadeOut();
        editEditor.setData('');
    });

    const viewNoteModalEl = document.getElementById('viewNoteModal')
    viewNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#viewNoteModal .modal-body').html('');
        $('#viewNoteModal .modal-footer .footerBtns').html('');
    });

    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
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
    
    $('#addNoteForm').on('change', '#addNoteDocument', function(){
        showFileName('addNoteDocument', 'addNoteDocumentName');
    });
    
    $('#editNoteForm').on('change', '#editNoteDocument', function(){
        showFileName('editNoteDocument', 'editNoteDocumentName');
    });

    function showFileName(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = fileInput.files[0].name;
        namePreview.innerText = fileName;
        return false;
    };

    $('#applicantNotesListTable').on('click', '.view_btn', function(e){
        var $btn = $(this);
        var noteId = $btn.attr('data-id');
        axios({
            method: "post",
            url: route('admission.show.note'),
            data: {noteId : noteId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#viewNoteModal .modal-body').html(response.data.message);
            if(response.data.btns != ''){
                $('#viewNoteModal .modal-footer .footerBtns').html(response.data.btns);
            }
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        }).catch(error => {
            console.log('error');
        });
    })

    $('#addNoteForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addNoteForm');
    
        document.querySelector('#saveNote').setAttribute('disabled', 'disabled');
        document.querySelector("#saveNote svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addNoteForm input[name="document"]')[0].files[0]); 
        form_data.append("content", addEditor.getData());
        axios({
            method: "post",
            url: route('admission.store.note'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveNote').removeAttribute('disabled');
            document.querySelector("#saveNote svg").style.cssText = "display: none;";
            //console.log(response.data.message);
            //return false;

            if (response.status == 200) {
                addNoteModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Applicant Note successfully stored.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            applicantNotesListTable.init();
        }).catch(error => {
            document.querySelector('#saveNote').removeAttribute('disabled');
            document.querySelector("#saveNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addNoteForm .${key}`).addClass('border-danger');
                        $(`#addNoteForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#applicantNotesListTable').on('click', '.edit_btn', function(e){
        var $btn = $(this);
        var noteId = $btn.attr('data-id');
        axios({
            method: "post",
            url: route('admission.get.note'),
            data: {noteId : noteId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            let dataset = response.data.res;
            editEditor.setData(dataset.note ? dataset.note : '');
            $('#editNoteModal input[name="id"]').val(noteId);
            if(dataset.docURL != ''){
                $('#editNoteModal .downloadExistAttachment').attr('href', dataset.docURL).fadeIn();
            }else{
                $('#editNoteModal .downloadExistAttachment').attr('href', '#').fadeOut();
            }
        }).catch(error => {
            console.log('error');
        });
    });

    $('#editNoteForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editNoteForm');
    
        document.querySelector('#UpdateNote').setAttribute('disabled', 'disabled');
        document.querySelector("#UpdateNote svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#editNoteForm input[name="document"]')[0].files[0]); 
        form_data.append("content", editEditor.getData());
        axios({
            method: "post",
            url: route('admission.update.note'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#UpdateNote').removeAttribute('disabled');
            document.querySelector("#UpdateNote svg").style.cssText = "display: none;";
            //console.log(response.data.message);
            //return false;

            if (response.status == 200) {
                editNoteModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Applicant Note successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            applicantNotesListTable.init();
        }).catch(error => {
            document.querySelector('#UpdateNote').removeAttribute('disabled');
            document.querySelector("#UpdateNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editNoteForm .${key}`).addClass('border-danger');
                        $(`#editNoteForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });


    $('#applicantNotesListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var noteId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Note from applicant list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', noteId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETENOT');
        });
    });

    $('#applicantNotesListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var noteId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this Note from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', noteId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTORENOT');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let applicant = $agreeBTN.attr('data-applicant');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETENOT'){
            axios({
                method: 'delete',
                url: route('admission.destory.note'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantNotesListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant note successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORENOT'){
            axios({
                method: 'post',
                url: route('admission.resotore.note'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantNotesListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant note successfully resotred.');
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

    $('#applicantNotesListTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('admission.document.download'), 
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

    $('#viewNoteModal').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('admission.document.download'), 
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