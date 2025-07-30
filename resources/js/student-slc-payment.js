import { set } from "lodash";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const addPaymentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPaymentModal"));
    const editPaymentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPaymentModal"));
    const sendMailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendMailModal"));

    const addPaymentModalEl = document.getElementById('addPaymentModal')
    addPaymentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addPaymentModal .acc__input-error').html('');
        $('#addPaymentModal .modal-body select').val('');
        $('#addPaymentModal .modal-body input').val('');
        $('#addPaymentModal .modal-body textarea').val('');

        $('#editInstallmentModal [name="slc_agreement_id"]').val(0);
    });

    const editPaymentModalEl = document.getElementById('editPaymentModal')
    editPaymentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editPaymentModal .acc__input-error').html('');
        $('#editPaymentModal .modal-body select').val('');
        $('#editPaymentModal .modal-body input').val('');
        $('#editPaymentModal .modal-body textarea').val('');

        $('#editPaymentModal [name="id"]').val(0);
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

    $('.addPaymentBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var agreement_id = $theBtn.attr('data-agr-id');
        var invoice_no = Math.floor(Date.now() / 1000);

        $('#addPaymentModal [name="invoice_no"]').val(invoice_no);
        $('#addPaymentModal [name="slc_agreement_id"]').val(agreement_id);
    });

    $('.sendAccountMailBtn').on('click', function(e){
        e.preventDefault();
        
        var $theBtn = $(this);
        var payment_id = $theBtn.attr('data-id');
        var student_id = $theBtn.attr('data-student');
        $("#sendMailModal .agreeWith").attr('data-payment_id', payment_id);
        sendMailModal.show();
        // document.getElementById("sendMailModal").addEventListener("shown.tw.modal", function (event) {
        //     setTimeout(function(){
        //         $(".ring-loading").removeClass('hidden');
        //         $(".success-on").addClass('hidden');
        //     }, 1000);
        //     $("#sendMailModal .sendMailModalTitle").html("Sending Mail" );
            
        // });
    });

    $('#sendMailModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let payment_id = $agreeBTN.attr('data-payment_id');
        let student = $agreeBTN.attr('data-student');
        $('#sendMailModal button').attr('disabled', 'disabled');
        $("#sendMailModal .sendMailModalTitle").html("Sending Mail..." );
        $("#sendMailModal .sendMailModalDesc").html("Please wait while we send the email." );
        $(".ring-loading").removeClass('hidden');
        $(".success-on").addClass('hidden');
        axios({
                method: 'get',
                url: route('student.accounts.send_mail',[student, payment_id]),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#sendMailModal button').removeAttr('disabled');
                    
                    $(".ring-loading").addClass('hidden');
                    $(".success-on").removeClass('hidden');
                    $("#sendMailModal .sendMailModalTitle").html("Email the money receipt?" );
                    $("#sendMailModal .sendMailModalDesc").html("Money receipt will send to student e-mail" );
                    
                    sendMailModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s payment mail successfully sent.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);

                }
            }).catch(error =>{
                console.log(error);
                $('#sendMailModal button').removeAttr('disabled');
                $(".ring-loading").addClass('hidden');
                $(".success-on").removeClass('hidden');
                $("#sendMailModal .sendMailModalTitle").html("Email the money receipt?" );
                $("#sendMailModal .sendMailModalDesc").html("Money receipt will send to student e-mail" );
            });
    });

    $('#addPaymentForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('addPaymentForm');
    
        document.querySelector('#savePayment').setAttribute('disabled', 'disabled');
        document.querySelector("#savePayment svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.store.slc.payment'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#savePayment').removeAttribute('disabled');
            document.querySelector("#savePayment svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addPaymentModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student payment successfully add under selected agreement.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#savePayment').removeAttribute('disabled');
            document.querySelector("#savePayment svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addPaymentForm .${key}`).addClass('border-danger');
                        $(`#addPaymentForm  .error-${key}`).html(val);
                    }
                }else {
                    console.log('error');
                }
            }
        });
    });

    $('.editPaymentBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var payment_id = $theBtn.attr('data-id');

        axios({
            method: "post",
            url: route('student.edit.slc.payment'),
            data: {payment_id : payment_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            var res = response.data.res;

            $('#editPaymentModal [name="invoice_no"]').val(res.invoice_no);
            $('#editPaymentModal [name="payment_date"]').val(res.payment_date);
            $('#editPaymentModal [name="slc_payment_method_id"]').val(res.slc_payment_method_id);

            $('#editPaymentModal [name="term_declaration_id"]').val(res.term_declaration_id);
            $('#editPaymentModal [name="session_term"]').val(res.session_term);
            $('#editPaymentModal [name="amount"]').val(res.amount);
            $('#editPaymentModal [name="payment_type"]').val(res.payment_type);
            $('#editPaymentModal [name="remarks"]').val(res.remarks);

            $('#editPaymentModal [name="id"]').val(payment_id);

        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#editPaymentForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('editPaymentForm');
    
        document.querySelector('#updatePayment').setAttribute('disabled', 'disabled');
        document.querySelector("#updatePayment svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.slc.payment'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updatePayment').removeAttribute('disabled');
            document.querySelector("#updatePayment svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editPaymentModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student payment successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updatePayment').removeAttribute('disabled');
            document.querySelector("#updatePayment svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editPaymentForm .${key}`).addClass('border-danger');
                        $(`#editPaymentForm  .error-${key}`).html(val);
                    }
                }else {
                    console.log('error');
                }
            }
        });
    });

    $('.deletePaymentBtn').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var recordid  = $theLink.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this payment from the list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordid);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETE');
        });
    });

    $(document).on('click', '.assignPaymentToAgr', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let agr_id = $theBtn.attr('data-agr');
        let pay_id = $theBtn.attr('data-pay');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to move this #'+pay_id+' payment to #'+agr_id+' agreement?');
            $("#confirmModal .agreeWith").attr('data-recordid', agr_id+'_'+pay_id);
            $("#confirmModal .agreeWith").attr('data-status', 'ASSIGNPAYTOAGR');
        });
    })

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('student.destory.slc.payment'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s payment  successfully deleted.');
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
        }else if(action == 'ASSIGNPAYTOAGR'){
            axios({
                method: 'post',
                url: route('student.sync.slc.payment.to.agreement'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s payment  successfully deleted.');
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