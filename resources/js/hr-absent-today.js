import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
 

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const absentUpdateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#absentUpdateModal"));

    const absentUpdateModalEl = document.getElementById('absentUpdateModal')
    absentUpdateModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#absentUpdateModal .modal-body select').val('');
        $('#absentUpdateModal .modal-body input').val('');
        $('#absentUpdateModal .modal-body textarea').val('');

        $('#absentUpdateModal input[name="employee_id"]').val('0');
        $('#absentUpdateModal input[name="minutes"]').val('0');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });


    $('.absentTodayBtn').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var employee = $this.attr('data-emloyee');
        var minute = $this.attr('data-minute');
        var hourminute = $this.attr('data-hour-min');

        absentUpdateModal.show();
        $('#absentUpdateForm input[name="hour"]').val(hourminute);
        $('#absentUpdateForm input[name="employee_id"]').val(employee)
        $('#absentUpdateForm input[name="minutes"]').val(minute)
    });

    $('#absentUpdateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('absentUpdateForm');
    
        document.querySelector('#updateAbsent').setAttribute('disabled', 'disabled');
        document.querySelector("#updateAbsent svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('hr.portal.update.absent'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateAbsent').removeAttribute('disabled');
            document.querySelector("#updateAbsent svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                absentUpdateModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Absent details successfully updated .');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateAbsent').removeAttribute('disabled');
            document.querySelector("#updateAbsent svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#absentUpdateForm .${key}`).addClass('border-danger');
                        $(`#absentUpdateForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})();