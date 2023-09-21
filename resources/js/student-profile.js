import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

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
    });

    

    const editAdmissionPersonalDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionPersonalDetailsModal"));
    const editOtherPersonalInfoModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editOtherPersonalInfoModal"));
    const editAdmissionKinDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionKinDetailsModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });

    /*Address Modal*/
    if($('#addressModal').length > 0){
        const addressModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addressModal"));

        const addressModalEl = document.getElementById('addressModal')
        addressModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addressModal .acc__input-error').html('');
            $('#addressModal input').val('');
        });
        $('.addressPopupToggler').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);
            var wrapid = $btn.attr('data-address-wrap');
            var prefix = $btn.attr('data-prefix');

            $('#addressModal input[name="place"]').val(wrapid);
            $('#addressModal input[name="prefix"]').val(prefix);
            if($(wrapid).hasClass('active')){
                $('#addressModal #student_address_address_line_1').val($(wrapid+' input[name="'+prefix+'_address_line_1"]').val());
                $('#addressModal #student_address_address_line_2').val($(wrapid+' input[name="'+prefix+'_address_line_2"]').val());
                $('#addressModal #student_address_city').val($(wrapid+' input[name="'+prefix+'_address_city"]').val());
                $('#addressModal #student_address_state_province_region').val($(wrapid+' input[name="'+prefix+'_address_state"]').val());
                $('#addressModal #student_address_postal_zip_code').val($(wrapid+' input[name="'+prefix+'_address_postal_zip_code"]').val());
                $('#addressModal #student_address_country').val($(wrapid+' input[name="'+prefix+'_address_country"]').val());
            }
        });

        $('#addressForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var wrapid = $('input[name="place"]', $form).val();
            var prefix = $('input[name="prefix"]', $form).val();

            document.querySelector('#insertAddress').setAttribute('disabled', 'disabled');
            document.querySelector('#insertAddress svg').style.cssText = 'display: inline-block;';

            var err = 0;
            $('input.required', $form).each(function(){
                if($(this).val() == ''){
                    $(this).siblings('.acc__input-error').html('This field is required.');
                    err += 1;
                }else{
                    $(this).siblings('.acc__input-error').html('');
                }
            });

            if(err > 0){
                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';
            }else{
                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';

                var htmls = '';
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_1', $form).val()+'</span><br/>';
                if($('#student_address_address_line_2', $form).val() != ''){
                    htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_2', $form).val()+'</span><br/>';
                }
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_city', $form).val()+'</span>, ';
                if($('#student_address_state_province_region', $form).val() != ''){
                    htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_state_province_region', $form).val()+'</span>, <br/>';
                }else{
                    htmls += '<br/>';
                }
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_postal_zip_code', $form).val()+'</span>,<br/>';
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_country', $form).val()+'</span><br/>';

                htmls += '<input type="hidden" name="'+prefix+'_address" value="'+$('#student_address_address_line_1', $form).val()+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_line_1" value="'+($('#student_address_address_line_1', $form).val() != '' ? $('#student_address_address_line_1', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_line_2" value="'+($('#student_address_address_line_2', $form).val() != '' ? $('#student_address_address_line_2', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_city" value="'+($('#student_address_city', $form).val() != '' ? $('#student_address_city', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_state" value="'+($('#student_address_state_province_region', $form).val() != '' ? $('#student_address_state_province_region', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_postal_zip_code" value="'+($('#student_address_postal_zip_code', $form).val() != '' ? $('#student_address_postal_zip_code', $form).val() : '')+'"/>';
                htmls += '<input type="hidden" name="'+prefix+'_address_country" value="'+($('#student_address_country', $form).val() != '' ? $('#student_address_country', $form).val() : '')+'"/>';

                addressModal.hide();
                $(wrapid).fadeIn().html(htmls).addClass('active');
                $('button[data-address-wrap="'+wrapid+'"] span').html('Update Address')
            }
        });
    }
    /*Address Modal*/

    /* Edit Personal Details */
    $('#editAdmissionPersonalDetailsForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editAdmissionPersonalDetailsForm');
    
        document.querySelector('#savePD').setAttribute('disabled', 'disabled');
        document.querySelector("#savePD svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        let applicantId = $('[name="applicant_id"]', $form).val();
        axios({
            method: "post",
            url: route('student.update.personal.details'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#savePD').removeAttribute('disabled');
                document.querySelector("#savePD svg").style.cssText = "display: none;";

                editAdmissionPersonalDetailsModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Personal Data successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.show();
                    window.location.reload();
                }, 5000);
            }
        }).catch(error => {
            document.querySelector('#savePD').removeAttribute('disabled');
            document.querySelector("#savePD svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editAdmissionPersonalDetailsForm .${key}`).addClass('border-danger');
                        $(`#editAdmissionPersonalDetailsForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    /* Edit Personal Details*/


    /* Edit Other Personal Information */
    $('#disability_status').on('change', function(){
        if($('#disability_status').prop('checked')){
            $('.disabilityItems').fadeIn('fast', function(){
                $('.disabilityItems input[type="checkbox"]').prop('checked', false);
                $('.disabilityAllowance').fadeOut();
                $('.disabilityAllowance input[type="checkbox"]').prop('checked', false);
            });
        }else{
            $('.disabilityItems').fadeOut('fast', function(){
                $('.disabilityItems input[type="checkbox"]').prop('checked', false);
                $('.disabilityAllowance').fadeOut();
                $('.disabilityAllowance input[type="checkbox"]').prop('checked', false);
            });
        }
    });

    $('.disabilityItems input[type="checkbox"]').on('change', function(){
        if($('.disabilityItems input[type="checkbox"]:checked').length > 0){
            if(!$('.disabilityAllowance').is(':visible')){
                $('.disabilityAllowance').fadeIn('fast', function(){
                    $('input[type="checkbox"]', this).prop('checked', false);
                });
            }
        }else{
            $('.disabilityAllowance').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            });
        }
    });
    $('#editOtherPersonalInfoForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editOtherPersonalInfoForm');
    
        document.querySelector('#saveSOI').setAttribute('disabled', 'disabled');
        document.querySelector("#saveSOI svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        let applicantId = $('[name="applicant_id"]', $form).val();
        axios({
            method: "post",
            url: route('student.update.other.personal.details'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                
                document.querySelector('#saveSOI').removeAttribute('disabled');
                document.querySelector("#saveSOI svg").style.cssText = "display: none;";

                editOtherPersonalInfoModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Other Personal Information successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.show();
                    window.location.reload();
                }, 5000);
            }
        }).catch(error => {
            document.querySelector('#saveSOI').removeAttribute('disabled');
            document.querySelector("#saveSOI svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editOtherPersonalInfoForm .${key}`).addClass('border-danger');
                        $(`#editOtherPersonalInfoForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    /* Edit Other Personal Information */


    /* Edit Kin Details */
    if($('#editAdmissionKinDetailsForm').length > 0){
        $('#editAdmissionKinDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionKinDetailsForm');
        
            document.querySelector('#saveNOK').setAttribute('disabled', 'disabled');
            document.querySelector("#saveNOK svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('student.update.kin.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#saveNOK').removeAttribute('disabled');
                    document.querySelector("#saveNOK svg").style.cssText = "display: none;";

                    editAdmissionKinDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Next of Kin Data successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 5000);
                }
            }).catch(error => {
                document.querySelector('#saveNOK').removeAttribute('disabled');
                document.querySelector("#saveNOK svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionKinDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionKinDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        // $('#successModal .successCloser').on('click', function(e){
        //     e.preventDefault();
        //     if($(this).attr('data-action') == 'RELOAD'){
        //         successModal.hide();
        //         window.location.reload();
        //     }else{
        //         successModal.hide();
        //     }
        // })
    }
    /* Edit Kin Details*/
})();