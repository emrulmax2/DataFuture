import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    let confModalDelTitle = 'Are you sure?';

    $('#holidayYearLeaveOptionForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('holidayYearLeaveOptionForm');

        $('#holidayYearLeaveOptionForm').find('input').removeClass('border-danger')
        $('#holidayYearLeaveOptionForm').find('.acc__input-error').html('')

        document.querySelector('#updateLO').setAttribute('disabled', 'disabled');
        document.querySelector('#updateLO svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route('holiday.year.update.leave.option'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateLO').removeAttribute('disabled');
            document.querySelector('#updateLO svg').style.cssText = 'display: none;';
            
            if (response.status == 200) {
                
                successModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Congratulations!');
                    $('#successModal .successModalDesc').html('Holiday year leave options successfully updated.');
                });
            }
        }).catch(error => {
            document.querySelector('#updateLO').removeAttribute('disabled');
            document.querySelector('#updateLO svg').style.cssText = 'display: none;';
            if(error.response){
                if(error.response.status == 422){
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editHolidayYearForm .${key}`).addClass('border-danger')
                        $(`#editHolidayYearForm  .error-${key}`).html(val)
                    }
                }else{
                    console.log('error');
                }
            }
        });
    });
})()