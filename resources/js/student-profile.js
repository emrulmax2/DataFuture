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
    const editAdmissionContactDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionContactDetailsModal"));
    const editOtherPersonalInfoModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editOtherPersonalInfoModal"));
    const editAdmissionKinDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionKinDetailsModal"));
    const editOtherItentificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editOtherItentificationModal"));
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
            $('#addressModal .modal-body input').val('');
            $('#addressModal input[name="address_id"]').val('0');
        });

        $('.addressPopupToggler').on('click', function(e){
            e.preventDefault();

            var $btn = $(this);
            var $wrap = $btn.parents('.addressWrap');
            var $addressIdField = $btn.siblings('.address_id_field');

            var wrap_id = '#'+$wrap.attr('id');
            var address_id = $addressIdField.val();
            if(address_id > 0){
                axios({
                    method: "post",
                    url: route('address.get'),
                    data: {address_id : address_id},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var dataset = response.data.res;
                        
                        $('#addressModal #student_address_address_line_1').val(dataset.address_line_1 ? dataset.address_line_1 : '');
                        $('#addressModal #student_address_address_line_2').val(dataset.address_line_2 ? dataset.address_line_2 : '');
                        $('#addressModal #student_address_city').val(dataset.city ? dataset.city : '');
                        $('#addressModal #student_address_state_province_region').val(dataset.state ? dataset.state : '');
                        $('#addressModal #student_address_postal_zip_code').val(dataset.post_code ? dataset.post_code : '');
                        $('#addressModal #student_address_country').val(dataset.country ? dataset.country : '');

                        $('#addressModal input[name="place"]').val(wrap_id);
                        $('#addressModal input[name="address_id"]').val(address_id);
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('#addressModal input[name="place"]').val(wrap_id);
                $('#addressModal .modal-body input').val('');
                $('#addressModal input[name="address_id"]').val('0');
            }
        });

        $('#addressForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addressForm');
            var $form = $(this);
            var wrapid = $('input[name="place"]', $form).val();
            var address_id = $('input[name="address_id"]', $form).val();

            
            var htmls = '';
            var post_code = $('#student_address_postal_zip_code', $form).val();
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

            document.querySelector('#insertAddress').setAttribute('disabled', 'disabled');
            document.querySelector('#insertAddress svg').style.cssText = 'display: inline-block;';

            
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('address.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    var dataset = response.data.res;
                    var newAddressId = (dataset.id ? dataset.id : 0);
                    
                    addressModal.hide();
                    $(wrapid+' .addresses').html(htmls);
                    $(wrapid +' button.addressPopupToggler span').html('Update Address');
                    $(wrapid +' input.address_id_field').val(newAddressId);

                    if(wrapid == '#permanentAddressWrap' && $('#editAdmissionContactDetailsModal input[name="permanent_post_code"]').val() == ''){
                        $('#editAdmissionContactDetailsModal input[name="permanent_post_code"]').val(post_code)
                    }
                }
                
            }).catch(error => {
                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';
                if(error.response){
                    if(error.response.status == 422){
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addressForm .${key}`).addClass('border-danger')
                            $(`#addressForm  .error-${key}`).html(val)
                        }
                    }else{
                        console.log('error');
                    }
                }
            });
        });
    };
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
                    successModal.hide();
                    window.location.reload();
                }, 2000);
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
                    successModal.hide();
                    window.location.reload();
                }, 2000);
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


    /* Edit Contact Details */
    if($('#editAdmissionContactDetailsForm').length > 0){
        $('#editAdmissionContactDetailsForm').on('click', '#editInstEmail', function(e){
            if($(this).hasClass('editable')){
                $(this).removeClass('editable');
                $(this).siblings('#org_email').attr('readonly', 'readonly');
            }else{
                $(this).addClass('editable');
                $(this).siblings('#org_email').removeAttr('readonly');
            }
        });

        $('#editAdmissionContactDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionContactDetailsForm');
        
            document.querySelector('#saveCD').setAttribute('disabled', 'disabled');
            document.querySelector("#saveCD svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('student.update.contact.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#saveCD').removeAttribute('disabled');
                    document.querySelector("#saveCD svg").style.cssText = "display: none;";

                    editAdmissionContactDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Contact Details Data successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#saveCD').removeAttribute('disabled');
                document.querySelector("#saveCD svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionContactDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionContactDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
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

        $('#editAdmissionContactDetailsForm #personal_email').on('keyup paste', function(){
            var $input = $(this);
            var $btn = $(this).siblings('#sendEmailVerifiCode');
            var $orgStatusInput = $(this).siblings('.personal_email_verification');
    
            var orgCode = $input.attr('data-org');
            var code = $input.val();
            var orgStatus = $orgStatusInput.attr('data-org');
            var status = $orgStatusInput.val();
            if(code != '' && code != orgCode){
                $btn.css({'display': 'inline-flex'}).removeClass('btn-primary verified').addClass('btn-danger').html('<i data-lucide="link" class="w-4 h-4 mr-1"></i> Send Code');
                $input.css({'border-color': 'red'});
                $orgStatusInput.val('0');
                status = 0;

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }else if(code == orgCode){
                if(orgStatus == 1){
                    $btn.css({'display': 'inline-flex'}).removeClass('btn-danger').addClass('btn-primary verified').html('<i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified');
                    $input.css({'border-color': 'rgba(226, 232, 240, 1)'});
                
                    $orgStatusInput.val(orgStatus);
                    status = orgStatus;
                }else{
                    $btn.css({'display': 'inline-flex'}).removeClass('btn-primary verified').addClass('btn-danger').html('<i data-lucide="link" class="w-4 h-4 mr-1"></i> Send Code');
                    $input.css({'border-color': 'red'});
                    $orgStatusInput.val('0');
                    status = 0;
                }

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }else{
                $btn.fadeOut();
                $input.css({'border-color': 'rgba(226, 232, 240, 1)'});
                $orgStatusInput.val(orgStatus);
                status = orgStatus;
            }
        });

        $('#sendEmailVerifiCode').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var $theInput = $theBtn.siblings('input[name="personal_email"]');
            if(!$theBtn.hasClass('verified')){
                var student_id = $theBtn.attr('data-student-id');
                var personal_email = $theInput.val();

                $theBtn.attr('disabled', 'disabled');
                $theInput.attr('readonly', 'readonly');

                 
                axios({
                    method: "post",
                    url: route('student.send.email.verification.code'),
                    data: {student_id : student_id, personal_email : personal_email},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#editAdmissionContactDetailsForm .emailVerifyCodeGroup').fadeIn(function(){
                            $('#email_verification_code', this).val('').removeAttr('readonly');
                            $('#verifyEmail', this).removeAttr('disabled');
                        })
                    }
                }).catch(error => {
                    $theBtn.removeAttr('disabled');
                    $theInput.removeAttr('readonly');

                    if (error.response) {
                        console.log('error');
                    }
                });
            }
        });

        $('#verifyEmail').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var $theInput = $theBtn.siblings('input[name="email_verification_code"]');
            var $orgStatusInput = $('#editAdmissionContactDetailsForm .personal_email_verification');

            $theBtn.attr('disabled', 'disabled');
            $theInput.attr('readonly', 'readonly');

            if($theInput.val() != '' && $theInput.val().length == 6){
                $('.error-email_verification_error').html('');

                var student_id = $theBtn.attr('data-student-id');
                var code = $theInput.val();
                var email = $('#editAdmissionContactDetailsForm input[name="personal_email"]').val();

                axios({
                    method: "post",
                    url: route('student.email.verify.code'),
                    data: {student_id : student_id, code : code, email : email},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {

                        if(response.data.suc == 1){
                            $('.error-email_verification_error').html('');
                            $('#editAdmissionContactDetailsForm .emailVerifyCodeGroup').fadeOut(function(){
                                $('#email_verification_code', this).val('').removeAttr('readonly');
                                $('#verifyEmail', this).removeAttr('disabled');
                            });

                            $('#editAdmissionContactDetailsForm #sendEmailVerifiCode').css({'display': 'inline-flex'}).removeClass('btn-danger').addClass('btn-primary verified').html('<i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified');
                            $('#editAdmissionContactDetailsForm input[name="personal_email"]').css({'border-color': 'rgba(226, 232, 240, 1)'});
                        
                            $orgStatusInput.val(1);
                        }else{
                            $theBtn.removeAttr('disabled');
                            $theInput.removeAttr('readonly');

                            $('.error-email_verification_error').html('Verification code does not found. Please insert a valid one.')
                        }

                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $theBtn.removeAttr('disabled');
                $theInput.removeAttr('readonly');

                $('.error-email_verification_error').html('Verification code can not be empty and code length should be 6 digit.')
            }
        });

        $('#editAdmissionContactDetailsForm #mobile').on('keyup paste', function(){
            var $input = $(this);
            var $btn = $(this).siblings('#sendMobileVerifiCode');
            var $orgStatusInput = $(this).siblings('.mobile_verification');
    
            var orgCode = $input.attr('data-org');
            var code = $input.val();
            var orgStatus = $orgStatusInput.attr('data-org');
            var status = $orgStatusInput.val();
            if(code != '' && code != orgCode){
                $btn.css({'display': 'inline-flex'}).removeClass('btn-primary verified').addClass('btn-danger').html('<i data-lucide="link" class="w-4 h-4 mr-1"></i> Send Code');
                $input.css({'border-color': 'red'});
                $orgStatusInput.val('0');
                status = 0;

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }else if(code == orgCode){
                if(orgStatus == 1){
                    $btn.css({'display': 'inline-flex'}).removeClass('btn-danger').addClass('btn-primary verified').html('<i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified');
                    $input.css({'border-color': 'rgba(226, 232, 240, 1)'});
                
                    $orgStatusInput.val(orgStatus);
                    status = orgStatus;
                }else{
                    $btn.css({'display': 'inline-flex'}).removeClass('btn-primary verified').addClass('btn-danger').html('<i data-lucide="link" class="w-4 h-4 mr-1"></i> Send Code');
                    $input.css({'border-color': 'red'});
                    $orgStatusInput.val('0');
                    status = 0;
                }

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }else{
                $btn.fadeOut();
                $input.css({'border-color': 'rgba(226, 232, 240, 1)'});
                $orgStatusInput.val(orgStatus);
                status = orgStatus;
            }
        });

        $('#sendMobileVerifiCode').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var $theInput = $theBtn.siblings('input[name="mobile"]');
            if(!$theBtn.hasClass('verified')){
                var student_id = $theBtn.attr('data-student-id');
                var mobileNo = $theInput.val();

                $theBtn.attr('disabled', 'disabled');
                $theInput.attr('readonly', 'readonly');

                 
                axios({
                    method: "post",
                    url: route('student.send.mobile.verification.code'),
                    data: {student_id : student_id, mobileNo : mobileNo},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#editAdmissionContactDetailsForm .verifyCodeGroup').fadeIn(function(){
                            $('#verification_code', this).val('').removeAttr('readonly');
                            $('#verifyMobile', this).removeAttr('disabled');
                        })
                    }
                }).catch(error => {
                    $theBtn.removeAttr('disabled');
                    $theInput.removeAttr('readonly');

                    if (error.response) {
                        console.log('error');
                    }
                });
            }
        });

        $('#verifyMobile').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var $theInput = $theBtn.siblings('input[name="verification_code"]');
            var $orgStatusInput = $('#editAdmissionContactDetailsForm .mobile_verification');

            $theBtn.attr('disabled', 'disabled');
            $theInput.attr('readonly', 'readonly');

            if($theInput.val() != '' && $theInput.val().length == 6){
                $('.error-mobile_verification_error').html('');

                var student_id = $theBtn.attr('data-student-id');
                var code = $theInput.val();
                var mobile = $('#editAdmissionContactDetailsForm input[name="mobile"]').val();

                axios({
                    method: "post",
                    url: route('student.mobile.verify.code'),
                    data: {student_id : student_id, code : code, mobile : mobile},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {

                        if(response.data.suc == 1){
                            $('.error-mobile_verification_error').html('');
                            $('#editAdmissionContactDetailsForm .verifyCodeGroup').fadeOut(function(){
                                $('#verification_code', this).val('').removeAttr('readonly');
                                $('#verifyMobile', this).removeAttr('disabled');
                            });

                            $('#editAdmissionContactDetailsForm #sendMobileVerifiCode').css({'display': 'inline-flex'}).removeClass('btn-danger').addClass('btn-primary verified').html('<i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified');
                            $('#editAdmissionContactDetailsForm input[name="mobile"]').css({'border-color': 'rgba(226, 232, 240, 1)'});
                        
                            $orgStatusInput.val(1);
                        }else{
                            $theBtn.removeAttr('disabled');
                            $theInput.removeAttr('readonly');

                            $('.error-mobile_verification_error').html('Verification code does not found. Please insert a valid one.')
                        }

                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $theBtn.removeAttr('disabled');
                $theInput.removeAttr('readonly');

                $('.error-mobile_verification_error').html('Verification code can not be empty and code length should be 6 digit.')
            }
        });
    }
    /* Edit Contact Details*/


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
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
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
    }
    /* Edit Kin Details*/

    /* Edit Personal Identification Details */
    $('#editOtherItentificationForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editOtherItentificationForm');
    
        document.querySelector('#updateSOID').setAttribute('disabled', 'disabled');
        document.querySelector("#updateSOID svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.other.identification'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#updateSOID').removeAttribute('disabled');
                document.querySelector("#updateSOID svg").style.cssText = "display: none;";
                
                editOtherItentificationModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student Other Identification successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateSOID').removeAttribute('disabled');
            document.querySelector("#updateSOID svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editOtherItentificationForm .${key}`).addClass('border-danger');
                        $(`#editOtherItentificationForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    /* Edit Personal Identification Details*/
})();