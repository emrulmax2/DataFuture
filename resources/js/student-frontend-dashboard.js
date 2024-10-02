import { createIcons, icons } from "lucide";

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
        $('.save').on('click', function(e){
            e.preventDefault();

            let tthis = $(this);
            let parentForm = tthis.parents('form');
            let formID = parentForm.attr('id');
            const form = document.getElementById(formID);
            let rurl = $("#"+formID+" input[name=url]").val();
            let mobile = $("#"+formID+" input[name=mobile]").val();
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

                    successModal.show();
                    
                    if(code=="") {
                        confirmPersonalEmailUpdateModal.hide();
                        
                    } else {
                        confirmPersonalEmailUpdateModal.hide();
                        confirmPersonalMobileUpdateModal.hide();
                        
                    }
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Success!");
                        $("#successModal .successModalDesc").html('Data Send');
                    });
                    
                    
                    
                    setTimeout(function(){
                        successModal.hide();
                    }, 1200); 
                    location.reload();
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