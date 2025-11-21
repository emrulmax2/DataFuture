import axios from "axios";
import SignaturePad from "signature_pad";

    (function() {
        const locationModalEl = document.getElementById('LocationPermissionModal');

        const locationModal = tailwind.Modal.getOrCreateInstance(locationModalEl);

        const allowBtn = document.getElementById('allowLocationBtn');

        function requestLocation() {
            if (!navigator.geolocation) {
                locationModal.show();
                return;
            }

        navigator.geolocation.getCurrentPosition((position) => {
                    locationModal.hide();


                 const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;

                    const applicantId = document.querySelector('#applicationForm input[name="applicant_id"]')?.value;
                    if (!applicantId) {
                        console.error('Applicant ID not found!');
                        return;
                    }

                    axios.post(route('applicant.e.signature.location', applicantId), {
                    latitude: latitude,
                    longitude: longitude
                    }, {
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
                }).catch(error => {
                        alert('Failed to update location. Please try again.');
                    });


                },
                (error) => {
                    if (error.code === error.PERMISSION_DENIED) {
                        locationModal.show();
                    } else {
                        console.error(error.message);
                    }
            }
            );
        }

    allowBtn.addEventListener('click', function() {
        requestLocation();
});
    requestLocation();
})();





(function(){
    const successModalEl = document.querySelector("#successModal");
    const warningModalEl = document.querySelector("#warningModal");

    const successModal = successModalEl ? tailwind.Modal.getOrCreateInstance(successModalEl) : null;
    const warningModal = warningModalEl ? tailwind.Modal.getOrCreateInstance(warningModalEl) : null;

    const addSignatureModal = tailwind.Modal.getOrCreateInstance('#addSignatureModal');


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


    /* New Signature Pad */
    const theSignaturePadCanvas = document.getElementById('theSignaturePad');

    let theSignaturePadEl = null;

    document.getElementById('addSignatureModal').addEventListener('hide.tw.modal', function(event) {
        if(theSignaturePadEl != null){
            theSignaturePadEl.clear();
        }
        $('.clearSignature').attr('disabled', 'disabled').html('Sign Here');

        $('#imageData').val('');
        $('#signatureImageFile').val('');
        $('#signatureImage').attr('src', '').addClass('hidden');
        $('.clearSignatureImg').attr('disabled', 'disabled').html('Select or Drop Image');

        $('#saveSign').attr('disabled', 'disabled');
    });

    $(document).on('click', '.signatureWrap', function(e){
        e.preventDefault();

        addSignatureModal.show();
        document.getElementById('addSignatureModal').addEventListener("shown.tw.modal", function(){
            theSignaturePadEl = new SignaturePad(theSignaturePadCanvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)'
            });

            theSignaturePadEl.addEventListener("beginStroke", () => {
                $('.clearSignature').removeAttr('disabled').html('Clear Signature');
                $('#saveSign').removeAttr('disabled');
            }, { once: true });
        });
    });

    function resizeSignatureCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        theSignaturePadCanvas.width = theSignaturePadCanvas.offsetWidth * ratio;
        theSignaturePadCanvas.height = theSignaturePadCanvas.offsetHeight * ratio;
        theSignaturePadCanvas.getContext("2d").scale(ratio, ratio);
        if(theSignaturePadEl != null){
            theSignaturePadEl.clear();
        }
    }

    // Call resize on initial load and window resize
    window.addEventListener('resize', resizeSignatureCanvas);
    resizeSignatureCanvas();

    $('.clearSignature').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        if(!$theBtn.is(":disabled")){
            $('.clearSignature').attr('disabled', 'disabled').html('Sign Here');
        }
        if(theSignaturePadEl != null){
            theSignaturePadEl.clear();
        }
        $('#saveSign').attr('disabled', 'disabled');
    });

    $('#signatureImageFile').on('change', function(){
        if($(this) != ''){
            showPreview('signatureImageFile', 'signatureImage');
        }else{
            $('#signatureImage').attr('src', '').addClass('hidden');
        }
    })
    function showPreview(inputId, targetImageId) {
        var src = document.getElementById(inputId);
        var target = document.getElementById(targetImageId);
        var title = document.getElementById('selected_image_title');
        var fr = new FileReader();
        fr.onload = function (e) {
            target.src = fr.result;
            $('#imageData').val(fr.result);
            //console.log(base64String);
        }
        fr.readAsDataURL(src.files[0]);
        target.classList.remove('hidden');
        $('.clearSignatureImg').removeAttr('disabled').html('Clear Signature');
        $('#saveSign').removeAttr('disabled');
    };

    $('.clearSignatureImg').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        
        $('#imageData').val('');
        $('#signatureImageFile').val('');
        $('#signatureImage').attr('src', '').addClass('hidden');
        $('.clearSignatureImg').attr('disabled', 'disabled').html('Select or Drop Image');
        $('#saveSign').attr('disabled', 'disabled');
    });

    $('.signatureTabMenu').on('click', 'button.nav-link', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let theTarget = $theBtn.attr('data-tw-target');

        if(theSignaturePadEl != null){
            theSignaturePadEl.clear();
        }
        if(theTarget == '#signatureTab'){
            $('#imageData').val('');
            $('#signatureImageFile').val('');
            $('#signatureImage').attr('src', '').addClass('hidden');
            $('.clearSignatureImg').attr('disabled', 'disabled').html('Select or Drop Image');
        }else{
            $('.clearSignature').attr('disabled', 'disabled').html('Sign Here');
            if(theSignaturePadEl != null){
                theSignaturePadEl.clear();
            }
        }
        $('#saveSign').attr('disabled', 'disabled');
    });

    $('#addSignatureForm').on('submit', function(e){
        e.preventDefault();
        let $theForm = $(this);
        let $theBtn = $theForm.find('#saveSign');

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg').fadeIn();

        let activeTab = $('.signatureTabMenu').find('button.active').attr('data-tw-target');
        let imageData = null;
        if(activeTab == '#imageTab'){
            imageData = $('#imageData').val();
        }else{
            if(theSignaturePadEl && !theSignaturePadEl.isEmpty()){
                imageData = theSignaturePadEl.toDataURL('image/png');

                const strokes = theSignaturePadEl.toData(); 
                //console.log(imageData); 
                //console.log(strokes); 
            }
        }

        if(imageData != null){
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg').fadeOut();
            $('#theSignature').attr('src', imageData).removeClass('hidden');
            $('#signature-input').val(imageData);
            addSignatureModal.hide();
        }else{
            $theBtn.find('svg').fadeOut();
            $('#theSignature').attr('src', '').addClass('hidden');
            $('#signature-input').val('');
        }
    });
    /* New Signature Pad */

    // --- Application Form ---
    const applicationForm = document.getElementById('applicationForm');

    if (applicationForm) {
        applicationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            const submitBtn = applicationForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.setAttribute('disabled', 'disabled');
                const spinner = submitBtn.querySelector('svg');
                if (spinner) spinner.style.cssText = "display: inline-block;";
            }

            function submitWithLocation(latitude, longitude) {
                let signature = '';
                if (theSignaturePadEl && !theSignaturePadEl.isEmpty()) {
                    signature = theSignaturePadEl.toDataURL('image/png');
                    const sigInput = document.getElementById('signature-input');
                    if (sigInput) sigInput.value = signature;
                }

                const applicantId = applicationForm.querySelector('input[name="applicant_id"]')?.value || '';
                const videoConsent = applicationForm.querySelector('input[name="video_consent"]')?.checked ? 1 : 0;
                const declaration = applicationForm.querySelector('input[name="declaration"]')?.checked ? 1 : 0;
                const signatureDate = applicationForm.querySelector('input[name="signature_date"]')?.value || '';
                const signatureData = applicationForm.querySelector('input[name="signature"]')?.value || '';
                
                if (!videoConsent || !declaration) {
                    alert('Consent required.');
                    submitBtn.removeAttribute('disabled');
                    const spinner = submitBtn.querySelector('svg');
                    if(spinner) spinner.style.cssText = "display: none;";
                    return;
                }

                if (!signatureData) {
                    alert('Signature Required.');
                    submitBtn.removeAttribute('disabled');
                    const spinner = submitBtn.querySelector('svg');
                    if(spinner) spinner.style.cssText = "display: none;";
                    return;
                }

                let formData = new FormData(applicationForm);
                formData.append('video_consent', videoConsent);
                formData.append('declaration', declaration);
                formData.append('signature_date', signatureDate);
                formData.append('latitude', latitude);
                formData.append('longitude', longitude);

                axios({
                    method: "post",
                    url: route('applicant.e.signature.store', applicantId),
                    data: formData,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if(submitBtn){
                        submitBtn.removeAttribute('disabled');
                        const spinner = submitBtn.querySelector('svg');
                        if(spinner) spinner.style.cssText = "display: none;";
                    }
                    if(successModal) successModal.show();
                    successModalEl.addEventListener("shown.tw.modal", function(){
                        const title = successModalEl.querySelector('.successModalTitle');
                        const desc = successModalEl.querySelector('.successModalDesc');
                        if(title) title.innerHTML = "Success!";
                        if(desc) desc.innerHTML = response.data.message || 'Offer acceptance submitted successfully.';
                        if(successCloser) successCloser.setAttribute('data-action', 'RELOAD');
                    });
                }).catch(error => {
                    if(submitBtn){
                        submitBtn.removeAttribute('disabled');
                        const spinner = submitBtn.querySelector('svg');
                        if(spinner) spinner.style.cssText = "display: none;";
                    }

                    if(error.response){
                        if(error.response.status === 422){
                            for(const [key, val] of Object.entries(error.response.data.errors)){
                                const input = applicationForm.querySelector(`[name="${key}"]`);
                                if(input) input.classList.add('border-danger');
                                const errorEl = applicationForm.querySelector(`.error-${key}`);
                                if(errorEl) errorEl.innerHTML = val;
                            }
                        } else {
                            alert('Something went wrong. Please try again.');
                            console.error(error.response.data);
                        }
                    }
                });
            }

           navigator.geolocation.getCurrentPosition(
            (position) => {
                submitWithLocation(position.coords.latitude, position.coords.longitude);
            },
            (error) => {
                warningModal.show();
                warningModalEl.addEventListener("shown.tw.modal", function(){
                    const title = warningModalEl.querySelector('.warningModalTitle');
                    const desc = warningModalEl.querySelector('.warningModalDesc');
                    if(title) title.innerHTML = "Location Required!";
                    if(desc) desc.innerHTML = "You must allow location access to submit this form.";
                });
                

                if(submitBtn){
                    submitBtn.removeAttribute('disabled');
                    const spinner = submitBtn.querySelector('svg');
                    if(spinner) spinner.style.cssText = "display: none;";
                }
            },
            { enableHighAccuracy: true }
        );
        });
    }

})()
