import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { each } from "jquery";
import Dropzone from "dropzone";

(function(){
    /* Start Dropzone */
    if($("#addApplicantPhotoModal").length > 0){
        let dzErrors = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.addApplicantPhotoForm = {
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


        var drzn1 = new Dropzone('#addApplicantPhotoForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#addApplicantPhotoModal .modal-content .uploadError').remove();
            $('#addApplicantPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#addApplicantPhotoModal .modal-content .uploadError').remove();
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
            $('#uploadPhotoBtn').removeAttr('disabled');
            document.querySelector("#uploadPhotoBtn svg").style.cssText ="display: none;";

            if(!dzErrors){
                drzn1.removeAllFiles();

                $('#addApplicantPhotoModal .modal-content .uploadError').remove();
                $('#addApplicantPhotoModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> WOW! Student photo successfully uploaded.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#addApplicantPhotoModal .modal-content .uploadError').remove();
                    window.location.reload();
                }, 3000);
            }else{
                $('#addApplicantPhotoModal .modal-content .uploadError').remove();
                $('#addApplicantPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try later.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                setTimeout(function(){
                    $('#addApplicantPhotoModal .modal-content .uploadError').remove();
                }, 5000);
            }
        })

        $('#uploadPhotoBtn').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadPhotoBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadPhotoBtn svg").style.cssText ="display: inline-block;";
            
            drzn1.processQueue();
            
        });
    }
    /* End Dropzone */

    /* Update Status */
    if($('.changeApplicantStatus').length > 0){
        const statusConfirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#statusConfirmModal"));
        const statusConfirmModalEl = document.getElementById('statusConfirmModal')
        statusConfirmModalEl.addEventListener('hide.tw.modal', function(event) {
            $("#statusConfirmModal .confModTitle").html("Are you sure?");
            $("#statusConfirmModal .confModDesc").html('');
            $("#statusConfirmModal .agreeWith").attr('data-statusid', '0');
            $("#statusConfirmModal .rejectedReasonArea").fadeOut(function(){
                $("#statusConfirmModal .rejectedReasonArea select").val('');
            });
            $('#statusConfirmModal .modal-content .validationErrors').remove();
        });

        $('.changeApplicantStatus').on('click', function(e){
            e.preventDefault();
            var statusID = $(this).attr('data-statusid');
            var applicantID = $(this).attr('data-applicantid');

            statusConfirmModal.show();
            if(statusID == 8){
                var message = 'Do you want to change the applicant status? Please Select a Reason and click on agree to continue.';
            }else{
                var message = 'Do you want to change the applicant status? Please click on agree to continue.';
            }
            document.getElementById("statusConfirmModal").addEventListener("shown.tw.modal", function (event) {
                $("#statusConfirmModal .confModTitle").html("Are you sure?");
                $("#statusConfirmModal .confModDesc").html(message);
                if(statusID == 8){
                    $("#statusConfirmModal .rejectedReasonArea").fadeIn(function(){
                        $("#statusConfirmModal .rejectedReasonArea select").val('');
                    });
                }else{
                    $("#statusConfirmModal .rejectedReasonArea").fadeOut(function(){
                        $("#statusConfirmModal .rejectedReasonArea select").val('');
                    });
                }
                $("#statusConfirmModal .agreeWith").attr('data-statusid', statusID);
            });
        });

        $('#statusConfirmModal .agreeWith').on('click', function(e){
            e.preventDefault();
            var applicantID = $(this).attr('data-applicant');
            var statusidID = $(this).attr('data-statusid');
            var rejectedReason = $('#statusConfirmModal [name="rejected_reason"]').val();

            $('#statusConfirmModal button').attr('disabled', 'disabled');
            if(statusidID == 8 && rejectedReason == ''){
                $('#statusConfirmModal button').removeAttr('disabled');
                $('#statusConfirmModal .modal-content .validationErrors').remove();
                $('#statusConfirmModal .modal-content').prepend('<div class="alert validationErrors alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select a reason.</div>');
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#statusConfirmModal .modal-content .validationErrors').remove();
                }, 5000);
            }else{
                axios({
                    method: "post",
                    url: route('admission.student.update.status'),
                    data: {applicantID : applicantID, statusidID : statusidID, rejectedReason : rejectedReason},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#statusConfirmModal button').removeAttr('disabled');

                        statusConfirmModal.hide();
                        window.location.reload();
                    }
                }).catch(error => {
                    $('#statusConfirmModal button').removeAttr('disabled');
                    if (error.response) {
                        if (error.response.status == 422) {
                            $('#statusConfirmModal .modal-content .validationErrors').remove();
                            $('#statusConfirmModal .modal-content').prepend('<div class="alert validationErrors alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try again later or contact with the administrator.</div>');
                            
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });

                            setTimeout(function(){
                                $('#statusConfirmModal .modal-content .validationErrors').remove();
                            }, 10000);
                        } else {
                            console.log('error');
                        }
                    }
                });
            }
        })
    }

})()