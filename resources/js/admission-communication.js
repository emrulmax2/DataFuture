import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var applicantCommLetterListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#applicantCommLetterListTable").attr('data-applicant') != "" ? $("#applicantCommLetterListTable").attr('data-applicant') : "0";
        let queryStrCML = $("#query-CML").val() != "" ? $("#query-CML").val() : "";
        let statusCML = $("#status-CML").val() != "" ? $("#status-CML").val() : "1";

        let tableContent = new Tabulator("#applicantCommLetterListTable", {
            ajaxURL: route("admission.communication.letter.list"),
            ajaxParams: { applicantId: applicantId, queryStrCML : queryStrCML, statusCML : statusCML},
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
                    title: "Type",
                    field: "letter_type",
                    headerHozAlign: "left"
                },
                {
                    title: "Subject",
                    field: "letter_title",
                    headerHozAlign: "left"
                },
                {
                    title: "Signatory",
                    field: "signatory_name",
                    headerHozAlign: "left"
                },
                {
                    title: "Issued By",
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
                        if(cell.getData().docurl != ''){
                            btns += '<a href="'+cell.getData().docurl+'" download class="btn btn-twitter text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                        }
                        if (cell.getData().deleted_at == null) {
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
        $("#tabulator-export-csv-CML").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CML").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CML").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Admission Communication Details",
            });
        });

        $("#tabulator-export-html-CML").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CML").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
var applicantCommEmailListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#applicantCommEmailListTable").attr('data-applicant') != "" ? $("#applicantCommEmailListTable").attr('data-applicant') : "0";
        let queryStrCME = $("#query-CME").val() != "" ? $("#query-CME").val() : "";
        let statusCME = $("#status-CME").val() != "" ? $("#status-CME").val() : "1";

        let tableContent = new Tabulator("#applicantCommEmailListTable", {
            ajaxURL: route("admission.communication.mail.list"),
            ajaxParams: { applicantId: applicantId, queryStrCME : queryStrCME, statusCME : statusCME},
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
                    headerHozAlign: "left"
                },
                {
                    title: "From",
                    field: "smtp",
                    headerHozAlign: "left"
                },
                {
                    title: "Issued By",
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
        $("#tabulator-export-csv-CME").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CME").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CME").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Applicant Email Details",
            });
        });

        $("#tabulator-export-html-CME").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CME").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var applicantCommSMSListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#applicantCommSMSListTable").attr('data-applicant') != "" ? $("#applicantCommSMSListTable").attr('data-applicant') : "0";
        let queryStrCMS = $("#query-CMS").val() != "" ? $("#query-CMS").val() : "";
        let statusCMS = $("#status-CMS").val() != "" ? $("#status-CMS").val() : "1";

        let tableContent = new Tabulator("#applicantCommSMSListTable", {
            ajaxURL: route("admission.communication.sms.list"),
            ajaxParams: { applicantId: applicantId, queryStrCMS : queryStrCMS, statusCMS : statusCMS},
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
                sheetName: "Applicant SMS Details",
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
    if ($("#applicantCommSMSListTable").length) {
        // Init Table
        applicantCommSMSListTable.init();

        // Filter function
        function filterHTMLFormCMS() {
            applicantCommSMSListTable.init();
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

    if ($("#applicantCommEmailListTable").length) {
        // Init Table
        applicantCommEmailListTable.init();

        // Filter function
        function filterHTMLFormCME() {
            applicantCommEmailListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-CME").on("click", function (event) {
            filterHTMLFormCME();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CME").on("click", function (event) {
            $("#query-CME").val("");
            $("#status-CME").val("1");
            filterHTMLFormCME();
        });

    }

    if ($("#applicantCommLetterListTable").length) {
        // Init Table
        applicantCommLetterListTable.init();

        // Filter function
        function filterHTMLFormCML() {
            applicantCommLetterListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-CML").on("click", function (event) {
            filterHTMLFormCML();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CML").on("click", function (event) {
            $("#query-CML").val("");
            $("#status-CML").val("1");
            filterHTMLFormCML();
        });

    }

    let letterEditor;
    if($("#letterEditor").length > 0){
        const el = document.getElementById('letterEditor');
        ClassicEditor.create(el).then(newEditor => {
            letterEditor = newEditor;
        }).catch((error) => {
            console.error(error);
        });
    }

    let mailEditor;
    if($("#mailEditor").length > 0){
        const el = document.getElementById('mailEditor');
        ClassicEditor.create(el).then(newEditor => {
            mailEditor = newEditor;
        }).catch((error) => {
            console.error(error);
        });
    }


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addLetterModal"));
    const sendEmailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendEmailModal"));
    const smsSMSModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#smsSMSModal"));
    const viewCommunicationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewCommunicationModal"));

    const addLetterModalEl = document.getElementById('addLetterModal')
    addLetterModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addLetterModal .acc__input-error').html('');
        $('#addLetterModal .modal-body input').val('');
        $('#addLetterModal .modal-body select').val('');
        $('#addLetterModal .modal-footer input#is_send_email').prop('checked', true);
        $('#addLetterModal .letterEditorArea').fadeOut();
        letterEditor.setData('');
    });

    const sendEmailModalEl = document.getElementById('sendEmailModal')
    sendEmailModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#sendEmailModal .acc__input-error').html('');
        $('#sendEmailModal .modal-body input#sendMailsDocument').val('');
        $('#sendEmailModal .modal-body input, #sendEmailModal .modal-body select').val('');
        $('#addNoteModal .sendMailsDocumentNames').html('').fadeOut();
        mailEditor.setData('');
    });

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

    $('#sendEmailForm #sendMailsDocument').on('change', function(){
        var inputs = document.getElementById('sendMailsDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>'+name+'</div>';
        }

        $('#sendEmailForm .sendMailsDocumentNames').fadeIn().html(html);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
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

    $('#sendEmailForm [name="email_template_id"]').on('change', function(){
        var emailTemplateID = $(this).val();
        if(emailTemplateID != ''){
            axios({
                method: "post",
                url: route('admission.communication.get.mail.template'),
                data: {emailTemplateID : emailTemplateID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    if(response.data.row.description){
                        mailEditor.setData(response.data.row.description);
                    }else{
                        mailEditor.setData('');
                    }
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            mailEditor.setData('');
        }
    })

    $('#sendEmailForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('sendEmailForm');
    
        document.querySelector('#sendEmailBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sendEmailBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#sendEmailForm input#sendMailsDocument')[0].files[0]); 
        axios({
            method: "post",
            url: route('admission.communication.send.mail'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sendEmailBtn').removeAttribute('disabled');
            document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";
            //console.log(response.data.message);
            //return false;

            if (response.status == 200) {
                sendEmailModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Mail successfully sent to applicant.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 5000);
            }
            applicantCommEmailListTable.init();
        }).catch(error => {
            document.querySelector('#sendEmailBtn').removeAttribute('disabled');
            document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#sendEmailForm .${key}`).addClass('border-danger');
                        $(`#sendEmailForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#applicantCommEmailListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Mail from applicant list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETEMAIL');
        });
    });

    $('#applicantCommEmailListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this Mail from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTOREMAIL');
        });
    });

    $('#applicantCommEmailListTable').on('click', '.view_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        viewCommunicationModal.show();
        axios({
            method: 'post',
            url: route('admission.communication.mail.show'),
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
                url: route('admission.communication.get.sms.template'),
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
            url: route('admission.communication.send.sms'),
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
            applicantCommSMSListTable.init();
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

    $('#applicantCommSMSListTable').on('click', '.view_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        viewCommunicationModal.show();
        axios({
            method: 'post',
            url: route('admission.communication.sms.show'),
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

    $('#applicantCommSMSListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this SMS from applicant list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETESMS');
        });
    });

    $('#applicantCommSMSListTable').on('click', '.restore_btn', function(e){
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
        let applicant = $agreeBTN.attr('data-applicant');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEMAIL'){
            axios({
                method: 'delete',
                url: route('admission.communication.mail.destroy'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommEmailListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication Mail successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'DELETESMS'){
            axios({
                method: 'delete',
                url: route('admission.communication.sms.destroy'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommSMSListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication SMS successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'DELETELETTER'){
            axios({
                method: 'delete',
                url: route('admission.communication.letter.destroy'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommLetterListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication Letter successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTOREMAIL'){
            axios({
                method: 'post',
                url: route('admission.communication.mail.restore'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommEmailListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication Mail successfully resotred.');
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
                url: route('admission.communication.sms.restore'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommSMSListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication SMS successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORELETTER'){
            axios({
                method: 'post',
                url: route('admission.communication.letter.restore'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommLetterListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication Letter successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    });


    /* Letter Area */
    $('#addLetterModal #letter_set_id').on('change', function(){
        var letterSetId = $(this).val();
        if(letterSetId > 0){
            axios({
                method: 'post',
                url: route('admission.communication.get.letter.set'),
                data: {letterSetId : letterSetId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#addLetterModal .letterEditorArea').fadeIn('fast', function(){
                        var description = response.data.res.description ? response.data.res.description : '';
                        letterEditor.setData(description)
                    })
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            $('#addLetterModal .letterEditorArea').fadeOut('fast', function(){
                letterEditor.setData('')
            })
        }
    });

    $('#addLetterForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addLetterForm');
    
        document.querySelector('#sendLetterBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sendLetterBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('admission.communication.send.letter'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sendLetterBtn').removeAttribute('disabled');
            document.querySelector("#sendLetterBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addLetterModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Letter successfully generated and send.');
                    $("#successModal .successCloser").attr('data-action', 'DISMISS');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 5000);
            }
            applicantCommLetterListTable.init();
        }).catch(error => {
            document.querySelector('#sendLetterBtn').removeAttribute('disabled');
            document.querySelector("#sendLetterBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addLetterForm .${key}`).addClass('border-danger');
                        $(`#addLetterForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#applicantCommLetterListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Letter from applicant list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETELETTER');
        });
    });

    $('#applicantCommLetterListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this LETTER from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTORELETTER');
        });
    });
    /* Letter Area */

})();