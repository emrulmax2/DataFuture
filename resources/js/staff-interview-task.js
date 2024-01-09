import Dropzone from "dropzone";
import { createIcons, icons } from "lucide";

("use strict");

const editModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

$(document).on("click", ".interview-result", function (e) { 
        e.preventDefault();
        //interviewId = $(this).attr("data-id");
        document.getElementById('id').value = $(this).attr("data-id");
    
});
$(document).on("click", ".interview-taskend", function (e) { 
            
            e.preventDefault();

            const theId = $(this).attr("data-id");
            console.log(theId);
            axios({
                method: "post",
                url: route('applicant.interview.task.update'),
                data: {
                  id: theId
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {

                if (response.status == 200) {
                    editModal.hide();
                    succModal.show();
                    
                    let status = response.data.status;
                    document.getElementById("ProgressStatus").innerHTML = status;
                    
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(response.data.msg);
                            $("#successModal .successModalDesc").html('success');
                        });    
                        
                        $("#magic-button1").addClass('hidden');
                        $("#magic-button2").addClass('hidden');
                        $("#magic-button3").addClass('hidden');
                }
                
                //interviewListTable.init();

            }).catch(error => {
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

});

$(document).on("click", ".interview-start", function (e) { 
            
    e.preventDefault();

    const theId = $(this).attr("data-id");
    console.log(theId);
    axios({
        method: "post",
        url: route('applicant.interview.start'),
        data: {
          id: theId
        },
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {

        if (response.status == 200) {
            
            let startTime = response.data.data.start;
            let ProgressStatus = response.data.data.status;
            document.getElementById("progressStart").innerHTML = startTime;
            document.getElementById("ProgressStatus").innerHTML = ProgressStatus;
            document.querySelector(".interview-start").setAttribute('disabled', 'disabled');
            editModal.hide();
            succModal.show();
            document.getElementById("successModal")
                .addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html(response.data.msg);
                    $("#successModal .successModalDesc").html('success');
                });   
                
            
        }

        //interviewListTable.init();

    }).catch(error => {
        
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#${key}`).addClass('border-danger')
                    $(`#error-${key}`).html(val)
                }
            } else {
                console.log('error');
            }
        }
    });

});


$(document).on("click", ".interview-end", function (e) { 
            
    e.preventDefault();

    const theId = $(this).attr("data-id");
    
    axios({
        method: "post",
        url: route('applicant.interview.end'),
        data: {
          id: theId
        },
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {

        if (response.status == 200) {
            editModal.hide();
            succModal.show();
            
            let endTime = response.data.data.end;
            document.getElementById("progressEnd").innerHTML = endTime;

            document.getElementById("successModal")
                .addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html(response.data.msg);
                    $("#successModal .successModalDesc").html('success');
                });    

                $("#magic-button1").addClass('hidden');
                $("#magic-button2").removeClass('hidden');
        }

        //interviewListTable.init();

    }).catch(error => {
        
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#${key}`).addClass('border-danger')
                    $(`#error-${key}`).html(val)
                }
            } else {
                console.log('error');
            }
        }
    });

});


(function () {
    // To get value of interview result field
    var interview_result = document.getElementById('interview_result');
    var resultValue = document.getElementById('resultValue');

    var updateInterviewResult = function () {
        resultValue.value = interview_result.value;
    }

    if (interview_result.addEventListener) {
        interview_result.addEventListener('change', function () {
            updateInterviewResult();
        });
    }

    $('#errorModal .errorCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            errorModal.hide();
            window.location.reload();
        }else{
            errorModal.hide();
        }
    });

    // Start Dropzone
    if($("#editForm").length > 0){
        let dzError = false;
        let errorResponse = {};
        Dropzone.autoDiscover = false;
        Dropzone.options.editForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 20,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
        };
        
        let options = {
            accept: (file, done) => {
                console.log("Uploaded");             
                done();
            },
        };

        var drzn1 = new Dropzone('#editForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#editForm .modal-content .uploadError').remove();
            $('#editForm .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 1 file at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#editForm .modal-content .uploadError').remove();
            }, 4000)
        });

        drzn1.on("error", function(file, response){
            dzError = true;
            errorResponse = response
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on('queuecomplete', function(){
            $('#update').removeAttr('disabled');
            document.querySelector("#update svg").style.cssText ="display: none;";

            editModal.hide();
            if(!dzError){
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Success!" );
                    $("#successModal .successModalDesc").html('Successfully uploaded.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    succModal.hide();
                    window.location.reload();
                }, 1500);
            }else{
                console.log(errorResponse);
                errorModal.show();
                document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                    $("#errorModal .errorModalTitle").html("Error!" );
                    $("#errorModal .errorModalDesc").html(errorResponse.message);
                    $("#errorModal .errorCloser").attr('data-action', 'DISMISS');
                });
                // setTimeout(function(){
                //     errorModal.hide();
                //     window.location.reload();
                // }, 5000);
                drzn1.removeAllFiles(true);
            }
        })

        $('#update').on('click', function(e) {
            e.preventDefault();
            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector("#update svg").style.cssText ="display: inline-block;";
       
            if($('#editModal [name="resultValue"]').val() !=""){
                var result = $('#editModal [name="resultValue"]').val();
                $('#editModal input[name="resultValue"]').val(result)
                drzn1.processQueue();
                $("#magic-button2").addClass('hidden');
                $("#magic-button3").removeClass('hidden');
            }else{
                $('#editModal .modal-content .uploadError').remove();
                $('#editModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select result type.</div>');
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#editModal .modal-content .uploadError').remove();
                }, 5000)
            }
            
        });

        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){

            let id =$('#confirmModal .agreeWith').attr('data-id');
            let actionDelete = $('#confirmModal .agreeWith').attr('data-action');
            console.log(actionDelete)
            console.log(id)
        });
        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function() {
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('applicant.interview.file.remove', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Uploaded interview file successfully deleted!');
                        });
                    }
                    document.getElementById('fileLoadedView').innerHTML='<i data-lucide="slash" class="w-5 h-5"></i>';
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }).catch(error =>{
                    console.log(error)
                });
            } 
        })
    }
    // End Dropzone

})()