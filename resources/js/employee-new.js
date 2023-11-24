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

    $('.lccTom').each(function(){
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
            console.log(currentActiveStep);
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
        let tthis = $(this)

        let typeText = $('option:selected',tthis).text();
        
        if(typeText.match(/Employee/gi)!=null) {
            $('input[name="works_number"]').parent().removeClass('invisible')
            
        }  else {
            $('input[name="works_number"]').parent().addClass('invisible')
        }
        

    });

    
    $('#eligible_to_work_status').on('change', function() {
        let tthis = $(this)

        if(tthis.prop('checked')){

            $('select[name="workpermit_type"]').parent().removeClass('invisible')
            
        }  else {

            $('select[name="workpermit_type"]').parent().addClass('invisible')
        }
        

    });
    $('select[name="workpermit_type"]').on('change', function() {
        let tthis = $(this)

        let typeText = $('option:selected',tthis).text();

        if(typeText.match(/British Citizen/gi)==null) {
            $('input[name="workpermit_number"]').parent().removeClass('invisible')
            $('input[name="workpermit_expire"]').parent().removeClass('invisible')
        } else {

            $('input[name="workpermit_number"]').parent().addClass('invisible')
            $('input[name="workpermit_expire"]').parent().addClass('invisible')
        }

    });
    

})();

