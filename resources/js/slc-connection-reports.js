

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#checkAll').on('click', function(e){
        if($(this).prop('checked')){
            $('#connectionListTable .slc_money_receipt_id').each(function(){
                $(this).prop('checked', true);
                $(this).closest('.receiptRow').addClass('selectedRow');
            });
        }else{
            $('#connectionListTable .slc_money_receipt_id').each(function(){
                $(this).prop('checked', false);
                $(this).closest('.receiptRow').removeClass('selectedRow');
            });
        }

        calculateMoneyRecipt();
    })

    $('#connectionListTable').on('change', '.slc_money_receipt_id', function(){
        if($(this).prop('checked')){
            $(this).closest('.receiptRow').addClass('selectedRow');
        }else{
            $(this).closest('.receiptRow').removeClass('selectedRow');
        }

        calculateMoneyRecipt();
    });

    $(window).on('load', function(){
        calculateMoneyRecipt();
    });

    function calculateMoneyRecipt(){
        let transAmount = $('#transaction_amount').val() * 1;
        let received = 0;
        let refund = 0;
        $('#connectionListTable tr.selectedRow').each(function(){
            let $theRow = $(this);
            let rowType = $theRow.find('.payment_type').val();
            let rowAmount = $theRow.find('.amount').val() * 1;

            if(rowType == 'Refund'){
                refund += rowAmount;
            }else{
                received += rowAmount;
            }
        });

        $('#totalCourseFees').html('£'+received.toFixed(2));
        $('#totalRefunds').html('£'+refund.toFixed(2));

        let totalReceived = received - refund;
        if(transAmount == totalReceived.toFixed(2)){
            $('#transactionAmount').addClass('text-success');
            $('#saveConnectionBtn').fadeIn('fast');
        }else{
            $('#transactionAmount').removeClass('text-success');
            $('#saveConnectionBtn').fadeOut('fast');
        }
        //console.log(transAmount+' - '+received+' - '+refund+' - '+totalReceived);
    }

    $('#transConnectionForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('transConnectionForm');
    
        $('#saveConnectionBtn').attr('disabled', 'disabled');
        $("#saveConnectionBtn svg.loaders").fadeIn();

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('reports.accounts.transaction.connection.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#saveConnectionBtn').removeAttr('disabled');
            $("#saveConnectionBtn svg.loaders").fadeOut();
            
            if (response.status == 200) {

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html(response.data.msg);
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });   
                
                setTimeout(() => {
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            $('#saveConnectionBtn').removeAttr('disabled');
            $("#saveConnectionBtn svg.loaders").fadeOut();
            if (error.response) {
                if (error.response.status == 422) {
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html( "Congratulations!" );
                        $("#warningModal .warningModalDesc").html(error.response.data.msg);
                    });   
                    
                    setTimeout(() => {
                        successModal.hide();
                    }, 2000);
                } else {
                    console.log('error');
                }
            }
        });
    });
})();