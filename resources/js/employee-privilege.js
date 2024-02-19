import { createIcons, icons } from "lucide";
import dayjs from "dayjs";
import Litepicker from "litepicker";

(function(){
    let rangeDateOpton = {
        autoApply: true,
        singleMode: false,
        numberOfColumns: 2,
        numberOfMonths: 2,
        showWeekNumbers: false,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const picker = new Litepicker({ 
        element: document.getElementById('rangepicker'),
        ...rangeDateOpton,
    });

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

    /* Login Date Range Toggle Start */
    $('#permission_remote_access_1').on('change', function(){
        $('#dateRangeWrap').fadeOut(function(e){
            $('.rangepicker', this).val('').removeAttr('required');
        })
        if($(this).prop('checked')){
            $(this).siblings('.ra_status_label').text('Allowed');
            $('#inRangeSwitch').fadeIn('fast', function(){
                $('#permission_remote_access_2').prop('checked', false);
            })
        }else{
            $(this).siblings('.ra_status_label').text('Not Allowed');
            $('#inRangeSwitch').fadeOut('fast', function(){
                $('#permission_remote_access_2').prop('checked', false);
            })
        }
    });
    $('#permission_remote_access_2').on('change', function(){
        if($(this).prop('checked')){
            $('#dateRangeWrap').fadeIn(function(e){
                $('.rangepicker', this).val('').attr('required', 'required');
            });
        }else{
            $('#dateRangeWrap').fadeOut(function(e){
                $('.rangepicker', this).val('').removeAttr('required');
            });
        }
    })
    /* Login Date Range Toggle End */
})();