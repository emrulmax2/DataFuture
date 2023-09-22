import { createIcons, icons } from "lucide";

(function(){
    const editStudentConsentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentConsentModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('#editStudentConsentModal .readOnlyConsent').on('click', function(){
        return false;
    });

    $('#editStudentConsentForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editStudentConsentForm');
    
        document.querySelector('#editSCP').setAttribute('disabled', 'disabled');
        document.querySelector("#editSCP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.consent'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#editSCP').removeAttribute('disabled');
                document.querySelector("#editSCP svg").style.cssText = "display: none;";

                editStudentConsentModal.hide();

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
            document.querySelector('#editSCP').removeAttribute('disabled');
            document.querySelector("#editSCP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editStudentConsentForm .${key}`).addClass('border-danger');
                        $(`#editStudentConsentForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
})()