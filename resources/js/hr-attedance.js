import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

import Litepicker from "litepicker";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const queryDateInput = document.getElementById('queryDate');
    const monthNames = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
    ];

    const parseMonthValue = function(value) {
        let cleanValue = (value || '').toString().trim();
        let numericMatch = cleanValue.match(/^(\d{1,2})-(\d{4})$/);

        if (numericMatch) {
            let month = Math.min(Math.max(parseInt(numericMatch[1], 10), 1), 12);

            return {
                month: month - 1,
                year: parseInt(numericMatch[2], 10),
            };
        }

        let longMonthMatch = cleanValue.match(/^([A-Za-z]+)\s+(\d{4})$/);
        if (longMonthMatch) {
            let month = monthNames.findIndex(function(monthName) {
                return monthName.toLowerCase() === longMonthMatch[1].toLowerCase();
            });

            if (month >= 0) {
                return {
                    month: month,
                    year: parseInt(longMonthMatch[2], 10),
                };
            }
        }

        let fallbackDate = new Date();

        return {
            month: fallbackDate.getMonth(),
            year: fallbackDate.getFullYear(),
        };
    };

    const formatDisplayMonth = function(dateParts) {
        return monthNames[dateParts.month] + ' ' + dateParts.year;
    };

    const formatPayloadMonth = function(dateParts) {
        return String(dateParts.month + 1).padStart(2, '0') + '-' + dateParts.year;
    };

    const getSelectedMonthParts = function(picker) {
        let selectedDate = picker.getDate();

        if (selectedDate) {
            return {
                month: selectedDate.getMonth(),
                year: selectedDate.getFullYear(),
            };
        }

        return parseMonthValue(queryDateInput.value);
    };

    const getQueryMonthParam = function() {
        let dateParts = parseMonthValue(queryDateInput.value);

        queryDateInput.dataset.org = formatPayloadMonth(dateParts);

        return queryDateInput.dataset.org;
    };

    const renderQueryMonthCalendar = function(picker) {
        if (!picker.ui) {
            return;
        }

        picker.ui.classList.add('attendance-month-litepicker');

        let monthItem = picker.ui.querySelector('.month-item');
        if (!monthItem) {
            return;
        }

        let weekdaysRow = monthItem.querySelector('.month-item-weekdays-row');
        let daysGrid = monthItem.querySelector('.container__days');

        if (weekdaysRow) {
            weekdaysRow.style.display = 'none';
        }
        if (daysGrid) {
            daysGrid.style.display = 'none';
        }

        let calendarDate = picker.calendars && picker.calendars[0]
            ? picker.calendars[0]
            : null;
        let activeYear = calendarDate ? calendarDate.getFullYear() : getSelectedMonthParts(picker).year;
        let selectedMonth = getSelectedMonthParts(picker);
        let monthGrid = monthItem.querySelector('.attendance-month-grid');

        if (!monthGrid) {
            monthGrid = document.createElement('div');
            monthGrid.className = 'attendance-month-grid';
            monthItem.appendChild(monthGrid);
        }

        monthGrid.innerHTML = '';

        monthNames.forEach(function(monthName, monthIndex) {
            let monthButton = document.createElement('button');
            monthButton.type = 'button';
            monthButton.className = 'attendance-month-option';
            monthButton.textContent = monthName;

            if (selectedMonth.month === monthIndex && selectedMonth.year === activeYear) {
                monthButton.classList.add('is-active');
            }

            monthButton.addEventListener('click', function() {
                let date = new Date(activeYear, monthIndex, 1);
                picker.setDate(date);
                queryDateInput.value = formatDisplayMonth({
                    month: monthIndex,
                    year: activeYear,
                });
                queryDateInput.dataset.org = formatPayloadMonth({
                    month: monthIndex,
                    year: activeYear,
                });
                picker.hide();
            });

            monthGrid.appendChild(monthButton);
        });

        let yearField = picker.ui.querySelector('.month-item-year');
        if (yearField && !yearField.dataset.attendanceMonthBound) {
            yearField.dataset.attendanceMonthBound = 'true';
            yearField.addEventListener('change', function() {
                let year = parseInt(yearField.value, 10);
                let currentMonth = picker.calendars && picker.calendars[0]
                    ? picker.calendars[0].getMonth()
                    : getSelectedMonthParts(picker).month;

                if (!Number.isNaN(year)) {
                    picker.gotoDate(new Date(year, currentMonth, 1));
                    renderQueryMonthCalendar(picker);
                }
            });
        }
    };

    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#confirmModal .confModDesc').html('');
        $('#confirmModal .agreeWith').attr('data-date', '').attr('data-action', 'none');
    });


    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        format: "MMMM YYYY",
        startDate: queryDateInput.dataset.date,
        switchingMonths: 12,
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: false,
            years: true,
        },
        setup: function(picker) {
            picker.on('show', function() {
                renderQueryMonthCalendar(picker);
            });
            picker.on('change:month', function() {
                renderQueryMonthCalendar(picker);
            });
        },
    };

    const queryDate = new Litepicker({
        element: queryDateInput,
        ...dateOption
    });

    $('#generateReport').on('click', function(e){
        e.preventDefault();
        var theDate = getQueryMonthParam();
        window.location.href = route('hr.portal.reports.attendance', theDate);
    })

    $('#filterMonthAtten').on('click', function() {
        getQueryMonthParam();
    });

    $('#filterMonthAttenForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('filterMonthAttenForm');

        document.querySelector('#filterMonthAtten').setAttribute('disabled', 'disabled');
        document.querySelector('#filterMonthAtten svg').style.cssText = 'display: inline-block;';
        document.querySelector('.leaveTableLoader').classList.add('active');

        let form_data = new FormData(form);
        form_data.set('queryDate', getQueryMonthParam());
        axios({
            method: "POST",
            url: route('hr.attendance.sync.listhtml'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#filterMonthAtten').removeAttribute('disabled');
            document.querySelector('#filterMonthAtten svg').style.cssText = 'display: none;';
            document.querySelector('.leaveTableLoader').classList.remove('active');
            
            if (response.status == 200) {
                var res = response.data.res;
                $('#attendanceSyncListTable table tbody').html(res);
                createIcons({icons, "stroke-width": 1.5, nameAttr: "data-lucide"}); 
            } 
        }).catch(error => {
            document.querySelector('#filterMonthAtten').removeAttribute('disabled');
            document.querySelector('#filterMonthAtten svg').style.cssText = 'display: none;';
            document.querySelector('.leaveTableLoader').classList.remove('active');
            if(error.response){
                console.log('error');
            }
        });
    })


    $('#attendanceSyncListTable').on('click', '.syncroniseAttendance', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var theDate = $theBtn.attr('data-date');
        var $allBtn = $('#attendanceSyncListTable').find('.syncroniseAttendance');

        $allBtn.attr('disabled', 'disabled');
        $theBtn.find('svg').fadeIn();

        axios({
            method: "post",
            url: route('hr.attendance.sync'),
            data: {theDate : theDate},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $theBtn.find('svg').fadeOut();
            $allBtn.attr('disabled', 'disabled');
            
            if (response.status == 200) {
                //console.log(response.data);
                
                var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check-circle" class="lucide lucide-check-circle w-4 h-4 mr-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                $theBtn.removeClass('btn-success').addClass('btn-primary').html(svg+' Synchronised');
                window.location.href = response.data.url;
            }
        }).catch(error => {
            $allBtn.removeAttr('disabled');
            $theBtn.find('svg').fadeOut();
            if (error.response) {
                if (error.response.status == 422) {
                    console.log('error');
                }
            }
        });

    });

    // Delete All Sync Data
    $('#attendanceSyncListTable').on('click', '.deleteAllSyncd', function(){
        let $theBtn = $(this);
        let theDate = $theBtn.attr('data-date');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete all attendance for the day?  If yes, the please click on agree btn.');
            $('#confirmModal .agreeWith').attr('data-date', theDate);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETESYNCD');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();

        let $agreeBTN = $(this);
        let theDate = $agreeBTN.attr('data-date');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETESYNCD'){
            axios({
                method: 'delete',
                url: route('hr.attendance.destroy.all'),
                data: {theDate : theDate},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    let suc = response.data.suc;
                    let msg = response.data.msg;

                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    if(suc == 2){
                        warningModal.show();
                        document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                            $('#warningModal .warningModalTitle').html('Oops!');
                            $('#warningModal .warningModalDesc').html(msg);
                        });

                        setTimeout(function(){
                            warningModal.hide();
                            $('#filterMonthAttenForm').trigger('submit');
                        }, 500)
                    }else{
                        successModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html(msg);
                        });

                        setTimeout(function(){
                            successModal.hide();
                            $('#filterMonthAttenForm').trigger('submit');
                        }, 500)
                    }
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    });
})();
