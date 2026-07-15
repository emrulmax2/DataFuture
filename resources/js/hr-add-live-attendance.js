
import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";

import IMask from 'imask';

import Litepicker from "litepicker";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const $saveButtons = $('.liveAttendanceSaveBtn');
    const $rowsCard = $('#liveAttendanceRowsCard');
    const $tableFooter = $('.hr-live-add-table-footer');
    const $selectedCount = $('.hr-live-add-selected-count');
    const $footerCount = $('#attendanceSelectedCountFooter');
    const $recordWord = $('.hr-live-add-record-word');
    let employeeIDS = null;

    const getInitials = (name = '') => {
        const parts = name.replace(/^(Mr|Mrs|Ms|Miss|Dr)\.?\s+/i, '').trim().split(/\s+/).filter(Boolean);
        const first = parts[0] || 'L';
        const last = parts.length > 1 ? parts[parts.length - 1] : first;

        return `${first.charAt(0)}${last.charAt(0)}`.toUpperCase();
    };

    const getPaletteStyle = (seed = '') => {
        const palette = [
            ['#e4f1ee', '#0d7c73'],
            ['#f3ecd8', '#a1802f'],
            ['#e6ecf5', '#2f5fa1'],
            ['#f4e6ec', '#a13f6b'],
            ['#e9f0e4', '#4a7a2f'],
            ['#ece4f5', '#7a4fa3'],
            ['#fbe8df', '#b5602f'],
            ['#dff0ef', '#137a70'],
        ];
        let hash = 0;
        for (let i = 0; i < seed.length; i += 1) {
            hash = ((hash * 31) + seed.charCodeAt(i)) >>> 0;
        }
        const color = palette[hash % palette.length];

        return `background:${color[0]};color:${color[1]};`;
    };

    const hasUploadedPhoto = (photo = '') => {
        const cleanPhoto = photo.trim();

        return cleanPhoto !== '' && !cleanPhoto.startsWith('data:');
    };

    const renderAvatar = (name = '', photo = '', className = '', escape) => {
        if (hasUploadedPhoto(photo)) {
            return `<span class="${className} ${className}--photo"><img src="${escape(photo)}" alt="${escape(name)}"></span>`;
        }

        return `<span class="${className}" style="${getPaletteStyle(name)}">${escape(getInitials(name))}</span>`;
    };

    const formatLongDate = (value = '') => {
        const parts = value.split('-');
        if (parts.length !== 3) {
            return '';
        }
        const date = new Date(Number(parts[2]), Number(parts[1]) - 1, Number(parts[0]));
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        return date.toLocaleDateString('en-GB', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    };

    const setButtonsVisible = (visible) => {
        if (visible) {
            $saveButtons.css('display', 'inline-flex');
            $tableFooter.css('display', 'flex');
        } else {
            $saveButtons.hide();
            $tableFooter.hide();
        }
    };

    const updateDateNote = () => {
        $('#liveAttendanceDateLong').text(formatLongDate($('#liveAttendanceDate').val()));
    };

    const refreshAttendanceState = () => {
        const attendanceRows = $('#addLiveAttendanceTable tbody tr.employeeAttendanceRow').length;
        const selectedRows = employeeIDS ? employeeIDS.items.length : attendanceRows;
        const placeholder = selectedRows > 0 ? 'Add another...' : 'Search & select employees...';

        if (employeeIDS) {
            employeeIDS.settings.placeholder = placeholder;
            employeeIDS.control_input.setAttribute('placeholder', placeholder);
            employeeIDS.inputState();
        }

        $selectedCount.html(`&middot; ${selectedRows} selected`);
        $footerCount.text(attendanceRows);
        $recordWord.text(attendanceRows === 1 ? 'record' : 'records');
        $rowsCard.toggleClass('has-rows', attendanceRows > 0);
        setButtonsVisible(attendanceRows > 0);

        if (attendanceRows > 0) {
            $('#addLiveAttendanceTable tbody tr.noticeRow').hide();
        } else {
            $('#addLiveAttendanceTable tbody tr.noticeRow').show();
        }
    };

    const maskClockInputs = () => {
        $('#addLiveAttendanceTable').find('input.clockMask').each(function(){
            if (!this.dataset.clockMaskReady) {
                IMask(this, {mask: '00:00'});
                this.dataset.clockMaskReady = '1';
            }
        });
    };

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
            remove_button: {
                title: "Remove this employee",
            },
        },
        placeholder: 'Search & select employees...',
        create: false,
        persist: false,
        hidePlaceholder: false,
        maxOptions: null,
        render: {
            option: function(data, escape) {
                const name = data.text || '';
                const role = data.role || 'Employee';
                const photo = data.photo || '';

                return `
                    <div class="hr-live-add-select-option">
                        ${renderAvatar(name, photo, 'hr-live-add-select-avatar', escape)}
                        <span class="hr-live-add-select-copy">
                            <strong>${escape(name)}</strong>
                            <small>${escape(role)}</small>
                        </span>
                        <span class="hr-live-add-select-plus">+</span>
                    </div>
                `;
            },
            item: function(data, escape) {
                const name = data.text || '';
                const photo = data.photo || '';

                return `
                    <div class="hr-live-add-select-item">
                        ${renderAvatar(name, photo, 'hr-live-add-select-item__avatar', escape)}
                        <span class="hr-live-add-select-item__name">${escape(name)}</span>
                    </div>
                `;
            },
            no_results: function(data, escape) {
                return `<div class="hr-live-add-select-empty">No employees found for "${escape(data.input)}".</div>`;
            },
        },
    };

    employeeIDS = new TomSelect('#employeeIDS', tomOptions);

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
        employeeIDS.clear(true);
        updateDateNote();
        refreshAttendanceState();
    });

    $('#liveAttendanceDate').each(function(){
        IMask(
            this, {
                mask: '00-00-0000'
            }
        )
    });

    $('#liveAttendanceDate').on('input', updateDateNote);

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
        refreshAttendanceState();
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
                $('#addLiveAttendanceTable tbody tr.noticeRow').hide();
                if(res){
                    $('#addLiveAttendanceTable > tbody').append(res);
                }
                refreshAttendanceState();
                maskClockInputs();
                createIcons({ icons });
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
        refreshAttendanceState();
    });

    $('.liveAttendanceSaveBtn').on('click', function(e){
        e.preventDefault();
        var $form = $('#attendanceLiveForm');
        const form = document.getElementById('attendanceLiveForm');
    
        $('.leaveTableLoader').addClass('active');
        $saveButtons.attr('disabled', 'disabled');
        $saveButtons.find('.hr-live-add-btn__spinner').fadeIn();

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('hr.portal.live.attedance.fee.data'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('.leaveTableLoader').removeClass('active');
            $saveButtons.removeAttr('disabled');
            $saveButtons.find('.hr-live-add-btn__spinner').fadeOut();
            
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
            $saveButtons.removeAttr('disabled');
            $saveButtons.find('.hr-live-add-btn__spinner').fadeOut();
            if (error.response) {
                console.log('error');
            }
        });
    });

    updateDateNote();
    refreshAttendanceState();
    createIcons({ icons });
})()
