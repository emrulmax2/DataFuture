import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";

("use strict");
var communicationStudentListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let plans = $("#communicationStudentListTable").attr('data-plans') ? $("#communicationStudentListTable").attr('data-plans') : '';
        let tableContent = new Tabulator("#communicationStudentListTable", {
            ajaxURL: route("bulk.communication.student.list"),
            ajaxParams: { plans: plans },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 100,
            paginationSizeSelector: [true, 50, 100, 200, 300, 500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No assigned students were found under selected class plans.",
            selectable:true,
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "left", 
                    headerHozAlign: "left",
                    width: "60",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, cell){
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: "Student ID",
                    field: "registration_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '<div class="break-all whitespace-normal">';
                                html += cell.getData().registration_no;
                                html += '<input type="hidden" class="student_ids" name="student_ids[]" value="'+cell.getData().id+'"/>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Intek Semester",
                    field: "semester",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="break-all whitespace-normal">'+cell.getData().course+'</div>';
                    }
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: "150"
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
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    $('#communicationBtnsArea').fadeIn();
                }else{
                    $('#communicationBtnsArea').fadeOut();
                }
            },
            selectableCheck:function(row){
                return row.getData().id > 0;
            },
            rowFormatter:function(row){
                var data = row.getData();
                if(data.checked == 1){
                    row.select();
                }
            },
        });

        // Redraw table onresize     checked
        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    if ($("#communicationStudentListTable").length) {
        communicationStudentListTable.init();

        function filterCommunicationSTDForm() {
            communicationStudentListTable.init();
        }
    }

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        //maxItems: null,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    let sms_template_id = new TomSelect('#sms_template_id', tomOptions);
    let email_template_id = new TomSelect('#email_template_id', tomOptions);
    let letter_set_id = new TomSelect('#letter_set_id', tomOptions);

    let mailEditor;
    if($("#mailEditor").length > 0){
        const el = document.getElementById('mailEditor');
        ClassicEditor.create(el).then((editor) => {
            mailEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    let letterEditor;
    if($("#letterEditor").length > 0){
        const el = document.getElementById('letterEditor');
        ClassicEditor.create(el).then((editor) => {
            letterEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const sendBulkSmsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendBulkSmsModal"));
    const sendBulkMailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendBulkMailModal"));
    const generateBulkLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#generateBulkLetterModal"));

    const sendBulkSmsModalEl = document.getElementById('sendBulkSmsModal')
    sendBulkSmsModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#sendBulkSmsModal .acc__input-error').html('');
        $('#sendBulkSmsModal .modal-body input, #sendBulkSmsModal .modal-body textarea').val('');
        $('#sendBulkSmsModal .sms_countr').html('160 / 1');
        $('#sendBulkSmsModal input[name="student_ids"]').val('');
        sms_template_id.clear(true);
    });

    const sendBulkMailModalEl = document.getElementById('sendBulkMailModal')
    sendBulkMailModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#sendBulkMailModal .acc__input-error').html('');
        $('#sendBulkMailModal .modal-body input#sendMailsDocument').val('');
        $('#sendBulkMailModal .modal-body input, #sendBulkMailModal .modal-body select').val('');
        $('#sendBulkMailModal .sendMailsDocumentNames').html('').fadeOut();
        $('#sendBulkMailModal input[name="student_ids"]').val('');

        mailEditor.setData('');
        email_template_id.clear(true);
    });

    const generateBulkLetterModalEl = document.getElementById('generateBulkLetterModal')
    generateBulkLetterModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#generateBulkLetterModal .acc__input-error').html('');
        $('#generateBulkLetterModal .modal-body input:not([type="checkbox"])').val('');
        $('#generateBulkLetterModal .modal-body select').val('');
        $('#generateBulkLetterModal .letterEditorArea').fadeOut();
        $('#generateBulkLetterModal input[name="student_ids"]').val('');
        $('#generateBulkLetterModal .modal-body input#send_in_email').prop('checked', false);
        $('#generateBulkLetterModal .commonSmtpWrap').fadeOut();

        letterEditor.setData('');
        letter_set_id.clear(true);

        const today = new Date();
        const yyyy = today.getFullYear();
        let mm = today.getMonth() + 1;
        let dd = today.getDate();

        if (dd < 10) dd = '0' + dd;
        if (mm < 10) mm = '0' + mm;

        const todayDate = dd + '-' + mm + '-' + yyyy;
        $('#generateBulkLetterModal .modal-body input[name="issued_date"]').val(todayDate);
    });

    /* Bulk Letter Start */
    $('.generateBulkLetterBtn').on('click', function(e){
        var $btn = $(this);
        var ids = [];
        
        $('#communicationStudentListTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.student_ids').val());
        });

        if(ids.length > 0){
            generateBulkLetterModal.show();
            document.getElementById("generateBulkLetterModal").addEventListener("shown.tw.modal", function (event) {
                $('#generateBulkLetterModal [name="student_ids"]').val(ids.join(','));
            });
        }else{
            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html("Error Found!");
                $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
            });
        }
    });


    $('#send_in_email').on('change', function() {
        if($(this).prop('checked')){
            $('.commonSmtpWrap').fadeIn('fast', function(){
                $('select', this).val('')
            });
        }else{
            $('.commonSmtpWrap').fadeOut('fast', function(){
                $('select', this).val('')
            });
        }
    });

    $('#generateBulkLetterModal #letter_set_id').on('change', function(){
        var letterSetId = $(this).val();
        if(letterSetId > 0){
            axios({
                method: 'post',
                url: route('bulk.communication.get.letter.set'),
                data: {letterSetId : letterSetId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#generateBulkLetterModal .letterEditorArea').fadeIn('fast', function(){
                        var description = response.data.res.description ? response.data.res.description : '';
                        letterEditor.setData(description)
                    })
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            $('#generateBulkLetterModal .letterEditorArea').fadeOut('fast', function(){
                letterEditor.setData('')
            })
        }
    });

    $('#generateBulkLetterForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('generateBulkLetterForm');
        let print_pdf = $form.find('input[name="print_pdf"]').val();
    
        document.querySelector('#sendLetterBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sendLetterBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append("letter_body", letterEditor.getData());
        axios({
            method: "post",
            url: route('bulk.communication.send.letter'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sendLetterBtn').removeAttribute('disabled');
            document.querySelector("#sendLetterBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                generateBulkLetterModal.hide();
                let pdf_url = (response.data.pdf_url ? response.data.pdf_url : '');
                if(print_pdf == 1 && pdf_url != ''){
                    window.open(pdf_url, '_blank');
                }

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Letter successfully generated and send it to selected students.');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#sendLetterBtn').removeAttribute('disabled');
            document.querySelector("#sendLetterBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#generateBulkLetterForm .${key}`).addClass('border-danger');
                        $(`#generateBulkLetterForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    /* Bulk Letter End */

    /* Bulk Email Start */
    $('.sendBulkMailBtn').on('click', function(e){
        var $btn = $(this);
        var ids = [];
        
        $('#communicationStudentListTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.student_ids').val());
        });

        if(ids.length > 0){
            sendBulkMailModal.show();
            document.getElementById("sendBulkMailModal").addEventListener("shown.tw.modal", function (event) {
                $('#sendBulkMailModal [name="student_ids"]').val(ids.join(','));
            });
        }else{
            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html("Error Found!");
                $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
            });
        }
    });

    $('#sendBulkMailForm #sendMailsDocument').on('change', function(){
        var inputs = document.getElementById('sendMailsDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>'+name+'</div>';
        }

        $('#sendBulkMailForm .sendMailsDocumentNames').fadeIn().html(html);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#sendBulkMailForm [name="email_template_id"]').on('change', function(){
        var emailTemplateID = $(this).val();
        if(emailTemplateID != ''){
            axios({
                method: "post",
                url: route('bulk.communication.get.mail.template'),
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
    });

    $('#sendBulkMailForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('sendBulkMailForm');
    
        document.querySelector('#sendEmailBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sendEmailBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#sendBulkMailForm input#sendMailsDocument')[0].files[0]); 
        form_data.append("body", mailEditor.getData());
        axios({
            method: "post",
            url: route('bulk.communication.send.email'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sendEmailBtn').removeAttribute('disabled');
            document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                sendBulkMailModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.message);
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#sendEmailBtn').removeAttribute('disabled');
            document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#sendBulkMailForm .${key}`).addClass('border-danger');
                        $(`#sendBulkMailForm  .error-${key}`).html(val);
                    }
                } else if(error.response.status == 412){
                    warningModal.show(); 
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html('Oops!');
                        $("#warningModal .warningModalDesc").html(error.response.data.message);
                    });
                
                    setTimeout(function(){
                        warningModal.hide();
                    }, 5000);
                } else {
                    console.log('error');
                }
            }
        });
    });
    /* Bulk Email End */

    /* Bulk SMS Start */
    $('.sendBulkSmsBtn').on('click', function(e){
        var $btn = $(this);
        var ids = [];
        
        $('#communicationStudentListTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.student_ids').val());
        });

        if(ids.length > 0){
            sendBulkSmsModal.show();
            document.getElementById("sendBulkSmsModal").addEventListener("shown.tw.modal", function (event) {
                $('#sendBulkSmsModal [name="student_ids"]').val(ids.join(','));
            });
        }else{
            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html("Error Found!");
                $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
            });
        }
    });

    $('#smsTextArea').on('keyup', function(){
        var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
        if(chars > 0){
            if(chars >= maxlength && maxlength > 0){
                $('#sendBulkSmsModal .modal-content .smsWarning').remove();
                $('#sendBulkSmsModal .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
            }else{
                $('#sendBulkSmsModal .modal-content .smsWarning').remove();
            }
            $('#sendBulkSmsModal .sms_countr').html(remaining +' / '+messages);
        }else{
            $('#sendBulkSmsModal .sms_countr').html('160 / 1');
            $('#sendBulkSmsModal .modal-content .smsWarning').remove();
        }
    });

    $('#sendBulkSmsForm #sms_template_id').on('change', function(){
        var smsTemplateId = $(this).val();
        if(smsTemplateId != ''){
            axios({
                method: "post",
                url: route('bulk.communication.get.sms.template'),
                data: {smsTemplateId : smsTemplateId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#sendBulkSmsForm #smsTextArea').val(response.data.row.description ? response.data.row.description : '').trigger('keyup');
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            })
        }else{
            $('#sendBulkSmsForm #smsTextArea').val('');
            $('#sendBulkSmsForm .sms_countr').html('160 / 1');
        }
    });

    $('#sendBulkSmsForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('sendBulkSmsForm');
    
        document.querySelector('#sendSMSBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sendSMSBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('bulk.communication.send.sms'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sendSMSBtn').removeAttribute('disabled');
            document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                sendBulkSmsModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html('Congratulation!');
                    $("#successModal .successModalDesc").html(response.data.message);
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 5000);
            }
        }).catch(error => {
            document.querySelector('#sendSMSBtn').removeAttribute('disabled');
            document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#sendBulkSmsForm .${key}`).addClass('border-danger');
                        $(`#sendBulkSmsForm  .error-${key}`).html(val);
                    }
                } else if(error.response.status == 412){
                    warningModal.show(); 
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html('Oops!');
                        $("#warningModal .warningModalDesc").html(error.response.data.message);
                    });
                
                    setTimeout(function(){
                        warningModal.hide();
                    }, 5000);
                }else {
                    console.log('error');
                }
            }
        });
    });
    /* Bulk SMS End */
})()