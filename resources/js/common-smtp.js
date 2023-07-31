import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var smtpSettingsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#smtpSettingsListTable", {
            ajaxURL: route("common.smtp.list"),
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
                    width: "80",
                },
                {
                    title: "User",
                    field: "smtp_user",
                    headerHozAlign: "left",
                },
                {
                    title: "Password",
                    field: "smtp_pass",
                    headerHozAlign: "left",
                },
                {
                    title: "Host",
                    field: "smtp_host",
                    headerHozAlign: "left",
                },
                {
                    title: "Port",
                    field: "smtp_port",
                    headerHozAlign: "left",
                },
                {
                    title: "Encryption",
                    field: "smtp_encryption",
                    headerHozAlign: "left",
                },
                {
                    title: "Auth",
                    field: "smtp_authentication",
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
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editSmtpModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
                            btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +=
                                '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Document Settings Details",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {

    if ($("#smtpSettingsListTable").length) {
        // Init Table
        smtpSettingsListTable.init();

        // Filter function
        function filterHTMLForm() {
            smtpSettingsListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterHTMLForm();
        });
    }

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addSmtpModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSmtpModal"));
    const editSmtpModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSmtpModal"));
    let confModalDelTitle = 'Are you sure?';

    const addSmtpModalEl = document.getElementById('addSmtpModal')
    addSmtpModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addSmtpModal .acc__input-error').html('');
        $('#addSmtpModal input').val('');
        $('#addSmtpModal select').val('');
    });

    const editSmtpModalEl = document.getElementById('editSmtpModal')
    editSmtpModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editSmtpModal .acc__input-error').html('');
        $('#editSmtpModal input').val('');
        $('#editSmtpModal input[name="id"]').val('0');
        $('#editSmtpModal select').val('');
    });

    document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
        $('#confirmModal .agreeWith').attr('data-id', '0');
        $('#confirmModal .agreeWith').attr('data-action', 'none');
    });

    $('#smtpSettingsListTable').on('click', '.edit_btn', function(){
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        axios({
            method: "get",
            url: route("common.smtp.edit", recordId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                
                $('#editSmtpModal input[name="smtp_user"]').val(dataset.smtp_user ? dataset.smtp_user : '');
                $('#editSmtpModal input[name="smtp_pass"]').val(dataset.smtp_pass ? dataset.smtp_pass : '');
                $('#editSmtpModal input[name="smtp_host"]').val(dataset.smtp_host ? dataset.smtp_host : '');
                $('#editSmtpModal input[name="smtp_port"]').val(dataset.smtp_port ? dataset.smtp_port : '');
                $('#editSmtpModal select[name="smtp_encryption"]').val(dataset.smtp_encryption ? dataset.smtp_encryption : '');
                $('#editSmtpModal select[name="smtp_authentication"]').val(dataset.smtp_authentication ? dataset.smtp_authentication : '');
                
                $('#editSmtpModal input[name="id"]').val(recordId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    
    $('#editSmtpForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editSmtpForm');
        let editId = $('#editSmtpForm input[name="id"]').val();
    
        document.querySelector('#updateSMTP').setAttribute('disabled', 'disabled');
        document.querySelector("#updateSMTP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('common.smtp.update', editId),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateSMTP').removeAttribute('disabled');
            document.querySelector("#updateSMTP svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editSmtpModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Common SMTP successfully updated.');
                });                
                
                setTimeout(function(){
                    succModal.hide();
                }, 3000);
            }
            smtpSettingsListTable.init();
        }).catch(error => {
            document.querySelector('#updateSMTP').removeAttribute('disabled');
            document.querySelector("#updateSMTP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editSmtpForm .${key}`).addClass('border-danger')
                        $(`#editSmtpForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    
    $('#addSmtpForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addSmtpForm');
    
        document.querySelector('#saveSMTP').setAttribute('disabled', 'disabled');
        document.querySelector("#saveSMTP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('common.smtp.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveSMTP').removeAttribute('disabled');
            document.querySelector("#saveSMTP svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addSmtpModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Common SMTP successfully inserted.');
                });                
                
                setTimeout(function(){
                    succModal.hide();
                }, 3000);
            }
            smtpSettingsListTable.init();
        }).catch(error => {
            document.querySelector('#saveSMTP').removeAttribute('disabled');
            document.querySelector("#saveSMTP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addSmtpForm .${key}`).addClass('border-danger')
                        $(`#addSmtpForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Delete Course
    $('#smtpSettingsListTable').on('click', '.delete_btn', function(){
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
    $('#smtpSettingsListTable').on('click', '.restore_btn', function(){
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
                url: route('common.smtp.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Common SMTP item successfully deleted!');
                    });
                }
                smtpSettingsListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('common.smtp.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Common SMTP item successfully restored!');
                    });
                }
                smtpSettingsListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

})();