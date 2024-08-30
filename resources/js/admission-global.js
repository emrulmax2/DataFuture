import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";

(function(){
    /* Site Preloader */
    if($('.sitePreLoader').length > 0){
        $(window).on('load', function(){
            $('.sitePreLoader').fadeOut();
        })
    }
    /* Site Preloader */

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
            }, 2000)
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
                }, 2000);
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
                }, 2000);
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
        const statusStudentProgressModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#progressBarModal"));
        const statusConfirmModalEl = document.getElementById('statusConfirmModal')
        statusConfirmModalEl.addEventListener('hide.tw.modal', function(event) {
            $("#statusConfirmModal .confModTitle").html("Are you sure?");
            $("#statusConfirmModal .confModDesc").html('');
            $("#statusConfirmModal .agreeWith").attr('data-statusid', '0');
            $("#statusConfirmModal .rejectedReasonArea").fadeOut(function(){
                $("#statusConfirmModal .rejectedReasonArea select").val('');
            });

            $("#statusConfirmModal .offerAcceptedErrorArea").fadeOut(function(){
                $("#statusConfirmModal .offerAcceptedErrorArea > div").fadeOut();
                $("#statusConfirmModal .offerAcceptedErrorArea select").val('');
                $("#statusConfirmModal .offerAcceptedErrorArea input").val('');
            });

            $('#statusConfirmModal .modal-content .validationErrors').remove();
            $('#statusConfirmModal button').removeAttr('disabled');
        });

        $('.changeApplicantStatus').on('click', function(e){
            e.preventDefault();
            var statusID = $(this).attr('data-statusid');
            var applicantID = $(this).attr('data-applicantid');
            var theValidation;
            
            statusConfirmModal.show();
            var title = 'Are you sure?';
            var message = 'Do you want to change the applicant status? Please click on agree to continue.';
            if(statusID == 8){
                message = 'Do you want to change the applicant status? Please Select a Reason and click on agree to continue.';
            }else if(statusID == 7){
                $.ajax({
                    method: 'POST',
                    url: route('admission.student.status.validation'),
                    data: { applicantID : applicantID },
                    async: false,
                    cache: false,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                    success: function(res, textStatus, xhr){
                        theValidation = res.msg;
                        if(res.msg.suc == 2){
                            title = 'Validation Error Found!'
                            message = 'There are some validation error found. Please fill out all fields and click on agree to continue.';
                        }else{
                            message = 'Do you want to change the applicant status? Please click on agree to continue.';
                        }
                    }
                });
            }

            document.getElementById("statusConfirmModal").addEventListener("shown.tw.modal", function (event) {
                $("#statusConfirmModal .confModTitle").html(title);
                $("#statusConfirmModal .confModDesc").html(message);
                if(statusID == 8){
                    $("#statusConfirmModal .rejectedReasonArea").fadeIn(function(){
                        $("#statusConfirmModal .rejectedReasonArea select").val('');
                    });
                }else if(statusID == 7 && theValidation.suc == 2){
                    $("#statusConfirmModal .offerAcceptedErrorArea").fadeIn('fast', function(){
                        if(theValidation.proof_type.suc == 2){
                            $('#statusConfirmModal .offerAcceptedErrorArea > div.proof_type').fadeIn('fast', function(){
                                $('#statusConfirmModal .offerAcceptedErrorArea select[name="proof_type"]').val('')
                            });
                        }else{
                            $('#statusConfirmModal .offerAcceptedErrorArea > div.proof_type').fadeOut('fast', function(){
                                $('#statusConfirmModal .offerAcceptedErrorArea select[name="proof_type"]').val(theValidation.proof_type.vals)
                            });
                        }
                        if(theValidation.proof_id.suc == 2){
                            $('#statusConfirmModal .offerAcceptedErrorArea > div.proof_id').fadeIn('fast', function(){
                                $('#statusConfirmModal .offerAcceptedErrorArea input[name="proof_id"]').val('')
                            });
                        }else{
                            $('#statusConfirmModal .offerAcceptedErrorArea > div.proof_id').fadeOut('fast', function(){
                                $('#statusConfirmModal .offerAcceptedErrorArea input[name="proof_id"]').val(theValidation.proof_id.vals)
                            });
                        }
                        if(theValidation.proof_expiredate.suc == 2){
                            $('#statusConfirmModal .offerAcceptedErrorArea > div.proof_expiredate').fadeIn('fast', function(){
                                $('#statusConfirmModal .offerAcceptedErrorArea input[name="proof_expiredate"]').val('')
                            });
                        }else{
                            $('#statusConfirmModal .offerAcceptedErrorArea > div.proof_expiredate').fadeOut('fast', function(){
                                $('#statusConfirmModal .offerAcceptedErrorArea input[name="proof_expiredate"]').val(theValidation.proof_expiredate.vals)
                            });
                        }
                        if(theValidation.fee_eligibility_id.suc == 2){
                            $('#statusConfirmModal .offerAcceptedErrorArea > div.fee_eligibility_id').fadeIn('fast', function(){
                                $('#statusConfirmModal .offerAcceptedErrorArea select[name="fee_eligibility_id"]').val('')
                            });
                        }else{
                            $('#statusConfirmModal .offerAcceptedErrorArea > div.fee_eligibility_id').fadeOut('fast', function(){
                                $('#statusConfirmModal .offerAcceptedErrorArea select[name="fee_eligibility_id"]').val(theValidation.fee_eligibility_id.vals)
                            });
                        }
                    });
                }else{
                    $("#statusConfirmModal .rejectedReasonArea").fadeOut(function(){
                        $("#statusConfirmModal .rejectedReasonArea select").val('');
                    });
                    $("#statusConfirmModal .offerAcceptedErrorArea").fadeOut('fast', function(){
                        $('#statusConfirmModal .offerAcceptedErrorArea > div').fadeOut();
                    });
                }
                $("#statusConfirmModal .agreeWith").attr('data-statusid', statusID);
            });
        });

        $('#statusConfirmModal .agreeWith').on('click', function(e){
            e.preventDefault();
            var applicantID = $(this).attr('data-applicant');
            var statusidID = $(this).attr('data-statusid');
            var rejectedReason = document.getElementById("rejected_reason").value;
            var proof_type = document.getElementById("sts_proof_type").value;
            var proof_id = document.getElementById("sts_proof_id").value;
            var proof_expiredate = document.getElementById("sts_proof_expiredate").value;
            var fee_eligibility_id = document.getElementById("sts_fee_eligibility_id").value;
            var $theBtn = $(this);

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
                }, 2000);
            }else if(statusidID == 7 && (proof_type == '' || proof_id == '' || proof_expiredate == '' || fee_eligibility_id == '') && $('#statusConfirmModal .offerAcceptedErrorArea').is(':visible')){
                $('#statusConfirmModal button').removeAttr('disabled');
                $('#statusConfirmModal .modal-content .validationErrors').remove();
                $('#statusConfirmModal .modal-content').prepend('<div class="alert validationErrors alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please fill out all required fields.</div>');
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#statusConfirmModal .modal-content .validationErrors').remove();
                }, 2000);
            }else{
                axios({
                    method: "post",
                    url: route('admission.student.update.status'),
                    data: {applicantID : applicantID, statusidID : statusidID, rejectedReason : rejectedReason, proof_type: proof_type, proof_id : proof_id, proof_expiredate : proof_expiredate, fee_eligibility_id : fee_eligibility_id},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#statusConfirmModal button').removeAttr('disabled');
                        if(statusidID==7) {
                            statusConfirmModal.hide();
                            statusStudentProgressModal.show();

                            setTimeout(function(){
                                let inProgress = $('#progressBarModal input#progress').val();
                                if(inProgress==100) {
                                    statusStudentProgressModal.hide();
                                    //window.location.reload();
                                }
                            }, 2000);
                        } else {   
                            statusConfirmModal.hide();
                            //window.location.reload();
                        }
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
                            }, 2000);
                        } else {
                            console.log('error');
                        }
                    }
                });
            }
        })
    }

})()