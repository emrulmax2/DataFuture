import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const editAttendanceModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAttendanceModal"));
    const addAttendanceModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAttendanceModal"));

    const editAttendanceModalEl = document.getElementById('editAttendanceModal')
    editAttendanceModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editAttendanceModal .acc__input-error').html('');
        $('#editAttendanceModal .modal-body select').val('');
        $('#editAttendanceModal .modal-body input:not([type="checkbox"])').val('');
        $('#editAttendanceModal .modal-body input[name="slc_attendance_id"]').val('0');

        $('#editAttendanceModal .attendanceYear').html('');
    });

    const addAttendanceModalEl = document.getElementById('addAttendanceModal')
    editAttendanceModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addAttendanceModal .acc__input-error').html('');
        $('#addAttendanceModal .modal-body select').val('');
        $('#addAttendanceModal .modal-body input:not([type="checkbox"])').val('');
        $('#addAttendanceModal .modal-body textarea').val('');
        
        $('#addAttendanceModal .addAttenInstallmentAmountWrap').fadeOut();
        $('#addAttendanceModal .addAttenInstallmentAmountNotice').fadeOut();
        $('#addAttendanceModal .cocReqWrap').fadeOut();

        $('#addAttendanceModal .attendanceYear').html('');
        $('#addAttendanceModal .modal-footer [name="slc_registration_id"]').val('0');
        $('#addAttendanceModal .modal-footer [name="instance_fees"]').val('0');
        $('#addAttendanceModal .modal-footer [name="attendance_year"]').val('0');
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

    $('.edit_attendance_btn').on('click', function(e){
        var $theBtn = $(this);
        var attendance_id = $theBtn.attr('data-id');

        axios({
            method: "post",
            url: route('student.edit.slc.attendance'),
            data: {attendance_id : attendance_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            var res = response.data.res;

            $('#editAttendanceForm [name="confirmation_date"]').val(res.confirmation_date);
            $('#editAttendanceForm .attendanceYear').html('Year '+res.attendance_year);
            $('#editAttendanceForm [name="term_declaration_id"]').val(res.term_declaration_id);
            $('#editAttendanceForm [name="session_term"]').val(res.session_term);
            $('#editAttendanceForm [name="attendance_code_id"]').val(res.attendance_code_id );
            $('#editAttendanceForm [name="attendance_note"]').val(res.note);

            $('#editAttendanceForm [name="slc_attendance_id"]').val(attendance_id);
        }).catch(error => {
            if (error.response){
                if (error.response.status && error.response.status == 422) {
                    console.log('error');
                }
            }
        });
    });

    $('#editAttendanceModal [name="attendance_code_id"]').on('change', function(){
        var $attendance_code_id = $(this);
        var attendance_code_id = $attendance_code_id.val();
        var coc_required = $('option:selected', $attendance_code_id).attr('data-coc-required');

        if(coc_required == 1){
            $('#editAttendanceModal .cocReqWrap').fadeIn();
        }else{
            $('#editAttendanceModal .cocReqWrap').fadeOut();
        }
        
    });

    $('#editAttendanceForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editAttendanceForm');
    
        document.querySelector('#updateAtten').setAttribute('disabled', 'disabled');
        document.querySelector("#updateAtten svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.slc.attendance'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateAtten').removeAttribute('disabled');
            document.querySelector("#updateAtten svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editAttendanceModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC Attendance successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateAtten').removeAttribute('disabled');
            document.querySelector("#updateAtten svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editAttendanceForm .${key}`).addClass('border-danger');
                        $(`#editAttendanceForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });


    $('.add_attendance_btn').on('click', function(){
        var $theBtn = $(this);
        var reg_id = $theBtn.attr('data-reg-id');

        axios({
            method: "post",
            url: route('student.slc.attendance.populate'),
            data: {reg_id : reg_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            var res = response.data.res;

            $('#addAttendanceModal .attendanceYear').text('Year '+res.year);
            $('#addAttendanceForm [name="session_term"]').val('');

            $('#addAttendanceForm [name="instance_fees"]').val(res.fees);
            $('#addAttendanceForm [name="slc_registration_id"]').val(reg_id);
            $('#addAttendanceForm [name="attendance_year"]').val(res.year);
        }).catch(error => {
            if (error.response){
                if (error.response.status && error.response.status == 422) {
                    console.log('error');
                }
            }
        });
    })

    $('#addAttendanceModal [name="attendance_code_id"]').on('change', function(){
        var $attendance_code_id = $(this);
        var attendance_code_id = $attendance_code_id.val();
        var studen_id = $('#addAttendanceModal [name="studen_id"]').val();
        var slc_registration_id = $('#addAttendanceModal [name="slc_registration_id"]').val();
        var attendance_year = $('#addAttendanceModal [name="attendance_year"]').val();
        var session_term = $('#addAttendanceModal [name="session_term"]').val();
        var instance_fees = $('#addAttendanceModal [name="instance_fees"]').val();
            instance_fees = instance_fees != '' ? parseInt(instance_fees, 10) : 0;
        var coc_required = $('option:selected', $attendance_code_id).attr('data-coc-required');

        if(coc_required == 1){
            $('#addAttendanceModal .cocReqWrap').fadeIn();
        }else{
            $('#addAttendanceModal .cocReqWrap').fadeOut();
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

            axios({
                method: "post",
                url: route('student.installment.existence'),
                data: {studen_id : studen_id, slc_registration_id : slc_registration_id, attendance_year : attendance_year, session_term : session_term},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    if(response.data.res > 0){
                        var msg = 'Opps! Installment already exist under this selected attendance year and term.'
                        if(response.data.res == 2){
                            msg = 'Opps! Agreement not found under this attendance year.';
                        }
                        $('#addAttendanceModal .addAttenInstallmentAmountNotice').fadeIn('fast', function(){
                            $('.alert', this).html(msg);
                        });
                        $('#addAttendanceModal .addAttenInstallmentAmountWrap').removeClass('opened');
                        $('#addAttendanceModal .addAttenInstallmentAmountWrap').fadeOut('fast', function(){
                            $('#addAttendanceModal [name="installment_amount"]').val('');
                        });
                    }else{
                        $('#addAttendanceModal .addAttenInstallmentAmountNotice').fadeOut();
                        $('#addAttendanceModal .addAttenInstallmentAmountWrap').addClass('opened');
                        $('#addAttendanceModal .addAttenInstallmentAmountWrap').fadeIn('fast', function(){
                            $('#addAttendanceModal [name="installment_amount"]').val(installment_amount > 0 && installment_amount != '' ? installment_amount.toFixed(2) : '');
                        })
                    }
                }
            }).catch(error => {
                if (error.response){
                    console.log('error');
                }
            });
        }else{
            $('#addAttendanceModal .addAttenInstallmentAmountNotice').fadeOut();
            $('#addAttendanceModal .addAttenInstallmentAmountWrap').removeClass('opened');
            $('#addAttendanceModal .addAttenInstallmentAmountWrap').fadeOut('fast', function(){
                $('#addAttendanceModal [name="installment_amount"]').val('');
            })
        }
    });

    $('#addAttendanceModal [name="session_term"]').on('change', function(){
        var $session_term = $(this);
        var session_term = $session_term.val();

        var attendance_code_id = $('#addAttendanceModal [name="attendance_code_id"]').val();
        var studen_id = $('#addAttendanceModal [name="studen_id"]').val();
        var slc_registration_id = $('#addAttendanceModal [name="slc_registration_id"]').val();
        var attendance_year = $('#addAttendanceModal [name="attendance_year"]').val();
        var session_term = $('#addAttendanceModal [name="session_term"]').val();
        var instance_fees = $('#addAttendanceModal [name="instance_fees"]').val();
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

            axios({
                method: "post",
                url: route('student.installment.existence'),
                data: {studen_id : studen_id, slc_registration_id : slc_registration_id, attendance_year : attendance_year, session_term : session_term},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    if(response.data.res > 0){
                        var msg = 'Opps! Installment already exist under this selected attendance year and term.'
                        if(response.data.res == 2){
                            msg = 'Opps! Agreement not found under this attendance year.';
                        }
                        $('#addAttendanceModal .addAttenInstallmentAmountNotice').fadeIn('fast', function(){
                            $('.alert', this).html(msg);
                        });
                        $('#addAttendanceModal .addAttenInstallmentAmountWrap').removeClass('opened');
                        $('#addAttendanceModal .addAttenInstallmentAmountWrap').fadeOut('fast', function(){
                            $('#addAttendanceModal [name="installment_amount"]').val('');
                        });
                    }else{
                        $('#addAttendanceModal .addAttenInstallmentAmountNotice').fadeOut();
                        $('#addAttendanceModal .addAttenInstallmentAmountWrap').addClass('opened');
                        $('#addAttendanceModal .addAttenInstallmentAmountWrap').fadeIn('fast', function(){
                            $('#addAttendanceModal [name="installment_amount"]').val(installment_amount > 0 && installment_amount != '' ? installment_amount.toFixed(2) : '');
                        })
                    }
                }
            }).catch(error => {
                if (error.response){
                    console.log('error');
                }
            });
        }else{
            $('#addAttendanceModal .addAttenInstallmentAmountNotice').fadeOut();
            $('#addAttendanceModal .addAttenInstallmentAmountWrap').removeClass('opened');
            $('#addAttendanceModal .addAttenInstallmentAmountWrap').fadeOut('fast', function(){
                $('#addAttendanceModal [name="installment_amount"]').val('');
            })
        }
    });

    
    $('#addAttendanceForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addAttendanceForm');
    
        document.querySelector('#addAtten').setAttribute('disabled', 'disabled');
        document.querySelector("#addAtten svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.store.slc.attendance'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#addAtten').removeAttribute('disabled');
            document.querySelector("#addAtten svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addAttendanceModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC Attendance successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#addAtten').removeAttribute('disabled');
            document.querySelector("#addAtten svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addAttendanceForm .${key}`).addClass('border-danger');
                        $(`#addAttendanceForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.delete_attendance_btn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var slc_attendance_id = $theBtn.attr('data-id');

        axios({
            method: 'post',
            url: route('student.slc.attendance.has.data'),
            data: {slc_attendance_id : slc_attendance_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                if(response.data.res == 0){
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html("Oops!" );
                        $("#warningModal .warningModalDesc").html('Oops! You can not delete this attendance. To delete attendance please remove related COC and Installments first.');
                        $("#warningModal .warningCloser").attr('data-status', 'DISMISS');
                    });
                }else{
                    confirmModal.show();
                    document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                        $("#confirmModal .confModTitle").html("Are you sure?" );
                        $("#confirmModal .confModDesc").html('Want to delete this attendance from the list? Please click on agree to continue.');
                        $("#confirmModal .agreeWith").attr('data-recordid', slc_attendance_id);
                        $("#confirmModal .agreeWith").attr('data-status', 'DELETEATN');
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

        if(action == 'DELETEATN'){
            axios({
                method: 'delete',
                url: route('student.slc.attendance.destroy'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s SLC Attendance document successfully deleted.');
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