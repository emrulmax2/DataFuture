import TomSelect from "tom-select";
import IMask from 'imask';
import { createIcons, icons } from "lucide";
import Dropzone from "dropzone";

("use strict");
(function(){
/* Start Dropzone */
if($("#addStudentPhotoModal").length > 0){
    let dzErrors = false;
    Dropzone.autoDiscover = false;
    Dropzone.options.addStudentPhotoForm = {
        autoProcessQueue: false,
        maxFiles: 1,
        maxFilesize: 5,
        parallelUploads: 1,
        acceptedFiles: ".jpeg,.jpg,.png,.gif",
        addRemoveLinks: true,
        //thumbnailWidth: 100,
        //thumbnailHeight: 100,
    };

    let options = {
        accept: (file, done) => {
            console.log("Uploaded");
            done();
        },
    };


    var drzn1 = new Dropzone('#addStudentPhotoForm', options);

    drzn1.on("maxfilesexceeded", (file) => {
        $('#addStudentPhotoModal .modal-content .uploadError').remove();
        $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
        drzn1.removeFile(file);
        setTimeout(function(){
            $('#addStudentPhotoModal .modal-content .uploadError').remove();
        }, 4000)
    });

    drzn1.on("error", function(file, response){
        dzErrors = true;
    });

    drzn1.on("success", function(file, response){
        //console.log(response);
        return file.previewElement.classList.add("dz-success");
    });

    drzn1.on("complete", function(file) {
        //drzn1.removeFile(file);
    }); 

    drzn1.on('queuecomplete', function(){
        $('#uploadStudentPhotoBtn').removeAttr('disabled');
        document.querySelector("#uploadStudentPhotoBtn svg").style.cssText ="display: none;";

        if(!dzErrors){
            drzn1.removeAllFiles();

            $('#addStudentPhotoModal .modal-content .uploadError').remove();
            $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> WOW! Employee photo successfully uploaded.</div>');
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(function(){
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
                window.location.reload();
            }, 3000);
        }else{
            $('#addStudentPhotoModal .modal-content .uploadError').remove();
            $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try later.</div>');
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
            
            setTimeout(function(){
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
            }, 5000);
        }
    })

    $('#uploadStudentPhotoBtn').on('click', function(e){
        e.preventDefault();
        document.querySelector('#uploadStudentPhotoBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#uploadStudentPhotoBtn svg").style.cssText ="display: inline-block;";
        
        drzn1.processQueue();
        
    });
}
/* End Dropzone */
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
    const editAddressUpdateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAddressUpdateModal"));
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
                    editAddressUpdateModal.hide();
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
})();

