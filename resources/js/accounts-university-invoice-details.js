

(function(){
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#successModal button.successCloser').attr('data-action', 'none').attr('data-redirect', 'NONE');
    });

    $(document).on('click', '.successCloser', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            if($(this).attr('data-redirect') != 'NONE'){
                window.location.href = $(this).attr('data-redirect');
            }else{
                window.location.reload();
            }
        }else{
            successModal.hide();
        }
    })


    $(document).on('change', '.checkUncheckAll', function () {
        let checked = $(this).is(':checked');
        $('#universityClaimStudentsTable tbody .installMentCheck').prop('checked', checked);
        calculateInstallment();
    });

    $(document).on('change', '#universityClaimStudentsTable tbody .installMentCheck', function () {
        let total = $('#universityClaimStudentsTable tbody .installMentCheck').length;
        let checked = $('#universityClaimStudentsTable tbody .installMentCheck:checked').length;

        $('.checkUncheckAll').prop('checked', total === checked);
        calculateInstallment();
    });

    function calculateInstallment(){
        let noOfStudent = 0;
        let totalAmount = 0;

        $('#universityClaimStudentsTable tbody tr').each(function(){
            let $tr = $(this);
            let $checks = $tr.find('.installMentCheck');

            if($checks.prop('checked')){
                noOfStudent += 1;
                let rowAmount = $tr.find('.installmentAmount').val() ? $tr.find('.installmentAmount').val() * 1 : 0;
                totalAmount += rowAmount;
            }
        });

        if(noOfStudent > 0){
            $('.selectedInstStats').fadeIn('fast', function(){
                $('.noOfStd').text('No of Student: '+noOfStudent);
                $('.totalAmnt').text('Total: £'+totalAmount.toFixed(2));
            })
        }else{
            $('.selectedInstStats').fadeOut('fast', function(){
                $('.noOfStd').text('');
                $('.totalAmnt').text('');
            })
        }
    }

    $('#uniPayInvoicedForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        let $theBtn = $form.find('#createInvoice');
        const form = document.getElementById('uniPayInvoicedForm');

        let university_payment_claim_id = $('#uniPayInvoicedForm input[name="university_payment_claim_id"]').val();
        var university_payment_claim_student_ids = [];
        $('#universityClaimStudentsTable tbody').find('.installMentCheck:checked').each(function(){
            university_payment_claim_student_ids.push($(this).val());
        });
    
        $theBtn.attr('disabled', 'disabled');
        $theBtn.find("svg").fadeIn();

        axios({
            method: "post",
            url: route('university.claims.invoices.store'),
            data: { university_payment_claim_id : university_payment_claim_id, university_payment_claim_student_ids : university_payment_claim_student_ids},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $theBtn.removeAttr('disabled');
            $theBtn.find("svg").fadeOut();
            
            if (response.status == 200) {
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Invoices successfully created.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD').attr('data-redirect', response.data.red);
                });  
                
                setTimeout(() => {
                    successModal.hide();
                    if(response.data.red && response.data.red != ''){
                        window.location.href = response.data.red
                    }else{
                        window.location.reload();
                    }
                }, 2000);
            }
        }).catch(error => {
            $theBtn.removeAttr('disabled');
            $theBtn.find("svg").fadeOut();
            if (error.response) {
                console.log('error');
            }
        });
    });
})()