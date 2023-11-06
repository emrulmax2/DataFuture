import IMask from 'imask';

(function(){
    const empHolidayAdjustmentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#empHolidayAdjustmentModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    const empHolidayAdjustmentModalEl = document.getElementById('empHolidayAdjustmentModal')
    empHolidayAdjustmentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#empHolidayAdjustmentModal [name="adjustmentOpt"]').prop('checked', false);
        $('#empHolidayAdjustmentModal [name="adjustment"]').val('');
        $('#empHolidayAdjustmentModal [name="hr_holiday_year_id"]').val('0');
        $('#empHolidayAdjustmentModal [name="employee_working_pattern_id"]').val('0');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });

    if($('input[name="adjustment"]').length > 0){
        var maskOptions = {
            mask: '00:00'
        };
        $('input[name="adjustment"]').each(function(){
            var mask = IMask(this, maskOptions);
        })
    }


    $('.holidayAdjustmentBtn').on('click', function(){
        var year = $(this).attr('data-year');
        var pattern = $(this).attr('data-pattern');

        $('#empHolidayAdjustmentModal [name="hr_holiday_year_id"]').val(year);
        $('#empHolidayAdjustmentModal [name="employee_working_pattern_id"]').val(pattern);
    })

    $('#empHolidayAdjustmentModal [name="adjustmentOpt"]').on('change', function(){
        if($('#empHolidayAdjustmentModal [name="adjustmentOpt"]:checked').length > 0){
            $('#empHolidayAdjustmentModal [name="adjustment"]').val('').removeAttr('disabled');
        }else{
            $('#empHolidayAdjustmentModal [name="adjustment"]').val('').attr('disabled', 'disabled');
        }
    });

    $('#empHolidayAdjustmentForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('empHolidayAdjustmentForm');

        $('#empHolidayAdjustmentForm').find('input').removeClass('border-danger')
        $('#empHolidayAdjustmentForm').find('.acc__input-error').html('')

        document.querySelector('#updateADJ').setAttribute('disabled', 'disabled');
        document.querySelector('#updateADJ svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route('employee.holiday.update.adjustment'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateADJ').removeAttribute('disabled');
            document.querySelector('#updateADJ svg').style.cssText = 'display: none;';
            
            if (response.status == 200) {
                empHolidayAdjustmentModal.hide();
                
                successModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Congratulations!');
                    $('#successModal .successModalDesc').html('Holiday adjustment successfully updated.');
                    $('#successModal .successCloser').attr('data-action', 'RELOAD');
                });

                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 5000);
            } 
        }).catch(error => {
            document.querySelector('#updateADJ').removeAttribute('disabled');
            document.querySelector('#updateADJ svg').style.cssText = 'display: none;';
            if(error.response){
                if(error.response.status == 422){
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#empHolidayAdjustmentForm .${key}`).addClass('border-danger')
                        $(`#empHolidayAdjustmentForm  .error-${key}`).html(val)
                    }
                }else{
                    console.log('error');
                }
            }
        });
    });


})();