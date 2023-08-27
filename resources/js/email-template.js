import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var emailTemplateListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-EMAIL").val() != "" ? $("#query-EMAIL").val() : "";
        let status = $("#status-EMAIL").val() != "" ? $("#status-EMAIL").val() : "";
        
        let tableContent = new Tabulator("#emailTemplateListTable", {
            ajaxURL: route("email.template.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [5, 10, 20, 30, 40],
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
                    title: "Template Title",
                    field: "email_title",
                    headerHozAlign: "left",
                },
                {
                    title: "Description",
                    field: "description",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "180",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editEmailModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
                            btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-EMAIL").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EMAIL").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EMAIL").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Email Template Details",
            });
        });

        $("#tabulator-export-html-EMAIL").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EMAIL").on("click", function (event) {
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
    if ($("#emailTemplateListTable").length) {
        // Init Table
        emailTemplateListTable.init();

        // Filter function
        function filterHTMLForm() {
            emailTemplateListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-EMAIL").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EMAIL").on("click", function (event) {
            $("#query-EMAIL").val("");
            $("#status-EMAIL").val("1");
            filterHTMLForm();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addEmailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmailModal"));
    const editEmailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmailModal"));

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

    const addEmailModalEl = document.getElementById('addEmailModal')
    addEmailModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addEmailModal .acc__input-error').html('');
        $('#addEmailModal input').val('');
        addEditor.setData('');
    });

    const editEmailModalEl = document.getElementById('editEmailModal')
    editEmailModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editEmailModal .acc__input-error').html('');
        $('#editEmailModal .modal-body input').val('');
        editEditor.setData('');
    });

    document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
        $('#confirmModal .agreeWith').attr('data-id', '0');
        $('#confirmModal .agreeWith').attr('data-action', 'none');
    });

    $('#addEmailForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addEmailForm');
    
        document.querySelector('#saveEmailSet').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEmailSet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('email.template.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveEmailSet').removeAttribute('disabled');
            document.querySelector("#saveEmailSet svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addEmailModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Email Template successfully inserted.');
                });                
                
                setTimeout(function(){
                    successModal.hide();
                }, 3000);
            }
            emailTemplateListTable.init();
        }).catch(error => {
            document.querySelector('#saveEmailSet').removeAttribute('disabled');
            document.querySelector("#saveEmailSet svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addEmailForm .${key}`).addClass('border-danger')
                        $(`#addEmailForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#emailTemplateListTable').on('click', '.edit_btn', function(){
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        axios({
            method: "get",
            url: route("email.template.edit", recordId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                
                $('#editEmailModal input[name="email_title"]').val(dataset.email_title ? dataset.email_title : '');
                editEditor.setData(dataset.description ? dataset.description : '');
                $('#editEmailModal input[name="id"]').val(recordId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });


    $('#editEmailForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editEmailForm');
    
        document.querySelector('#editEmailSet').setAttribute('disabled', 'disabled');
        document.querySelector("#editEmailSet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('email.template.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#editEmailSet').removeAttribute('disabled');
            document.querySelector("#editEmailSet svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editEmailModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Email Template successfully updated.');
                });                
                
                setTimeout(function(){
                    successModal.hide();
                }, 3000);
            }
            emailTemplateListTable.init();
        }).catch(error => {
            document.querySelector('#editEmailSet').removeAttribute('disabled');
            document.querySelector("#editEmailSet svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editEmailForm .${key}`).addClass('border-danger')
                        $(`#editEmailForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Delete Course
    $('#emailTemplateListTable').on('click', '.delete_btn', function(){
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
    $('#emailTemplateListTable').on('click', '.restore_btn', function(){
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
                url: route('email.template.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Email Template successfully deleted!');
                    });
                }
                emailTemplateListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('email.template.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Email Template successfully restored!');
                    });
                }
                emailTemplateListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    });

})()