import { createIcons, icons } from "lucide";
import Litepicker from "litepicker";

("use strict");

(function(){
    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    };

    const attendanceForm = document.getElementById('attendanceReportForm');
    const employeeSelect = document.getElementById('employee_id');
    const workTypeSelect = document.getElementById('employee_work_type_id');
    const dateInput = document.getElementById('the_date');
    const reportMonthInput = document.getElementById('report_month_picker');
    const downloadExcel = document.getElementById('downloadExcel');
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

    refreshIcons();

    const parseMonthValue = (value) => {
        const cleanValue = (value || '').toString().trim();
        const numericMatch = cleanValue.match(/^(\d{1,2})-(\d{4})$/);

        if(numericMatch){
            return {
                month: Math.min(Math.max(parseInt(numericMatch[1], 10), 1), 12) - 1,
                year: parseInt(numericMatch[2], 10),
            };
        }

        const longMonthMatch = cleanValue.match(/^([A-Za-z]+)\s+(\d{4})$/);
        if(longMonthMatch){
            const month = monthNames.findIndex((monthName) => monthName.toLowerCase() === longMonthMatch[1].toLowerCase());

            if(month >= 0){
                return {
                    month,
                    year: parseInt(longMonthMatch[2], 10),
                };
            }
        }

        const fallbackDate = new Date();
        return {
            month: fallbackDate.getMonth(),
            year: fallbackDate.getFullYear(),
        };
    };

    const formatDisplayMonth = (dateParts) => `${monthNames[dateParts.month]} ${dateParts.year}`;
    const formatRouteMonth = (dateParts) => `${String(dateParts.month + 1).padStart(2, '0')}-${dateParts.year}`;
    const formatPayloadDate = (dateParts) => `${dateParts.year}-${String(dateParts.month + 1).padStart(2, '0')}-01`;

    const closeMenus = (except = null) => {
        document.querySelectorAll('#attendanceReportPage [data-ar-employee-menu], #attendanceReportPage [data-ar-select-menu], #attendanceReportPage [data-ar-month-picker]').forEach((menu) => {
            if(menu === except){
                return;
            }

            menu.classList.remove('is-open');
            menu.querySelector('[data-ar-menu-toggle]')?.setAttribute('aria-expanded', 'false');
            menu.querySelector('[data-ar-employee-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    };

    const setupEmployeeSelect = (menu) => {
        const toggle = menu.querySelector('[data-ar-employee-toggle]');
        const label = menu.querySelector('[data-ar-employee-label]');
        const options = menu.querySelectorAll('[data-ar-employee-option]');
        const searchInput = menu.querySelector('[data-ar-employee-search]');
        const emptyState = menu.querySelector('[data-ar-employee-empty]');

        if(!toggle || !label || !employeeSelect){
            return;
        }

        const filterOptions = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            options.forEach((option) => {
                if(option.dataset.value === 'all'){
                    option.style.display = '';
                    return;
                }

                const haystack = option.dataset.search || option.dataset.label || '';
                const isVisible = query === '' || haystack.includes(query);
                option.style.display = isVisible ? '' : 'none';
                visibleCount += isVisible ? 1 : 0;
            });

            if(emptyState){
                emptyState.style.display = visibleCount > 0 ? 'none' : 'block';
            }
        };

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const isOpen = menu.classList.contains('is-open');
            closeMenus(menu);
            menu.classList.toggle('is-open', !isOpen);
            toggle.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');

            if(!isOpen && searchInput){
                searchInput.value = '';
                filterOptions();
                setTimeout(() => searchInput.focus(), 0);
            }
        });

        if(searchInput){
            ['click', 'keydown'].forEach((eventName) => {
                searchInput.addEventListener(eventName, (event) => event.stopPropagation());
            });

            searchInput.addEventListener('input', filterOptions);
        }

        options.forEach((option) => {
            option.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                options.forEach((item) => item.classList.remove('is-active'));
                option.classList.add('is-active');

                const value = option.dataset.value || 'all';
                label.textContent = option.dataset.label || 'All Employees';
                label.classList.toggle('is-selected', value !== 'all');
                menu.dataset.selected = value !== 'all' ? '1' : '0';
                employeeSelect.value = value;
                closeMenus();
                employeeSelect.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    };

    const setupCustomSelect = (menu) => {
        const toggle = menu.querySelector('[data-ar-menu-toggle]');
        const label = menu.querySelector('[data-ar-menu-label]');
        const options = menu.querySelectorAll('[data-ar-menu-option]');

        if(!toggle || !label || !workTypeSelect){
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = menu.classList.contains('is-open');
            closeMenus(menu);
            menu.classList.toggle('is-open', !isOpen);
            toggle.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
        });

        options.forEach((option) => {
            option.addEventListener('click', (event) => {
                event.stopPropagation();
                options.forEach((item) => item.classList.remove('is-active'));
                option.classList.add('is-active');

                const value = option.dataset.value || '';
                label.textContent = option.dataset.label || 'All';
                label.classList.toggle('is-selected', value !== '');
                menu.dataset.selected = value !== '' ? '1' : '0';
                workTypeSelect.value = value;
                closeMenus();
                workTypeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                refreshIcons();
            });
        });
    };

    const setupMonthPicker = () => {
        if(!reportMonthInput){
            return;
        }

        const pickerWrap = reportMonthInput.closest('[data-ar-month-picker]');
        const today = new Date();
        const maxMonthParts = {
            month: today.getMonth(),
            year: today.getFullYear(),
        };

        const getSelectedMonthParts = (picker) => {
            const selectedDate = picker.getDate();

            if(selectedDate){
                return {
                    month: selectedDate.getMonth(),
                    year: selectedDate.getFullYear(),
                };
            }

            return parseMonthValue(reportMonthInput.value || reportMonthInput.dataset.routeValue);
        };

        const renderMonthCalendar = (picker) => {
            if(!picker.ui){
                return;
            }

            picker.ui.classList.add('attendance-month-litepicker');

            const monthItem = picker.ui.querySelector('.month-item');
            if(!monthItem){
                return;
            }

            const weekdaysRow = monthItem.querySelector('.month-item-weekdays-row');
            const daysGrid = monthItem.querySelector('.container__days');

            if(weekdaysRow){
                weekdaysRow.style.display = 'none';
            }

            if(daysGrid){
                daysGrid.style.display = 'none';
            }

            const calendarDate = picker.calendars && picker.calendars[0] ? picker.calendars[0] : null;
            const activeYear = calendarDate ? calendarDate.getFullYear() : getSelectedMonthParts(picker).year;
            const selectedMonth = getSelectedMonthParts(picker);
            let monthGrid = monthItem.querySelector('.attendance-month-grid');

            if(!monthGrid){
                monthGrid = document.createElement('div');
                monthGrid.className = 'attendance-month-grid';
                monthItem.appendChild(monthGrid);
            }

            monthGrid.innerHTML = '';

            monthNames.forEach((monthName, monthIndex) => {
                const monthButton = document.createElement('button');
                const monthParts = {
                    month: monthIndex,
                    year: activeYear,
                };
                const isFutureMonth = activeYear > maxMonthParts.year || (activeYear === maxMonthParts.year && monthIndex > maxMonthParts.month);

                monthButton.type = 'button';
                monthButton.className = 'attendance-month-option';
                monthButton.textContent = monthName;

                if(selectedMonth.month === monthIndex && selectedMonth.year === activeYear){
                    monthButton.classList.add('is-active');
                }

                if(isFutureMonth){
                    monthButton.classList.add('is-disabled');
                    monthButton.disabled = true;
                }else{
                    monthButton.addEventListener('click', () => {
                        const date = new Date(activeYear, monthIndex, 1);
                        picker.setDate(date);
                        reportMonthInput.value = formatDisplayMonth(monthParts);

                        if(dateInput){
                            dateInput.value = formatPayloadDate(monthParts);
                        }

                        picker.hide();
                        window.location.href = route('hr.portal.reports.attendance', formatRouteMonth(monthParts));
                    });
                }

                monthGrid.appendChild(monthButton);
            });

            const yearField = picker.ui.querySelector('.month-item-year');
            if(yearField && !yearField.dataset.attendanceReportMonthBound){
                yearField.dataset.attendanceReportMonthBound = 'true';
                yearField.addEventListener('change', () => {
                    const year = parseInt(yearField.value, 10);
                    const currentMonth = picker.calendars && picker.calendars[0]
                        ? picker.calendars[0].getMonth()
                        : getSelectedMonthParts(picker).month;

                    if(!Number.isNaN(year)){
                        picker.gotoDate(new Date(year, currentMonth, 1));
                        renderMonthCalendar(picker);
                    }
                });
            }
        };

        const initialParts = parseMonthValue(reportMonthInput.dataset.routeValue || reportMonthInput.value);
        const initialDate = new Date(initialParts.year, initialParts.month, 1);
        const monthPicker = new Litepicker({
            element: reportMonthInput,
            autoApply: true,
            singleMode: true,
            numberOfColumns: 1,
            numberOfMonths: 1,
            showWeekNumbers: false,
            format: "MMMM YYYY",
            startDate: initialDate,
            maxDate: today,
            switchingMonths: 12,
            dropdowns: {
                minYear: 1900,
                maxYear: today.getFullYear(),
                months: false,
                years: true,
            },
            setup: (picker) => {
                picker.on('show', () => {
                    pickerWrap?.classList.add('is-open');
                    renderMonthCalendar(picker);
                });
                picker.on('hide', () => {
                    pickerWrap?.classList.remove('is-open');
                });
                picker.on('change:month', () => renderMonthCalendar(picker));
            },
        });

        pickerWrap?.addEventListener('click', (event) => {
            event.stopPropagation();

            if(event.target === reportMonthInput){
                return;
            }

            event.preventDefault();
            reportMonthInput.focus();
            monthPicker.show(reportMonthInput);
        });
    };

    document.querySelectorAll('#attendanceReportPage [data-ar-employee-menu]').forEach(setupEmployeeSelect);
    document.querySelectorAll('#attendanceReportPage [data-ar-select-menu]').forEach(setupCustomSelect);
    setupMonthPicker();

    document.addEventListener('click', () => closeMenus());
    document.addEventListener('keydown', (event) => {
        if(event.key === 'Escape'){
            closeMenus();
        }
    });

    const selectedEmployeeIds = () => {
        const value = $('#attendanceReportForm #employee_id').val();
        return value && value !== 'all' ? [value] : [];
    };

    const updateDownloadUrl = () => {
        if(!downloadExcel){
            return;
        }

        const baseUrl = downloadExcel.getAttribute('data-base-url') || downloadExcel.getAttribute('href');
        const params = new URLSearchParams();
        const typeId = workTypeSelect ? workTypeSelect.value : '';

        selectedEmployeeIds().forEach((id) => {
            if(id !== ''){
                params.append('employee_id[]', id);
            }
        });

        if(typeId !== ''){
            params.append('employee_work_type_id', typeId);
        }

        downloadExcel.setAttribute('href', params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl);
    };

    const setLoading = (isLoading) => {
        if(!downloadExcel){
            return;
        }

        downloadExcel.classList.toggle('is-loading', isLoading);
        downloadExcel.setAttribute('aria-disabled', isLoading ? 'true' : 'false');
    };

    function fetchReport(){
        const theDate = dateInput ? dateInput.value : '';
        const employeeIds = selectedEmployeeIds();
        const typeId = workTypeSelect ? workTypeSelect.value : '';

        updateDownloadUrl();
        setLoading(true);

        axios({
            method: "post",
            url: route('hr.portal.reports.attendance.filter'),
            data: {
                the_date: theDate,
                employee_id: employeeIds,
                employee_work_type_id: typeId,
            },
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            setLoading(false);

            if (response.status == 200) {
                const res = response.data.res;
                $('.attendanceReportWrap').fadeIn().html(res.html);

                if(typeof res.count !== 'undefined'){
                    $('.hr-att-count').text(res.count);
                }

                refreshIcons();
            }
        }).catch(error => {
            setLoading(false);
            if (error.response) {
                console.log('error');
            }
        });
    }

    if(workTypeSelect){
        workTypeSelect.addEventListener('change', fetchReport);
    }

    if(employeeSelect){
        employeeSelect.addEventListener('change', fetchReport);
    }

    if(attendanceForm){
        attendanceForm.addEventListener('submit', function(e){
            e.preventDefault();
            fetchReport();
        });
    }

    updateDownloadUrl();
})();
