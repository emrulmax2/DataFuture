import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var CommunTemplateListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-LS").val() != "" ? $("#query-LS").val() : "";
        let status = $("#status-LS").val() != "" ? $("#status-LS").val() : "";
        
        let tableContent = new Tabulator("#CommunTemplateListTable", {
            ajaxURL: route("communication.template.list"),
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
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                },
                {
                    title: "Name",
                    field: "name",
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
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editTemplateModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
    if ($("#CommunTemplateListTable").length) {
        // Init Table
        CommunTemplateListTable.init();

        // Filter function
        function filterHTMLForm() {
            CommunTemplateListTable.init();
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
    const addTemplateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTemplateModal"));
    const editTemplateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editTemplateModal"));

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

    const addTemplateModalEl = document.getElementById('addTemplateModal')
    addTemplateModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addTemplateModal .acc__input-error').html('');
        $('#addTemplateModal input:not([type="radio"])').val('');
        $('#addTemplateModal input[type="radio"][value="1"]').prop('checked', true);

        $('#addTemplateForm .smsContentWrap').fadeOut('fast', function(){
            $('#addTemplateForm .smsContentWrap textarea').val('');
            $('#addTemplateForm .emailContentWrap').fadeIn();
            addEditor.setData('');
        })
        $('#addTemplateForm .sms_countr').html('160 / 1');
        $('#addTemplateForm .modal-content .smsWarning').remove();
    });

    const editTemplateModalEl = document.getElementById('editTemplateModal')
    editTemplateModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editTemplateModal .acc__input-error').html('');
        $('#editTemplateModal input:not([type="radio"])').val('');
        $('#addTemplateModal input[type="radio"][value="1"]').prop('checked', true);

        $('#editTemplateForm .smsContentWrap').fadeOut('fast', function(){
            $('#editTemplateForm .smsContentWrap textarea').val('');
            $('#editTemplateForm .emailContentWrap').fadeIn();
            editEditor.setData('');
        })
        $('#editTemplateForm .sms_countr').html('160 / 1');
        $('#editTemplateForm .modal-content .smsWarning').remove();
    });

    document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
        $('#confirmModal .agreeWith').attr('data-id', '0');
        $('#confirmModal .agreeWith').attr('data-action', 'none');
    });

    $('#addTemplateForm').on('change', '.templateType', function(){
        let templateType = $('#addTemplateForm input[name="type"]:checked').val();
        if(templateType == 2){
            $('#addTemplateForm .emailContentWrap').fadeOut('fast', function(){
                addEditor.setData('');
                $('#addTemplateForm .smsContentWrap').fadeIn();
                $('#addTemplateForm .smsContentWrap textarea').val('');
                $('#addTemplateForm .sms_countr').html('160 / 1');
            })
        }else{
            $('#addTemplateForm .smsContentWrap').fadeOut('fast', function(){
                $('#addTemplateForm .smsContentWrap textarea').val('');
                $('#addTemplateForm .emailContentWrap').fadeIn();
                addEditor.setData('');
            })
        }
        $('#addTemplateForm .modal-content .smsWarning').remove();
    });

    $('#editTemplateForm').on('change', '.templateType', function(){
        let templateType = $('#editTemplateForm input[name="type"]:checked').val();
        if(templateType == 2){
            $('#editTemplateForm .emailContentWrap').fadeOut('fast', function(){
                addEditor.setData('');
                $('#editTemplateForm .smsContentWrap').fadeIn();
                $('#editTemplateForm .smsContentWrap textarea').val('');
                $('#editTemplateForm .sms_countr').html('160 / 1');
            })
        }else{
            $('#editTemplateForm .smsContentWrap').fadeOut('fast', function(){
                $('#editTemplateForm .smsContentWrap textarea').val('');
                $('#editTemplateForm .emailContentWrap').fadeIn();
                addEditor.setData('');
            })
        }
        $('#editTemplateForm .modal-content .smsWarning').remove();
    });

    $('#sms_content').on('keyup', function(){
        var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
        if(chars > 0){
            if(chars >= maxlength && maxlength > 0){
                $('#addTemplateForm .modal-content .smsWarning').remove();
                $('#addTemplateForm .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
            }else{
                $('#addTemplateForm .modal-content .smsWarning').remove();
            }
            $('#addTemplateForm .sms_countr').html(remaining +' / '+messages);
        }else{
            $('#addTemplateForm .sms_countr').html('160 / 1');
            $('#addTemplateForm .modal-content .smsWarning').remove();
        }
    });

    $('#edit_sms_content').on('keyup', function(){
        var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
        if(chars > 0){
            if(chars >= maxlength && maxlength > 0){
                $('#addTemplateForm .modal-content .smsWarning').remove();
                $('#addTemplateForm .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
            }else{
                $('#addTemplateForm .modal-content .smsWarning').remove();
            }
            $('#addTemplateForm .sms_countr').html(remaining +' / '+messages);
        }else{
            $('#addTemplateForm .sms_countr').html('160 / 1');
            $('#addTemplateForm .modal-content .smsWarning').remove();
        }
    });

    $('#addTemplateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addTemplateForm');
    
        document.querySelector('#saveTemplate').setAttribute('disabled', 'disabled');
        document.querySelector("#saveTemplate svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('communication.template.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveTemplate').removeAttribute('disabled');
            document.querySelector("#saveTemplate svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addTemplateModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('System communication set successfully inserted.');
                });                
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            CommunTemplateListTable.init();
        }).catch(error => {
            document.querySelector('#saveTemplate').removeAttribute('disabled');
            document.querySelector("#saveTemplate svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addTemplateForm .${key}`).addClass('border-danger')
                        $(`#addTemplateForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#CommunTemplateListTable').on('click', '.edit_btn', function(){
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        axios({
            method: "get",
            url: route("communication.template.edit", recordId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                
                $('#editTemplateModal input[name="name"]').val(dataset.name ? dataset.name : '');
                $('#editTemplateModal input[type="radio"][value="'+dataset.type+'"]').prop('checked', true);
                if(dataset.type == 2){
                    $('#editTemplateModal .emailContentWrap').fadeOut('fast', function(){
                        editEditor.setData('');
                        $('#editTemplateModal .smsContentWrap').fadeIn();
                        $('#editTemplateModal .smsContentWrap textarea').val(dataset.content ? dataset.content : '');
                        $('#editTemplateModal .sms_countr').html('160 / 1');
                    })
                }else{
                    $('#editTemplateModal .smsContentWrap').fadeOut('fast', function(){
                        $('#editTemplateModal .smsContentWrap textarea').val('');
                        $('#editTemplateModal .emailContentWrap').fadeIn();
                        editEditor.setData(dataset.content ? dataset.content : '');
                        $('#editTemplateModal .sms_countr').html('160 / 1');
                    })
                }

                $('#editTemplateModal input[name="id"]').val(recordId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });


    $('#editTemplateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editTemplateForm');
    
        document.querySelector('#editTemplates').setAttribute('disabled', 'disabled');
        document.querySelector("#editTemplates svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('communication.template.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#editTemplates').removeAttribute('disabled');
            document.querySelector("#editTemplates svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editTemplateModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('System communication set successfully updated.');
                });                
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            CommunTemplateListTable.init();
        }).catch(error => {
            document.querySelector('#editTemplates').removeAttribute('disabled');
            document.querySelector("#editTemplates svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editTemplateForm .${key}`).addClass('border-danger')
                        $(`#editTemplateForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Delete Course
    $('#CommunTemplateListTable').on('click', '.delete_btn', function(){
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
    $('#CommunTemplateListTable').on('click', '.restore_btn', function(){
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
                url: route('communication.template.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('System Communication Template item successfully deleted!');
                    });
                }
                CommunTemplateListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('communication.template.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('System Communication Template item successfully restored!');
                    });
                }
                CommunTemplateListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    });

})()