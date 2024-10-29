import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

("use strict");
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
    //var employment_status = new TomSelect('#employment_status', tomOptions);
    var workpermit_type_tom = new TomSelect('#workpermit_type', tomOptions);
    var employee_work_type_tom = new TomSelect('#employee_work_type', tomOptions);

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

    $('.inputUppercase').on('keyup', function() {
		$(this).val($(this).val().toUpperCase());
	});

    // const studenttermTimeAddressAlertModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#termtime-address-modal"));
    // const studentpermanentAddressAlertModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#permanent-address-modal"));

    // click on next button
    $('.form-wizard-next-btn').on('click', function (e) {

        e.preventDefault();

        var parentFieldset = $(this).parents('.wizard-fieldset');
        var parentForm = $(this).parents('.wizard-step-form');
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-steps .active');
        var next = $(this);
        let nextWizardStep = true;
        //console.log(currentActiveStep);
        /* Form Submission Start*/
        var formID = parentForm.attr('id');
        const form = document.getElementById(formID);
        let studentId = $("#studentId").val();
        $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).attr('disabled', 'disabled');
        $('.form-wizard-next-btn svg', parentForm).fadeIn();

        let form_data = new FormData(form);
        form_data.append("student_id", studentId);
        let url, redURL;
        if(parentFieldset.index() == 2){
            url = route('employement.save');
        }else if(parentFieldset.index() == 3){
            url = route('eligibility.save');
        }else if(parentFieldset.index() == 4){
            url = route('emergency-contact.save');
              
        }else{
            url = route('employee.save');
        }

        

        $.ajax({
            method: 'POST',
            url: url,
            data: form_data,
            dataType: 'json',
            async: false,
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            success: function(res, textStatus, xhr) {

                $('.acc__input-error', parentForm).html('');
                $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).removeAttr('disabled');
                $('.form-wizard-next-btn svg', parentForm).fadeOut(); 
                if(xhr.status == 200){
                    if(parentFieldset.index() == 1){
                        //No work load here still
                    } else if(parentFieldset.index() == 2){
                        $('.reviewContentWrap').attr('data-review-id', res.user_id);
                    } else if(parentFieldset.index() == 3){
                        $('.reviewContentWrap').attr('data-review-id', res.user_id);
                       
                    } else if(parentFieldset.index() == 4){
                        $('.reviewContentWrap').attr('data-review-id', res.user_id);
                        window.location.href = route('profile.employee.view',res.user_id);
                    } 
                }
                nextWizardStep = true;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('.acc__input-error').html('');
                $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).removeAttr('disabled');
                $('.form-wizard-next-btn svg', parentForm).fadeOut();
                if(jqXHR.status == 422){
                    for (const [key, val] of Object.entries(jqXHR.responseJSON.errors)) {
                        $(`#${formID} .${key}`).addClass('border-danger');
                        $(`#${formID}  .error-${key}`).html(val);
                    }
                }else{
                    console.log(textStatus+' => '+errorThrown);
                }
                nextWizardStep = false;
            }
        });
        //nextWizardStep = false;
        /* Form Submission End*/
         
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
                    
                    /* Check If Last Step */
                    var $lastStep = $(this);
                    if($lastStep.hasClass('wizard-last-step') && $('.reviewContentWrap', $lastStep).length > 0) {
                       // var applicant_id = $('.reviewContentWrap', $lastStep).attr('data-review-id');
                        
                    }
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

    $('#employee_work_type').on('change', function() {
        let $this = $(this)
        var employee_work_type = $this.val();
        
        if(employee_work_type == 3) {
            $('.employeeWorkTypeFields').fadeIn('fast', function(){
                $('input', this).val('');
            });
            $('.taxRefNo').fadeOut('fast', function(){
                $('input', this).val('');
                $('.acc__input-error', this).html('');
            });
        }else if(employee_work_type == 2){
            $('.taxRefNo').fadeIn('fast', function(){
                $('input', this).val('');
                $('.acc__input-error', this).html('');
            });
            $('.employeeWorkTypeFields').fadeOut('fast', function(){
                $('input', this).val('');
            });
        }else{
            $('.employeeWorkTypeFields').fadeOut('fast', function(){
                $('input', this).val('');
            });
            $('.taxRefNo').fadeOut('fast', function(){
                $('input', this).val('');
                $('.acc__input-error', this).html('');
            });
        }
    });

    
    $('#eligible_to_work_status').on('change', function() {
        let $eligible_to_work_status = $(this);

        if($eligible_to_work_status.prop('checked')){
            workpermit_type_tom.clear(true);
            $('.workPermitTypeFields').fadeIn();
        }else{
            workpermit_type_tom.clear(true);
            $('.workPermitTypeFields').fadeOut();

            $('.workPermitFields').fadeOut('fast', function(){
                $('input', this).val('');
            })
        }
        

    });
    $('select[name="workpermit_type"]').on('change', function() {
        let $workpermit_type = $(this);
        var workpermit_type_id = $workpermit_type.val();

        if(workpermit_type_id == 3) {
            $('.workPermitFields').fadeIn('fast', function(){
                $('input', this).val('');
            })
        } else {
            $('.workPermitFields').fadeOut('fast', function(){
                $('input', this).val('');
            })
        }
    });

    $('#highest_qualification_on_entry_id').on('change', function() {
        let $this = $(this)
        var highest_qualification_on_entry_id = $this.val();
        
        if(highest_qualification_on_entry_id == 1) {
            $('.eduQuals .text-danger').fadeOut();
        }else{
            $('.eduQuals .text-danger').fadeIn();
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
                htmls += '<input type="hidden" name="'+prfix+'address_id" value="0"/>';

                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';

                addressModal.hide();
                $(wrapid+' .addresses').fadeIn().addClass('active').html(htmls);
                $(wrapid +' button.addressPopupToggler span').html('Update Address');
            }
        });
    }

})();

