import TomSelect from "tom-select";
import IMask from 'imask';
import { createIcons, icons } from "lucide";

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
    var employee_work_type_id_tom = new TomSelect('#employee_work_type_id', tomOptions);

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

    
    $('#employee_work_type_id').on('change', function() {
        let $this = $(this)
        var employee_work_type_id = $this.val();
        
        if(employee_work_type_id == 1) {
            $('.employeeWorkTypeFields').fadeIn('fast', function(){
                $('input', this).val('');
            });
        }else{
            $('.employeeWorkTypeFields').fadeOut('fast', function(){
                $('input', this).val('');
            });
        }
        

    });

    
    $('.inputUppercase').on('keyup', function() {
		$(this).val($(this).val().toUpperCase());
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
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const editPersonalModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionPersonalDetailsModal"));
    const editEmploymentlModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmploymentDetailsModal"));
    const editEligibilitesModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEligibilitesDetailsModal"));
    const editTermDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editTermDetailsModal"));
    //const editAddressUpdateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAddressUpdateModal"));
    const editEmergencyContactDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmergencyContactDetailsModal"));
    $('.save').on('click', function (e) {
        e.preventDefault();

        var parentForm = $(this).parents('form');
        
        var formID = parentForm.attr('id');
        
        const form = document.getElementById(formID);
        let url = $("#"+formID+" input[name=url]").val();
        
        let form_data = new FormData(form);

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
                
                if(xhr.status == 200){
                    //update Alert
                    editPersonalModal.hide();
                    editEmploymentlModal.hide();
                    editEligibilitesModal.hide();
                    editTermDetailsModal.hide();
                    //editAddressUpdateModal.hide();
                    editEmergencyContactDetailsModal.hide();
                    successModal.show();

                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Data updated.');
                    });                
                    
                    setTimeout(function(){
                        successModal.hide();
                        location.reload()
                    }, 1000);
                }
                
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('.acc__input-error').html('');
                
                if(jqXHR.status == 422){
                    for (const [key, val] of Object.entries(jqXHR.responseJSON.errors)) {
                        $(`#${formID} .${key}`).addClass('border-danger');
                        $(`#${formID}  .error-${key}`).html(val);
                    }
                }else{
                    console.log(textStatus+' => '+errorThrown);
                }
                
            }
        });
        
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
            var address_id = $btn.attr('data-id');
            var type = $btn.attr('data-type');

            var wrap_id = '#'+$wrap.attr('id');
            if(address_id > 0){
                axios({
                    method: "post",
                    url: route('employee.get.address'),
                    data: {address_id : address_id},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var dataset = response.data.res;
                        
                        $('#addressModal #student_address_address_line_1').val(dataset.address_line_1 ? dataset.address_line_1 : '');
                        $('#addressModal #student_address_address_line_2').val(dataset.address_line_2 ? dataset.address_line_2 : '');
                        $('#addressModal #student_address_city').val(dataset.city ? dataset.city : '');
                        $('#addressModal #student_address_postal_zip_code').val(dataset.post_code ? dataset.post_code : '');
                        $('#addressModal #student_address_country').val(dataset.country ? dataset.country : '');

                        $('#addressModal input[name="place"]').val(wrap_id);
                        $('#addressModal input[name="type"]').val(type);
                        $('#addressModal input[name="address_id"]').val(address_id);
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('#addressModal input[name="place"]').val(wrap_id);
                $('#addressModal input[name="type"]').val(type);
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
            htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_postal_zip_code', $form).val()+'</span>,<br/>';
            htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_country', $form).val()+'</span><br/>';

            document.querySelector('#insertAddress').setAttribute('disabled', 'disabled');
            document.querySelector('#insertAddress svg').style.cssText = 'display: inline-block;';

            
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('employee.address.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    var newAddressId = response.data.id;
                    
                    addressModal.hide();
                    $(wrapid+' .addresses').html(htmls);
                    $(wrapid +' button.addressPopupToggler').attr('data-id', newAddressId);

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
    }
})();