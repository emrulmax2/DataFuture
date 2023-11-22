import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        window.location.reload();
    });

    $('#hrConditionForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('hrConditionForm');
        let $form = $(this);

        $form.find('.updateHRC').attr('disabled', 'disabled');
        $form.find('.updateHRC svg').fadeIn();

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route('hr.condition.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $form.find('.updateHRC').removeAttr('disabled');
            $form.find('.updateHRC svg').fadeOut();
            
            if (response.status == 200) {
                
                successModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Congratulations!');
                    $('#successModal .successModalDesc').html('HR attendance conditions successfully updated.');
                });

                setTimeout(function(){
                    successModal.hide();
                }, 5000)
            }
            
        }).catch(error => {
            $form.find('.updateHRC').attr('disabled', 'disabled');
            $form.find('.updateHRC svg').fadeIn();

            if (error.response) {
                if (error.response.status == 422) {
                    console.log('error');
                }
            }
        });
    });


})();