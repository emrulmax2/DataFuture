import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const addAgreementModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAgreementModal"));
    const editAgreementModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAgreementModal"));
    const editInstallmentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editInstallmentModal"));
    
    const editAgreementModalEl = document.getElementById('editAgreementModal')
    editAgreementModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editAgreementModal .acc__input-error').html('');
        $('#editAgreementModal .modal-body select').val('');
        $('#editAgreementModal .modal-body input:not([type="checkbox"])').val('');
        $('#editAgreementModal .modal-body input[type="checkbox"]').prop('checked', false);
        $('#editAgreementModal .modal-body input[name="slc_agreement_id"]').val('0');
    });

    const addAgreementModalEl = document.getElementById('addAgreementModal')
    addAgreementModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editAgreementModal .acc__input-error').html('');
        $('#editAgreementModal .modal-body select').val('');
        $('#editAgreementModal .modal-body input:not([type="checkbox"])').val('');
        $('#editAgreementModal .modal-body input[type="checkbox"]').prop('checked', false);
    });

    const editInstallmentModalEl = document.getElementById('editInstallmentModal')
    editInstallmentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editInstallmentModal .acc__input-error').html('');
        $('#editInstallmentModal .modal-body select').val('');
        $('#editInstallmentModal .modal-body input:not([type="checkbox"])').val('');

        $('#editInstallmentModal .modal-body input[name="slc_installment_id"]').val('0');
        $('#editInstallmentModal .modal-body input[name="agreement_fees"]').val('0');
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

    $('#confirmModal .disAgreeWith').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            confirmModal.hide();
            window.location.reload();
        }else{
            confirmModal.hide();
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

    $('#addAgreementForm [name="course_creation_instance_id"]').on('change', function(){
        var $select = $(this);
        var course_creation_instance_id = $select.val();
        var studen_id = $('#addAgreementForm input[name="studen_id"]').val();

        if(course_creation_instance_id > 0 && course_creation_instance_id != ''){
            axios({
                method: "post",
                url: route('student.get.slc.agreement.instance.fees'),
                data: {studen_id : studen_id, course_creation_instance_id : course_creation_instance_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                var fees = response.data.fees;

                $('#addAgreementForm input[name="fees"]').val(fees);
            }).catch(error => {
                if (error.response.status == 422) {
                    console.log('error');
                }
            });
        }else{
            $('#addAgreementForm input[name="fees"]').val('');
        }
    });

    $('#addAgreementForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addAgreementForm');
    
        document.querySelector('#addAgre').setAttribute('disabled', 'disabled');
        document.querySelector("#addAgre svg").style.cssText ="display: inline-block;";

        var year = $('[name="year"]', $form).val();
        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.store.slc.agreement'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#addAgre').removeAttribute('disabled');
            document.querySelector("#addAgre svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addAgreementModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC Agreement successfully added.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#addAgre').removeAttribute('disabled');
            document.querySelector("#addAgre svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addAgreementForm .${key}`).addClass('border-danger');
                        $(`#addAgreementForm  .error-${key}`).html(val);
                    }
                }else if (error.response.status == 304){
                    $('#addAgreementForm').animate({ scrollTop: 0 });
                    $form.find('.alert').remove();
                    $('.modal-content', $form).prepend('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Existing agreement found under this sutdent active course relation for the year '+year+'.</div>')
                
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

    $('.edit_agreement_btn').on('click', function(){
        var $theBtn = $(this);
        var agreement_id = $theBtn.attr('data-id');

        axios({
            method: "post",
            url: route('student.edit.slc.agreement'),
            data: {agreement_id : agreement_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            var res = response.data.res;

            $('#editAgreementModal [name="slc_coursecode"]').val(res.slc_coursecode);
            $('#editAgreementModal [name="date"]').val(res.date);
            $('#editAgreementModal [name="year"]').val(res.year);
            $('#editAgreementModal [name="fees"]').val(res.fees);
            $('#editAgreementModal [name="discount"]').val(res.discount);
            if(res.is_self_funded == 1){
                $('#editAgreementModal [name="is_self_funded"]').prop('checked', true);
            }else{
                $('#editAgreementModal [name="discount"]').val(res.discount);
            }
            $('#editAgreementModal [name="note"]').val(res.note);

            $('#editAgreementModal [name="slc_agreement_id"]').val(agreement_id);
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#editAgreementForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editAgreementForm');
    
        document.querySelector('#updateAgre').setAttribute('disabled', 'disabled');
        document.querySelector("#updateAgre svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.slc.agreement'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateAgre').removeAttribute('disabled');
            document.querySelector("#updateAgre svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editAgreementModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC Agreement successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateAgre').removeAttribute('disabled');
            document.querySelector("#updateAgre svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editAgreementForm .${key}`).addClass('border-danger');
                        $(`#editAgreementForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.deleteAgreementBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var slc_agreement_id = $theBtn.attr('data-id');

        axios({
            method: 'post',
            url: route('student.slc.agreement.has.data'),
            data: {slc_agreement_id : slc_agreement_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                if(response.data.res == 0){
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html("Oops!" );
                        $("#warningModal .warningModalDesc").html('Oops! You can not delete this agreement. To delete this agreement please remove related Installment and Payments first.');
                        $("#warningModal .warningCloser").attr('data-status', 'DISMISS');
                    });
                }else{
                    confirmModal.show();
                    document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                        $("#confirmModal .confModTitle").html("Are you sure?" );
                        $("#confirmModal .confModDesc").html('Want to delete this agreement from the list? Please click on agree to continue.');
                        $("#confirmModal .agreeWith").attr('data-recordid', slc_agreement_id);
                        $("#confirmModal .agreeWith").attr('data-status', 'DELETEAGR');
                    });
                }
            }
        }).catch(error =>{
            confirmModal.hide();
            console.log(error);
        });
    });

    $(document).on('click', '.assignAgreementToReg', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let reg_id = $theBtn.attr('data-reg');
        let agr_id = $theBtn.attr('data-agr');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to assign #'+agr_id+' this agreement to #'+reg_id+' registration?');
            $("#confirmModal .agreeWith").attr('data-recordid', reg_id+'_'+agr_id);
            $("#confirmModal .agreeWith").attr('data-status', 'ASSIGNAGRTOREG');
        });
    })

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEAGR'){
            axios({
                method: 'delete',
                url: route('student.destory.slc.agreement'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s agreement  successfully deleted.');
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
        }else if(action == 'ASSIGNAGRTOREG'){
            axios({
                method: 'POST',
                url: route('student.assign.slc.agreement.to.registration'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s agreement  successfully re-assigned to registration.');
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

})()