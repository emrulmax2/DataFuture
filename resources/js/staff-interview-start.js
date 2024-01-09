import Dropzone from "dropzone";
import { createIcons, icons } from "lucide";

("use strict");



(function () {

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
    
    $('#interviewStartFromProfile').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('interviewStartFromProfile');
        let $applicant_id = $('#interviewStartFromProfile input[name="applicant_id"]').val();
    
        document.querySelector('#startInterviewSession').setAttribute('disabled', 'disabled');
        document.querySelector("#startInterviewSession svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('applicant.interview.start'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#startInterviewSession').removeAttribute('disabled');
            document.querySelector("#startInterviewSession svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Interview Started!");
                    $("#successModal .successModalDesc").html('Interview started at '+response.data.data.start);
                });                
                
                setTimeout(function(){
                    succModal.hide();
                }, 1400);
                let Data = response.data.data.ref;

                location.href=Data; 
            }
        }).catch(error => {
            document.querySelector('#startInterviewSession').removeAttribute('disabled');
            document.querySelector("#startInterviewSession svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#interviewStartFromProfile .${key}`).addClass('border-danger')
                        $(`#interviewStartFromProfile  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})()