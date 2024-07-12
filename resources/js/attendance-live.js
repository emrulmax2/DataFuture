import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

import dayjs from "dayjs";
import Litepicker from "litepicker";
import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";


(function(){
    const senMailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#senMailModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#successModal .successCloser').on('click', function(e){
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

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
            dropdown_input: {},
            remove_button: {
                title: "Remove this item",
            },
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    var ccEmail = new TomSelect('#cc_email', tomOptions);

    const senMailModalEl = document.getElementById('senMailModal')
    senMailModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#senMailModal .acc__input-error').html('');
        $('#senMailModal .modal-body input#sendMailsDocument').val('');
        $('#senMailModal .modal-body input, #senMailModal .modal-body select').val('');
        $('#senMailModal [name="to_email"]').val('').removeAttr('readonly');
        $('#senMailModal .sendMailsDocumentNames').html('').fadeOut();
        mailEditor.setData('');
        ccEmail.clear(true);
    });

    $('#liveAttendanceDept').on('change', function(e){
        if($('#liveAttendanceTable').length > 0){
            var departement = $('#liveAttendanceDept').val();
            var emp = $('#liveAttendanceEmp').val();

            $('.leaveTableLoader').addClass('active');
            axios({
                method: "post",
                url: route('attendance.live.attedance.ajax'),
                data: {departement : departement, emp : emp},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('.leaveTableLoader').removeClass('active');
                if (response.status == 200) {
                    let res = response.data.res;
                    $('.theDateHolder').html(res.the_date);
                    $('#liveAttendanceTable tbody').html(res.htm);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                $('.leaveTableLoader').removeClass('active');
                if (error.response) {
                    console.log('error');
                }
            });
        }
    });

    $('#liveAttendanceEmp').on('keyup', function(e){
        var departement = $('#liveAttendanceDept').val();
        var emp = $('#liveAttendanceEmp').val();

        $('.leaveTableLoader').addClass('active');
        axios({
            method: "post",
            url: route('attendance.live.attedance.ajax'),
            data: {departement : departement, emp : emp},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('.leaveTableLoader').removeClass('active');
            if (response.status == 200) {
                let res = response.data.res;
                $('.theDateHolder').html(res.the_date);
                $('#liveAttendanceTable tbody').html(res.htm);

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error => {
            $('.leaveTableLoader').removeClass('active');
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#senMailModal #sendMailsDocument').on('change', function(){
        var inputs = document.getElementById('sendMailsDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>'+name+'</div>';
        }

        $('#senMailModal .sendMailsDocumentNames').fadeIn().html(html);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#liveAttendanceTable').on('click', '.sendMailBtn', function(e){
        let $theLink = $(this);
        let employee_id = $theLink.attr('data-id');

        axios({
            method: "post",
            url: route('attendance.live.get.employee.mail'),
            data: {employee_id : employee_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                var emails = response.data.emails;
                if(emails != ''){
                    $('#senMailModal [name="to_email"]').val(emails).attr('readonly', 'readonly');
                }else{
                    $('#senMailModal [name="to_email"]').val('').removeAttr('readonly');
                }

                $('#senMailModal [name="employee_id"]').val(employee_id);
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
        
    });

    $('#senMailForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('senMailForm');
    
        document.querySelector('#sentMailBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sentMailBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#senMailForm input#sendMailsDocument')[0].files[0]); 
        form_data.append("mail_body", mailEditor.getData());
        axios({
            method: "post",
            url: route('attendance.live.attedance.sent.mail'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sentMailBtn').removeAttribute('disabled');
            document.querySelector("#sentMailBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                senMailModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Mail successfylly sent to selected employee.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#sentMailBtn').removeAttribute('disabled');
            document.querySelector("#sentMailBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#senMailForm .${key}`).addClass('border-danger');
                        $(`#senMailForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})();