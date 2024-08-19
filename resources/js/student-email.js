import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";


("use strict");
var studentCommEmailListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let studentId = $("#studentCommEmailListTable").attr('data-student') != "" ? $("#studentCommEmailListTable").attr('data-student') : "0";
        let queryStrCME = $("#query-CME").val() != "" ? $("#query-CME").val() : "";
        let statusCME = $("#status-CME").val() != "" ? $("#status-CME").val() : "1";

        let tableContent = new Tabulator("#studentCommEmailListTable", {
            ajaxURL: route("student.mail.list"),
            ajaxParams: { studentId: studentId, queryStrCME : queryStrCME, statusCME : statusCME},
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
        $("#tabulator-export-csv-CME").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CME").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CME").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Student Email Details",
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

(function(){
    if ($("#studentCommEmailListTable").length) {
        // Init Table
        studentCommEmailListTable.init();

        // Filter function
        function filterHTMLFormCME() {
            studentCommEmailListTable.init();
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

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: false,
        maxItems: null,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let email_template_id = new TomSelect('#email_template_id', tomOptions);

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const sendEmailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendEmailModal"));
    const viewCommunicationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewCommunicationModal"));

    const sendEmailModalEl = document.getElementById('sendEmailModal')
    sendEmailModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#sendEmailModal .acc__input-error').html('');
        $('#sendEmailModal .modal-body input#sendMailsDocument').val('');
        $('#sendEmailModal .modal-body input, #sendEmailModal .modal-body select').val('');
        $('#addNoteModal .sendMailsDocumentNames').html('').fadeOut();
        mailEditor.setData('');
        email_template_id.clear(true);
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

    $('#sendEmailForm [name="email_template_id"]').on('change', function(){
        var emailTemplateID = $(this).val();
        if(emailTemplateID != ''){
            axios({
                method: "post",
                url: route('student.get.mail.template'),
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
        form_data.append("body", mailEditor.getData());
        axios({
            method: "post",
            url: route('student.send.mail'),
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
                    $("#successModal .successModalDesc").html('Mail successfully sent to student.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            studentCommEmailListTable.init();
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

    $('#studentCommEmailListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Mail from student list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETEMAIL');
        });
    });

    $('#studentCommEmailListTable').on('click', '.restore_btn', function(e){
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

    $('#studentCommEmailListTable').on('click', '.view_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        viewCommunicationModal.show();
        axios({
            method: 'post',
            url: route('student.mail.show'),
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

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');
        
        if(action == 'DELETEMAIL'){
            axios({
                method: 'delete',
                url: route('student.mail.destroy'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    studentCommEmailListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student Communication Mail successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTOREMAIL'){
            axios({
                method: 'post',
                url: route('student.mail.restore'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    studentCommEmailListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student Communication Mail successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    });

    $('#viewCommunicationModal').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('student.email.attachment.download'), 
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