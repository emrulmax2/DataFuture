import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

(function(){
    let tomOptions = {
        plugins: {
            remove_button: {
                title: "Remove this item",
            },
        },
        placeholder: 'Filter by name...',
        dropdownParent: '.hr-leave-employee-select',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    var employee_filter = new TomSelect('#employee', tomOptions);
    createIcons({ icons });
    var $calendarWrap = $('.leaveCalendarWrap');
    var calendarState = {
        date: $calendarWrap.attr('data-calendar-date') || $('#leave-calendar-next').attr('data-date') || '',
        offset: parseInt($calendarWrap.attr('data-calendar-next-offset'), 10) || 30,
        hasMore: $calendarWrap.attr('data-calendar-has-more') === '1',
        loadingMore: false,
    };

    $('.hr-leave-employee-select').on('click', function(e) {
        if ($(e.target).closest('.ts-dropdown, .remove').length) {
            return;
        }

        employee_filter.focus();
        employee_filter.open();
    });

    function renderCalendarMeta(meta) {
        if (!meta) {
            return;
        }

        $('#leaveCalendarMonthLabel').text(meta.monthLabel || '');
        $('#leaveCalendarVisibleCount').text(meta.visibleCount ?? 0);
        $('#leaveCalendarOnLeaveToday').text(meta.onLeaveToday ?? 0);
        calendarState.offset = parseInt(meta.nextOffset, 10) || 0;
        calendarState.hasMore = Boolean(meta.hasMore);
    }

    function syncCalendarDateFilters(date) {
        if (!date) {
            return;
        }

        var parsedDate = new Date(date + 'T00:00:00');
        if (Number.isNaN(parsedDate.getTime())) {
            return;
        }

        $('#month').val(parsedDate.getMonth() + 1);
        $('#year').val(parsedDate.getFullYear());
    }

    function getLeaveCalendarFilters() {
        var $form = $('#leaveCalendarFilterForm');

        return {
            department: $('#department', $form).val(),
            employee: $('#employee', $form).val(),
            month: $('#month', $form).val(),
            year: $('#year', $form).val(),
        };
    }

    function setCalendarBusy(isBusy) {
        var $form = $('#leaveCalendarFilterForm');
        var $theLoader = $('.leaveTableLoader');

        if (isBusy) {
            $theLoader.addClass('active');
            $form.find('select').attr('readonly', 'readonly');
            $form.find('button').attr('disabled', 'disabled');
            return;
        }

        $theLoader.removeClass('active');
        $form.find('select').removeAttr('readonly');
        $form.find('button').removeAttr('disabled');
    }

    function setMoreLoader(isActive) {
        $('[data-leave-more-loader]').toggleClass('is-active', Boolean(isActive));
    }

    function resetCalendarScroll() {
        $('.leaveCalendarWrap').scrollTop(0);
    }

    function loadMoreCalendarRows() {
        if (calendarState.loadingMore || !calendarState.hasMore) {
            return;
        }

        var filters = getLeaveCalendarFilters();
        var $theWrap = $('.leaveCalendarWrap');
        var $tableBody = $theWrap.find('table.leaveCalendarTable tbody');

        calendarState.loadingMore = true;
        setMoreLoader(true);

        axios({
            method: "post",
            url: route('hr.portal.load.leave.calendar.rows'),
            data: {
                department: filters.department,
                employee: filters.employee,
                thedate: calendarState.date,
                offset: calendarState.offset,
            },
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                if (response.data.rows) {
                    $tableBody.append(response.data.rows);
                }

                if (response.data.date) {
                    calendarState.date = response.data.date;
                }

                renderCalendarMeta(response.data.meta);
            }
        }).catch(error => {
            if(error.response && error.response.status == 422){
                console.log('error');
            }
        }).finally(() => {
            calendarState.loadingMore = false;
            setMoreLoader(false);
        });
    }

    $('.leaveCalendarWrap').on('scroll', function() {
        if (calendarState.loadingMore || !calendarState.hasMore) {
            return;
        }

        if (this.scrollTop + this.clientHeight >= this.scrollHeight - 90) {
            loadMoreCalendarRows();
        }
    });

    $(window).on('scroll', function() {
        if (calendarState.loadingMore || !calendarState.hasMore) {
            return;
        }

        var calendarWrap = $('.leaveCalendarWrap').get(0);
        if (!calendarWrap) {
            return;
        }

        var rect = calendarWrap.getBoundingClientRect();
        if (rect.bottom <= window.innerHeight + 120) {
            loadMoreCalendarRows();
        }
    });


    $('#leaveCalendarFilterForm #department, #leaveCalendarFilterForm #employee, #leaveCalendarFilterForm #month, #leaveCalendarFilterForm #year').on('change', function(){
        var $form = $('#leaveCalendarFilterForm');

        var filters = getLeaveCalendarFilters();

        var $theWrap = $('.leaveCalendarWrap');
        var $table = $theWrap.find('table.leaveCalendarTable');

        setCalendarBusy(true);
        calendarState.loadingMore = false;
        setMoreLoader(false);

        axios({
            method: "post",
            url: route('hr.portal.filter.leave.calendar'),
            data: {department : filters.department, employee : filters.employee, month : filters.month, year : filters.year},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            setCalendarBusy(false);

            if (response.status == 200) {
                var res = response.data.res;
                $table.html(res);
                if (response.data.date) {
                    $form.find('button').attr('data-date', response.data.date);
                    calendarState.date = response.data.date;
                }
                renderCalendarMeta(response.data.meta);
                resetCalendarScroll();
            } 
        }).catch(error => {
            setCalendarBusy(false);
            if(error.response){
                if(error.response.status == 422){
                    console.log('error');
                }
            }
        });
    });

    $('#leaveCalendarFilterForm .leaveCalendarActionBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var theMonthStatus = $theBtn.attr('data-value');
        var thedate = $theBtn.attr('data-date');

        var $form = $('#leaveCalendarFilterForm');

        var filters = getLeaveCalendarFilters();

        var $theWrap = $('.leaveCalendarWrap');
        var $table = $theWrap.find('table.leaveCalendarTable');

        setCalendarBusy(true);
        calendarState.loadingMore = false;
        setMoreLoader(false);

        axios({
            method: "post",
            url: route('hr.portal.navigate.leave.calendar'),
            data: {department : filters.department, employee : filters.employee, theMonthStatus : theMonthStatus, thedate : thedate},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            setCalendarBusy(false);

            if (response.status == 200) {
                var res = response.data.res;
                var date = response.data.date;

                $table.html(res);
                $form.find('button').attr('data-date', date);
                calendarState.date = date;
                syncCalendarDateFilters(date);
                renderCalendarMeta(response.data.meta);
                resetCalendarScroll();
            } 
        }).catch(error => {
            setCalendarBusy(false);
            if(error.response){
                if(error.response.status == 422){
                    console.log('error');
                }
            }
        });
    });


    const viewLeaveModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewLeaveModal"));

    const viewLeaveModalEl = document.getElementById('viewLeaveModal')
    viewLeaveModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#viewLeaveModal .leaveDetailsModalLoader').fadeIn();
        $('#viewLeaveModal .leaveDetailsModalContent').html('').fadeOut();
        $('#viewLeaveModal .modal-titles').text('Leave Details');
    });

    $('.leaveCalendarTable').on('click', '.view_leave', function(e){
        e.preventDefault();
        var $theTd = $(this);
        var theLeaveDayId = $theTd.attr('data-leaveday-id');
        var theLeaveDate = $theTd.attr('data-date');
        var theEmployee = $theTd.attr('data-employee');

        axios({
            method: "post",
            url: route('hr.portal.get.leave.day.details'),
            data: {theLeaveDayId : theLeaveDayId, theLeaveDate : theLeaveDate, theEmployee : theEmployee},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                viewLeaveModal.show();
                document.getElementById('viewLeaveModal').addEventListener('shown.tw.modal', function(event){
                    $('#viewLeaveModal .leaveDetailsModalLoader').fadeOut();
                    $('#viewLeaveModal .leaveDetailsModalContent').html(response.data.htm).fadeIn();
                    $('#viewLeaveModal .modal-titles').text(response.data.title);
                });
            } 
        }).catch(error => {
            if(error.response){
                if(error.response.status == 422){
                    console.log('error');
                }
            }
        });
    });

})()
