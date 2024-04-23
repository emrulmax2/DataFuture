

(function(){

    $('#trans_type').on('change', function(e){
        let $trans_type = $(this);
        let trans_type = $trans_type.val();

        if(trans_type == 2){
            $('#acc_category_id_in, #acc_category_id_out').val('').fadeOut('fast', function(){
                $('#acc_bank_id').fadeIn().val('');
            });
            $('#expense, #income').removeAttr('readonly').val('');
        }else if(trans_type == 1){
            $('#acc_category_id_in, #acc_bank_id').val('').fadeOut('fast', function(){
                $('#acc_category_id_out').fadeIn().val('');
            });
            $('#expense').removeAttr('readonly').val('');
            $('#income').attr('readonly', 'readonly').val('');
        }else{
            $('#acc_category_id_out, #acc_bank_id').val('').fadeOut('fast', function(){
                $('#acc_category_id_in').fadeIn().val('');
            });
            $('#income').removeAttr('readonly').val('');
            $('#expense').attr('readonly', 'readonly').val('');
        }
    })

})()