
import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";

import IMask from 'imask';

import dayjs from "dayjs";
import Litepicker from "litepicker";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });
    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            warningModal.hide();
            window.location.reload();
        }else{
            warningModal.hide();
        }
    });

    let tomOptions = {
        plugins: {
            dropdown_input: {},
            remove_button: {
                title: "Remove this item",
            },
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    var employeeIDS = new TomSelect('#employeeIDS', tomOptions);

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
    const liveAttendanceDate = new Litepicker({
        element: document.getElementById('liveAttendanceDate'),
        ...dateOption
    });

    liveAttendanceDate.on('selected', (date) => {
        $('#addLiveAttendanceTable tbody tr.employeeAttendanceRow').remove();
        $('#addLiveAttendanceTable tbody tr.noticeRow').fadeIn('fast');
        $('#saveLiveAttendance').fadeOut();
        employeeIDS.clear(true);
    });

    $('#liveAttendanceDate').each(function(){
        IMask(
            this, {
                mask: '00-00-0000'
            }
        )
    });
    if($('.timeMask').length > 0){
        $('.timeMask').each(function(){
            IMask(
                this, {
                    mask: '00:00'
                }
            )
        });
    }

    employeeIDS.on('item_add', function(employee_id, item){
        $('.leaveTableLoader').addClass('active');
        let theDate = $('#liveAttendanceDate').val();
        axios({
            method: "post",
            url: route('hr.portal.live.attedance.get.day.data'),
            data: {employee_id : employee_id, theDate : theDate},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('.leaveTableLoader').removeClass('active');
            if (response.status == 200) {
                let res = response.data.res;
                $('#addLiveAttendanceTable tbody tr.noticeRow').fadeOut('fast', function(){
                    $('#addLiveAttendanceTable > tbody').append(res);
                });

                $('#saveLiveAttendance').fadeIn();
                
                setTimeout(function(){
                    $('#addLiveAttendanceTable').find('input.clockMask').each(function(){
                        console.log('hi')
                        IMask(this, {mask: '00:00'});
                    });
                    console.log('his '+$('#addLiveAttendanceTable').find('input').length)
                }, 1000);
            }
        }).catch(error => {
            $('.leaveTableLoader').removeClass('active');
            if (error.response) {
                console.log('error');
            }
        });
    });

    employeeIDS.on('item_remove', function(employee_id, $item){
        $('#addLiveAttendanceTable tbody tr#employeeAttendanceRow_'+employee_id).remove();
        var attendanceRows = $('#addLiveAttendanceTable tbody tr.employeeAttendanceRow').length;
        if(attendanceRows == 0){
            $('#addLiveAttendanceTable tbody tr.noticeRow').fadeIn('fast');
            $('#saveLiveAttendance').fadeOut();
        }else{
            $('#addLiveAttendanceTable tbody tr.noticeRow').fadeOut('fast');
            $('#saveLiveAttendance').fadeIn();
        }
    });

    $('#saveLiveAttendance').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var $form = $('#attendanceLiveForm');
        const form = document.getElementById('attendanceLiveForm');
    
        $('.leaveTableLoader').addClass('active');
        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg').fadeIn();

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('hr.portal.live.attedance.fee.data'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('.leaveTableLoader').removeClass('active');
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg').fadeOut();
            
            if (response.status == 200) {
                var res = response.data.res;

                if(res == 2){
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html( "Oops!" );
                        $("#warningModal .warningModalDesc").html('Something went wrong. Please reload the page and try again');
                        $("#warningModal .warningCloser").attr('data-action', 'RELOAD');
                    });   
                    
                    setTimeout(function(){
                        warningModal.hide();
                        window.location.reload();
                    }, 2000)
                }else{
                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Employee\'s live attendance data successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });   
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000)
                }
            }
        }).catch(error => {
            $('.leaveTableLoader').removeClass('active');
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg').fadeOut();
            if (error.response) {
                console.log('error');
            }
        });
    })
})()