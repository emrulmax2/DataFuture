import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const addInstallmentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addInstallmentModal"));
    const editInstallmentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editInstallmentModal"));

    const editInstallmentModalEl = document.getElementById('editInstallmentModal')
    editInstallmentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editInstallmentModal .acc__input-error').html('');
        $('#editInstallmentModal .modal-body select').val('');
        $('#editInstallmentModal .modal-body input:not([type="checkbox"])').val('');

        $('#editInstallmentModal .modal-body input[name="slc_installment_id"]').val('0');
        $('#editInstallmentModal .modal-body input[name="total_amount"]').val('0');
        $('#editInstallmentModal .modal-body input[name="remaining_amount"]').val('0');
        $('#editInstallmentModal .modal-body input[name="amount_org"]').val('0');

        $('#editInstallmentModal .modal-body .totalAmount').html('');
        $('#editInstallmentModal .modal-body .remainingAmount').html('');
    });

    const addInstallmentModalEl = document.getElementById('addInstallmentModal')
    addInstallmentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addInstallmentModal .acc__input-error').html('');
        $('#addInstallmentModal .modal-body select').val('');
        $('#addInstallmentModal .modal-body input:not([type="checkbox"])').val('');

        $('#addInstallmentModal .modal-body input[name="slc_agreement_id"]').val('0');
        $('#addInstallmentModal .modal-body input[name="total_amount"]').val('0');
        $('#addInstallmentModal .modal-body input[name="remaining_amount"]').val('0');
        $('#addInstallmentModal .modal-body input[name="amount_org"]').val('0');

        $('#addInstallmentModal .modal-body .totalAmount').html('');
        $('#addInstallmentModal .modal-body .remainingAmount').html('');
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



    $('.installmentRow').on('click', function(e){
        var $theRow = $(this);
        var installment_id = $theRow.attr('data-id');

        axios({
            method: "post",
            url: route('student.edit.slc.intallment'),
            data: {installment_id : installment_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            var res = response.data.res;

            editInstallmentModal.show();
            document.getElementById('editInstallmentModal').addEventListener('shown.tw.modal', function(event) {
                $('#editInstallmentModal [name="installment_date"]').val(res.installment_date);
                $('#editInstallmentModal [name="amount"]').val(res.amount);
                $('#editInstallmentModal [name="term"]').val(res.term);
                $('#editInstallmentModal [name="session_term"]').val(res.session_term);
                $('#editInstallmentModal [name="attendance_term"]').val(res.attendance_term);

                $('#editInstallmentModal [name="slc_installment_id"]').val(installment_id);
                $('#editInstallmentModal [name="total_amount"]').val(res.total_amount);
                $('#editInstallmentModal [name="remaining_amount"]').val(res.remaining_amount);
                $('#editInstallmentModal [name="amount_org"]').val(res.amount);

                $('#editInstallmentModal .totalAmount').html(res.total_amount_html);
                $('#editInstallmentModal .remainingAmount').html(res.remaining_amount_html);
            });

        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    })

    $('#editInstallmentForm [name="amount"]').on('keyup', function(){
        var $theInput = $(this);
        var newAmount = $theInput.val();
        var totalAmount = parseInt($('#editInstallmentForm [name="total_amount"]').val(), 10);
        var remainingAmount = parseInt($('#editInstallmentForm [name="remaining_amount"]').val(), 10);
        var orgAmount = parseInt($('#editInstallmentForm [name="amount_org"]').val(), 10);

        var orgTotal = remainingAmount + orgAmount;
        var newRemainingAmount = orgTotal - newAmount;

        $('#editInstallmentForm .remainingAmount').html('£'+newRemainingAmount.toFixed(2))
    });

    $('#editInstallmentForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editInstallmentForm');
    
        document.querySelector('#updateInst').setAttribute('disabled', 'disabled');
        document.querySelector("#updateInst svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.slc.intallment'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateInst').removeAttribute('disabled');
            document.querySelector("#updateInst svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editInstallmentModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC Installment successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 5000);
            }
        }).catch(error => {
            document.querySelector('#updateInst').removeAttribute('disabled');
            document.querySelector("#updateInst svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editInstallmentForm .${key}`).addClass('border-danger');
                        $(`#editInstallmentForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });


    $('.add_installment_btn').on('click', function(e){
        var $theRow = $(this);
        var agreement_id = $theRow.attr('data-agr-id');

        axios({
            method: "post",
            url: route('student.get.slc.intallment.details'),
            data: {agreement_id : agreement_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            var res = response.data.res;
            
            $('#addInstallmentModal [name="slc_agreement_id"]').val(agreement_id);
            $('#addInstallmentModal [name="total_amount"]').val(res.total_amount);
            $('#addInstallmentModal [name="remaining_amount"]').val(res.remaining_amount);
            $('#addInstallmentModal .totalAmount').html(res.total_amount_html);
            $('#addInstallmentModal .remainingAmount').html(res.remaining_amount_html);

        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });
    
    $('#addInstallmentForm [name="amount"]').on('keyup', function(){
        var $theInput = $(this);
        var newAmount = $theInput.val();
        var totalAmount = parseInt($('#addInstallmentForm [name="total_amount"]').val(), 10);
        var remainingAmount = parseInt($('#addInstallmentForm [name="remaining_amount"]').val(), 10);

        var newRemainingAmount = remainingAmount - newAmount;

        $('#addInstallmentForm .remainingAmount').html('£'+newRemainingAmount.toFixed(2))
    });

    $('#addInstallmentForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addInstallmentForm');
    
        document.querySelector('#addInst').setAttribute('disabled', 'disabled');
        document.querySelector("#addInst svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.store.slc.intallment'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#addInst').removeAttribute('disabled');
            document.querySelector("#addInst svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addInstallmentModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC Installment successfully added.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 5000);
            }
        }).catch(error => {
            document.querySelector('#addInst').removeAttribute('disabled');
            document.querySelector("#addInst svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addInstallmentForm .${key}`).addClass('border-danger');
                        $(`#addInstallmentForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})();