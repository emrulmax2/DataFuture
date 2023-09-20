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
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    /* Edit Personal Details */
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
})();