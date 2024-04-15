import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

(function(){
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const viewBreakModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewBreakModal"));

    const viewBreakModalEl = document.getElementById('viewBreakModal')
    viewBreakModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#viewBreakModal .modal-body').html('');
        $('#viewBreakModal input[name="id"]').val('0');
    });

    $(document).on('click', '.successCloser', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    if($('.dailyAttendanceTable input.time').length > 0){
        var maskOptions = {
            mask: '00:00'
        };
        $('.dailyAttendanceTable input.time').each(function(){
            var mask = IMask(this, maskOptions);
        })
    }

    $('.dailyAttendanceTable .attendanceRow .editRowNote').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var dataID = $theBtn.attr('data-id');
        var $parentTr = $theBtn.closest('tr.attendanceRow');
        var $theTable = $parentTr.closest('.dailyAttendanceTable');
        var theTableID = $theTable.attr('id');
        var $noteTr = $('#'+theTableID+' tr#attendanceNoteRow_'+dataID);

        $noteTr.fadeToggle();
    });

    $('.dailyAttendanceTable .onlyLeaveRow .editRowNote').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var dataID = $theBtn.attr('data-id');
        var $parentTr = $theBtn.closest('tr.onlyLeaveRow');
        var $theTable = $parentTr.closest('.dailyAttendanceTable');
        var theTableID = $theTable.attr('id');
        var $noteTr = $('#'+theTableID+' tr#attendanceNoteRow_'+dataID);

        $noteTr.fadeToggle();
    });

    $('.dailyAttendanceTable .clockin_system, .dailyAttendanceTable .clockout_system, .dailyAttendanceTable .adjustment').on('keyup', function(){
        var $changedInput = $(this);
        var $parentTr = $changedInput.closest('tr.attendanceRow');
        var $theTable = $parentTr.closest('.dailyAttendanceTable');
        var theTableID = $theTable.attr('id');
        var rowID = $parentTr.attr('data-id');
        var $theTr = $('#'+theTableID+' #attendanceRow_'+rowID);

        var $clockIn = $theTr.find('.clockin_system');
        var clockIn = $clockIn.val();

        var $clockOut = $theTr.find('.clockout_system');
        var clockOut = $clockOut.val();

        var $adjustment = $theTr.find('.adjustment');
        var adjustment = $adjustment.val();

        if(clockIn.length == 5 && clockOut.length == 5 && adjustment.length == 6){
            var $paid_break = $theTr.find('.paid_break');
            var paid_break = stringToMinute($paid_break.val());

            var $unpadi_break = $theTr.find('.unpadi_break');
            var unpadi_break = stringToMinute($unpadi_break.val());

            var $total_break = $theTr.find('.total_break');
            var total_break = parseInt($total_break.val(), 10);

            var $allowed_br = $theTr.find('.allowed_br');
            var allowed_br = parseInt($allowed_br.val(), 10);

            var $total_work_hour = $theTr.find('.total_work_hour');
            var total_work_hour = parseInt($total_work_hour.attr('data-prev'), 10);


            var total_adjustment = 0;
            var adjustmentOperator = 0;
            if (adjustment.indexOf('-') != -1) {
                adjustment = adjustment.replace('-', '');
                total_adjustment = stringToMinute(adjustment);
                adjustmentOperator = 1;
            } else if (adjustment.indexOf('+') != -1) {
                adjustment = adjustment.replace('+', '');
                total_adjustment = stringToMinute(adjustment);
                adjustmentOperator = 2;
            }

            var formatedClockIn = clockIn+':00';
            var formatedClockOut = clockOut+':00';
            var totalMinutes = getToTimeDiffMin(formatedClockIn, formatedClockOut);

            if (total_break > allowed_br) {
                var deduct = (total_break - allowed_br);
                var new_total = (totalMinutes - unpadi_break) - deduct;
                
                if(total_adjustment > 0 && adjustmentOperator == 1){
                    new_total = (new_total - total_adjustment);
                }else if(total_adjustment > 0 && adjustmentOperator == 2){
                    new_total = (new_total + total_adjustment);
                }
                
                if(new_total >= 0){
                    $total_work_hour.val(new_total).attr('data-prev', new_total);
                    $theTr.find('.total_work_hour_text').text(convertMinuteToHourMinute(new_total));
                    $theTr.find('.saveRow').removeAttr('disabled').removeClass('btn-danger').addClass('btn-success');
                    $theTr.find('.employee_attendance_id').removeAttr('disabled');
                }else{
                    var n_total = new_total * -1;
                    $total_work_hour.val('-'+n_total).attr('data-prev', '-'+n_total);
                    $theTr.find('.total_work_hour_text').text('-'+convertMinuteToHourMinute(n_total));
                    $theTr.find('.saveRow').attr('disabled', 'disabled').removeClass('btn-success').addClass('btn-danger');
                    $theTr.find('.employee_attendance_id').attr('disabled', 'disabled');
                }
            }else{
                var new_total = totalMinutes - unpadi_break;
                
                if(total_adjustment > 0 && adjustmentOperator == 1){
                    new_total = (new_total - total_adjustment);
                }else if(total_adjustment > 0 && adjustmentOperator == 2){
                    new_total = (new_total + total_adjustment);
                }
                console.log(new_total);
                if(new_total >= 0){
                    $total_work_hour.val(new_total).attr('data-prev', new_total);
                    $theTr.find('.total_work_hour_text').text(convertMinuteToHourMinute(new_total));
                    $theTr.find('.saveRow').removeAttr('disabled').removeClass('btn-danger').addClass('btn-success');
                    $theTr.find('.employee_attendance_id').removeAttr('disabled');
                }else{
                    var n_total = new_total * -1;
                    $total_work_hour.val('-'+n_total).attr('data-prev', '-'+n_total);
                    $theTr.find('.total_work_hour_text').text('-'+convertMinuteToHourMinute(n_total));
                    $theTr.find('.saveRow').attr('disabled', 'disabled').removeClass('btn-success').addClass('btn-danger');
                    $theTr.find('.employee_attendance_id').attr('disabled', 'disabled');
                }
            }
        }
    });

    $('.dailyAttendanceTable .attendanceLeaveRow .leave_adjustment').on('keyup', function(){
        var $changedInput = $(this);
        var $parentTr = $changedInput.closest('tr.attendanceLeaveRow');
        var $theTable = $parentTr.closest('.dailyAttendanceTable');
        var theTableID = $theTable.attr('id');
        var rowID = $changedInput.attr('data-id');
        var $theTr = $('#'+theTableID+' #attendanceLeaveRow_'+rowID);
        
        var $leave_adjustment = $theTr.find('.leave_adjustment');
        var leave_adjustment = $leave_adjustment.val();

        if(leave_adjustment.length == 6){
            var $leave_hour = $theTr.find('.leave_hour');
            var leave_hour = parseInt($leave_hour.attr('data-prev'), 10);

            var total_leave_adjustment = 0;
            var leaveAdjustmentOperator = 0;
            if (leave_adjustment.indexOf('-') != -1) {
                leave_adjustment = leave_adjustment.replace('-', '');
                total_leave_adjustment = stringToMinute(leave_adjustment);
                leaveAdjustmentOperator = 1;
            } else if (leave_adjustment.indexOf('+') != -1) {
                leave_adjustment = leave_adjustment.replace('+', '');
                total_leave_adjustment = stringToMinute(leave_adjustment);
                leaveAdjustmentOperator = 2;
            }

            var new_leave_hour = leave_hour;
            if(total_leave_adjustment > 0 && leaveAdjustmentOperator == 1){
                new_leave_hour = (leave_hour - total_leave_adjustment);
            }else if(total_leave_adjustment > 0 && leaveAdjustmentOperator == 2){
                new_leave_hour = (leave_hour + total_leave_adjustment);
            }

            $leave_hour.val(new_leave_hour).attr('data-prev', new_leave_hour);
            $theTr.find('.leave_hour_text').text(convertMinuteToHourMinute(new_leave_hour));
        }
    });

    $('.dailyAttendanceTable .onlyLeaveRow .leave_adjustment').on('keyup', function(){
        var $changedInput = $(this);
        var $parentTr = $changedInput.closest('tr.onlyLeaveRow');
        var $theTable = $parentTr.closest('.dailyAttendanceTable');
        var theTableID = $theTable.attr('id');
        var rowID = $changedInput.attr('data-id');
        var $theTr = $('#'+theTableID+' #onlyLeaveRow_'+rowID);
        
        var $leave_adjustment = $theTr.find('.leave_adjustment');
        var leave_adjustment = $leave_adjustment.val();

        if(leave_adjustment.length == 6){
            var $leave_hour = $theTr.find('.leave_hour');
            var leave_hour = parseInt($leave_hour.attr('data-prev'), 10);

            var total_leave_adjustment = 0;
            var leaveAdjustmentOperator = 0;
            if (leave_adjustment.indexOf('-') != -1) {
                leave_adjustment = leave_adjustment.replace('-', '');
                total_leave_adjustment = stringToMinute(leave_adjustment);
                leaveAdjustmentOperator = 1;
            } else if (leave_adjustment.indexOf('+') != -1) {
                leave_adjustment = leave_adjustment.replace('+', '');
                total_leave_adjustment = stringToMinute(leave_adjustment);
                leaveAdjustmentOperator = 2;
            }

            var new_leave_hour = leave_hour;
            if(total_leave_adjustment > 0 && leaveAdjustmentOperator == 1){
                new_leave_hour = (leave_hour - total_leave_adjustment);
            }else if(total_leave_adjustment > 0 && leaveAdjustmentOperator == 2){
                new_leave_hour = (leave_hour + total_leave_adjustment);
            }

            $leave_hour.val(new_leave_hour).attr('data-prev', new_leave_hour);
            $theTr.find('.leave_hour_text').text(convertMinuteToHourMinute(new_leave_hour));
        }
    });


    $('.dailyAttendanceTable .absent_adjustment').on('keyup', function(){
        var $changedInput = $(this);
        var $parentTr = $changedInput.closest('tr.attendanceAbsentRow');
        var $theTable = $parentTr.closest('.dailyAttendanceTable');
        var theTableID = $theTable.attr('id');
        var rowID = $changedInput.attr('data-id');
        var $theTr = $('#'+theTableID+' #attendanceRow_'+rowID);
        
        var $absent_adjustment = $theTr.find('.absent_adjustment');
        var absent_adjustment = $absent_adjustment.val();

        if(absent_adjustment.length == 6){
            var $absent_hour = $theTr.find('.absent_hour');
            var absent_hour = parseInt($absent_hour.attr('data-prev'), 10);

            var total_absent_adjustment = 0;
            var leaveAdjustmentOperator = 0;
            if (absent_adjustment.indexOf('-') != -1) {
                absent_adjustment = absent_adjustment.replace('-', '');
                total_absent_adjustment = stringToMinute(absent_adjustment);
                leaveAdjustmentOperator = 1;
            } else if (absent_adjustment.indexOf('+') != -1) {
                absent_adjustment = absent_adjustment.replace('+', '');
                total_absent_adjustment = stringToMinute(absent_adjustment);
                leaveAdjustmentOperator = 2;
            }
            
            var new_absent_hour = absent_hour;
            if(total_absent_adjustment > 0 && leaveAdjustmentOperator == 1){
                new_absent_hour = (absent_hour - total_absent_adjustment);
            }else if(total_absent_adjustment > 0 && leaveAdjustmentOperator == 2){
                new_absent_hour = (absent_hour + total_absent_adjustment);
            }

            $absent_hour.val(new_absent_hour).attr('data-prev', new_absent_hour);
            $theTr.find('.absent_hour_text').text(convertMinuteToHourMinute(new_absent_hour));
        }
    })

    function stringToMinute(hourMinutes){
        if(hourMinutes == '' && hourMinutes.length != 5){
            return 0;
        }

        var hourMinutes = hourMinutes.split(':');
        var minutes = parseInt(hourMinutes[0], 10) * 60;
        minutes += parseInt(hourMinutes[1], 10);
        
        return minutes;
    }

    function getToTimeDiffMin(startTime, endTime){
        var timeStart = new Date();
        var timeEnd = new Date();
        var startTime = startTime.split(':');
        var endTime = endTime.split(':');

        timeStart.setHours(startTime[0], startTime[1], startTime[2], 0);
        timeEnd.setHours(endTime[0], endTime[1], endTime[2], 0);

        var theMin = ((timeEnd - timeStart) / 1000) / 60;

        return theMin;
    }

    function convertMinuteToHourMinute(minutes) {
        var hours = Math.floor(minutes / 60);
        if (hours < 10) {
            hours = '0' + hours;
        }
        var minutes = minutes % 60;
        if (minutes < 10) {
            minutes = '0' + minutes;
        }
        return hours + ':' + minutes;
    }

    $('.checkAll').on('change', function(){
        var $theCheck = $(this);
        var $theTable = $theCheck.closest('.dailyAttendanceTable');
        var theID = $theTable.attr('id');
        if($theCheck.prop('checked')){
            $theTable.find('.employee_attendance_id').prop('checked', true);
            $('.saveAllRow[data-table="#'+theID+'"]').fadeIn();
        }else{
            $theTable.find('.employee_attendance_id').prop('checked', false);
            $('.saveAllRow[data-table="#'+theID+'"]').fadeOut();
        }
    });

    $('.employee_attendance_id').on('change', function(){
        var $theTable = $(this).closest('.dailyAttendanceTable');
        var theID = $theTable.attr('id');

        var allCheckBox = $theTable.find('tbody .employee_attendance_id').length;
        var allChecked = $theTable.find('tbody .employee_attendance_id:checked').length;
        var allUnChecked = allCheckBox - allChecked;

        if(allChecked > 0){
            $('.saveAllRow[data-table="#'+theID+'"]').fadeIn();
        }else{
            $('.saveAllRow[data-table="#'+theID+'"]').fadeOut();
        }

        if(allUnChecked > 0){
            $theTable.find('.checkAll').prop('checked', false);
        }else{
            $theTable.find('.checkAll').prop('checked', true);
        }
    });

    $('.saveAllAttendance').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var tableID = $theBtn.attr('data-table');
        var $theTable = $(tableID);
        var $theBody = $theTable.find('tbody');

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg.theLoader').fadeIn();
        $theTable.find('button.saveRow').attr('disabled', 'disabled');

        var allChecked = $theTable.find('tbody .employee_attendance_id:checked').length;
        if(allChecked > 0){
            let allData = $theBody.find('textarea, input').serialize();
            axios({
                method: "post",
                url: route('hr.attendance.update.all'),
                data: {allData : allData},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.theLoader').fadeOut();
                $theTable.find('button.saveRow').removeAttr('disabled');

                if (response.status == 200) {
                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('All selected attendance rows are successfully updated.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });
                }
            }).catch(error => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.theLoader').fadeOut();
                $theTable.find('button.saveRow').removeAttr('disabled');

                if(error.response){
                    if(error.response.status == 422){
                        warningModal.show();
                        document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                            $('#warningModal .warningModalTitle').html('Error Found!');
                            $('#warningModal .warningModalDesc').html('Something went wrong. Please try later or contact with the administrator.');
                        });

                        setTimeout(function(){
                            warningModal.hide();
                        }, 2000);
                        console.log('error');
                    }
                }
            });
        }else{
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut();
            $theTable.find('button.saveRow').removeAttr('disabled');

            warningModal.show();
            document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                $('#warningModal .warningModalTitle').html('Error Found!');
                $('#warningModal .warningModalDesc').html('Please check at least one row for submission.');
            });

            setTimeout(function(){
                warningModal.hide();
            }, 2000);
        }
    })

    $('.dailyAttendanceTable').on('click', '.saveRow', function(e){
        e.preventDefault;
        var $theBtn = $(this);
        var rowID = $theBtn.attr('data-id');
        var $parentTr = $theBtn.closest('tr.attendanceSyncRow');
        var $theTable = $parentTr.closest('.dailyAttendanceTable');
        var theTableID = $theTable.attr('id');

        var $theTr = ($parentTr.hasClass('onlyLeaveRow') ? $('#'+theTableID+' #onlyLeaveRow_'+rowID) : $('#'+theTableID+' #attendanceRow_'+rowID));
        var $theNoteTr = $('#'+theTableID+' #attendanceNoteRow_'+rowID);

        $theTable.find('button.saveRow').attr('disabled', 'disabled');

        var $clockIn = $theTr.find('.clockin_system');
        var clockIn = $clockIn.val();

        var $clockOut = $theTr.find('.clockout_system');
        var clockOut = $clockOut.val();

        var $adjustment = $theTr.find('.adjustment');
        var adjustment = $adjustment.val();

        var absentAdjustment = '';
        if($theTr.find('.absent_adjustment').length > 0){
            var $absentAdjustment = $theTr.find('.absent_adjustment');
            absentAdjustment = $absentAdjustment.val();
        }

        var leaveAdjustment = '';
        if($theTr.find('.absent_adjustment').length > 0){
            var $leaveAdjustment = $theTr.find('.leave_adjustment');
            leaveAdjustment = $leaveAdjustment.val();
        }
        
        if((clockIn.length == 5 && clockOut.length == 5 && adjustment.length == 6) || ($theTr.hasClass('attendanceAbsentRow') && absentAdjustment.length == 6) || $theTr.hasClass('onlyLeaveRow')){
            let rowData = $theTr.find('input').serialize();
            let rowNote = $theNoteTr.find('textarea').val();
            let leaveData = '';
            let isLeaveRow = ($theTr.hasClass('onlyLeaveRow') ? true : false);
            if($('#'+theTableID+' #attendanceLeaveRow_'+rowID).length > 0){
                var $theLeaveTr = $('#'+theTableID+' #attendanceLeaveRow_'+rowID);
                leaveData = $theLeaveTr.find('input').serialize();
            }
            axios({
                method: "post",
                url: route('hr.attendance.update'),
                data: {rowData : rowData, rowNote : rowNote, leaveData : leaveData, isLeaveRow : isLeaveRow},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theTable.find('button.saveRow').removeAttr('disabled');
                $theTr.find('.form-control').removeClass('border-danger');
                $theTr.find('.text-danger').removeClass('text-danger');

                if (response.status == 200) {
                    console.log(response.data);
                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('The attendance row has been successfully updated.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });
                }
            }).catch(error => {
                $theTable.find('button.saveRow').removeAttr('disabled');
                if(error.response){
                    if(error.response.status == 422){
                        warningModal.show();
                        document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                            $('#warningModal .warningModalTitle').html('Error Found!');
                            $('#warningModal .warningModalDesc').html('Something went wrong. Please try later or contact with the administrator.');
                        });

                        setTimeout(function(){
                            warningModal.hide();
                        }, 2000);
                        console.log('error');
                    }
                }
            });
        }else{
            $theTable.find('button.saveRow').removeAttr('disabled');

            warningModal.show();
            document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                $('#warningModal .warningModalTitle').html('Error Found!');
                $('#warningModal .warningModalDesc').html('ClockIn System, ClockOut System, and Adjustment field can not be empty! Please fill out all required field and submit it again.');
            });

            setTimeout(function(){
                warningModal.hide();
            }, 5000);
        }
    });

    $('.view_break').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var rowID = $theLink.attr('data-id');
        var hasError = $theLink.attr('data-haserror');

        axios({
            method: "post",
            url: route('hr.attendance.edit'),
            data: {rowID : rowID},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                let res = response.data.res;
                $('#viewBreakModal .modal-body').html(res);
                $('#viewBreakModal input[name="id"]').val(rowID);

                createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});

                if($("#viewBreakModal .modal-body .timepicker").length > 0){
                    var maskOptions = {
                        mask: '00:00'
                    };
                    $('#viewBreakModal input.timepicker').each(function(){
                        var mask = IMask(this, {
                            overwrite: true,
                            autofix: true,
                            mask: 'HH:MM',
                            blocks: {
                                HH: {
                                    mask: IMask.MaskedRange,
                                    placeholderChar: 'HH',
                                    from: 0,
                                    to: 23,
                                    maxLength: 2
                                },
                                MM: {
                                    mask: IMask.MaskedRange,
                                    placeholderChar: 'MM',
                                    from: 0,
                                    to: 59,
                                    maxLength: 2
                                }
                            }
                        });
                    })
                }
            }
        }).catch(error => {
            if(error.response){
                console.log('error');
            }
        });
    });

    $('#viewBreakModal').on('keyup change paste', '.breakStart, .breakEnd', function(){
        var startTime = $(this).closest('tr.breakRow').find('.breakStart').val();
        var endTime = $(this).closest('tr.breakRow').find('.breakEnd').val();

        if (startTime.length == 5 && endTime.length == 5) {
            var breakHourInSecond = (new Date(get_current_date() + " " + endTime + ':00').getTime() - new Date(get_current_date() + " " + startTime + ':00').getTime()) / 1000;
            
            var totalBreakHour = breakHourInSecond / 60;
            totalBreakHour = hour_minute_formate(totalBreakHour);
            $(this).closest('tr.breakRow').find('.breakRowTotal').val(totalBreakHour);
        }else{
            $(this).closest('tr.breakRow').find('.breakRowTotal').val('00:00');
        }

        calculateDayBreakTotal('#viewBreakModal')
    });

    function calculateDayBreakTotal(modalId){
        var dayTotal = 0;
        if($(modalId+' .breakRowTotal').length > 0){
            $(modalId+' .breakRowTotal').each(function (e) {
                var rowTotal = $(this).val();
                if (rowTotal != '') {
                    var time = rowTotal.split(':');
                    var timeInMin = (parseInt(time[0], 10) * 60) + parseInt(time[1], 10);
                    dayTotal += timeInMin;
                }
            });
            $(modalId+' tfoot .breakGrandTotal').val(hour_minute_formate(dayTotal));
        }else{
            $(modalId+' tfoot .breakGrandTotal').val('00:00');
        }
    }

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

    function get_current_date() {
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1; //January is 0!
        var yyyy = today.getFullYear();

        if (dd < 10) {
            dd = '0' + dd
        }

        if (mm < 10) {
            mm = '0' + mm
        }

        return today = mm + '/' + dd + '/' + yyyy;
    }

    $('#viewBreakForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('viewBreakForm');

        document.querySelector('#updateBreak').setAttribute('disabled', 'disabled');
        document.querySelector('#updateBreak svg').style.cssText = 'display: inline-block;';

        var errors = 0;
        $('#viewBreakModal').find('.breakRow').each(function(){
            var $breakStart = $(this).find('.breakStart');
            var $breakEnd = $(this).find('.breakEnd');
            var $breakRowTotal = $(this).find('.breakRowTotal');

            if(($breakStart.val() == '' || $breakStart.val() == '00:00') || ($breakEnd.val() == '' || $breakEnd.val() == '00:00') || $breakRowTotal.val() == ''){
                errors += 1;
            }
        });
        if($('#viewBreakModal .breakGrandTotal').val() == ''){
            errors += 1;
        }
        
        if(errors > 0){
            $form.find('.modError').remove();
            $('.modal-content', $form).prepend('<div class="modError alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Invalid time found. Please valid and formated time.</div>');
            createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            
            setTimeout(function(){
                $form.find('.modError').remove();
            }, 2000);
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('hr.attendance.update.break'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateBreak').removeAttribute('disabled');
                document.querySelector('#updateBreak svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    viewBreakModal.hide();
                    
                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Attendance break successfully updated.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateBreak').removeAttribute('disabled');
                document.querySelector('#updateBreak svg').style.cssText = 'display: none;';
                if(error.response){
                    console.log('error');
                }
            });
        }
    });
})()