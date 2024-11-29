import { createIcons, icons } from "lucide";
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


    let acc_category_id_in = new TomSelect('#acc_category_id_in', accTomOptions);
    let acc_category_id_out = new TomSelect('#acc_category_id_out', accTomOptions);
    let acc_bank_id = new TomSelect('#acc_bank_id', accTomOptions);

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#income').on('keyup paste change', function(){
        $('#expense').val('');
    });
    $('#expense').on('keyup paste change', function(){
        $('#income').val('');
    });

    $('#trans_type').on('change', function(e){
        let $trans_type = $(this);
        let trans_type = $trans_type.val();

        if(trans_type == 2){
            $('#acc_category_id_in_wrap, #acc_category_id_out_wrap').fadeOut('fast', function(){
                $('#acc_bank_id_wrap').fadeIn();
                acc_bank_id.clear(true);
                acc_category_id_in.clear(true);
                acc_category_id_out.clear(true);
            });
        }else if(trans_type == 1){
            $('#acc_category_id_in_wrap, #acc_bank_id_wrap').fadeOut('fast', function(){
                acc_category_id_out.clear(true);
                $('#acc_category_id_out_wrap').fadeIn();
                acc_bank_id.clear(true);
                acc_category_id_in.clear(true);
            });
        }else{
            $('#acc_category_id_out_wrap, #acc_bank_id_wrap').fadeOut('fast', function(){
                $('#acc_category_id_in_wrap').fadeIn();
                acc_bank_id.clear(true);
                acc_category_id_in.clear(true);
                acc_category_id_out.clear(true);
            });
        }
    });

    $(document).on('click', '#cancelEdit', function(e){
        e.preventDefault();
        $('#editTransactionFormWrap').fadeOut('fast', function(){
            $('#storageTransactionForm input:not([type="checkbox"]):not("#transaction_date"):not([type="file"])').val('');
            $('#transaction_document').val('');
            $('#storageTransactionForm input[type="checkbox"]').prop('checked', true);
            $('#trans_type').val('0');

            $('#acc_category_id_out_wrap, #acc_bank_id_wrap').val('').fadeOut();
            $('#acc_category_id_in_wrap').fadeIn();
            acc_bank_id.clear(true);
            acc_category_id_in.clear(true);
            acc_category_id_out.clear(true);

            $('#income').val('');
            $('#expense').val('');
            $('#storageTransactionForm #transaction_id').val('0');
            $('#storageTransactionForm #deleteTransaction').fadeOut().attr('data-id', '0');
            $('#storageTransactionForm #transaction_date').val($('#storageTransactionForm #transaction_date').attr('data-today'));
        });
    });

    $('#transactionListTable').on('click', '.editTransaction', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('accounts.storage.trans.edit'),
            data: {transaction_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let row = response.data.res;

                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                
                $('#editTransactionFormWrap').fadeIn('fast', function(){
                    $('#storageTransactionForm #transaction_date').val(row.transaction_date_2);
                    $('#storageTransactionForm #detail').val(row.detail);
                    $('#storageTransactionForm #trans_type').val(row.transaction_type);

                    $('#storageTransactionForm #deleteTransaction').fadeIn().attr('data-id', row.id)
                    if(row.transaction_type == 0){
                        $('#expense').val((row.flow == 1 ? row.transaction_amount : ''));
                        $('#income').val((row.flow == 0 ? row.transaction_amount : ''));

                        $('#acc_category_id_in_wrap').fadeIn();
                        acc_category_id_in.addItem(row.acc_category_id)

                        acc_category_id_out.clear(true);
                        acc_bank_id.clear(true);
                        $('#acc_category_id_out_wrap').fadeOut();
                        $('#acc_bank_id_wrap').fadeOut();

                        $('#storeTransaction').fadeIn();
                    }else if(row.transaction_type == 1){
                        $('#expense').val((row.flow == 1 ? row.transaction_amount : ''));
                        $('#income').val((row.flow == 0 ? row.transaction_amount : ''));

                        acc_category_id_in.clear(true)
                        $('#acc_category_id_in_wrap').fadeOut();
                        $('#acc_category_id_out_wrap').fadeIn();
                        acc_category_id_out.addItem(row.acc_category_id);
                        acc_bank_id.clear(true)
                        $('#acc_bank_id_wrap').fadeOut();

                        $('#storeTransaction').fadeIn();
                    }else if(row.transaction_type == 2){
                        $('#expense').val((row.flow == 1 ? row.transaction_amount : ''));
                        $('#income').val((row.flow == 0 ? row.transaction_amount : ''));

                        acc_category_id_in.clear(true);
                        acc_category_id_out.clear(true);
                        $('#acc_category_id_in_wrap').fadeOut();
                        $('#acc_category_id_out_wrap').fadeOut();
                        $('#acc_bank_id').fadeIn();
                        acc_bank_id.addItem(row.transfer_bank_id);

                        $('#storeTransaction').fadeOut();
                    }
                    $('#storageTransactionForm #invoice_no').val(row.invoice_no);
                    $('#storageTransactionForm #invoice_date').val(row.invoice_date);
                    $('#storageTransactionForm #description').val(row.description);
                    if(row.audit_status == 1){
                        $('#storageTransactionForm #audit_status').prop('checked', true);
                    }else{
                        $('#storageTransactionForm #audit_status').prop('checked', false);
                    }
                    $('#storageTransactionForm #transaction_id').val(row.id);
                    $('#storageTransactionForm #storage_id').val(row.acc_bank_id);
                });
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });

    $('#transactionListTable').on('click', '.downloadTransDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('accounts.storage.trans.download.link'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });
})();