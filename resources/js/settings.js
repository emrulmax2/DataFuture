import { createIcons, icons } from "lucide";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('.settingsMenu ul li.hasChild > a').on('click', function(e){
        e.preventDefault();
        
        $(this).toggleClass('active text-primary font-medium');
        $(this).siblings('ul').slideToggle();
    });

    $('#companySettingsForm').on('change', '#siteFaviconUpload', function(){
        showPreview('siteFaviconUpload', 'siteFaviconImg')
    });

    $('#companySettingsForm').on('change', '#siteLogoUpload', function(){
        showPreview('siteLogoUpload', 'siteLogoImg')
    });

    function showPreview(inputId, targetImageId) {
        var src = document.getElementById(inputId);
        var target = document.getElementById(targetImageId);
        var title = document.getElementById('selected_image_title');
        var fr = new FileReader();
        fr.onload = function () {
            target.src = fr.result;
        }
        fr.readAsDataURL(src.files[0]);
    };

    $('#companySettingsForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('companySettingsForm');
    
        document.querySelector('#updateCINF').setAttribute('disabled', 'disabled');
        document.querySelector("#updateCINF svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        if($('#companySettingsForm input[name="site_logo"]').length > 0){
            form_data.append('file', $('#companySettingsForm input[name="site_logo"]')[0].files[0]); 
        }
        if($('#companySettingsForm input[name="site_logo"]').length > 0){
            form_data.append('file', $('#companySettingsForm input[name="site_favicon"]')[0].files[0]); 
        }
        axios({
            method: "post",
            url: route('site.setting.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateCINF').removeAttribute('disabled');
            document.querySelector("#updateCINF svg").style.cssText = "display: none;";
            console.log(response.data.msg);
            if (response.status == 200) {
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Active settings data successfully updated.');
                });     
            }
        }).catch(error => {
            document.querySelector('#updateCINF').removeAttribute('disabled');
            document.querySelector("#updateCINF svg").style.cssText = "display: none;";
            if (error.response) {
                console.log('error');
            }
        });
    });


})();