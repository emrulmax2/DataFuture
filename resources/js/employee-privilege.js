import { createIcons, icons } from "lucide";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#employeePrivilegeForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('employeePrivilegeForm');
    
        $('#employeePrivilegeForm').find('button[type="submit"]').each(function(){
            $(this).attr('disabled', 'disabled');
        });

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.privilege.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            console.log(response.data);
            $('#employeePrivilegeForm').find('button[type="submit"]').each(function(){
                $(this).removeAttr('disabled');
            });
            
            if (response.status == 200) {
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee privilege successfully stored into the DB.');
                });                
                    
            }
        }).catch(error => {
            $('#employeePrivilegeForm').find('button[type="submit"]').each(function(){
                $(this).removeAttr('disabled');
            });
            if (error.response) {
                console.log('error');
            }
        });
    });
})();