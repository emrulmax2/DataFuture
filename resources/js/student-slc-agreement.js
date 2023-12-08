import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

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
                }, 5000);
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

})()