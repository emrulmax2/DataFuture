import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

import dayjs from "dayjs";
import Litepicker from "litepicker";
import 'litepicker/dist/plugins/multiselect';

(function(){
    const empHolidayAdjustmentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#empHolidayAdjustmentModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const empNewLeaveRequestModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#empNewLeaveRequestModal"));

    const empHolidayAdjustmentModalEl = document.getElementById('empHolidayAdjustmentModal')
    empHolidayAdjustmentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#empHolidayAdjustmentModal [name="adjustmentOpt"]').prop('checked', false);
        $('#empHolidayAdjustmentModal [name="adjustment"]').val('');
        $('#empHolidayAdjustmentModal [name="hr_holiday_year_id"]').val('0');
        $('#empHolidayAdjustmentModal [name="employee_working_pattern_id"]').val('0');
    });

    const empNewLeaveRequestModalEl = document.getElementById('empNewLeaveRequestModal')
    empNewLeaveRequestModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#empNewLeaveRequestModal .modal-body').html('');
        $('#empNewLeaveRequestModal [name="employee_leave_id"]').val('0');
    });

    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#confirmModal .confModTitle').html('');
        $('#confirmModal .confModDesc').html('');
        $('#confirmModal .agreeWith').attr('data-id', '0').attr('data-action', 'NONE');
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


    /* Leave Form Start */
    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        inlineMode: true,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: null,
            months: false,
            years: false,
        },
    };

    if($('#leaveCalendar').length > 0){
        let leaveStart = $('#leaveCalendar').attr('data-start');
        if (typeof leaveStart !== 'undefined' && leaveStart !== false) {
            dateOption.minDate = leaveStart;
        }
        let leaveEnd = $('#leaveCalendar').attr('data-end');
        if (typeof leaveEnd !== 'undefined' && leaveEnd !== false && leaveEnd != 'unknown') {
            dateOption.maxDate = leaveEnd;
        }
        let leaveDisableDates = $('#leaveCalendar').attr('data-disable-dates');
        if (typeof leaveDisableDates !== 'undefined' && leaveDisableDates !== false && leaveDisableDates != 'unknown' && leaveDisableDates != '') {
            leaveDisableDates = leaveDisableDates.split(',')
            dateOption.lockDays = leaveDisableDates;
        }

        const leaveCalendar = new Litepicker({
            element: document.getElementById('leaveCalendar'),
            ...dateOption,
            autoRefresh: true,
            plugins: ['multiselect'],
            multiselect: {
                max: 20,
            },
            lockDaysFilter: (day) => {
                let leaveDisableDays = $('#leaveCalendar').attr('data-disable-days');
                
                if (typeof leaveDisableDays !== 'undefined' && leaveDisableDays !== false && leaveDisableDays != ''){
                    leaveDisableDays = leaveDisableDays.split(',');
                    if(leaveDisableDays.length > 0){
                        var ldd = [];
                        for(var i = 0; i < leaveDisableDays.length; i++){
                            ldd.push(parseInt(leaveDisableDays[i], 10));
                        }
                        const d = day.getDay();
                        return ldd.includes(d);
                    }
                }
             },
        });
        leaveCalendar.on('multiselect.select', (date) => {
            var $form = $('#employeeLeaveForm');
            var $LeaveYear = $form.find('[name="leave_holiday_years"]');
            var $LeavePattern = $form.find('[name="leave_pattern"]');
            var $LeaveType = $form.find('[name="leave_type"]');

            var LeaveYear = $LeaveYear.val();
            var LeavePattern = $LeavePattern.val();
            var LeaveType = $LeaveType.val();
            var EmployeeId = $('[name="employee_id"]', $form).val();

            var LeaveDates = [];
            var LeaveDatesStamps = leaveCalendar.preMultipleDates;
            LeaveDatesStamps.sort(function(a, b){return a - b});
            if(LeaveDatesStamps.length > 0){
                for(var i = 0; i < LeaveDatesStamps.length; i++){
                    let theDate = new Date(LeaveDatesStamps[i]);
                    LeaveDates.push(theDate.toLocaleDateString('en-GB'));
                }
            }

            if(LeaveDates.length > 0){
                axios({
                    method: "POST",
                    url: route('employee.holiday.ajax.limit'),
                    data: {EmployeeId : EmployeeId, LeaveYear : LeaveYear, LeavePattern : LeavePattern, LeaveType : LeaveType, LeaveDates : LeaveDates},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        let dataset = response.data.res;
                        if(dataset.suc == 2){
                            $('.leaveFormStep2').fadeIn('fast').html(dataset.html);
                            $('#confirmRequest').attr('disabled', 'disabled');
                        }else{
                            $('.leaveFormStep2').fadeIn('fast').html(dataset.html);
                            $('#confirmRequest').removeAttr('disabled');
                        }
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });

                        var maskOptionsNew = {
                            mask: '00:00'
                        };
                        $('.leaveFormStep2 .timeMask').each(function(){
                            var mask = IMask(this, maskOptionsNew);
                        });
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('.leaveFormStep2').fadeOut('fast').html('');
                $('#confirmRequest').attr('disabled', 'disabled');
            }
        });
        leaveCalendar.on('multiselect.deselect', (date) => {
            var $form = $('#employeeLeaveForm');
            var $LeaveYear = $form.find('[name="leave_holiday_years"]');
            var $LeavePattern = $form.find('[name="leave_pattern"]');
            var $LeaveType = $form.find('[name="leave_type"]');

            var LeaveYear = $LeaveYear.val();
            var LeavePattern = $LeavePattern.val();
            var LeaveType = $LeaveType.val();
            var EmployeeId = $('[name="employee_id"]', $form).val();

            var LeaveDates = [];
            var LeaveDatesStamps = leaveCalendar.preMultipleDates;
            LeaveDatesStamps.sort(function(a, b){return a - b});
            if(LeaveDatesStamps.length > 0){
                for(var i = 0; i < LeaveDatesStamps.length; i++){
                    let theDate = new Date(LeaveDatesStamps[i]);
                    LeaveDates.push(theDate.toLocaleDateString('en-GB'));
                }
            }

            if(LeaveDates.length > 0){
                axios({
                    method: "POST",
                    url: route('employee.holiday.ajax.limit'),
                    data: {EmployeeId : EmployeeId, LeaveYear : LeaveYear, LeavePattern : LeavePattern, LeaveType : LeaveType, LeaveDates : LeaveDates},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        let dataset = response.data.res;
                        if(dataset.suc == 2){
                            $('.leaveFormStep2').fadeIn('fast').html(dataset.html);
                            $('#confirmRequest').attr('disabled', 'disabled');
                        }else{
                            $('.leaveFormStep2').fadeIn('fast').html(dataset.html);
                            $('#confirmRequest').removeAttr('disabled');
                        }
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });

                        var maskOptionsNew = {
                            mask: '00:00'
                        };
                        $('.leaveFormStep2 .timeMask').each(function(){
                            var mask = IMask(this, maskOptionsNew);
                        });
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('.leaveFormStep2').fadeOut('fast').html('');
                $('#confirmRequest').attr('disabled', 'disabled');
            }
        });

        $('#employeeLeaveForm [name="leave_holiday_years"], #employeeLeaveForm [name="leave_pattern"], #employeeLeaveForm [name="leave_type"]').on('change', function(){
            var $form = $('#employeeLeaveForm');
            var $LeaveYear = $form.find('[name="leave_holiday_years"]');
            var $LeavePattern = $form.find('[name="leave_pattern"]');
            var $LeaveType = $form.find('[name="leave_type"]');

            var LeaveYear = $LeaveYear.val();
            var LeavePattern = $LeavePattern.val();
            var LeaveType = $LeaveType.val();
            var EmployeeId = $('[name="employee_id"]', $form).val();

            leaveCalendar.clearSelection();
            $('.leaveFormStep2').fadeOut('fast').html('');
            $('#confirmRequest').attr('disabled', 'disabled');
            
            if($LeaveYear.val() == '' || $LeavePattern.val() == '' || $LeaveType.val() == ''){
                $('.holidayStatistics').html('<div class="alert alert-danger-soft show flex items-start mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <div><strong>Holiday Year, Work Pattern, or Type </strong> can not be empty.</div></div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }else{
                axios({
                    method: "POST",
                    url: route('employee.holiday.ajax.statistics'),
                    data: {EmployeeId : EmployeeId, LeaveYear : LeaveYear, LeavePattern : LeavePattern, LeaveType : LeaveType},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        let dataset = response.data.res;
                        
                        $('.holidayStatistics').html(dataset.statistics);
                        $('#leaveCalendar').attr('data-start', dataset.startDate);
                        $('#leaveCalendar').attr('data-end', dataset.endDate);
                        $('#leaveCalendar').attr('data-end', dataset.endDate);
                        $('#leaveCalendar').attr('data-disable-dates', dataset.disableDates);
                        $('#leaveCalendar').attr('data-disable-days', dataset.disableDays);

                        leaveCalendar.setOptions({
                            minDate: dataset.startDate,
                            maxDate: (dataset.endDate != '' && dataset.endDate != 'unknown' ? dataset.endDate : ''),
                            lockDaysFilter: (day) => {
                                let leaveDisableDays = dataset.disableDays;
                                
                                if (leaveDisableDays != ''){
                                    leaveDisableDays = leaveDisableDays.split(',');
                                    if(leaveDisableDays.length > 0){
                                        var ldd = [];
                                        for(var i = 0; i < leaveDisableDays.length; i++){
                                            ldd.push(parseInt(leaveDisableDays[i], 10));
                                        }
                                        const d = day.getDay();
                                        return ldd.includes(d);
                                    }else{
                                        const d = day.getDay();
                                        return [].includes(d);
                                    }
                                }else{
                                    const d = day.getDay();
                                    return [].includes(d);
                                }
                            }
                        });
                        leaveDisableDates = (dataset.disableDates != '' ? dataset.disableDates.split(',') : []);
                        leaveCalendar.setOptions({
                            lockDays: leaveDisableDates
                        });
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }
        });
    }

    $('#employeeLeaveForm').on('keyup paste', '.leaveDatesHours', function(){
        var $theInput = $(this);
        var $theTr = $theInput.parents('tr');

        var availableBalance = parseInt($('#employeeLeaveForm [name="balance_left"]').val(), 10);
        var inputDayMax = parseInt($theInput.attr('data-daymax'), 10);
        var inputMaxHour = parseInt($theInput.attr('data-maxhour'), 10);
        
        var inputVal = $theInput.val();
        if(inputVal != '' && inputVal.length == 5){
            var inputMinute = string_to_minute(inputVal);
            if(inputMinute > inputDayMax && !$theTr.hasClass('defaultFractionRow')){
                // Can not insert more than input day max hour for normal row.
                $theInput.val(hour_minute_formate(inputDayMax));
            }else if($theTr.hasClass('defaultFractionRow') && inputMinute > inputMaxHour){
                // Can not insert more than input max hour for default fraction row row.
                $theInput.val(hour_minute_formate(inputMaxHour));
            }

            var totalBookingHour = calculateTotalBookedHour();
            if(totalBookingHour > availableBalance){
                resetHolidayBookingHours();
            }else{
                var currentBalance = availableBalance - totalBookingHour;
                $('#employeeLeaveForm .requestedHours').html(hour_minute_formate(totalBookingHour));
                $('#employeeLeaveForm .balanceLeft').html(hour_minute_formate(currentBalance));
            }
        }
    });

    $('#employeeLeaveForm').on('change', '.fractionIndicator', function(e){
        var $theCheckbox = $(this);
        var $theTr = $theCheckbox.parents('tr');
        var $theInput = $theTr.find('.leaveDatesHours');
        var maxHour = parseInt($theInput.attr('data-maxhour'), 10);
        if($theCheckbox.prop('checked')){
            $theInput.removeAttr('readonly');
        }else{
            $theInput.attr('readonly', 'readonly').val(hour_minute_formate(maxHour));
            resetHolidayBookingHours();
        }
    });

    function hour_minute_formate(total_min) {
        var hours = Math.floor(total_min / 60);
        if (hours < 10) {
            hours = '0' + hours;
        }
        var minutes = total_min % 60;
        if (minutes < 10) {
            minutes = '0' + minutes;
        }
        return hours + ':' + minutes;
    }

    function resetHolidayBookingHours(){
        var availableBalance = parseInt($('#employeeLeaveForm [name="balance_left"]').val(), 10);
        $('#employeeLeaveForm .leaveDatesHours').each(function(){
            var $input = $(this);
            var minutes = parseInt($input.attr('data-maxhour'), 10);

            $input.val(hour_minute_formate(minutes));          
        });
        var totalBookingHour = calculateTotalBookedHour();
        var currentBalance = availableBalance - totalBookingHour;

        $('#employeeLeaveForm .requestedHours').html(hour_minute_formate(totalBookingHour));
        $('#employeeLeaveForm .balanceLeft').html(hour_minute_formate(currentBalance));
    }

    function calculateTotalBookedHour(){
        var minutes = 0;
        $('#employeeLeaveForm .leaveDatesHours').each(function(){
            var $input = $(this);
            var hourMinutes = $input.val();

            if(hourMinutes != '' && hourMinutes.length == 5){
                minutes += string_to_minute(hourMinutes);
            }            
        });

        return minutes;
    }
    
    function string_to_minute(string){
        var hourMinutes = string.split(':');
        var minute = 0;
        minute += parseInt(hourMinutes[0], 10) * 60;
        minute += parseInt(hourMinutes[1], 10);

        return minute;
    }

    $('#employeeLeaveForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('employeeLeaveForm');

        var $LeaveYear = $form.find('[name="leave_holiday_years"]');
        var $LeavePattern = $form.find('[name="leave_pattern"]');
        var $LeaveType = $form.find('[name="leave_type"]');

        var LeaveYear = $LeaveYear.val();
        var LeavePattern = $LeavePattern.val();
        var LeaveType = $LeaveType.val();

        var emptyLeave = 0;
        $('.leaveDatesHours', $form).each(function(){
            if($(this).val() == '00:00' || $(this).val() == ''){
                emptyLeave += 1;
            }
        })
    
        document.querySelector('#confirmRequest').setAttribute('disabled', 'disabled');
        document.querySelector("#confirmRequest svg.loaderSvg").style.cssText ="display: inline-block;";

        if($('.leaveDatesHours', $form).length > 0 && (LeaveYear != '' && LeavePattern != '' && LeaveType != '') && emptyLeave == 0){
            let form_data = new FormData(form);
            axios({
                method: "POST",
                url: route('employee.holiday.leave.submission'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#confirmRequest').setAttribute('disabled', 'disabled');
                document.querySelector("#confirmRequest svg.loaderSvg").style.cssText ="display: none;";
                
                if (response.status == 200) {
                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('You leave request successfull submitted for review.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    }); 
                }

            }).catch(error => {
                document.querySelector('#confirmRequest').setAttribute('disabled', 'disabled');
                document.querySelector("#confirmRequest svg.loaderSvg").style.cssText ="display: none;";

                if (error.response) {
                    if (error.response.status == 422) {
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html( "Oops!" );
                            $("#warningModal .warningModalDesc").html('Something went wrong. Please try latter or contact with the administrator');
                        });   
                    
                        setTimeout(function(){
                            warningModal.hide();
                        }, 2000)
                        console.log('error');
                    }
                }
            });
        }else{
            document.querySelector('#confirmRequest').setAttribute('disabled', 'disabled');
            document.querySelector("#confirmRequest svg.loaderSvg").style.cssText ="display: none;";

            $form.remove('.errorAlert').prepend('<div class="alert errorAlert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Form validation error found. Please fill out all fields and select some date first. </div>');
        }
    });
    /* Leave Form End */


    /* Leave Request Start */
    $('.newRequestRow').on('click', function(e){
        e.preventDefault();
        var $theTr = $(this);
        var employee_leave_id = $theTr.attr('data-id');

        if(!$theTr.hasClass('disabledRow')){
            axios({
                method: "post",
                url: route('employee.holiday.get.leave'),
                data: {employee_leave_id : employee_leave_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    empNewLeaveRequestModal.show();
                    $('#empNewLeaveRequestModal .modal-body').html(response.data.res);
                    $('#empNewLeaveRequestModal [name="employee_leave_id"]').val(employee_leave_id);
                } 
            }).catch(error => {
                if(error.response){
                    if(error.response.status == 422){
                        console.log('error');
                    }
                }
            });
        }
    })
    $('#empNewLeaveRequestForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('empNewLeaveRequestForm');

        document.querySelector('#updateNLR').setAttribute('disabled', 'disabled');
        document.querySelector('#updateNLR svg').style.cssText = 'display: inline-block;';

        var err = 0;
        $('#empNewLeaveRequestModal .leaveRequestDaysTable tbody tr').each(function(){
            var $tableTr = $(this);
            if($('input[type="radio"]:checked', $tableTr).length == 0){
                err += 1;
            }
        });

        if(err > 0){
            document.querySelector('#updateNLR').removeAttribute('disabled');
            document.querySelector('#updateNLR svg').style.cssText = 'display: none;';

            $('#empNewLeaveRequestForm .validationWarning').remove();
            $('#empNewLeaveRequestForm .modal-content').prepend('<div class="alert validationWarning alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Validation error found! Leave status can nto be un-checked.</div>')
            
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
            
            setTimeout(function(){
                $('#empNewLeaveRequestForm .validationWarning').remove()
            }, 2000);
        }else{
            let form_data = new FormData(form);
            axios({
                method: "POST",
                url: route('employee.holiday.update.leave'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateNLR').removeAttribute('disabled');
                document.querySelector('#updateNLR svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    empNewLeaveRequestModal.hide();
                    
                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Employee leave request successfully updated.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                } 
            }).catch(error => {
                document.querySelector('#updateNLR').removeAttribute('disabled');
                document.querySelector('#updateNLR svg').style.cssText = 'display: none;';
                if(error.response){
                    console.log('error');
                }
            });
        }
    }); 

    
    $('.rejectedDayRow').on('click', function(){
        var $theRow = $(this);
        var leave_day_id = $theRow.attr('data-leavedayid');
        

        if(!$theRow.hasClass('disabledRow')){
            axios({
                method: 'post',
                url: route('employee.holiday.check.day.is.approved'),
                data: {leave_day_id : leave_day_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    if(response.data.res == 1){
                        warningModal.show();
                        document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                            $('#warningModal .warningModalTitle').html('Oops!');
                            $('#warningModal .warningModalDesc').html('Existing approved leave found for this day. You can not approved another leave on same day.');
                        });

                        setTimeout(function(){
                            warningModal.hide();
                        }, 5000);
                    }else{
                        confirmModal.show();
                        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                            $('#confirmModal .confModTitle').html('Are you sure?');
                            $('#confirmModal .confModDesc').html('Do you really want to approve this day\'s leave hour? Then click on agree to continue.');
                            $('#confirmModal .agreeWith').attr('data-id', leave_day_id).attr('data-action', 'APPROVELEAVE');
                        });
                    }
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    });

    $('.approvedDayRow').on('click', function(){
        var $theRow = $(this);
        var leave_day_id = $theRow.attr('data-leavedayid');

        if(!$theRow.hasClass('disabledRow')){
            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html('Are you sure?');
                $('#confirmModal .confModDesc').html('Do you really want to reject this day\'s leave hour? Then click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', leave_day_id).attr('data-action', 'REJECTLEAVE');
            });
        }
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var row_id = $theBtn.attr('data-id');
        var action = $theBtn.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'APPROVELEAVE'){
            axios({
                method: 'post',
                url: route('employee.holiday.approve.leave'),
                data: {row_id : row_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Employee leave day successfully approved.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                $('#confirmModal button').removeAttr('disabled');
                if (error.response.status == 422) {
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html( "Oops!" );
                        $("#warningModal .warningModalDesc").html('Something went wrong. Please try latter or contact with the administrator');
                    });   
                
                    setTimeout(function(){
                        warningModal.hide();
                    }, 2000)
                    console.log('error');
                }
            });
        }else if(action == 'REJECTLEAVE'){
            axios({
                method: 'post',
                url: route('employee.holiday.rject.leave'),
                data: {row_id : row_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Employee leave day successfully rejected.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                $('#confirmModal button').removeAttr('disabled');
                if (error.response.status == 422) {
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html( "Oops!" );
                        $("#warningModal .warningModalDesc").html('Something went wrong. Please try latter or contact with the administrator');
                    });   
                
                    setTimeout(function(){
                        warningModal.hide();
                    }, 2000)
                    console.log('error');
                }
            });
        }
    })
    /* Leave Request End */

    /* Holiday Adjustment Start */
    if($('input[name="adjustment"]').length > 0){
        var maskOptions = {
            mask: '00:00'
        };
        $('input[name="adjustment"]').each(function(){
            var mask = IMask(this, maskOptions);
        })
    }


    $('.holidayAdjustmentBtn').on('click', function(){
        var year = $(this).attr('data-year');
        var pattern = $(this).attr('data-pattern');

        $('#empHolidayAdjustmentModal [name="hr_holiday_year_id"]').val(year);
        $('#empHolidayAdjustmentModal [name="employee_working_pattern_id"]').val(pattern);
    })

    $('#empHolidayAdjustmentModal [name="adjustmentOpt"]').on('change', function(){
        if($('#empHolidayAdjustmentModal [name="adjustmentOpt"]:checked').length > 0){
            $('#empHolidayAdjustmentModal [name="adjustment"]').val('').removeAttr('disabled');
        }else{
            $('#empHolidayAdjustmentModal [name="adjustment"]').val('').attr('disabled', 'disabled');
        }
    });

    $('#empHolidayAdjustmentForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('empHolidayAdjustmentForm');

        $('#empHolidayAdjustmentForm').find('input').removeClass('border-danger')
        $('#empHolidayAdjustmentForm').find('.acc__input-error').html('')

        document.querySelector('#updateADJ').setAttribute('disabled', 'disabled');
        document.querySelector('#updateADJ svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route('employee.holiday.update.adjustment'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateADJ').removeAttribute('disabled');
            document.querySelector('#updateADJ svg').style.cssText = 'display: none;';
            
            if (response.status == 200) {
                empHolidayAdjustmentModal.hide();
                
                successModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Congratulations!');
                    $('#successModal .successModalDesc').html('Holiday adjustment successfully updated.');
                    $('#successModal .successCloser').attr('data-action', 'RELOAD');
                });

                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            } 
        }).catch(error => {
            document.querySelector('#updateADJ').removeAttribute('disabled');
            document.querySelector('#updateADJ svg').style.cssText = 'display: none;';
            if(error.response){
                if(error.response.status == 422){
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#empHolidayAdjustmentForm .${key}`).addClass('border-danger')
                        $(`#empHolidayAdjustmentForm  .error-${key}`).html(val)
                    }
                }else{
                    console.log('error');
                }
            }
        });
    });
    /* Holiday Adjustment End */


    $('.bankHolidayTable thead').on('click', function(){
        $('.bankHolidayTable tbody').fadeToggle();
    })

})();