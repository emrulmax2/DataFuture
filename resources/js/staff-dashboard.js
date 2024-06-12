import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

import dayjs from "dayjs";
import Litepicker from "litepicker";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";


(function(){

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const senGroupMailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#senGroupMailModal"));
    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });

    let mailEditor;
    if($("#mailEditor").length > 0){
        const el = document.getElementById('mailEditor');
        ClassicEditor.create(el).then(newEditor => {
            mailEditor = newEditor;
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

    var department_ids = new TomSelect('#department_ids', tomOptions);
    var groups_ids = new TomSelect('#groups_ids', tomOptions);
    var employee_ids = new TomSelect('#employee_ids', tomOptions);

    const senGroupMailModalEl = document.getElementById('senGroupMailModal')
    senGroupMailModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#senGroupMailModal .acc__input-error').html('');
        $('#senGroupMailModal .modal-body input#sendMailsDocument').val('');
        $('#senGroupMailModal .modal-body input, #senGroupMailModal .modal-body select').val('');
        $('#senGroupMailModal [name="to_email"]').val('').removeAttr('readonly');
        $('#senGroupMailModal .sendMailsDocumentNames').html('').fadeOut();
        mailEditor.setData('');

        department_ids.clear(true);
        groups_ids.clear(true);
        employee_ids.clear(true);
    });

    
    /* Home Work Start */
    $('.attendance_action_btn').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var action_type = $this.attr('data-value');

        $('.attendance_action_btn').addClass('disabled');
        axios({
            method: 'post',
            url: route('dashboard.feed.attendance'),
            data: {action_type : action_type},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            $('.attendance_action_btn').removeClass('disabled');

            if (response.status == 200) {
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html(response.data.res);
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                }); 

                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000)
            }
        }).catch((error) => {
            $('.attendance_action_btn').removeClass('disabled');
            if (error.response) {
                console.log("error");
            }
        });
        
    });
    /* Home Work End */


    /* Process BTN Toggle Start */
    $('.processParents').on('click', function(e){
        e.preventDefault();
        var $process = $(this);
        var  process_id = $process.attr('data-process');
        
        if($process.hasClass('active')){
            $('.processTask.process_'+process_id+'_task').fadeOut();
            $process.removeClass('active');
        }else{
            $('.processTask.process_'+process_id+'_task').fadeIn();
            $process.addClass('active');
        }
    })
    /* Process BTN Toggle END */

    $('#senGroupMailModal #sendMailsDocument').on('change', function(){
        var inputs = document.getElementById('sendMailsDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>'+name+'</div>';
        }

        $('#senGroupMailModal .sendMailsDocumentNames').fadeIn().html(html);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#department_ids').on('change', function(e){
        let $department = $('#department_ids');
        let department_ids = $department.val();

        groups_ids.clear(true);
        employee_ids.clear(true);
        if(department_ids.length > 0){
            axios({
                method: "post",
                url: route('dashboard.get.dept.employee.ids'),
                data: {department_ids : department_ids, group_ids : []},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    let emps = response.data.emps;
                    if(emps.length > 0){
                        for (var employee_id of emps) {
                            employee_ids.addItem(employee_id, true);
                        }
                    }else{
                        employee_ids.clear(true);
                    }
                }
            }).catch(error => {
                employee_ids.clear(true);
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            employee_ids.clear(true);
        }
    });

    $('#groups_ids').on('change', function(e){
        let $group = $('#groups_ids');
        let groups_ids = $group.val();

        department_ids.clear(true);
        employee_ids.clear(true);
        if(groups_ids.length > 0){
            axios({
                method: "post",
                url: route('dashboard.get.dept.employee.ids'),
                data: {department_ids : [], group_ids : groups_ids},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    let emps = response.data.emps;
                    if(emps.length > 0){
                        for (var employee_id of emps) {
                            employee_ids.addItem(employee_id, true);
                        }
                    }else{
                        employee_ids.clear(true);
                    }
                }
            }).catch(error => {
                employee_ids.clear(true);
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            employee_ids.clear(true);
        }
    });


    $('#senGroupMailForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('senGroupMailForm');
    
        document.querySelector('#sentMailBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sentMailBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#senGroupMailForm input#sendMailsDocument')[0].files[0]); 
        axios({
            method: "post",
            url: route('dashboard.get.send.group.mail'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sentMailBtn').removeAttribute('disabled');
            document.querySelector("#sentMailBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                senGroupMailModal.hide();
                let suc = response.data.suc;
                if(suc == 2){
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html( "Oops!" );
                        $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact with the administrator.');
                    }); 
                    
                    setTimeout(function(){
                        warningModal.hide();
                    }, 2000);
                }else{
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
            }
        }).catch(error => {
            document.querySelector('#sentMailBtn').removeAttribute('disabled');
            document.querySelector("#sentMailBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#senGroupMailForm .${key}`).addClass('border-danger');
                        $(`#senGroupMailForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    if($('#attendanceHistoryLocModal').length > 0){
        const attendanceHistoryLocModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#attendanceHistoryLocModal"));
        attendanceHistoryLocModal.show();

        $('#attendanceHistoryLocModal .actionBtn').on('click', function(e){
            e.preventDefault();
            let $theBtn = $(this);

            $('#attendanceHistoryLocModal .actionBtn').attr('disabled', 'disabled');
            if($theBtn.hasClass('disagreeWith')){ 
                axios({
                    method: 'post',
                    url: route('dashboard.ignore.feed.attendance'),
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                }).then((response) => {
                    $('#attendanceHistoryLocModal .actionBtn').removeAttr('disabled');
                    attendanceHistoryLocModal.hide();
                    
                    if (response.status == 200) {
                        window.location.reload();
                    }
                }).catch((error) => {
                    $('#attendanceHistoryLocModal .actionBtn').removeAttr('disabled');
                    if (error.response) {
                        console.log("error");
                    }
                });
            }else if($theBtn.hasClass('agreeWith')){
                var action_type = $theBtn.attr('data-value');
                axios({
                    method: 'post',
                    url: route('dashboard.feed.attendance'),
                    data: {action_type : action_type},
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                }).then((response) => {
                    $('#attendanceHistoryLocModal .actionBtn').removeAttr('disabled');
                    attendanceHistoryLocModal.hide();
                    
                    if (response.status == 200) {
                        successModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html(response.data.res);
                            $("#successModal .successCloser").attr('data-action', 'RELOAD');
                        }); 
        
                        setTimeout(function(){
                            successModal.hide();
                            window.location.reload();
                        }, 2000)
                    }
                }).catch((error) => {
                    $('#attendanceHistoryLocModal .actionBtn').removeAttr('disabled');
                    if (error.response) {
                        console.log("error");
                    }
                });
            }
        })
    }

})();