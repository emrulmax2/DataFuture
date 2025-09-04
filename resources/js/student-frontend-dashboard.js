import { createIcons, icons } from "lucide";
import INTAddressLookUps from "./address_lookup";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addressUpdateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addressUpdateModal"));

    // INIT Address Lookup
    if($('.theAddressWrap').length > 0){
        INTAddressLookUps();
    }

    $('#addressUpdateModal #addrProofDocument').on('change', function(){
        var inputs = document.getElementById('addrProofDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>'+name+'</div>';
        }

        $('#addressUpdateModal .addrProofDocumentNames').fadeIn().html(html);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#addressUpdateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addressUpdateForm');
    
        document.querySelector('#updtAddress').setAttribute('disabled', 'disabled');
        document.querySelector("#updtAddress svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addressUpdateForm input[name="document"]')[0].files[0]);
        axios({
            method: "post",
            url: route('students.update.address.request'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updtAddress').removeAttribute('disabled');
            document.querySelector("#updtAddress svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addressUpdateModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.message);
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updtAddress').removeAttribute('disabled');
            document.querySelector("#updtAddress svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addressUpdateForm .${key}`).addClass('border-danger');
                        $(`#addressUpdateForm  .error-${key}`).html(val);
                    }
                } else if(error.response.status == 304){
                    addressUpdateModal.hide();
                    
                    warningModal.show(); 
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .successModalTitle").html("Error!" );
                        $("#warningModal .successModalDesc").html(error.response.data.message);
                    }); 
                
                    setTimeout(function(){
                        warningModal.hide();
                    }, 2000);
                }else {
                    console.log('error');
                }
            }
        });
    });
})();


/* Profile Menu Start */
if($('.liveStudentMainMenu').length > 0){
    $('.liveStudentMainMenu li.hasChildren > a').on('click', function(e){
        e.preventDefault();
        var $this = $(this);

        if($this.hasClass('active')){
            $this.removeClass('active');
            $this.siblings('.liveStudentSubMenu').removeClass('show');
            $('.liveStudentMainMenu').animate({'padding-bottom' : '0'}, 'fast');
        }else{
            $this.parent('li').siblings('li').children('a').removeClass('active');
            $this.parent('li').siblings('li').children('.liveStudentSubMenu').removeClass('show');

            $this.addClass('active');
            $('.liveStudentMainMenu').animate({'padding-bottom' : '55px'}, 350, function(){
                $this.siblings('.liveStudentSubMenu').addClass('show');
            });
        }
    })
}

if($('.doitOnlineSecondBoxToggle').length > 0) {

    $(".doitOnlineSecondBoxToggle").on('click', function(e){
        e.preventDefault();
        $("#doitOnlineSecondBox").toggle("slow");
    });
}


