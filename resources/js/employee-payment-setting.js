import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let tomOptionsMul = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };
    var hour_authorised_by = new TomSelect('#hour_authorised_by', tomOptionsMul);
    var holiday_authorised_by = new TomSelect('#holiday_authorised_by', tomOptionsMul);

    var edit_hour_authorised_by = new TomSelect('#edit_hour_authorised_by', tomOptionsMul);
    var edit_holiday_authorised_by = new TomSelect('#edit_holiday_authorised_by', tomOptionsMul);

    
    const addEmployeePaymentSettingModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmployeePaymentSettingModal"));
    const editEmployeePaymentSettingModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmployeePaymentSettingModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });

    $('#addEmployeePaymentSettingForm [name="payment_method"]').on('change', function(e){
        var $input = $(this);
        var method = $input.val();

        if(method == 'Bank Transfer'){
            $('#addEmployeePaymentSettingForm .bankDetailsArea').fadeIn('fast', function(){
                $('input', this).val('');
            })
        }else{
            $('#addEmployeePaymentSettingForm .bankDetailsArea').fadeOut('fast', function(){
                $('input', this).val('');
            })
        }
    });

    $('#addEmployeePaymentSettingForm [name="subject_to_clockin"]').on('change', function(e){
        var $input = $(this);

        if($input.prop('checked')){
            $('#addEmployeePaymentSettingForm .hourAuthorisedByArea').fadeIn('fast', function(){
                hour_authorised_by.clear(true);
            })
        }else{
            $('#addEmployeePaymentSettingForm .hourAuthorisedByArea').fadeOut('fast', function(){
                hour_authorised_by.clear(true);
            })
        }
    });

    $('#editEmployeePaymentSettingForm [name="subject_to_clockin"]').on('change', function(e){
        var $input = $(this);

        if($input.prop('checked')) {
            
            $('#editEmployeePaymentSettingForm .hourAuthorisedByArea').fadeIn('fast', function(){
                $('input:not([type="checkbox"])', this).val('');
                $('input[type="checkbox"]', this).prop('checked', false);
                edit_hour_authorised_by.clear(true);
            })
        }else{

            $('#editEmployeePaymentSettingForm .hourAuthorisedByArea').fadeOut('fast', function(){
                
                $('input:not([type="checkbox"])', this).val('');
                $('input[type="checkbox"]', this).prop('checked', false);
                edit_hour_authorised_by.clear(true);
            })
        }
    });

    $('#addEmployeePaymentSettingForm [name="holiday_entitled"]').on('change', function(e){
        var $input = $(this);

        if($input.prop('checked')){

            $('#addEmployeePaymentSettingForm .holidayEntitlementArea').fadeIn('fast', function(){

                $('input:not([type="checkbox"])', this).val('');
                $('input[type="checkbox"]', this).prop('checked', false);

                holiday_authorised_by.clear(true);
            });
        }else{

            $('#addEmployeePaymentSettingForm .holidayEntitlementArea').fadeOut('fast', function(){
                $('input:not([type="checkbox"])', this).val('');
                $('input[type="checkbox"]', this).prop('checked', false);
                holiday_authorised_by.clear(true);
            });
        }
    });

    $('#editEmployeePaymentSettingForm [name="holiday_entitled"]').on('change', function(e){
        var $input = $(this);

        if($input.prop('checked')){
            $('#editEmployeePaymentSettingForm .holidayEntitlementArea').fadeIn('fast', function(){
                $('input:not([type="checkbox"])', this).val('');
                $('input[type="checkbox"]', this).prop('checked', false);
                edit_holiday_authorised_by.clear(true);
            });
        }else{
            $('#editEmployeePaymentSettingForm .holidayEntitlementArea').fadeOut('fast', function(){
                $('input:not([type="checkbox"])', this).val('');
                $('input[type="checkbox"]', this).prop('checked', false);
                edit_holiday_authorised_by.clear(true);
            });
        }
    });

    $('#addEmployeePaymentSettingForm [name="pension_enrolled"]').on('change', function(e){
        var $input = $(this);

        if($input.prop('checked')){
            $('#addEmployeePaymentSettingForm .penssionEnrolledArea').fadeIn('fast', function(){
                $('input', this).val('');
                $('select', this).val('');
            });
        }else{
            $('#addEmployeePaymentSettingForm .penssionEnrolledArea').fadeOut('fast', function(){
                $('input', this).val('');
                $('select', this).val('');
            });
        }
    });

    if($('input[name="sort_code"]').length > 0){
        var maskOptions = {
            mask: '00-00-00'
        };
        $('input[name="sort_code"]').each(function(){
            var mask = IMask(this, maskOptions);
        })
    }

    $('#addEmployeePaymentSettingForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addEmployeePaymentSettingForm');
    
        document.querySelector('#savePBS').setAttribute('disabled', 'disabled');
        document.querySelector("#savePBS svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.payment.settings.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#savePBS').removeAttribute('disabled');
            document.querySelector("#savePBS svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addEmployeePaymentSettingModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee\'s Payment Settings successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });   
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000)
            }
        }).catch(error => {
            document.querySelector('#savePBS').removeAttribute('disabled');
            document.querySelector("#savePBS svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addEmployeePaymentSettingForm .${key}`).addClass('border-danger');
                        $(`#addEmployeePaymentSettingForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#editEmployeePaymentSettingForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editEmployeePaymentSettingForm');
    
        document.querySelector('#updatePBS').setAttribute('disabled', 'disabled');
        document.querySelector("#updatePBS svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.payment.settings.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updatePBS').removeAttribute('disabled');
            document.querySelector("#updatePBS svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editEmployeePaymentSettingModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee\'s Payment Settings successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });   
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000)
            }
        }).catch(error => {
            document.querySelector('#updatePBS').removeAttribute('disabled');
            document.querySelector("#updatePBS svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editEmployeePaymentSettingForm .${key}`).addClass('border-danger');
                        $(`#editEmployeePaymentSettingForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})();