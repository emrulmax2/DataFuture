import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var smsTempalteListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-SMS").val() != "" ? $("#query-SMS").val() : "";
        let status = $("#status-SMS").val() != "" ? $("#status-SMS").val() : "";
        let phase = $("#phase-SMS").val() != "" ? $("#phase-SMS").val() : "";
        
        let tableContent = new Tabulator("#smsTempalteListTable", {
            ajaxURL: route("sms.template.list"),
            ajaxParams: { querystr: querystr, status: status, phase: phase },
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
                    title: "Template Title",
                    field: "sms_title",
                    headerHozAlign: "left",
                },
                {
                    title: "Admission",
                    field: "admission",
                    headerHozAlign: "left",
                    width: "120",
                    formatter(cell, formatterParams) {
                        return '<div class="form-check form-switch"><input data-phase="admission" data-id="'+cell.getData().id+'" '+(cell.getData().admission == 1 ? 'Checked' : '')+' value="'+cell.getData().admission+'" type="checkbox" class="updatePhase form-check-input"> </div>';
                    }
                },
                {
                    title: "Live",
                    field: "live",
                    headerHozAlign: "left",
                    width: "120",
                    formatter(cell, formatterParams) {
                        return '<div class="form-check form-switch"><input data-phase="live" data-id="'+cell.getData().id+'" '+(cell.getData().live == 1 ? 'Checked' : '')+' value="'+cell.getData().live+'" type="checkbox" class="updatePhase form-check-input"> </div>';
                    }
                },
                {
                    title: "HR",
                    field: "hr",
                    width: "120",
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
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editSmsModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-SMS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-SMS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-SMS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "SMS Template Details",
            });
        });

        $("#tabulator-export-html-SMS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-SMS").on("click", function (event) {
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
    if ($("#smsTempalteListTable").length) {
        // Init Table
        smsTempalteListTable.init();

        // Filter function
        function filterHTMLForm() {
            smsTempalteListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-SMS").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-SMS").on("click", function (event) {
            $("#query-SMS").val("");
            $("#status-SMS").val("1");
            $("#phase-SMS").val("");
            filterHTMLForm();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addSmsModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSmsModal"));
    const editSmsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSmsModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    
    const addSmsModalEl = document.getElementById('addSmsModal')
    addSmsModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addSmsModal .acc__input-error').html('');
        $('#addSmsModal .modal-body input:not([type="checkbox"]), #addSmsModal .modal-body textarea').val('');
        $('#addSmsModal .phaseCheckboxs').prop('checked', false);
        $('#addSmsModal #status').prop('checked', true);
    });

    const editSmsModalEl = document.getElementById('editSmsModal')
    editSmsModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editSmsModal .acc__input-error').html('');
        $('#editSmsModal .modal-body input:not([type="checkbox"]), #editSmsModal .modal-body textarea').val('');
        $('#editSmsModal .phaseCheckboxs').prop('checked', false);
        $('#editSmsModal #edit_status').prop('checked', false);
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
    
    $('#addSmsTextArea').on('keyup', function(){
        var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
        if(chars > 0){
            if(chars >= maxlength && maxlength > 0){
                $('#addSmsModal .modal-content .smsWarning').remove();
                $('#addSmsModal .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
            }else{
                $('#addSmsModal .modal-content .smsWarning').remove();
            }
            $('#addSmsModal .sms_countr').html(remaining +' / '+messages);
        }else{
            $('#addSmsModal .sms_countr').html('160 / 1');
            $('#addSmsModal .modal-content .smsWarning').remove();
        }
    });

    $('#editSmsTextArea').on('keyup', function(){
        var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
        if(chars > 0){
            if(chars >= maxlength && maxlength > 0){
                $('#editSmsModal .modal-content .smsWarning').remove();
                $('#editSmsModal .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
            }else{
                $('#editSmsModal .modal-content .smsWarning').remove();
            }
            $('#editSmsModal .sms_countr').html(remaining +' / '+messages);
        }else{
            $('#editSmsModal .sms_countr').html('160 / 1');
            $('#editSmsModal .modal-content .smsWarning').remove();
        }
    });

    document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
        $('#confirmModal .agreeWith').attr('data-id', '0');
        $('#confirmModal .agreeWith').attr('data-action', 'none');
    });

    $('#addSmsForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addSmsForm');
    
        document.querySelector('#saveSmsSet').setAttribute('disabled', 'disabled');
        document.querySelector("#saveSmsSet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('sms.template.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveSmsSet').removeAttribute('disabled');
            document.querySelector("#saveSmsSet svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addSmsModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('SMS Template successfully inserted.');
                });                
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            smsTempalteListTable.init();
        }).catch(error => {
            document.querySelector('#saveSmsSet').removeAttribute('disabled');
            document.querySelector("#saveSmsSet svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addSmsForm .${key}`).addClass('border-danger')
                        $(`#addSmsForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#smsTempalteListTable').on('click', '.edit_btn', function(){
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        axios({
            method: "get",
            url: route("sms.template.edit", recordId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                
                $('#editSmsModal input[name="sms_title"]').val(dataset.sms_title ? dataset.sms_title : '');
                $('#editSmsModal textarea[name="description"]').val(dataset.description ? dataset.description : '');
                $('#editSmsModal input[name="id"]').val(recordId);

                if(dataset.admission == 1){
                    $('#editSmsModal #edit_phase_admission').prop('checked', true);
                }else{
                    $('#editSmsModal #edit_phase_admission').prop('checked', false);
                }
                if(dataset.live == 1){
                    $('#editSmsModal #edit_phase_live').prop('checked', true);
                }else{
                    $('#editSmsModal #edit_phase_live').prop('checked', false);
                }
                if(dataset.hr == 1){
                    $('#editSmsModal #edit_phase_hr').prop('checked', true);
                }else{
                    $('#editSmsModal #edit_phase_hr').prop('checked', false);
                }
                if(dataset.status == 1){
                    $('#editSmsModal #edit_status').prop('checked', true);
                }else{
                    $('#editSmsModal #edit_status').prop('checked', false);
                }
                

                let tthisSMS = $("#editSmsTextArea");
                let maxlength = (tthisSMS.attr('maxlength') > 0 && tthisSMS.attr('maxlength') != '' ? tthisSMS.attr('maxlength') : 0);
                let chars = dataset.description.length * 1,
                    smsCount = Math.ceil(chars / 160),
                    remaining = smsCount * 160 - (chars % (smsCount * 160) || smsCount * 160);
                if(chars > 0){
                    if(chars >= maxlength && maxlength > 0){
                        $('#editSmsModal .modal-content .smsWarning').remove();
                        $('#editSmsModal .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
                    }else{
                        $('#editSmsModal .modal-content .smsWarning').remove();
                    }
                    $('#editSmsModal .sms_countr').html(remaining +' / '+smsCount);
                }else{
                    $('#editSmsModal .sms_countr').html('160 / 1');
                    $('#editSmsModal .modal-content .smsWarning').remove();
                }
            }
        }).catch((error) => {
            console.log(error);
        });
    });


    $('#editSmsForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editSmsForm');
    
        document.querySelector('#editSmsSet').setAttribute('disabled', 'disabled');
        document.querySelector("#editSmsSet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('sms.template.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#editSmsSet').removeAttribute('disabled');
            document.querySelector("#editSmsSet svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editSmsModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('SMS Template successfully updated.');
                });                
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            smsTempalteListTable.init();
        }).catch(error => {
            document.querySelector('#editSmsSet').removeAttribute('disabled');
            document.querySelector("#editSmsSet svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editSmsForm .${key}`).addClass('border-danger')
                        $(`#editSmsForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Delete Course
    $('#smsTempalteListTable').on('click', '.delete_btn', function(){
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
    $('#smsTempalteListTable').on('click', '.restore_btn', function(){
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
    $('#smsTempalteListTable').on('click', '.status_updater', function(){
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
    $('#smsTempalteListTable').on('click', '.updatePhase', function(){
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
                url: route('sms.template.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('SMS Template item successfully deleted!');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                smsTempalteListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('sms.template.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('SMS Template item successfully restored!');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                smsTempalteListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'CHANGESTAT'){
            axios({
                method: 'post',
                url: route('sms.template.update.status'),
                data: {row_id : recordID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('SMS Template status successfully updated!');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                smsTempalteListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'CHANGEPHS'){
            axios({
                method: 'post',
                url: route('sms.template.update.phase.status'),
                data: {row_id : recordID, phase : phase},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('SMS Template Phase status successfully updated!');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                smsTempalteListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    });

})()