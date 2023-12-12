import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const editAttendanceModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAttendanceModal"));

    const editAttendanceModalEl = document.getElementById('editAttendanceModal')
    editAttendanceModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editAttendanceModal .acc__input-error').html('');
        $('#editAttendanceModal .modal-body select').val('');
        $('#editAttendanceModal .modal-body input:not([type="checkbox"])').val('');
        $('#editAttendanceModal .modal-body input[name="slc_registration_id"]').val('0');
    });

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
            $('#editAttendanceForm [name="attendance_year"]').val(res.attendance_year);
            $('#editAttendanceForm [name="attendance_term"]').val(res.attendance_term);
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

            $('#addAttendanceForm [name="attendance_year"]').val(res.year);
            $('#addAttendanceForm [name="session_term"]').html(res.sterm);

            $('#addAttendanceForm [name="instance_fees"]').val(res.fees);
            $('#addAttendanceForm [name="slc_registration_id"]').val(reg_id);
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
            $('#addAttendanceModal .addAttenInstallmentAmountWrap').addClass('opened');
            $('#addAttendanceModal .addAttenInstallmentAmountWrap').fadeIn('fast', function(){
                $('#addAttendanceModal [name="installment_amount"]').val(installment_amount > 0 && installment_amount != '' ? installment_amount.toFixed(2) : '');
            })
        }else{
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
            $('#addAttendanceModal [name="installment_amount"]').val(installment_amount > 0 && installment_amount != '' ? installment_amount.toFixed(2) : '');
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
                editAttendanceModal.hide();

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

})();