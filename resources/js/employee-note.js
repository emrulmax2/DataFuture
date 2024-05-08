import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var employeeNotesListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let employeeId = $("#employeeNotesListTable").attr('data-employee') != "" ? $("#employeeNotesListTable").attr('data-employee') : "0";
        let queryStr = $("#query-EN").val() != "" ? $("#query-EN").val() : "";
        let status = $("#status-EN").val() != "" ? $("#status-EN").val() : "1";

        let tableContent = new Tabulator("#employeeNotesListTable", {
            ajaxURL: route("employee.note.list"),
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
                        if(cell.getData().employee_document_id > 0){
                            btns +='<a data-id="'+cell.getData().employee_document_id+'" href="javascript:void(0);" class="downloadDoc btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                        }
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" data-tw-toggle="modal" data-tw-target="#viewEmpNoteModal"  class="view_btn btn btn-twitter text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="presentation" class="w-4 h-4"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" data-tw-toggle="modal" data-tw-target="#editEmpNoteModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-EN").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EN").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EN").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Employee Note Details",
            });
        });

        $("#tabulator-export-html-EN").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EN").on("click", function (event) {
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
    if ($("#employeeNotesListTable").length) {
        // Init Table
        employeeNotesListTable.init();

        // Filter function
        function filterHTMLFormEN() {
            employeeNotesListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-EN").on("click", function (event) {
            filterHTMLFormEN();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EN").on("click", function (event) {
            $("#query-EN").val("");
            $("#status-EN").val("1");
            filterHTMLFormEN();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addEmpNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmpNoteModal"));
    const viewEmpNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewEmpNoteModal"));
    const editEmpNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmpNoteModal"));

    let addEmpNoteEditor;
    if($("#addEmpNoteEditor").length > 0){
        const el = document.getElementById('addEmpNoteEditor');
        ClassicEditor.create(el).then(newEditor => {
            addEmpNoteEditor = newEditor;
        }).catch((error) => {
            console.error(error);
        });
    }

    let editEmpNoteEditor;
    if($("#editEmpNoteEditor").length > 0){
        const el = document.getElementById('editEmpNoteEditor');
        ClassicEditor.create(el).then(newEditor => {
            editEmpNoteEditor = newEditor;
        }).catch((error) => {
            console.error(error);
        });
    }

    const addNoteModalEl = document.getElementById('addEmpNoteModal')
    addNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addEmpNoteModal .acc__input-error').html('');
        $('#addEmpNoteModal input[name="document"]').val('');
        $('#addEmpNoteModal #addEmpNoteDocument').html('');
        addEmpNoteEditor.setData('');
    });

    const editNoteModalEl = document.getElementById('editEmpNoteModal')
    editNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editEmpNoteModal .acc__input-error').html('');
        $('#editEmpNoteModal input[name="opening_date"]').val('');
        $('#editEmpNoteModal input[name="document"]').val('');
        $('#editEmpNoteModal #editEmpNoteDocument').html('');
        $('#editEmpNoteModal input[name="id"]').val('0');
        $('#editEmpNoteModal .downloadExistAttachment').attr('href', '#').fadeOut();
        editEmpNoteEditor.setData('');
    });

    const viewNoteModalEl = document.getElementById('viewEmpNoteModal')
    viewNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#viewEmpNoteModal .modal-body').html('');
        $('#viewEmpNoteModal .modal-footer .footerBtns').html('');
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
    
    $('#addEmpNoteForm').on('change', '#addEmpNoteDocument', function(){
        showFileName('addEmpNoteDocument', 'addEmpNoteDocumentName');
    });
    
    $('#editEmpNoteForm').on('change', '#editEmpNoteDocument', function(){
        showFileName('editEmpNoteDocument', 'editEmpNoteDocumentName');
    });

    function showFileName(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = fileInput.files[0].name;
        namePreview.innerText = fileName;
        return false;
    };

    $('#employeeNotesListTable').on('click', '.view_btn', function(e){
        var $btn = $(this);
        var noteId = $btn.attr('data-id');
        axios({
            method: "post",
            url: route('employee.show.note'),
            data: {noteId : noteId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#viewEmpNoteModal .modal-body').html(response.data.message);
            if(response.data.btns != ''){
                $('#viewEmpNoteModal .modal-footer .footerBtns').html(response.data.btns);
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

    $('#addEmpNoteForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addEmpNoteForm');
    
        document.querySelector('#saveEmpNote').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEmpNote svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addEmpNoteForm input[name="document"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('employee.store.note'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveEmpNote').removeAttribute('disabled');
            document.querySelector("#saveEmpNote svg").style.cssText = "display: none;";
            //console.log(response.data.message);
            //return false;

            if (response.status == 200) {
                addEmpNoteModal.hide();

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
            employeeNotesListTable.init();
        }).catch(error => {
            document.querySelector('#saveEmpNote').removeAttribute('disabled');
            document.querySelector("#saveEmpNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addEmpNoteForm .${key}`).addClass('border-danger');
                        $(`#addEmpNoteForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#employeeNotesListTable').on('click', '.edit_btn', function(e){
        var $btn = $(this);
        var noteId = $btn.attr('data-id');
        axios({
            method: "post",
            url: route('employee.get.note'),
            data: {noteId : noteId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            let dataset = response.data.res;
            editEmpNoteEditor.setData(dataset.note ? dataset.note : '');
            $('#editEmpNoteModal [name="opening_date"]').val(dataset.opening_date ? dataset.opening_date : '');
            $('#editEmpNoteModal input[name="id"]').val(noteId);
            if(dataset.docURL != ''){
                $('#editEmpNoteModal .downloadExistAttachment').attr('href', dataset.docURL).fadeIn();
            }else{
                $('#editEmpNoteModal .downloadExistAttachment').attr('href', '#').fadeOut();
            }
        }).catch(error => {
            console.log('error');
        });
    });

    $('#editEmpNoteForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editEmpNoteForm');
    
        document.querySelector('#updateEmpNote').setAttribute('disabled', 'disabled');
        document.querySelector("#updateEmpNote svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#editEmpNoteForm input[name="document"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('employee.update.note'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateEmpNote').removeAttribute('disabled');
            document.querySelector("#updateEmpNote svg").style.cssText = "display: none;";
            //console.log(response.data.message);
            //return false;

            if (response.status == 200) {
                editEmpNoteModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Employee Note successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            employeeNotesListTable.init();
        }).catch(error => {
            document.querySelector('#updateEmpNote').removeAttribute('disabled');
            document.querySelector("#updateEmpNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editEmpNoteForm .${key}`).addClass('border-danger');
                        $(`#editEmpNoteForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#employeeNotesListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var noteId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Note from employee list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', noteId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETENOT');
        });
    });

    $('#employeeNotesListTable').on('click', '.restore_btn', function(e){
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
        let employee = $agreeBTN.attr('data-employee');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETENOT'){
            axios({
                method: 'delete',
                url: route('employee.destory.note'),
                data: {employee : employee, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    employeeNotesListTable.init();

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
                url: route('employee.restore.note'),
                data: {employee : employee, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    employeeNotesListTable.init();

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

    $('#employeeNotesListTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('employee.note.download.url'),
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