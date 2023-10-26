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
    $('#employee_work_type').on('change', function() {
        let tthis = $(this)

        let typeText = $('option:selected',tthis).text();
        if(typeText.match(/employee/gi)!=null) {
            $('input[name="works_number"]').parent().removeClass('invisible')
            
        }  else {
            $('input[name="works_number"]').parent().addClass('visible')
        }
        

    });

    
    $('#eligible_to_work_status').on('change', function() {
        let tthis = $(this)

        if(tthis.prop('checked')){

            $('select[name="workpermit_type"]').parent().removeClass('invisible')
            
        }  else {

            $('select[name="workpermit_type"]').parent().addClass('invisible')
            $('input[name="workpermit_number"]').parent().addClass('invisible')
            $('input[name="workpermit_expire"]').parent().addClass('invisible')
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
})();

