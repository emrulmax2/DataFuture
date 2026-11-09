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

        let photoTaskNote = "";
        drzn1.on("success", function(file, response){
            //console.log(response);
            /* the server also files the photo in the applicant's photo task (and completes it when open) */
            photoTaskNote = response && response.task_note ? response.task_note : "";
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
                // $('#addApplicantPhotoModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> WOW! Student photo successfully uploaded.</div>');
                const okAlert = $('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span></span></div>');
                okAlert.find('span').text('WOW! Student photo successfully uploaded.' + (photoTaskNote ? ' ' + photoTaskNote : ''));
                $('#addApplicantPhotoModal .modal-content').prepend(okAlert);
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#addApplicantPhotoModal .modal-content .uploadError').remove();
                    window.location.reload();
                }, photoTaskNote ? 3500 : 2000); // a little longer to read the task note
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
    if($('.rejectApplicationBtn').length > 0){
        const rejectedConfirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#rejectedConfirmModal"));

        const rejectedConfirmModalEl = document.getElementById('rejectedConfirmModal')
        rejectedConfirmModalEl.addEventListener('hide.tw.modal', function(event) {
            $("#rejectedConfirmModal .rejectedConfModTitle").html("Are you sure?");
            $("#rejectedConfirmModal .rejectedConfModDesc").html('');
            $("#rejectedConfirmModal .agreeWith").attr('data-statusid', '0');
        });

        $(document).on('click', '.rejectApplicationBtn', function(e){
            let statusID = $(this).attr('data-statusid');
            let applicantID = $(this).attr('data-applicantid');
            

            rejectedConfirmModal.show();
            document.getElementById("rejectedConfirmModal").addEventListener("shown.tw.modal", function (event) {
                $("#rejectedConfirmModal .rejectedConfModTitle").html("Are you sure?" );
                if(statusID == 3){
                    $("#rejectedConfirmModal .rejectedConfModDesc").html('Would you like to move this applicant back to "In Progress" status? Please click on agree to continue.');
                }else{
                    $("#rejectedConfirmModal .rejectedConfModDesc").html('Do you want to reject the applicantion? Please click on agree to continue.');
                }
                $("#rejectedConfirmModal .agreeWith").attr('data-statusid', statusID);
            }); 
        });

        $('#rejectedConfirmModal .agreeWith').on('click', function(e){
            e.preventDefault();
            var applicantID = $(this).attr('data-applicant');
            var statusidID = $(this).attr('data-statusid');
            var $theBtn = $(this);

            $('#rejectedConfirmModal button').attr('disabled', 'disabled');
            axios({
                method: "post",
                url: route('admission.student.reject'),
                data: {applicantID : applicantID, statusidID : statusidID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#rejectedConfirmModal button').removeAttr('disabled');

                    rejectedConfirmModal.hide();
                    window.location.reload();
                }
            }).catch(error => {
                $('#rejectedConfirmModal button').removeAttr('disabled');
                if (error.response) {
                    if (error.response.status == 422) {
                        $('#rejectedConfirmModal .modal-content .validationErrors').remove();
                        $('#rejectedConfirmModal .modal-content').prepend('<div class="alert validationErrors alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try again later or contact with the administrator.</div>');
                        
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });

                        setTimeout(function(){
                            $('#rejectedConfirmModal .modal-content .validationErrors').remove();
                        }, 2000);
                    } else {
                        console.log('error');
                    }
                }
            });
        })
    }
    
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

                            // The student-migration jobs run sequentially on the queue and take
                            // much longer than a single 2s tick, so poll until the progress bar
                            // (updated live by the Vue app) reaches 100% before closing/reloading.
                            let progressWatcher = setInterval(function(){
                                let inProgress = parseInt($('#progressBarModal input#progress').val());
                                let conversionFailed = parseInt($('#progressBarModal input#conversionFailed').val());
                                if(conversionFailed > 0) {
                                    // A conversion step failed: leave the modal open so the
                                    // error panel (and its Conversion Log link) stays readable
                                    // instead of reloading it away.
                                    clearInterval(progressWatcher);
                                    return;
                                }
                                if(inProgress >= 100) {
                                    clearInterval(progressWatcher);
                                    statusStudentProgressModal.hide();
                                    window.location.reload();
                                }
                            }, 2000);
                        } else {   
                            statusConfirmModal.hide();
                            window.location.reload();
                        }
                    }
                }).catch(error => {
                    $('#statusConfirmModal button').removeAttr('disabled');
                    if (error.response) {
                        if (error.response.status == 422) {
                            $('#statusConfirmModal .modal-content .validationErrors').remove();
                            if (error.response.data && error.response.data.missing) {
                                /* Accepted / Offer Placed refused: proof of ID or fee eligibility missing */
                                const $alert = $('<div class="alert validationErrors alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span></span></div>');
                                $alert.find('span').text(error.response.data.message);
                                $('#statusConfirmModal .modal-content').prepend($alert);
                            } else {
                            $('#statusConfirmModal .modal-content').prepend('<div class="alert validationErrors alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try again later or contact with the administrator.</div>');
                            }
                            
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });

                            if (!(error.response.data && error.response.data.missing)) {
                            setTimeout(function(){
                                $('#statusConfirmModal .modal-content .validationErrors').remove();
                            }, 2000);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });
            }
        })
    }


    const sendOfferForm = document.getElementById('sendOfferAcceptanceForm');
    if(sendOfferForm){
        const successModalEl = document.querySelector("#successModal");
        const warningModalEl = document.querySelector("#warningModal");
        const sendOfferAcceptanceModalEl = document.querySelector("#sendOfferAcceptanceModal");

        const successModal = successModalEl ? tailwind.Modal.getOrCreateInstance(successModalEl) : null;
        const warningModal = warningModalEl ? tailwind.Modal.getOrCreateInstance(warningModalEl) : null;
        const sendOfferAcceptanceModal = sendOfferAcceptanceModalEl ? tailwind.Modal.getOrCreateInstance(sendOfferAcceptanceModalEl) : null;

        if(sendOfferAcceptanceModalEl){
            sendOfferAcceptanceModalEl.addEventListener('hide.tw.modal', function () {
                const errors = sendOfferAcceptanceModalEl.querySelectorAll('.acc__input-error');
                errors.forEach(el => el.innerHTML = '');
                const checkboxes = sendOfferAcceptanceModalEl.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(cb => cb.checked = false);
            });
        }

        const successCloser = successModalEl ? successModalEl.querySelector('.successCloser') : null;
        if(successCloser){
            successCloser.addEventListener('click', function(e){
                e.preventDefault();
                if(successModal) successModal.hide();
                if(this.getAttribute('data-action') === 'RELOAD') window.location.reload();
            });
        }

        const warningCloser = warningModalEl ? warningModalEl.querySelector('.warningCloser') : null;
        if(warningCloser){
            warningCloser.addEventListener('click', function(e){
                e.preventDefault();
                if(warningModal) warningModal.hide();
                if(this.getAttribute('data-action') === 'RELOAD') window.location.reload();
            });
        }

        /* The sender's position goes on the request (signer #1's location). A desktop
           often has permission but no fix (Wi-Fi off, no GPS): try a quick fix, then a
           precise one, then watch for up to 45 s; only a refusal blocks the request,
           "no position" sends it without coordinates, as the automatic path does. */
        const geoDetail = (err) => {
            const code = err && err.code;
            const name = code === 1 ? 'PERMISSION_DENIED' : code === 2 ? 'POSITION_UNAVAILABLE' : code === 3 ? 'TIMEOUT' : 'UNSUPPORTED';
            return 'Error ' + (code === undefined ? '?' : code) + ' · ' + name + (err && err.message ? ' · ' + err.message : '');
        };
        const setSendState = (label) => {
            const btn = document.getElementById('sendOfferBtn');
            if (!btn) return;
            if (!btn.dataset.label) btn.dataset.label = btn.childNodes[0] ? btn.childNodes[0].textContent : 'Send request';
            if (label) { btn.setAttribute('disabled', 'disabled'); btn.childNodes[0].textContent = label + ' '; }
            else { btn.removeAttribute('disabled'); btn.childNodes[0].textContent = btn.dataset.label; }
        };
        const locateSender = (onOk, onErr) => {
            if (!navigator.geolocation) { onErr({ code: 0, message: 'navigator.geolocation is not available' }); return; }
            const fast = { enableHighAccuracy: false, timeout: 10000, maximumAge: 10 * 60 * 1000 };
            const precise = { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 };
            navigator.geolocation.getCurrentPosition(onOk, (err) => {
                if (err && err.code === 1) { onErr(err); return; }
                setSendState('Finding your location…');
                navigator.geolocation.getCurrentPosition(onOk, (err2) => {
                    if (err2 && err2.code === 1) { onErr(err2); return; }
                    let settled = false;
                    const stop = (id) => { settled = true; try { navigator.geolocation.clearWatch(id); } catch (e) { /* no watch */ } };
                    const id = navigator.geolocation.watchPosition(
                        (pos) => { if (settled) return; stop(id); onOk(pos); },
                        (err3) => { if (!settled && err3 && err3.code === 1) { stop(id); onErr(err3); } },
                        { enableHighAccuracy: true, timeout: 45000, maximumAge: 0 }
                    );
                    setTimeout(() => { if (settled) return; stop(id); onErr(err2 || { code: 3, message: 'timeout' }); }, 45000);
                }, precise);
            }, fast);
        };

        sendOfferForm.addEventListener('submit', function(e){
            e.preventDefault();
            setSendState('Checking location…');

            locateSender(
                function (position) {
                    submitOfferForm(position.coords.latitude, position.coords.longitude, null);
                },
                function (error) {
                    if (error && error.code === 1) {
                        setSendState(null);
                        if (warningModal) {
                            warningModal.show();
                            warningModalEl.addEventListener("shown.tw.modal", function () {
                                const title = warningModalEl.querySelector('.warningModalTitle');
                                const desc = warningModalEl.querySelector('.warningModalDesc');
                                if (title) title.innerHTML = "Location permission required";
                                if (desc) desc.innerHTML = "Allow location access for this site in your browser and try again. Your position is recorded on the request as the sender's location.<br><small style=\"opacity:.7\">" + geoDetail(error) + "</small>";
                                if (warningCloser) warningCloser.setAttribute('data-action', 'NONE');
                            }, { once: true });
                        }
                        return;
                    }
                    /* allowed, but the device returned no position: send anyway, say so */
                    submitOfferForm(null, null, geoDetail(error));
                }
            );
        });

        function submitOfferForm(latitude, longitude, locationNote) {
            const sendOfferBtn = document.getElementById('sendOfferBtn');
            setSendState('Sending…');
            if(sendOfferBtn){
                const spinner = sendOfferBtn.querySelector('svg');
                if(spinner) spinner.style.display = 'inline-block';
            }

            const formData = new FormData(sendOfferForm);
            if (latitude !== null && latitude !== undefined && longitude !== null && longitude !== undefined) {
                formData.append('latitude', latitude);
                formData.append('longitude', longitude);
            }

            axios({
                method: "post",
                url: route('admission.send.e.signature.request'),
                data: formData,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            }).then(response => {
                setSendState(null);
                if(sendOfferBtn){
                    const spinner = sendOfferBtn.querySelector('svg');
                    if(spinner) spinner.style.display = 'none';
                }

                if(sendOfferAcceptanceModal) sendOfferAcceptanceModal.hide();

                    successModal.show();
                    successModalEl.addEventListener("shown.tw.modal", function(){
                        const title = successModalEl.querySelector('.successModalTitle');
                        const desc = successModalEl.querySelector('.successModalDesc');
                        if(title) title.innerHTML = "Request sent";
                        let text = response.data.message || 'E-signature request sent.';
                        if (locationNote) text += '<br><small style="opacity:.75">Your device returned no position, so the sender\'s location was not recorded (' + locationNote + ').</small>';
                        if(desc) desc.innerHTML = text;
                        if(successCloser) successCloser.setAttribute('data-action', 'NONE');
                    }, { once: true });
                    /* the e-signature overview shows the new request once reloaded */
                    const reloads = !!document.querySelector('[data-adp-esign-reload]');
                    setTimeout(() => { successModal.hide(); if (reloads) window.location.reload(); }, 2000);
                
            }).catch(error => {
                setSendState(null);
                if(sendOfferBtn){
                    const spinner = sendOfferBtn.querySelector('svg');
                    if(spinner) spinner.style.display = 'none';
                }

                let errorMessage = 'An error occurred. Please try again.';
                if(error.response){
                    if([400, 404].includes(error.response.status)){
                        errorMessage = error.response.data.message || errorMessage;
                    }
                }

                if(warningModal){
                    warningModal.show();
                    warningModalEl.addEventListener("shown.tw.modal", function(){
                        const title = warningModalEl.querySelector('.warningModalTitle');
                        const desc = warningModalEl.querySelector('.warningModalDesc');
                        if(title) title.innerHTML = "Error!";
                        if(desc) desc.innerHTML = errorMessage;
                        if(warningCloser) warningCloser.setAttribute('data-action', 'NONE');
                    });
                }
            });
        }
    }


    $(document).on('click', '#downloadEsignBtn', function(e) {
        e.preventDefault();

        const btn = $(this);
        const id = btn.data('id');
        const spinner = btn.find('svg');

        spinner.show();
        btn.prop('disabled', true);

        axios({
            url: route('applicant.e.signature.download', id),
            method: 'GET',
            responseType: 'blob'
        })
        .then(response => {
            const blob = new Blob([response.data], { type: 'application/pdf' });
            const url = window.URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = url;

            const contentDisposition = response.headers['content-disposition'];
            let fileName = 'esignature.pdf';
            if (contentDisposition) {
                const match = contentDisposition.match(/filename="?([^"]+)"?/);
                if (match) fileName = match[1];
            }

            link.setAttribute('download', fileName);
            document.body.appendChild(link);
            link.click();
            link.remove();

            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Error downloading PDF:', error);
            alert('Failed to download the PDF. Please try again.');
        })
        .finally(() => {
            spinner.hide();
            btn.prop('disabled', false);
        });
    });


})()