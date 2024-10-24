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

    $('#employee_work_type').on('change', function() {
        let $this = $(this)
        var employee_work_type = $this.val();
        
        if(employee_work_type == 2) {
            $('.taxRefNo').fadeIn('fast', function(){
                $('input', this).val('');
            });
        }else{
            $('.taxRefNo').fadeOut('fast', function(){
                $('input', this).val('');
            });
        }
    });

    $('.form-wizard-next-btn').on('click', function () {
        var parentFieldset = $(this).parents('.wizard-fieldset');
        var parentForm = $(this).parents('.wizard-step-form');
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-steps .active');
        var next = $(this);
        let nextWizardStep = true;

        /* Form Submission Start*/
        var formID = parentForm.attr('id');
        const form = document.getElementById(formID);
    
        //$('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).attr('disabled', 'disabled');
        //$('.form-wizard-next-btn svg', parentForm).fadeIn();

        let form_data = new FormData(form);
        let applicantId = $('[name="applicant_id"]', parentForm).val();
        let url, redURL;
        if(parentFieldset.index() == 2){
            url = route('applicant.application.store.course');
        }else if(parentFieldset.index() == 3){
            url = route('applicant.application.store.submission');
            redURL = $('input[name="url"]', parentForm).val();
        }else{
            url = route('applicant.application.store.personal');
        }

        /*$.ajax({
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
            success: function(res, textStatus, xhr){
                $('.acc__input-error', parentForm).html('');
                $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).removeAttr('disabled');
                $('.form-wizard-next-btn svg', parentForm).fadeOut(); 
                if(xhr.status == 200){
                    if(parentFieldset.index() == 1){
                        $(document.body).find('input[name="applicant_id"]').val(res.applicant_id);
                        $('#educationQualTable, #employmentHistoryTable').attr('data-applicant', res.applicant_id);
                        $('#varifiedReferral').attr('data-applicant-id', res.applicant_id);
                    } else if(parentFieldset.index() == 2){
                        $('.reviewContentWrap').attr('data-review-id', res.applicant_id);
                    } else if(parentFieldset.index() == 3){
                        window.location.href = redURL;
                    }
                }
                nextWizardStep = true;
            },
            error: function (jqXHR, textStatus, errorThrown) {
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
        });*/
        //console.log(nextWizardStep);
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
                    if($lastStep.hasClass('wizard-last-step') && $('.reviewContentWrap', $lastStep).length > 0){
                        var applicant_id = $('.reviewContentWrap', $lastStep).attr('data-review-id');
                        /*axios({
                            method: "post",
                            url: route('applicant.application.review'),
                            data: {applicant_id : applicant_id},
                            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                        }).then(response => {
                            if (response.status == 200) {
                                $('.reviewLoader', $lastStep).fadeOut('fast', function(){
                                    $('.reviewContentWrap', $lastStep).fadeIn('fast', function(){
                                        $('.reviewContent', $lastStep).html(response.data.htmls);
                                        const applicantReviewAccordion = tailwind.Accordion.getOrCreateInstance(document.querySelector("#applicantReviewAccordion"));
                                        createIcons({
                                            icons,
                                            "stroke-width": 1.5,
                                            nameAttr: "data-lucide",
                                        });
                                    })
                                })
                            }
                        }).catch(error => {
                            if (error.response) {
                                console.log('error');
                            }
                        });*/
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
})();