if($('#awardingBodyEditModal').length > 0 ) {
    
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const awardingBodyEditModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#awardingBodyEditModal"));
    const confirmAwardMissModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmAwardingBodyMissingInformationModal"));
    awardingBodyEditModal.show();
    $("#awardingBodyDetailsVerificationEditModalForm").on('submit', function(e){
        e.preventDefault();
            const form = document.getElementById('awardingBodyDetailsVerificationEditModalForm');
        
            
            $('#agreeWithAwarding').attr('disabled', 'disabled');
            $("#agreeWithAwarding .loadingClass").removeClass('hidden')

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('students.awarding.body.status.update'),
                
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('#agreeWithAwarding').removeAttr('disabled');
                $("#agreeWithAwarding .loadingClass").addClass('hidden');
                
                if (response.status == 200) {

                    $('#agreeWithAwarding').removeAttr('disabled');
                    $("#agreeWithAwarding .loadingClass").addClass('hidden');
                    awardingBodyEditModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Success!");
                        $("#successModal .successModalDesc").html('Pearson Verification Successfully Saved.');
                    });    
                    window.location.reload();     
                }
                
            }).catch(error => {
                $('#agreeWithAwarding').removeAttr('disabled');
                    $("#agreeWithAwarding .loadingClass").addClass('hidden');
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addForm .${key}`).addClass('border-danger')
                            $(`#addForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        
    });
    
    $("#confirmModalconfirmAwardingBodyMissingInformationModalForm").on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('confirmModalconfirmAwardingBodyMissingInformationModalForm');
            $('#formSubmitAward').attr('disabled', 'disabled');
            $("#formSubmitAward .loadingClass").removeClass('hidden');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('students.awarding.body.status.update'),
                
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                    $('#formSubmitAward').removeAttr('disabled');
                    $("#formSubmitAward .loadingClass").addClass('hidden');
                
                if (response.status == 200) {

                    $('#formSubmitAward').removeAttr('disabled');
                    $("#formSubmitAward .loadingClass").addClass('hidden');
                    awardingBodyEditModal.hide();
                    confirmAwardMissModal.hide();
                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Success!");
                        $("#successModal .successModalDesc").html('Pearson Verification Missing Information Saved.');
                    });    
                    window.location.reload();     
                }
                
            }).catch(error => {
                $('#formSubmitAward').removeAttr('disabled');
                    $("#formSubmitAward .loadingClass").addClass('hidden');
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addForm .${key}`).addClass('border-danger')
                            $(`#addForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });
}

if($('.save').length>0) {
    const confirmPersonalEmailUpdateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmPersonalEmailUpdateModal"));
    const confirmPersonalMobileUpdateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmPersonalMobileUpdateModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
        $('.save').on('click', function(e){
            e.preventDefault();

            let tthis = $(this);
            let parentForm = tthis.parents('form');
            let formID = parentForm.attr('id');
            const form = document.getElementById(formID);
            let rurl = $("#"+formID+" input[name=url]").val();
            let mobile = $("#"+formID+" input[name=mobile]").val();
            let email = $("#"+formID+" input[name=email]").val();
            let code = $("#"+formID+" input[name=code]").val();
            
            tthis.attr('disabled', 'disabled');
            $(".loadingClass",tthis).removeClass('hidden');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: rurl,
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {

                tthis.removeAttr('disabled');
                $(".loadingClass",tthis).addClass('hidden');

                if (response.status == 200) {

                    tthis.removeAttr('disabled');
                    
                    $(".loadingClass",tthis).addClass('hidden');

                    
                    if(rurl== route('students.verify.email')) {
                        confirmPersonalEmailUpdateModal.hide();
                        confirmPersonalMobileUpdateModal.hide();
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .successModalTitle").html("Attention!");
                            $("#warningModal .successModalDesc").html('We’ve sent a verification link to your email. Please check your inbox and click the verify button. Without this verification, we can’t update your email.');
                        });
                        setTimeout(function(){
                            warningModal.hide();
                        }, 30000); 

                    }
                    if(rurl==route('students.verify.mobile')) {
                            confirmPersonalEmailUpdateModal.hide();
                            successModal.show();
                            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                                $("#successModal .successModalTitle").html("Success!");
                                $("#successModal .successModalDesc").html('OTP SEND');
                            });
                            setTimeout(function(){
                                successModal.hide();
                            }, 1200); 
                            $('#confirmModalForm2').addClass('hidden');
                            $('#confirmModalForm3').removeClass('hidden');
                        
                    }

                    if(rurl==route('students.update.mobile')) {

                        successModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html("Success!");
                            $("#successModal .successModalDesc").html('Mobile number updated successfully');
                        });
                        setTimeout(function(){
                            successModal.hide();
                            location.reload();
                        }, 4500); 
                    }
                    
                    
                }
            }).catch(error => {
                
                tthis.removeAttr('disabled');
                $("svg",tthis).css("display", "none");

                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#${formID} .${key}`).addClass('border-danger')
                            $(`#${formID}  .error-${key}`).html(val)
                        }
                    }if(error.response.status == 304){
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .successModalTitle").html("Alert!");
                            $("#warningModal .successModalDesc").html('No mobile changes found to be updated.');
                        });
                        setTimeout(function(){
                            warningModal.hide();
                            location.reload();
                        }, 6000); 

                    } else {
                        console.log('error');
                    }
                }
            });
        });
        if($('#success-notification-toggle').length>0) {
            $("#success-notification-toggle").trigger('click');
        }
}
/* Profile Menu End */