import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

import dayjs from "dayjs";
import Litepicker from "litepicker";
 

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

        $('#absentUpdateModal input[name="leave_day_id"]').val('0');
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

    if($('.timeMask').length > 0){
        var maskOptions = {
            mask: '00:00'
        };
        $('.timeMask').each(function(){
            var mask = IMask(this, maskOptions);
        })
    }

    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        inlineMode: false,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };
    const absentAttendanceDate = new Litepicker({
        element: document.getElementById('absentAttendanceDate'),
        ...dateOption
    });

    absentAttendanceDate.on('selected', (date) => {
        var theDate = date.getTime() / 1000; //year+'-'+(month < 10 ? '0'+month : month)+'-'+(day < 10 ? '0'+day : day); 
        window.location.href = route('hr.portal.absent.employee', $('#absentAttendanceDate').val());
    });


    $('.absentTodayTr').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var employee = $this.attr('data-emloyee');
        var minute = $this.attr('data-minute');
        var hourminute = $this.attr('data-hour-min');
        
        var leavetype = $this.attr('data-leavetype');
        var leavedayid = $this.attr('data-leavedayid');
        var leavedayminute = $this.attr('data-leavedayminute');
        var leavedayhourminute = $this.attr('data-leavedayhourminute');
        var leavenote = $this.attr('data-leavenote');

        absentUpdateModal.show();
        $('#absentUpdateForm input[name="employee_id"]').val(employee);

        if(leavedayid > 0){
            $('#absentUpdateForm [name="leave_day_id"]').val(leavedayid);
            $('#absentUpdateForm [name="leave_type"]').val(leavetype);
            $('#absentUpdateForm input[name="hour"]').val(leavedayhourminute);
            $('#absentUpdateForm input[name="minutes"]').val(leavedayminute);
            $('#absentUpdateForm [name="note"]').val(leavenote);
            if(leavetype == 5){
                $('#absentUpdateForm input[name="hour"]').attr('data-todayhour', leavedayhourminute).removeAttr('readonly');
            }else{
                $('#absentUpdateForm input[name="hour"]').attr('data-todayhour', hourminute).attr('readonly', 'readonly');
            }
        }else{
            $('#absentUpdateForm [name="leave_day_id"]').val(0);
            $('#absentUpdateForm [name="leave_type"]').val('');
            $('#absentUpdateForm input[name="hour"]').attr('data-todayhour', hourminute).attr('readonly', 'readonly');
            $('#absentUpdateForm input[name="minutes"]').val(minute)
            $('#absentUpdateForm [name="note"]').val('');
        }
    });

    $('#absentUpdateForm [name="leave_type"]').on('change', function(){
        if($(this).val() == 5){
            $('#absentUpdateForm input[name="hour"]').val($('#absentUpdateForm input[name="hour"]').attr('data-todayhour')).removeAttr('readonly');
        }else{
            $('#absentUpdateForm input[name="hour"]').val('00:00').attr('readonly', 'readonly');
        }
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