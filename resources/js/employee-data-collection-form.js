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
    var workpermit_type_tom = new TomSelect('#workpermit_type', tomOptions);
    var employee_work_type_tom = new TomSelect('#employee_work_type', tomOptions);
    
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })
    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            warningModal.hide();
        }
    })

    $('.lccToms').each(function(){
        if ($(this).attr("multiple") !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: "Remove this item",
                    },
                }
            };
        }
        new TomSelect(this, tomOptions);
    })
    
    $(".date-picker").each(function () {
        var maskOptions = {
            mask: Date,
            min: new Date(1900, 0, 1),
            max: new Date(2050, 0, 1),
            lazy: false
        };
        var mask = IMask(this, maskOptions);
    });

    $(".ni-number").each(function () {
        var maskOptions = {
            mask: 'aa-000000-a'
        };
        var mask = IMask(this, maskOptions);
    });

    $(".sortCode").each(function () {
        var maskOptions = {
            mask: '00-00-00'
        };
        var mask = IMask(this, maskOptions);
    });

    $(".account_number").each(function () {
        var maskOptions = {
            mask: '00000000'
        };
        var mask = IMask(this, maskOptions);
    });

    $('.inputUppercase').on('keyup', function() {
		$(this).val($(this).val().toUpperCase());
	});

    $('#disability_status').on('change', function() {
        let tthis = $(this)
        let disabilityItems = $("#disabilityItems");
        if(tthis.prop('checked')){
            disabilityItems.fadeIn('fast', function(){
                $('input[type="checkbox"]',disabilityItems).prop('checked', false);
            });
        }else{
            disabilityItems.fadeOut('fast', function(){
                disabilityItems.prop('checked', false);
            });
        }
    });

    $('#eligible_to_work_status').on('change', function() {
        let $eligible_to_work_status = $(this);

        if($eligible_to_work_status.prop('checked')){
            workpermit_type_tom.clear(true);
            $('.workPermitTypeFields').fadeIn();
            $('#workpermit_type').addClass('tomRequire')
            $('.workPermitTypeFields .acc__input-error').html('');
        }else{
            workpermit_type_tom.clear(true);
            $('.workPermitTypeFields').fadeOut();
            $('#workpermit_type').removeClass('tomRequire')
            $('.workPermitTypeFields .acc__input-error').html('');

            $('.workPermitFields').fadeOut('fast', function(){
                $('input', this).val('').removeClass('require');
                $('.acc__input-error', this).html('');
            })
        }
    });

    $('select[name="workpermit_type"]').on('change', function() {
        let $workpermit_type = $(this);
        var workpermit_type_id = $workpermit_type.val();

        if(workpermit_type_id == 3) {
            $('.workPermitFields').fadeIn('fast', function(){
                $('input', this).val('').addClass('require');
                $('.acc__input-error', this).html('');
            })
        } else {
            $('.workPermitFields').fadeOut('fast', function(){
                $('input', this).val('').removeClass('require');
                $('.acc__input-error', this).html('');
            })
        }
    });

    $('#employee_work_type').on('change', function() {
        let $this = $(this)
        var employee_work_type = $this.val();
        
        if(employee_work_type == 2) {
            $('.taxRefNo').fadeIn('fast', function(){
                $('input', this).val('');
                $('input', this).val('').addClass('require');
                $('.acc__input-error', this).html('');
            });
        }else{
            $('.taxRefNo').fadeOut('fast', function(){
                $('input', this).val('');
                $('input', this).val('').removeClass('require');
                $('.acc__input-error', this).html('');
            });
        }
    });

    $('#highest_qualification_on_entry_id').on('change', function() {
        let $this = $(this)
        var highest_qualification_on_entry_id = $this.val();
        
        if(highest_qualification_on_entry_id == 1) {
            $('.eduQuals .text-danger').fadeOut();
            $('.eduQuals input').removeClass('require');
        }else{
            $('.eduQuals .text-danger').fadeIn();
            $('.eduQuals input').addClass('require');
        }
    });

    $('.form-wizard-next-btn').on('click', function () {
        var parentFieldset = $(this).parents('.wizard-fieldset');
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-steps .active');
        var step_id = parentFieldset.attr('id');
        var next = $(this);
        let nextWizardStep = true;

        /* Step Validation Start*/
        var stepError = 0;
        parentFieldset.find('.require').each(function(){
            var $theField = $(this);
            var theFieldName = $theField.attr('name');
            if($theField.val() == ''){
                $('.error-'+theFieldName).html('This field is required.');
                stepError += 1;
            }else{
                $('.error-'+theFieldName).html('');
            }
        });
        parentFieldset.find('select.tomRequire').each(function(){
            var $theField = $(this);
            var theFieldName = $theField.attr('name');
            if($theField.val() == ''){
                $('.error-'+theFieldName).html('This field is required.');
                stepError += 1;
            }else{
                $('.error-'+theFieldName).html('');
            }
        });
        if(step_id == 'step_1'){
            if($('#disability_status').prop('checked')){
                var checkedLen = $('.disability_ids:checked').length;
                if(checkedLen == 0){
                    stepError += 1;
                    $('.error-disability_id').html('Please checked some disabilities.');
                }else{
                    $('.error-disability_id').html('');
                }
            }else{
                $('.error-disability_id').html('');
            }

            if($('#empAddressWrap [name="emp_address_line_1"]').length > 0 && $('#empAddressWrap [name="emp_address_line_1"]').val() != ''){
                $('.error-emp_address_line_1').html('');
            }else{
                $('.error-emp_address_line_1').html('Address can not be empty.');
                stepError += 1;
            }
        }
        console.log(stepError);
        
        if(stepError > 0){
            nextWizardStep = false;
        }
        /* Step Validation End*/
         
        if (nextWizardStep) {
            next.parents('.wizard-fieldset').removeClass("show");
            currentActiveStep.removeClass('active').addClass('activated').next().addClass('active');
            next.parents('.wizard-fieldset').next('.wizard-fieldset').addClass("show");
            $(document).find('.wizard-fieldset').each(function () {
                if ($(this).hasClass('show')) {
                    var activeIndex = $(this).index();
                    var indexCount = 1;
                    $(document).find('.form-wizard-steps .form-wizard-step-item').each(function () {
                        if (activeIndex == indexCount) {
                            $(this).addClass('active');
                        } else {
                            $(this).removeClass('active');
                        }
                        indexCount++;
                    });
                }
            });
        }
    });
    //click on previous button
    $('.form-wizard-previous-btn').on('click', function () {
        var counter = parseInt($(".wizard-counter").text());
        
        var prev = $(this);
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-steps .active');
        prev.parents('.wizard-fieldset').removeClass("show");
        prev.parents('.wizard-fieldset').prev('.wizard-fieldset').addClass("show");
        currentActiveStep.removeClass('active').prev().removeClass('activated').addClass('active');
        $(document).find('.wizard-fieldset').each(function () {
            if ($(this).hasClass('show')) {
                var activeIndex = $(this).index();
                var indexCount = 1;
                $(document).find('.form-wizard-steps .form-wizard-step-item').each(function () {
                    if (activeIndex == indexCount) {
                        $(this).addClass('active');
                    } else {
                        $(this).removeClass('active');
                    }
                    indexCount++;
                });
            }
        });
    });

    $('#theEmployeeDataCollectionForm').on('submit', function(e){
        e.preventDefault();
        let $form = $('#theEmployeeDataCollectionForm');
        const form = document.getElementById('theEmployeeDataCollectionForm');
    
        $('#saveEmpData').attr('disabled', 'disabled');
        $('#saveEmpData svg').fadeIn();
        $('#saveEmpData').siblings('form-wizard-previous-btn').attr('disabled', 'disabled');

        var stepError = 0;
        $('#step_4').find('.require').each(function(){
            var $theField = $(this);
            var theFieldName = $theField.attr('name');
            if($theField.val() == ''){
                $('.error-'+theFieldName).html('This field is required.');
                stepError += 1;
            }else{
                $('.error-'+theFieldName).html('');
            }
        });
        $('#step_4').find('select.tomRequire').each(function(){
            var $theField = $(this);
            var theFieldName = $theField.attr('name');
            if($theField.val() == ''){
                $('.error-'+theFieldName).html('This field is required.');
                stepError += 1;
            }else{
                $('.error-'+theFieldName).html('');
            }
        });
        if($('#emcAddressWrap [name="emc_address_line_1"]').length > 0 && $('#emcAddressWrap [name="emc_address_line_1"]').val() != ''){
            $('.error-emc_address_line_1').html('');
        }else{
            $('.error-emc_address_line_1').html('Address can not be empty.');
            stepError += 1;
        }

        if(stepError > 0){
            $('#saveEmpData').removeAttr('disabled');
            $('#saveEmpData svg').fadeOut();
            $('#saveEmpData').siblings('form-wizard-previous-btn').removeAttr('disabled');
        } else {
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('forms.employee.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('#saveEmpData').removeAttr('disabled');
                $('#saveEmpData svg').fadeOut();
                $('#saveEmpData').siblings('form-wizard-previous-btn').removeAttr('disabled');
                if (response.status == 200) {

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulation!" );
                        $("#successModal .successModalDesc").html('Data successfully submitted for review. We will get back to you ASAP.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });   
                    
                    setTimeout(() => {
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                $('#saveEmpData').removeAttr('disabled');
                $('#saveEmpData svg').fadeOut();
                $('#saveEmpData').siblings('form-wizard-previous-btn').removeAttr('disabled');
                if (error.response) {
                    if (error.response.status == 422) {
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html( "Error Found!" );
                            $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact with the HR Manager.');
                            $("#warningModal .warningCloser").attr('data-action', 'NONE');
                        }); 
                    
                        setTimeout(() => {
                            warningModal.hide();
                        }, 2000);
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    /*Address Modal*/
    if($('#addressModal').length > 0){
        const addressModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addressModal"));

        const addressModalEl = document.getElementById('addressModal')
        addressModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addressModal .acc__input-error').html('');
            $('#addressModal .modal-body input').val('');
            $('#addressModal input[name="prfix"]').val('');
            $('#addressModal input[name="place"]').val('');
        });

        $('.addressPopupToggler').on('click', function(e){
            e.preventDefault();

            var $btn = $(this);
            var $wrap = $btn.parents('.addressWrap');
            var $addressFieldPrefix = $btn.siblings('.address_prfix_field').val();

            var wrap_id = '#'+$wrap.attr('id');
            $('#addressModal input[name="place"]').val(wrap_id);
            $('#addressModal .modal-body input').val('');
            $('#addressModal input[name="prfix"]').val($addressFieldPrefix);
        });

        $('#addressForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addressForm');
            var $form = $(this);
            var wrapid = $('input[name="place"]', $form).val();
            var prfix = $('input[name="prfix"]', $form).val();

            document.querySelector('#insertAddress').setAttribute('disabled', 'disabled');
            document.querySelector('#insertAddress svg').style.cssText = 'display: inline-block;';

            var err = 0;
            $('input', $form).each(function(){
                var $input = $(this);
                var name = $input.attr('name');
                if(name != 'address_line_2' && $input.val() == ''){
                    err += 1;
                }
            })
            
            if(err > 0){
                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';

                $form.find('.mod-error').remove();
                $form.find('.modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Please fill out all required fields.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $form.find('.mod-error').remove();
                }, 2000);
            }else{
                var htmls = '';
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_1', $form).val()+'</span><br/>';
                htmls += '<input type="hidden" name="'+prfix+'address_line_1" value="'+$('#student_address_address_line_1', $form).val()+'"/>';
                if($('#student_address_address_line_2', $form).val() != ''){
                    htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_2', $form).val()+'</span><br/>';
                    htmls += '<input type="hidden" name="'+prfix+'address_line_2" value="'+$('#student_address_address_line_2', $form).val()+'"/>';
                }
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_city', $form).val()+'</span>, ';
                htmls += '<input type="hidden" name="'+prfix+'city" value="'+$('#student_address_city', $form).val()+'"/>';

                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_postal_zip_code', $form).val()+'</span>,<br/>';
                htmls += '<input type="hidden" name="'+prfix+'post_code" value="'+$('#student_address_postal_zip_code', $form).val()+'"/>';

                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_country', $form).val()+'</span><br/>';
                htmls += '<input type="hidden" name="'+prfix+'country" value="'+$('#student_address_country', $form).val()+'"/>';

                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';

                addressModal.hide();
                $(wrapid+' .addresses').fadeIn().addClass('active').html(htmls);
                $(wrapid +' button.addressPopupToggler span').html('Update Address');
            }
        });
    }
})();