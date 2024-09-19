import { createIcons, icons } from "lucide";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#studentAttendanceExcuseForm').on('change', '#addEXCUSEDocument', function(){
        showFileNames('addEXCUSEDocument', 'addEXCUSEDocumentName');
    });

    function showFileNames(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = '';
        if(fileInput.files.length > 0){
            fileName += '<ul class="m-0">';
            $.each(fileInput.files, function(index, file){
                fileName += '<li class="mb-1 text-primary flex items-center"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>'+file.name+'</li>';
            });
            fileName += '</ul>';
        }
        
        $('#'+targetPreviewId).html(fileName);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });

        return false;
    };

    $('#studentAttendanceExcuseForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('studentAttendanceExcuseForm');
    
        document.querySelector('#submitExcuseBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#submitExcuseBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
            form_data.append('file', $('#studentAttendanceExcuseForm #addEXCUSEDocument')[0].files[0]); 

        axios({
            method: "post",
            url: route('students.excuse.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#submitExcuseBtn').removeAttribute('disabled');
            document.querySelector("#submitExcuseBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                //console.log(response.data.res);
                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Attendance excuse successfully submitted for review.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#submitExcuseBtn').removeAttribute('disabled');
            document.querySelector("#submitExcuseBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#studentAttendanceExcuseForm .${key}`).addClass('border-danger');
                        $(`#studentAttendanceExcuseForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
})();