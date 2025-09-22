import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";


(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const addRegistrationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addRegistrationModal"));
    const editRegistrationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editRegistrationModal"));

    const addRegistrationModalEl = document.getElementById('addRegistrationModal')
    addRegistrationModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addRegistrationModal .acc__input-error').html('');
        $('#addRegistrationModal .modal-body select').val('');
        $('#addRegistrationModal .modal-body input:not([type="checkbox"])').val('');
        $('#addRegistrationModal .modal-body input[type="checkbox"]').prop('checked', false);
        $('#addRegistrationModal input[name="instance_fees"]').val('');

        $('#addRegistrationModal .confirmAttendanceArea').fadeOut('fast', function(){
            $('#addRegistrationModal .confirmAttendanceArea').removeClass('opened');
            $('#addRegistrationModal select[name="self_funded_year"]').val('');
            //$('#addRegistrationModal select[name="session_term"]').html('<option value="">Please Select</option>').attr('readonly');
            $('#addRegistrationModal select[name="session_term"]').val('');
            $('#addRegistrationForm .regularCourseFee').removeClass('line-through text-danger'); 
            $('#addRegistrationForm .instanceCourseFee').addClass('hidden').html('');

            $('#addRegistrationForm select[name="attendance_code_id"]').val('');
            $('#addRegistrationForm textarea').val('');

            $('#addRegistrationForm [name="attendance_code_id"]').val('');
            $('#addRegistrationForm .installmentAmountWrap').fadeOut('fast', function(){
                $('#addRegistrationForm [name="installment_amount"]').val('');
            });
        });
    });

    const editRegistrationModalEl = document.getElementById('editRegistrationModal')
    editRegistrationModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editRegistrationModal .acc__input-error').html('');
        $('#editRegistrationModal .modal-body select').val('');
        $('#editRegistrationModal .modal-body input:not([type="checkbox"])').val('');
        $('#editRegistrationModal .modal-body input[name="slc_registration_id"]').val('0');
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
    })


    $('#addRegistrationForm #confirm_attendance').on('change', function(){
        if($(this).prop('checked')){
            var academic_year_id = $('#addRegistrationForm [name="academic_year_id"]').val();
            var course_creation_instance_id = $('#addRegistrationForm [name="course_creation_instance_id"]').val();
            var studen_id = $('#addRegistrationForm input[name="studen_id"]').val();

            axios({
                method: "post",
                url: route('student.get.registration.confirmation.details'),
                data: {studen_id : studen_id, academic_year_id : academic_year_id, course_creation_instance_id : course_creation_instance_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                var fees = response.data.fees;
                var session_term_html = response.data.session_term_html;

                $('#addRegistrationForm .confirmAttendanceArea').fadeIn('fast', function(){
                    $('#addRegistrationForm .confirmAttendanceArea').addClass('opened');
                    if(academic_year_id > 0 && academic_year_id != ''){
                        $('#addRegistrationForm select[name="self_funded_year"]').val(academic_year_id);
                    }else{
                        $('#addRegistrationForm select[name="self_funded_year"]').val('');
                    }

                    $('#addRegistrationForm select[name="attendance_term"]').val('');
                    $('#addRegistrationForm select[name="attendance_code_id"]').val('');
                    $('#addRegistrationForm textarea[name="attendance_note"]').val('');

                    $('#addRegistrationForm [name="attendance_code_id"]').val('');
                    $('#addRegistrationForm .installmentAmountWrap').fadeOut('fast', function(){
                        $('#addRegistrationForm [name="installment_amount"]').val('');
                    });
                });
            }).catch(error => {
                if (error.response.status == 422) {
                    console.log('error');
                }
            });
        }else{
            $('#addRegistrationForm .confirmAttendanceArea').fadeOut('fast', function(){
                $('#addRegistrationForm .confirmAttendanceArea').removeClass('opened');
                $('#addRegistrationForm select[name="self_funded_year"]').val('');
                $('#addRegistrationForm select[name="attendance_term"]').val('');
                $('#addRegistrationForm select[name="session_term"]').val('');

                $('#addRegistrationForm select[name="attendance_code_id"]').val('');
                $('#addRegistrationForm textarea[name="attendance_note"]').val('');

                $('#addRegistrationForm [name="attendance_code_id"]').val('');
                $('#addRegistrationForm .installmentAmountWrap').fadeOut('fast', function(){
                    $('#addRegistrationForm [name="installment_amount"]').val('');
                });
            });
        }
    });


    $('#addRegistrationForm [name="course_creation_instance_id"]').on('change', function(){
        var $select = $(this);
        var academic_year_id = $('#addRegistrationForm [name="academic_year_id"]').val();
        var course_creation_instance_id = $select.val();
        var studen_id = $('#addRegistrationForm input[name="studen_id"]').val();

        if(course_creation_instance_id > 0 && course_creation_instance_id != ''){
            axios({
                method: "post",
                url: route('student.get.registration.confirmation.details'),
                data: {studen_id : studen_id, academic_year_id : academic_year_id, course_creation_instance_id : course_creation_instance_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                var fees = response.data.fees;
                var fees_html = response.data.fees_html;
                var course_fee = $('#addRegistrationForm .regCourseFee').attr('data-fee') * 1;
                var session_term_html = response.data.session_term_html;

                
                $('#addRegistrationForm input[name="instance_fees"]').val(fees);
                $('#addRegistrationForm select[name="term_declaration_id"]').val('');
                $('#addRegistrationForm select[name="session_term"]').val('');
                if(fees != course_fee){
                    $('#addRegistrationForm .regularCourseFee').addClass('line-through text-danger');
                    $('#addRegistrationForm .instanceCourseFee').removeClass('hidden').html(fees_html);
                }else{
                    $('#addRegistrationForm .regularCourseFee').removeClass('line-through text-danger');
                    $('#addRegistrationForm .instanceCourseFee').addClass('hidden').html('');
                }
            }).catch(error => {
                if (error.response.status == 422) {
                    console.log('error');
                }
            });
        }else{
            $('#addRegistrationForm input[name="instance_fees"]').val('');
            if($('#addRegistrationForm .confirmAttendanceArea').hasClass('opened')){
                $('#addRegistrationForm select[name="session_term"]').val('');
                $('#addRegistrationForm select[name="term_declaration_id"]').val('');
            }
        }
    });


    $('#addRegistrationForm [name="academic_year_id"]').on('change', function(){
        var $academic_year_id = $(this);
        var academic_year_id = $academic_year_id.val();

        if($('#addRegistrationForm .confirmAttendanceArea').hasClass('opened')){
            if(academic_year_id > 0 && academic_year_id != ''){
                $('#addRegistrationForm select[name="self_funded_year"]').val(academic_year_id);
            }else{
                $('#addRegistrationForm select[name="self_funded_year"]').val('');
            }
        }
    });

    $('#addRegistrationForm [name="attendance_code_id"]').on('change', function(){
        var $attendance_code_id = $(this);
        var attendance_code_id = $attendance_code_id.val();
        var coc_required = $('option:selected', $attendance_code_id).attr('data-coc-required');
        var session_term = $('#addRegistrationForm [name="session_term"]').val();
        var instance_fees = $('#addRegistrationForm [name="instance_fees"]').val();
            instance_fees = instance_fees != '' ? parseInt(instance_fees, 10) : 0;

        if(coc_required == 1){
            $('#addRegistrationForm .cocReqWrap').fadeIn();
        }else{
            $('#addRegistrationForm .cocReqWrap').fadeOut();
        }
        if(attendance_code_id == 1){
            var installment_amount;
            if(session_term != '' && instance_fees != '' && instance_fees > 0){
                if(session_term == 1 || session_term == 2){
                    installment_amount = instance_fees * .25;
                }else if(session_term == 3){
                    installment_amount = instance_fees * .50;
                }
            }
            $('#addRegistrationForm .installmentAmountWrap').addClass('opened');
            $('#addRegistrationForm .installmentAmountWrap').fadeIn('fast', function(){
                $('#addRegistrationForm [name="installment_amount"]').val(installment_amount > 0 && installment_amount != '' ? installment_amount.toFixed(2) : '');
            });
        }else{
            $('#addRegistrationForm .installmentAmountWrap').removeClass('opened');
            $('#addRegistrationForm .installmentAmountWrap').fadeOut('fast', function(){
                $('#addRegistrationForm [name="installment_amount"]').val('');
            })
        }
    });

    $('#addRegistrationForm [name="session_term"]').on('change', function(){
        var $session_term = $(this);
        var session_term = $session_term.val();
        
        var attendance_code_id = $('#addRegistrationForm [name="attendance_code_id"]').val();
        var instance_fees = $('#addRegistrationForm [name="instance_fees"]').val();
            instance_fees = instance_fees != '' ? parseInt(instance_fees, 10) : 0;

        if(attendance_code_id == 1){
            var installment_amount;
            if(session_term != '' && instance_fees != '' && instance_fees > 0){
                if(session_term == 1 || session_term == 2){
                    installment_amount = instance_fees * .25;
                }else if(session_term == 3){
                    installment_amount = instance_fees * .50;
                }
            }
            $('#addRegistrationForm [name="installment_amount"]').val(installment_amount > 0 && installment_amount != '' ? installment_amount.toFixed(2) : '');
        }
    });


    $('#addRegistrationForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('addRegistrationForm');
    
        document.querySelector('#saveReg').setAttribute('disabled', 'disabled');
        document.querySelector("#saveReg svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.store.registration'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveReg').removeAttribute('disabled');
            document.querySelector("#saveReg svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addRegistrationModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC Registration successfully created.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveReg').removeAttribute('disabled');
            document.querySelector("#saveReg svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addRegistrationForm .${key}`).addClass('border-danger');
                        $(`#addRegistrationForm  .error-${key}`).html(val);
                    }
                } else if (error.response.status == 304){
                    $('#addRegistrationModal').animate({ scrollTop: 0 });
                    $form.find('.alert').remove();
                    $('.modal-content', $form).prepend('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Selected registration year already exist.</div>')
                
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                    setTimeout(function(){
                        $form.find('.alert').remove();
                    }, 3000)
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.edit_registration_btn').on('click', function(e){
        var $theBtn = $(this);
        var reg_id = $theBtn.attr('data-id');

        axios({
            method: "post",
            url: route('student.edit.registration'),
            data: {reg_id : reg_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            var res = response.data.res;

            $('#editRegistrationForm [name="ssn"]').val(res.ssn);
            $('#editRegistrationForm [name="confirmation_date"]').val(res.confirmation_date);
            $('#editRegistrationForm [name="academic_year_id"]').val(res.academic_year_id);
            $('#editRegistrationForm [name="registration_year"]').val(res.registration_year);
            $('#editRegistrationForm [name="course_creation_instance_id"]').val(res.course_creation_instance_id );
            $('#editRegistrationForm [name="slc_registration_status_id"]').val(res.slc_registration_status_id);
            $('#editRegistrationForm [name="note"]').val(res.note);

            $('#editRegistrationForm [name="slc_registration_id"]').val(reg_id);
        }).catch(error => {
            if (error.response.status == 422) {
                console.log('error');
            }
        });
    });

    $('#editRegistrationForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('editRegistrationForm');
    
        document.querySelector('#updateReg').setAttribute('disabled', 'disabled');
        document.querySelector("#updateReg svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.registration'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateReg').removeAttribute('disabled');
            document.querySelector("#updateReg svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editRegistrationModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC Registration successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateReg').removeAttribute('disabled');
            document.querySelector("#updateReg svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editRegistrationForm .${key}`).addClass('border-danger');
                        $(`#editRegistrationForm  .error-${key}`).html(val);
                    }
                } else if (error.response.status == 304){
                    $form.find('.alert').remove();
                    $('.modal-content', $form).prepend('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Selected registration year already exist.</div>')
                
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                    setTimeout(function(){
                        $form.find('.alert').remove();
                    }, 3000)
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.delete_reg_btn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var slc_registration_id = $theBtn.attr('data-id');

        axios({
            method: 'post',
            url: route('student.slc.registration.has.data'),
            data: {slc_registration_id : slc_registration_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                if(response.data.res == 0){
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html("Oops!" );
                        $("#warningModal .warningModalDesc").html('Oops! You can not delete this registration. To delete the registration please remove related Attendance, COC and Agreements first.');
                        $("#warningModal .warningCloser").attr('data-status', 'DISMISS');
                    });
                }else{
                    confirmModal.show();
                    document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                        $("#confirmModal .confModTitle").html("Are you sure?" );
                        $("#confirmModal .confModDesc").html('Want to delete this registration from the list? Please click on agree to continue.');
                        $("#confirmModal .agreeWith").attr('data-recordid', slc_registration_id);
                        $("#confirmModal .agreeWith").attr('data-status', 'DELETEREG');
                    });
                }
            }
        }).catch(error =>{
            confirmModal.hide();
            console.log(error);
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEREG'){
            axios({
                method: 'delete',
                url: route('student.slc.registration.destroy'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s SLC Registration successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    });

})();