import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var letterSettingsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-LS").val() != "" ? $("#query-LS").val() : "";
        let status = $("#status-LS").val() != "" ? $("#status-LS").val() : "";
        let phase = $("#phase-LS").val() != "" ? $("#phase-LS").val() : "";
        
        let tableContent = new Tabulator("#letterSettingsListTable", {
            ajaxURL: route("letter.set.list"),
            ajaxParams: { querystr: querystr, status: status, phase : phase },
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
                    width: "70",
                },
                {
                    title: "Letter Type",
                    field: "letter_type",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        return '<a href="'+route("letter.set.edit", cell.getData().id)+'" target="_blank" class="font-medium text-primary underline">'+cell.getData().letter_type+'</a>';
                    }
                },
                {
                    title: "Letter Title",
                    field: "letter_title",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        return '<a href="'+route("letter.set.edit", cell.getData().id)+'" target="_blank" class="font-medium text-primary underline">'+cell.getData().letter_title+'</a>';
                    }
                },
                {
                    title: "Document Requests",
                    field: "document_request",
                    headerHozAlign: "left",
                    width: "100",
                    formatter(cell, formatterParams) {
                        return '<div class="form-check form-switch"><input data-phase="document_request" data-id="'+cell.getData().id+'" '+(cell.getData().document_request == 1 ? 'Checked' : '')+' value="'+cell.getData().document_request+'" type="checkbox" class="updatePhase form-check-input"> </div>';
                    }
                },
                {
                    title: "Admission",
                    field: "admission",
                    headerHozAlign: "left",
                    width: "100",
                    formatter(cell, formatterParams) {
                        return '<div class="form-check form-switch"><input data-phase="admission" data-id="'+cell.getData().id+'" '+(cell.getData().admission == 1 ? 'Checked' : '')+' value="'+cell.getData().admission+'" type="checkbox" class="updatePhase form-check-input"> </div>';
                    }
                },
                {
                    title: "Live",
                    field: "live",
                    headerHozAlign: "left",
                    width: "100",
                    formatter(cell, formatterParams) {
                        return '<div class="form-check form-switch"><input data-phase="live" data-id="'+cell.getData().id+'" '+(cell.getData().live == 1 ? 'Checked' : '')+' value="'+cell.getData().live+'" type="checkbox" class="updatePhase form-check-input"> </div>';
                    }
                },
                {
                    title: "HR",
                    field: "hr",
                    width: "100",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        return '<div class="form-check form-switch"><input data-phase="hr" data-id="'+cell.getData().id+'" '+(cell.getData().hr == 1 ? 'Checked' : '')+' value="'+cell.getData().hr+'" type="checkbox" class="updatePhase form-check-input"> </div>';
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    width: "120",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().status == 1 ? 'Checked' : '')+' value="'+cell.getData().active+'" type="checkbox" class="status_updater form-check-input"> </div>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "100",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editLetterModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            //btns +='<a href="'+route("letter.set.edit", cell.getData().id)+'" class="btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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

        // Export
        $("#tabulator-export-csv-LS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx-LS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Letter Set Details",
            });
        });

        // Print
        $("#tabulator-print-LS").on("click", function (event) {
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
    if ($("#letterSettingsListTable").length) {
        // Init Table
        letterSettingsListTable.init();

        // Filter function
        function filterHTMLForm() {
            letterSettingsListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-LS").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-LS").on("click", function (event) {
            $("#query-LS").val("");
            $("#status-LS").val("1");
            $("#phase-LS").val("");
            filterHTMLForm();
        });

        $('#query-LS').on('keypress', function(e){
            var keycode = e.keyCode || e.which;
            if(keycode == 13) {
                filterHTMLForm();
                return false;
            }
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addLetterModal"));
    const editLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editLetterModal"));

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

    const addLetterModalEl = document.getElementById('addLetterModal')
    addLetterModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addLetterModal .acc__input-error').html('');
        $('#addLetterModal input:not([type="checkbox"])').val('');
        $('#addLetterModal .phaseCheckboxs').prop('checked', false);
        $('#addLetterModal #status').prop('checked', true);
        addEditor.setData('');
    });

    const editLetterModalEl = document.getElementById('editLetterModal')
    editLetterModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editLetterModal .acc__input-error').html('');
        $('#editLetterModal .modal-body input:not([type="checkbox"])').val('');
        $('#editLetterModal .phaseCheckboxs').prop('checked', false);
        $('#addLetterModal #edit_status').prop('checked', false);
        editEditor.setData('');
    });

    document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
        $('#confirmModal .agreeWith').attr('data-id', '0');
        $('#confirmModal .agreeWith').attr('data-phase', '');
        $('#confirmModal .agreeWith').attr('data-action', 'none');
    });

    $('#addLetterForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addLetterForm');
    
        document.querySelector('#saveLetterSet').setAttribute('disabled', 'disabled');
        document.querySelector("#saveLetterSet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append("description", addEditor.getData());
        axios({
            method: "post",
            url: route('letter.set.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveLetterSet').removeAttribute('disabled');
            document.querySelector("#saveLetterSet svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addLetterModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Letter set successfully inserted.');
                });                
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            letterSettingsListTable.init();
        }).catch(error => {
            document.querySelector('#saveLetterSet').removeAttribute('disabled');
            document.querySelector("#saveLetterSet svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addLetterForm .${key}`).addClass('border-danger')
                        $(`#addLetterForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#letterSettingsListTable').on('click', '.edit_btn', function(){
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        axios({
            method: "get",
            url: route("letter.set.get.row", recordId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                
                $('#editLetterModal input[name="letter_type"]').val(dataset.letter_type ? dataset.letter_type : '');
                $('#editLetterModal input[name="letter_title"]').val(dataset.letter_title ? dataset.letter_title : '');
                editEditor.setData(dataset.description ? dataset.description : '');
                $('#editLetterModal input[name="id"]').val(recordId);

                if(dataset.admission == 1){
                    $('#editLetterModal #edit_phase_admission').prop('checked', true);
                }else{
                    $('#editLetterModal #edit_phase_admission').prop('checked', false);
                }
                if(dataset.live == 1){
                    $('#editLetterModal #edit_phase_live').prop('checked', true);
                }else{
                    $('#editLetterModal #edit_phase_live').prop('checked', false);
                }
                if(dataset.hr == 1){
                    $('#editLetterModal #edit_phase_hr').prop('checked', true);
                }else{
                    $('#editLetterModal #edit_phase_hr').prop('checked', false);
                }
                if(dataset.status == 1){
                    $('#editLetterModal #edit_status').prop('checked', true);
                }else{
                    $('#editLetterModal #edit_status').prop('checked', false);
                }
            }
        }).catch((error) => {
            console.log(error);
        });
    });


    $('#editLetterForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editLetterForm');
    
        document.querySelector('#editLetterSet').setAttribute('disabled', 'disabled');
        document.querySelector("#editLetterSet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append("description", editEditor.getData());
        axios({
            method: "post",
            url: route('letter.set.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#editLetterSet').removeAttribute('disabled');
            document.querySelector("#editLetterSet svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editLetterModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Letter set successfully updated.');
                });                
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            letterSettingsListTable.init();
        }).catch(error => {
            document.querySelector('#editLetterSet').removeAttribute('disabled');
            document.querySelector("#editLetterSet svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editLetterForm .${key}`).addClass('border-danger')
                        $(`#editLetterForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Delete Course
    $('#letterSettingsListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? Click on agree btn to continue.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Restore Course
    $('#letterSettingsListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let dataID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree btn to continue.');
            $('#confirmModal .agreeWith').attr('data-id', dataID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
        });
    });

    // Update Status
    $('#letterSettingsListTable').on('click', '.status_updater', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTAT');
        });
    });

    // Update Phase
    $('#letterSettingsListTable').on('click', '.updatePhase', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');
        let phase = $statusBTN.attr('data-phase');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to change phase status of this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-phase', phase);
            $('#confirmModal .agreeWith').attr('data-action', 'CHANGEPHS');
        });
    });


    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');
        let phase = $agreeBTN.attr('data-phase');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('letter.set.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Letter Set item successfully deleted!');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                letterSettingsListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('letter.set.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Letter Set item successfully restored!');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                letterSettingsListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'CHANGESTAT'){
            axios({
                method: 'post',
                url: route('letter.set.update.status'),
                data: {row_id : recordID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Letter Set status successfully updated!');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                letterSettingsListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'CHANGEPHS'){
            axios({
                method: 'post',
                url: route('letter.set.update.phase.status'),
                data: {row_id : recordID, phase : phase},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Letter Set Phase status successfully updated!');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                letterSettingsListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    });

})()