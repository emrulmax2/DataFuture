import TomSelect from "tom-select";

(function(){
    let accTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let accTomOptionsMul = {
        ...accTomOptions,
        plugins: {
            ...accTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    let inToms = [];
    let outToms = [];
    let strToms = [];
    $('#csvTransTable .csvInToms').each(function(){
        var $theSelect = $(this);
        var theName = $theSelect.attr('name');
        inToms[theName] = new TomSelect(this, accTomOptions);
    })
    $('#csvTransTable .csvOutToms').each(function(){
        var $theSelect = $(this);
        var theName = $theSelect.attr('name');
        outToms[theName] = new TomSelect(this, accTomOptions);
    })
    $('#csvTransTable .csvStrToms').each(function(){
        var $theSelect = $(this);
        var theName = $theSelect.attr('name');
        strToms[theName] = new TomSelect(this, accTomOptions);
    })

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));


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


    $('#csvTransTable').on('change', '.transaction_type', function(){
        let $trans_type = $(this);
        let trans_type = $trans_type.val();

        let $theTr = $trans_type.closest('tr.transaction_row');
        let tr_id = $theTr.attr('id');

        let file_id = $theTr.attr('data-fileid');
        let trns_id = $theTr.attr('data-transid');

        let in_select = 'trans_'+file_id+'_'+trns_id+'_inccategory';
        let inTom = inToms[in_select];
        let out_select = 'trans_'+file_id+'_'+trns_id+'_expcategory';
        let outTom = outToms[out_select];
        let str_select = 'trans_'+file_id+'_'+trns_id+'_transstorage';
        let strTom = strToms[str_select];

        if(trans_type == 2){
            $theTr.find('.inTomWrap').fadeOut();
            $theTr.find('.outTomWrap').fadeOut();
            $theTr.find('.strgTomWrap').fadeIn();

            inTom.clear(true);
            outTom.clear(true);
            strTom.clear(true);
            //$theTr.find('.inc_category, .exp_category').val('').fadeOut();
            //$theTr.find('.trans_storage').val('').fadeIn();
        }else if(trans_type == 1){
            $theTr.find('.inTomWrap').fadeOut();
            $theTr.find('.strgTomWrap').fadeOut();
            $theTr.find('.outTomWrap').fadeIn();

            inTom.clear(true);
            outTom.clear(true);
            strTom.clear(true);
            //$theTr.find('.inc_category, .trans_storage').val('').fadeOut();
            //$theTr.find('.exp_category').val('').fadeIn();
        }else{
            $theTr.find('.inTomWrap').fadeIn();
            $theTr.find('.strgTomWrap').fadeOut();
            $theTr.find('.outTomWrap').fadeOut();

            inTom.clear(true);
            outTom.clear(true);
            strTom.clear(true);
            //$theTr.find('.exp_category, .trans_storage').val('').fadeOut();
            //$theTr.find('.inc_category').val('').fadeIn();
        }

        validateRow(tr_id);
    });

    $('#csvTransTable').on('change', '.inc_category, .exp_category, .trans_storage', function(){
        let $theTr = $(this).closest('tr.transaction_row');
        let tr_id = $theTr.attr('id');

        validateRow(tr_id);
    })

    $('.rowExpense').on('keyup paste change', function(){
        let $theTr = $(this).closest('tr.transaction_row');
        let tr_id = $theTr.attr('id');

        $theTr.find('.rowIncome').val('');
        validateRow(tr_id);
    })

    $('.rowIncome').on('keyup paste change', function(){
        let $theTr = $(this).closest('tr.transaction_row');
        let tr_id = $theTr.attr('id');

        $theTr.find('.rowExpense').val('');
        validateRow(tr_id);
    })

    function validateRow(row_id){
        let $theTr = $('#'+row_id);
        var transaction_type = $theTr.find('.transaction_type').val();
        var rowExpense = $theTr.find('.rowExpense').val();
        var rowIncome = $theTr.find('.rowIncome').val();

        let errors = 0;
        if(rowExpense == '' && rowIncome == ''){
            errors += 1;
        }
        if(transaction_type == 2 && $theTr.find('.trans_storage').val() == ''){
            errors += 1;
        }
        if(transaction_type == 1 && $theTr.find('.exp_category').val() == ''){
            errors += 1;
        }
        if(transaction_type == 0 && $theTr.find('.inc_category').val() == ''){
            errors += 1;
        }

        if(errors > 0){
            $theTr.find('.saveCsvTransRow').fadeOut();
        }else{
            $theTr.find('.saveCsvTransRow').fadeIn();
        }
    }


    $('#csvTransTable').on('click', '.saveCsvTransRow', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let id = $theBtn.attr('data-id');
        let file_id = $theBtn.attr('data-file-id');
        let $theTr = $('#transaction_row_'+id);

        $('#csvTransTable').find('.saveCsvTransRow').attr('disabled', 'disabled');

        let inputs = $theTr.find('input:not([type="file"])').serialize();
        let texts = $theTr.find('textarea').serialize();
        let uploads = $('#trans_up_'+id)[0].files[0];

        let formData = new FormData();
        $theTr.find('input:not([type="file"]):not([type="checkbox"]):not(.dropdown-input)').each(function(){
            var theNameOrg = $(this).attr('name');
            var theNameArr = theNameOrg.split('_');
            var theName = theNameArr[theNameArr.length - 1];
            formData.append(theName, $(this).val()); 
        });
        $theTr.find('textarea').each(function(){
            var theNameOrg = $(this).attr('name');
            var theNameArr = theNameOrg.split('_');
            var theName = theNameArr[theNameArr.length - 1];
            formData.append(theName, $(this).val()); 
        });
        $theTr.find('select').each(function(){
            var theNameOrg = $(this).attr('name');
            var theNameArr = theNameOrg.split('_');
            var theName = theNameArr[theNameArr.length - 1];
            formData.append(theName, $(this).val()); 
        });
        $theTr.find('input[type="checkbox"]').each(function(){
            var theNameOrg = $(this).attr('name');
            var theNameArr = theNameOrg.split('_');
            var theName = theNameArr[theNameArr.length - 1];
            if($(this).prop('checked')){
                formData.append(theName, 1);
            }else{
                formData.append(theName, 0);
            }
        });
        formData.append('acc_csv_transaction_id', id); 
        formData.append('acc_csv_file_id', file_id); 
        formData.append('document', $('#trans_up_'+id)[0].files[0]); 

        axios({
            method: "post",
            url: route('accounts.csv.update'),
            data: formData,
            headers: {
                'content-type': 'multipart/form-data',
                'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')
            },
        }).then(response => {
            if (response.status == 200) {
                let red = response.data.red;
                $theTr.remove();
                $('#csvTransTable').find('.saveCsvTransRow').removeAttr('disabled');

                successModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Congratulations!');
                    $('#successModal .successModalDesc').html('CSV transaction successfully inserted to transaction.');
                    $('#successModal .successCloser').attr('data-action', 'RELOAD').attr('data-redirect', red);
                });

                setTimeout(function(){
                    successModal.hide();
                    if(red != 'NONE'){
                        window.location.href = red;
                    }else{
                        window.location.reload();
                    }
                }, 500);
            }
        }).catch(error => {
            $('#csvTransTable').find('.saveCsvTransRow').removeAttr('disabled');
            if(error.response){
                console.log('error');
            }
        });
    })
})()