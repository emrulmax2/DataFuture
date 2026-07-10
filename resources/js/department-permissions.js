(function(){
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('#permissionUpdateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('permissionUpdateForm');
    
        document.querySelector('#savePermissionsBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#savePermissionsBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('permissions.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#savePermissionsBtn').removeAttribute('disabled');
            document.querySelector("#savePermissionsBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Permissions Successfully Updated.');
                });     
            }
        }).catch(error => {
            document.querySelector('#savePermissionsBtn').removeAttribute('disabled');
            document.querySelector("#savePermissionsBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#permissionUpdateForm .${key}`).addClass('border-danger');
                        $(`#permissionUpdateForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
})()