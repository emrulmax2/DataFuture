import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var studentCommSMSListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let studentId = $("#studentCommSMSListTable").attr('data-student') != "" ? $("#studentCommSMSListTable").attr('data-student') : "0";
        let queryStrCMS = $("#query-CMS").val() != "" ? $("#query-CMS").val() : "";
        let statusCMS = $("#status-CMS").val() != "" ? $("#status-CMS").val() : "1";

        let tableContent = new Tabulator("#studentCommSMSListTable", {
            ajaxURL: route("student.sms.list"),
            ajaxParams: { studentId: studentId, queryStrCMS : queryStrCMS, statusCMS : statusCMS},
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
                    title: "Subject",
                    field: "subject",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += cell.getData().subject;
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Template",
                    field: "template",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += cell.getData().template;
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Issued By",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: "180",
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
                    width: "120",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" data-tw-toggle="modal" data-tw-target="#viewCommunicationModal"  class="view_btn btn btn-twitter text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="eye-off" class="w-4 h-4"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-CMS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CMS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CMS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Student SMS Details",
            });
        });

        $("#tabulator-export-html-CMS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CMS").on("click", function (event) {
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
    if ($("#studentCommSMSListTable").length) {
        // Init Table
        studentCommSMSListTable.init();

        // Filter function
        function filterHTMLFormCMS() {
            studentCommSMSListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-CMS").on("click", function (event) {
            filterHTMLFormCMS();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CMS").on("click", function (event) {
            $("#query-CMS").val("");
            $("#status-CMS").val("1");
            filterHTMLFormCMS();
        });

    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const smsSMSModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#smsSMSModal"));
    const viewCommunicationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewCommunicationModal"));

    const smsSMSModalEl = document.getElementById('smsSMSModal')
    smsSMSModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#smsSMSModal .acc__input-error').html('');
        $('#smsSMSModal .modal-body input, #smsSMSModal .modal-body textarea').val('');
    });

    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
    });

    const viewCommunicationModalEl = document.getElementById('viewCommunicationModal')
    viewCommunicationModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#viewCommunicationModal .modal-body").html('');
        $('#viewCommunicationModal .modal-header h2').html('View Communication');
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

    $('#smsTextArea').on('keyup', function(){
        var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
        if(chars > 0){
            if(chars >= maxlength && maxlength > 0){
                $('#smsSMSModal .modal-content .smsWarning').remove();
                $('#smsSMSModal .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
            }else{
                $('#smsSMSModal .modal-content .smsWarning').remove();
            }
            $('#smsSMSModal .sms_countr').html(remaining +' / '+messages);
        }else{
            $('#smsSMSModal .sms_countr').html('160 / 1');
            $('#smsSMSModal .modal-content .smsWarning').remove();
        }
    });


    $('#smsSMSForm [name="sms_template_id"]').on('change', function(){
        var smsTemplateId = $(this).val();
        if(smsTemplateId != ''){
            axios({
                method: "post",
                url: route('student.get.sms.template'),
                data: {smsTemplateId : smsTemplateId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('#smsSMSForm #smsTextArea').val(response.data.row.description ? response.data.row.description : '').trigger('keyup');
            }).catch(error => {
                //console.log('error');
            })
        }else{
            $('#smsSMSForm #smsTextArea').val('');
            $('#smsSMSModal .sms_countr').html('160 / 1');
        }
    })

    $('#smsSMSForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('smsSMSForm');
    
        document.querySelector('#sendSMSBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sendSMSBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.send.sms'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sendSMSBtn').removeAttribute('disabled');
            document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                //console.log(response.data);
                smsSMSModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.message);
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 5000);
            }
            studentCommSMSListTable.init();
        }).catch(error => {
            document.querySelector('#sendSMSBtn').removeAttribute('disabled');
            document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#smsSMSForm .${key}`).addClass('border-danger');
                        $(`#smsSMSForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#studentCommSMSListTable').on('click', '.view_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        viewCommunicationModal.show();
        axios({
            method: 'post',
            url: route('student.sms.show'),
            data: {recordId : recordId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#viewCommunicationModal .modal-header h2').html(response.data.heading);
                $('#viewCommunicationModal .modal-body').html(response.data.html);

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error =>{
            console.log(error)
        });
    });

    $('#studentCommSMSListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this SMS from student list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETESMS');
        });
    });

    $('#studentCommSMSListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this SMS from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTORESMS');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETESMS'){
            axios({
                method: 'delete',
                url: route('student.sms.destroy'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    studentCommSMSListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student Communication SMS successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORESMS'){
            axios({
                method: 'post',
                url: route('student.sms.restore'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    studentCommSMSListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student Communication SMS successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    });

})();