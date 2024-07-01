import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var studentNotesListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let studentId = $("#studentNotesListTable").attr('data-student') != "" ? $("#studentNotesListTable").attr('data-student') : "0";
        let queryStr = $("#query-AN").val() != "" ? $("#query-AN").val() : "";
        let status = $("#status-AN").val() != "" ? $("#status-AN").val() : "1";
        let term = $("#term-SN").val() != "" ? $("#term-SN").val() : "";

        let tableContent = new Tabulator("#studentNotesListTable", {
            ajaxURL: route("student.note.list"),
            ajaxParams: { studentId: studentId, queryStr : queryStr, status : status, term : term},
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
                    width: "90",
                },
                {
                    title: "Term",
                    field: "term",
                    headerHozAlign: "left",
                    headerSort: false
                },
                {
                    title: "Opening Date",
                    field: "opening_date",
                    headerHozAlign: "left",
                    width: "150",
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
                },{
                    title: "Followed Up",
                    field: "followed_up",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        if(cell.getData().followed_up == 'yes'){
                            html += '<div>';
                                if(cell.getData().followed != ''){
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().followed+'</div>';
                                }
                                if(cell.getData().follow_up_start != '' || cell.getData().follow_up_end != ''){
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">';
                                        html += (cell.getData().follow_up_start != '' ? cell.getData().follow_up_start : '');
                                        html += (cell.getData().follow_up_end != '' ? ' - '+cell.getData().follow_up_end : '');
                                    html += '</div>';
                                }
                            html += '</div>';
                        }
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
                        if(cell.getData().student_document_id > 0){
                            btns +='<a data-id="'+cell.getData().student_document_id+'" href="javascript:void(0);" class="downloadDoc btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
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
                sheetName: "Student Note Details",
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
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: true,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    var termSN = new TomSelect('#term-SN', tomOptions);

    if ($("#studentNotesListTable").length) {
        // Init Table
        studentNotesListTable.init();

        // Filter function
        function filterHTMLFormAN() {
            studentNotesListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-AN").on("click", function (event) {
            filterHTMLFormAN();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-AN").on("click", function (event) {
            $("#query-AN").val("");
            $("#status-AN").val("1");
            termSN.clear(true);
            filterHTMLFormAN();
        });

    }

    var term_declaration_id = new TomSelect('#term_declaration_id', tomOptions);
    var edit_term_declaration_id = new TomSelect('#edit_term_declaration_id', tomOptions);
    var follow_up_by = new TomSelect('#follow_up_by', tomOptions);
    var edit_follow_up_by = new TomSelect('#edit_follow_up_by', tomOptions);

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addNoteModal"));
    const viewNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewNoteModal"));
    const editNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editNoteModal"));

    let addEditor;
    if($("#addEditor").length > 0){
        const el = document.getElementById('addEditor');
        ClassicEditor.create(el).then(newEditor => {
            addEditor = newEditor;
        }).catch((error) => {
            console.error(error);
        });
    }

    let editEditor;
    if($("#editEditor").length > 0){
        const el = document.getElementById('editEditor');
        ClassicEditor.create(el).then(newEditor => {
            editEditor = newEditor;
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
        term_declaration_id.clear(true);
        follow_up_by.clear(true);
    });

    const editNoteModalEl = document.getElementById('editNoteModal')
    editNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editNoteModal .acc__input-error').html('');
        $('#editNoteModal input[name="opening_date"]').val('');
        $('#editNoteModal input[name="document"]').val('');
        $('#editNoteModal #editNoteDocumentName').html('');
        $('#editNoteModal input[name="id"]').val('0');
        $('#editNoteModal .downloadExistAttachment').attr('href', '#').fadeOut();
        editEditor.setData('');
        edit_term_declaration_id.clear(true);
        edit_follow_up_by.clear(true);
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
    
    $('#addNoteForm').on('change', '[name="followed_up"]', function(){
        if($(this).prop('checked')){
            $('#addNoteForm .followedUpWrap').fadeIn('fast', function(){
                $('input', this).val('');
                follow_up_by.clear(true);
            });
        }else{
            $('#addNoteForm .followedUpWrap').fadeOut('fast', function(){
                $('input', this).val('');
                follow_up_by.clear(true);
            });
        }
    });
    
    $('#editNoteForm').on('change', '[name="followed_up"]', function(){
        if($(this).prop('checked')){
            $('#editNoteForm .followedUpWrap').fadeIn('fast', function(){
                $('input', this).val('');
                edit_follow_up_by.clear(true);
            });
        }else{
            $('#editNoteForm .followedUpWrap').fadeOut('fast', function(){
                $('input', this).val('');
                edit_follow_up_by.clear(true);
            });
        }
    });

    $('#studentNotesListTable').on('click', '.view_btn', function(e){
        var $btn = $(this);
        var noteId = $btn.attr('data-id');
        axios({
            method: "post",
            url: route('student.show.note'),
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
        axios({
            method: "post",
            url: route('student.store.note'),
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
                    $("#successModal .successModalDesc").html('Student Note successfully stored.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            studentNotesListTable.init();
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

    $('#studentNotesListTable').on('click', '.edit_btn', function(e){
        var $btn = $(this);
        var noteId = $btn.attr('data-id');
        axios({
            method: "post",
            url: route('student.get.note'),
            data: {noteId : noteId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            let dataset = response.data.res;
            editEditor.setData(dataset.note ? dataset.note : '');
            $('#editNoteModal [name="opening_date"]').val(dataset.opening_date ? dataset.opening_date : '');
            $('#editNoteModal input[name="id"]').val(noteId);
             
            if(dataset.term_declaration_id){
                edit_term_declaration_id.addItem(dataset.term_declaration_id, true)
            }else{
                edit_term_declaration_id.clear(true);
            }
            if(dataset.docURL != ''){
                $('#editNoteModal .downloadExistAttachment').attr('href', dataset.docURL).fadeIn();
            }else{
                $('#editNoteModal .downloadExistAttachment').attr('href', '#').fadeOut();
            }
            if(dataset.followed_up == 'yes'){
                $('#editNoteForm [name="followed_up"]').prop('checked', true);
                $('#editNoteForm .followedUpWrap').fadeIn('fast', function(){
                    $('#editNoteForm  [name="follow_up_start"]').val(dataset.follow_up_start ? dataset.follow_up_start : '');
                    $('#editNoteForm  [name="follow_up_end"]').val(dataset.follow_up_end ? dataset.follow_up_end : '');
                    edit_follow_up_by.addItem(dataset.follow_up_by, true);
                });
            }else{
                $('#editNoteForm [name="followed_up"]').prop('checked', false);
                $('#editNoteForm .followedUpWrap').fadeOut('fast', function(){
                    $('input', this).val('');
                    edit_follow_up_by.clear(true);
                });
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
        axios({
            method: "post",
            url: route('student.update.note'),
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
                    $("#successModal .successModalDesc").html('Student Note successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            studentNotesListTable.init();
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


    $('#studentNotesListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var noteId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Note from student list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', noteId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETENOT');
        });
    });

    $('#studentNotesListTable').on('click', '.restore_btn', function(e){
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
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETENOT'){
            axios({
                method: 'delete',
                url: route('student.destory.note'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    studentNotesListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student note successfully deleted.');
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
                url: route('student.resotore.note'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    studentNotesListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student note successfully resotred.');
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

    $('#studentNotesListTable').on('click', '.downloadDoc', function(e){
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

    $('#viewNoteModal').on('click', '.downloadDoc', function(e){
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


})();