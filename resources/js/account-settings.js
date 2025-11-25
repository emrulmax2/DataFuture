import { createIcons, icons } from "lucide";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('.settingsMenu ul li.hasChild > a').on('click', function(e){
        e.preventDefault();
        
        $(this).toggleClass('active text-primary font-medium');
        $(this).siblings('ul').slideToggle();
    });

    $('#accountSettingsForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('accountSettingsForm');
    
        document.querySelector('#updateCINF').setAttribute('disabled', 'disabled');
        document.querySelector("#updateCINF svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('site.setting.accounts.update'),
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


})()