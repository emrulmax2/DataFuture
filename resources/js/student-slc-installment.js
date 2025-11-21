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

        $('#editInstallmentModal .installmentAmountWrap').fadeOut('fast', function(){
            $('#editInstallmentModal [name="amount"]').val('');
        });
        $('#editInstallmentModal .installmentAmountNotice').fadeOut();
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

        $('#addInstallmentModal .installmentAmountWrap').fadeOut('fast', function(){
            $('#addInstallmentModal [name="amount"]').val('');
        });
        $('#addInstallmentModal .installmentAmountNotice').fadeOut();
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



    $('.editInstallmentBtn').on('click', function(e){
        var $theBtn = $(this);
        var installment_id = $theBtn.attr('data-id');

        axios({
            method: "post",
            url: route('student.edit.slc.intallment'),
            data: {installment_id : installment_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            var res = response.data.res;

            $('#editInstallmentModal [name="installment_date"]').val(res.installment_date);
            $('#editInstallmentModal [name="session_term"]').val(res.session_term);
            $('#editInstallmentModal [name="term_declaration_id"]').val(res.term_declaration_id);

            $('#editInstallmentModal [name="slc_installment_id"]').val(installment_id);
            $('#editInstallmentModal [name="total_amount"]').val(res.total_amount);
            $('#editInstallmentModal [name="remaining_amount"]').val(res.remaining_amount);
            $('#editInstallmentModal [name="amount_org"]').val(res.amount);

            if(res.commission > 0){
                $('#editInstallmentModal .totalAmount').html('<del class="text-slate-400 mr-2">'+res.total_amount_html+'</del>'+' '+res.total_amount_after_commission_html);
            }else{
                $('#editInstallmentModal .totalAmount').html(res.total_amount_html);
            }

            //$('#editInstallmentModal .totalAmount').html(res.total_amount_html);
            $('#editInstallmentModal .remainingAmount').html(res.remaining_amount_html);

            $('#editInstallmentModal .installmentAmountWrap').fadeIn('fast', function(){
                $('#editInstallmentModal [name="amount"]').val(res.amount);
            });
            $('#editInstallmentModal .installmentAmountNotice').fadeOut();

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

    $('#editInstallmentForm [name="session_term"]').on('change', function(){
        var $theSession = $(this);
        var theSession = $theSession.val();
        var student_id = $('#editInstallmentForm [name="studen_id"]').val();
        var slc_installment_id = $('#editInstallmentForm [name="slc_installment_id"]').val();

        var totalAmount = parseInt($('#editInstallmentForm [name="total_amount"]').val(), 10);
        var remainingAmount = parseInt($('#editInstallmentForm [name="remaining_amount"]').val(), 10);
        var orgAmount = parseInt($('#editInstallmentForm [name="amount_org"]').val(), 10);

        var orgTotal = remainingAmount + orgAmount;

        if(theSession > 0){
            axios({
                method: "post",
                url: route('student.slc.intallment.existence.edit'),
                data: {slc_installment_id : slc_installment_id, student_id : student_id, theSession : theSession},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    if(response.data.res > 0){
                        $('#editInstallmentForm .installmentAmountWrap').fadeIn('fast', function(){
                            $('#editInstallmentForm [name="amount"]').val(orgAmount);
                        });
                        $('#editInstallmentForm .installmentAmountNotice').fadeOut();
                    }else{
                        $('#editInstallmentForm [name="session_term"]').val(response.data.inst.session_term);
                        $('#editInstallmentForm .installmentAmountWrap').fadeIn('fast', function(){
                            $('#editInstallmentForm [name="amount"]').val(orgAmount);
                        });
                        $('#editInstallmentForm .installmentAmountNotice').fadeIn();
                    }
                }
            }).catch(error => {
                if (error.response){
                    console.log('error');
                }
            });
        }else{
            $('#editInstallmentForm .installmentAmountWrap').fadeOut('fast', function(){
                $('#editInstallmentForm [name="amount"]').val('');
            });
            $('#editInstallmentForm .installmentAmountNotice').fadeOut();
        }
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
                }, 2000);
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
            if(res.commission > 0){
                $('#addInstallmentModal .totalAmount').html('<del class="text-slate-400 mr-2">'+res.total_amount_html+'</del>'+' '+res.total_amount_after_commission_html);
            }else{
                $('#addInstallmentModal .totalAmount').html(res.total_amount_html);
            }

            $('#addInstallmentModal [name="remaining_amount"]').val(res.remaining_amount);
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

    $('#addInstallmentForm [name="session_term"]').on('change', function(){
        var $theSession = $(this);
        var theSession = $theSession.val();
        var student_id = $('#addInstallmentModal [name="student_id"]').val();
        var slc_agreement_id = $('#addInstallmentModal [name="slc_agreement_id"]').val();
        var totalAmount = parseInt($('#addInstallmentForm [name="total_amount"]').val(), 10);
        var remainingAmount = parseInt($('#addInstallmentForm [name="remaining_amount"]').val(), 10);

        if(theSession > 0){
            var installment_amount = 0;
            if(theSession != '' && theSession != '' && theSession > 0){
                if(theSession == 1 || theSession == 2){
                    installment_amount = totalAmount * .25;
                }else if(theSession == 3){
                    installment_amount = totalAmount * .50;
                }
            }

            axios({
                method: "post",
                url: route('student.slc.intallment.existence'),
                data: {theSession : theSession, slc_agreement_id : slc_agreement_id, student_id : student_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    if(response.data.res > 0){
                        var newRemainingAmount = remainingAmount - installment_amount;
                        
                        $('#addInstallmentForm .installmentAmountNotice').fadeOut();
                        $('#addInstallmentForm .installmentAmountWrap').fadeIn('fast', function(){
                            $('#addInstallmentForm .remainingAmount').html('£'+newRemainingAmount.toFixed(2));
                            if(installment_amount > 0){
                                $('#addInstallmentForm [name="amount"]').val(installment_amount.toFixed(2));
                            }else{
                                $('#addInstallmentForm [name="amount"]').val('');
                            }
                        });
                    }else{
                        $('#addInstallmentForm .installmentAmountWrap').fadeOut('fast', function(){
                            $('#addInstallmentForm [name="amount"]').val('');
                            $('#addInstallmentForm .installmentAmountNotice').fadeIn();
                        });
                    }
                }
            }).catch(error => {
                if (error.response){
                    console.log('error');
                }
            });
        }else{
            $('#addInstallmentForm .installmentAmountWrap').fadeOut('fast', function(){
                $('#addInstallmentForm [name="amount"]').val('');
            });
            $('#addInstallmentForm .installmentAmountNotice').fadeOut();
        }
    })

    $('#addInstallmentForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
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
                }, 2000);
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
                }else if (error.response.status == 304) {
                    $form.find('.alert').remove();
                    $('.modal-content', $form).prepend('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Selected session term already exist for this agreement.</div>')
                
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

    $('.deleteInstallmentBtn').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var recordid  = $theLink.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this payment plan from the list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordid);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETEINST');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEINST'){
            axios({
                method: 'delete',
                url: route('student.destory.slc.intallment'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s payment plan successfully deleted.');
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