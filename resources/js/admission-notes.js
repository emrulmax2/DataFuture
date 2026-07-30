import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { each } from "jquery";
import Dropzone from "dropzone";

("use strict");

function noteEscape(value) {
    return String(value == null ? "" : value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function noteInitials(name) {
    const initials = String(name || "")
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");

    return initials || "?";
}

function noteAvatarColor(seed) {
    const palette = ["#0d7a76", "#6d4bb0", "#2f7d4f", "#c0562a", "#2f8f7d", "#3a6ea5"];
    const text = String(seed || "");
    let hash = 0;

    for (let i = 0; i < text.length; i += 1) {
        hash = (hash + text.charCodeAt(i) * (i + 1)) % palette.length;
    }

    return palette[hash];
}

function noteRenderLucide() {
    createIcons({
        icons,
        "stroke-width": 1.8,
        nameAttr: "data-lucide",
    });
}

function noteFormatFileSize(bytes) {
    const size = Number(bytes) || 0;
    if (size < 1024) return size + " B";
    if (size < 1024 * 1024) return (size / 1024).toFixed(size < 10240 ? 1 : 0) + " KB";
    return (size / (1024 * 1024)).toFixed(1) + " MB";
}

function notePersonHtml(name, date, seed) {
    const safeName = noteEscape(name || "Unknown");
    const safeDate = noteEscape(date || "");

    return '<div class="adm-comm-by">' +
        '<span class="adm-comm-by__avatar" style="background:' + noteAvatarColor(seed || name) + ';">' + noteInitials(name) + '</span>' +
        '<span class="adm-comm-by__text">' +
            '<span class="adm-comm-by__name">' + safeName + '</span>' +
            '<span class="adm-comm-by__date">' + safeDate + '</span>' +
        '</span>' +
    '</div>';
}

function notePreviewHtml(data) {
    const note = noteEscape(data.note || "No note content found.");
    const hasAttachment = Number(data.applicant_document_id) > 0;

    return '<div class="adm-note-preview">' +
        '<span class="adm-note-preview__icon"><i data-lucide="file-text"></i></span>' +
        '<span class="adm-note-preview__text">' +
            '<span class="adm-note-preview__title">' + note + '</span>' +
            '<span class="adm-note-preview__meta">' +
                '<i data-lucide="' + (hasAttachment ? "paperclip" : "minus") + '"></i>' +
                (hasAttachment ? "Attachment included" : "No attachment") +
            '</span>' +
        '</span>' +
    '</div>';
}

function noteActionButton(action) {
    const tag = action.tag || "button";
    const attrs = [
        'data-id="' + noteEscape(action.id) + '"',
        'class="' + noteEscape(action.className) + ' adm-row-action adm-row-action--' + noteEscape(action.type) + '"',
        'title="' + noteEscape(action.title) + '"',
        'aria-label="' + noteEscape(action.title) + '"',
    ];

    if (tag === "button") attrs.push('type="button"');
    if (tag === "a") attrs.push('href="javascript:void(0);"');
    if (action.extra) attrs.push(action.extra);

    return '<' + tag + ' ' + attrs.join(" ") + '>' +
        '<i data-lucide="' + noteEscape(action.icon) + '"></i>' +
    '</' + tag + '>';
}

function noteActions(actions) {
    return '<div class="adm-row-actions">' +
        actions.filter(Boolean).map(noteActionButton).join("") +
    '</div>';
}

function noteViewHtml(rawHtml) {
    return '<div class="adm-note-view__hero">' +
        '<span class="adm-note-view__icon"><i data-lucide="file-text"></i></span>' +
        '<span class="adm-note-view__heading">' +
            '<span class="adm-note-view__title">Applicant Note</span>' +
            '<span class="adm-note-view__desc">Saved admission note details</span>' +
        '</span>' +
    '</div>' +
    '<div class="adm-note-view__content">' + rawHtml + '</div>';
}

function noteSetButtonBusy(selector, busy) {
    const button = document.querySelector(selector);
    if (!button) return;

    button.toggleAttribute("disabled", busy);
    const loader = button.querySelector(".adm-btn-loader");
    if (loader) {
        loader.style.display = busy ? "inline-block" : "none";
    }
}

function noteDecorateModalButtons(scope = document) {
    scope.querySelectorAll(".modal-footer .btn, .modal-body.p-0 .px-5.pb-8.text-center .btn").forEach((button) => {
        if (button.classList.contains("downloadDoc") || button.querySelector(".adm-btn-icon")) return;

        const text = button.textContent.trim().toLowerCase();
        let icon = "check";

        if (text.includes("cancel") || text.includes("no,")) icon = "x";
        else if (text.includes("save")) icon = "save";
        else if (text.includes("update")) icon = "check";
        else if (text.includes("upload")) icon = "upload-cloud";
        else if (text === "ok") icon = "check";

        const iconEl = document.createElement("i");
        iconEl.setAttribute("data-lucide", icon);
        iconEl.className = "adm-btn-icon";
        button.prepend(iconEl);
    });

    noteRenderLucide();
}

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
                    minWidth: 360,
                    formatter(cell, formatterParams){
                        return notePreviewHtml(cell.getData());
                    }
                },
                {
                    title: "Created By",
                    field: "created_by",
                    headerHozAlign: "left",
                    minWidth: 220,
                    formatter(cell, formatterParams){
                        return notePersonHtml(cell.getData().created_by, cell.getData().created_at, cell.getData().id);
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "170",
                    download: false,
                    formatter(cell, formatterParams) {
                        const data = cell.getData();
                        return noteActions([
                            Number(data.applicant_document_id) > 0 ? {
                                tag: "a",
                                type: "download",
                                className: "downloadDoc",
                                icon: "download",
                                id: data.applicant_document_id,
                                title: "Download attachment",
                            } : null,
                            data.deleted_at == null ? {
                                type: "view",
                                className: "view_btn",
                                icon: "eye",
                                id: data.id,
                                title: "View note",
                                extra: 'data-tw-toggle="modal" data-tw-target="#viewNoteModal"',
                            } : null,
                            data.deleted_at == null ? {
                                type: "edit",
                                className: "edit_btn",
                                icon: "pencil",
                                id: data.id,
                                title: "Edit note",
                                extra: 'data-tw-toggle="modal" data-tw-target="#editNoteModal"',
                            } : null,
                            data.deleted_at == null ? {
                                type: "delete",
                                className: "delete_btn",
                                icon: "trash-2",
                                id: data.id,
                                title: "Delete note",
                            } : {
                                type: "restore",
                                className: "restore_btn",
                                icon: "rotate-cw",
                                id: data.id,
                                title: "Restore note",
                            },
                        ]);
                    },
                },
            ],
            renderComplete() {
                noteRenderLucide();
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
            noteRenderLucide();
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

    noteDecorateModalButtons();

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
        $('#addNoteModal #addNoteDocumentName').html('').hide();
        noteSetButtonBusy("#saveNote", false);
        addEditor.setData('');
    });

    const editNoteModalEl = document.getElementById('editNoteModal')
    editNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editNoteModal .acc__input-error').html('');
        $('#editNoteModal input[name="document"]').val('');
        $('#editNoteModal #editNoteDocumentName').html('').hide();
        $('#editNoteModal input[name="id"]').val('0');
        $('#editNoteModal .downloadExistAttachment').attr('href', '#').fadeOut();
        noteSetButtonBusy("#UpdateNote", false);
        editEditor.setData('');
    });

    const viewNoteModalEl = document.getElementById('viewNoteModal')
    viewNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#viewNoteModal #viewNoteContent').html('');
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
        if (!fileInput || !namePreview || !fileInput.files || !fileInput.files.length) {
            if (namePreview) {
                namePreview.innerHTML = "";
                namePreview.style.display = "none";
            }
            return false;
        }

        const file = fileInput.files[0];
        namePreview.innerHTML = '<div class="adm-mail-upload__file">' +
            '<span class="adm-mail-upload__file-icon"><i data-lucide="file"></i></span>' +
            '<span class="adm-mail-upload__file-name">' + noteEscape(file.name) + '</span>' +
            '<span class="adm-mail-upload__file-size">' + noteFormatFileSize(file.size) + '</span>' +
        '</div>';
        namePreview.style.display = "grid";
        noteRenderLucide();
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
            $('#viewNoteModal #viewNoteContent').html(noteViewHtml(response.data.message));
            if(response.data.btns != ''){
                $('#viewNoteModal .modal-footer .footerBtns').html(response.data.btns);
            }
            noteDecorateModalButtons(document.getElementById("viewNoteModal"));
        }).catch(error => {
            console.log('error');
        });
    })

    $('#addNoteForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addNoteForm');
    
        noteSetButtonBusy("#saveNote", true);

        let form_data = new FormData(form);
        form_data.append('file', $('#addNoteForm input[name="document"]')[0].files[0]); 
        form_data.append("content", addEditor.getData());
        axios({
            method: "post",
            url: route('admission.store.note'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            noteSetButtonBusy("#saveNote", false);
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
            noteSetButtonBusy("#saveNote", false);
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
    
        noteSetButtonBusy("#UpdateNote", true);

        let form_data = new FormData(form);
        form_data.append('file', $('#editNoteForm input[name="document"]')[0].files[0]); 
        form_data.append("content", editEditor.getData());
        axios({
            method: "post",
            url: route('admission.update.note'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            noteSetButtonBusy("#UpdateNote", false);
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
            noteSetButtonBusy("#UpdateNote", false);
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
