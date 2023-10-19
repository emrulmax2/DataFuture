import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var letterSettingsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-LS").val() != "" ? $("#query-LS").val() : "";
        let status = $("#status-LS").val() != "" ? $("#status-LS").val() : "";
        
        let tableContent = new Tabulator("#letterSettingsListTable", {
            ajaxURL: route("letter.set.list"),
            ajaxParams: { querystr: querystr, status: status },
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
                    width: "120",
                },
                {
                    title: "Letter Type",
                    field: "letter_type",
                    headerHozAlign: "left",
                },
                {
                    title: "Letter Title",
                    field: "letter_title",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editLetterModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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

        $("#tabulator-export-json-LS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-LS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Letter Set Details",
            });
        });

        $("#tabulator-export-html-LS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
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
            filterHTMLForm();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addLetterModal"));
    const editLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editLetterModal"));

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
        ClassicEditor.create(el, {
            toolbar: {
                items: [
                    'undo', 'redo',
                    '|', 'heading',
                    '|', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
                    '|', 'bold', 'italic', 'strikethrough', 'subscript', 'superscript', 'code',
                    '|', 'link', 'uploadImage', 'blockQuote', 'codeBlock',
                    '|', 'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent'
                ],
                shouldNotGroupWhenFull: false
            }
        }).then(newEditor => {
            editEditor = newEditor;
        }).catch((error) => {
            console.error(error);
        });
    }

    const addLetterModalEl = document.getElementById('addLetterModal')
    addLetterModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addLetterModal .acc__input-error').html('');
        $('#addLetterModal input').val('');
        addEditor.setData('');
    });

    const editLetterModalEl = document.getElementById('editLetterModal')
    editLetterModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editLetterModal .acc__input-error').html('');
        $('#editLetterModal .modal-body input').val('');
        editEditor.setData('');
    });

    document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
        $('#confirmModal .agreeWith').attr('data-id', '0');
        $('#confirmModal .agreeWith').attr('data-action', 'none');
    });

    $('#addLetterForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addLetterForm');
    
        document.querySelector('#saveLetterSet').setAttribute('disabled', 'disabled');
        document.querySelector("#saveLetterSet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
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
                }, 3000);
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
            url: route("letter.set.edit", recordId),
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
                }, 3000);
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


    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

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
                }
                letterSettingsListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    });

})()