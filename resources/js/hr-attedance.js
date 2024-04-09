import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

import dayjs from "dayjs";
import Litepicker from "litepicker";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));


    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        format: "MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const queryDate = new Litepicker({
        element: document.getElementById('queryDate'),
        ...dateOption
    });

    $('#filterMonthAttenForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('filterMonthAttenForm');

        document.querySelector('#filterMonthAtten').setAttribute('disabled', 'disabled');
        document.querySelector('#filterMonthAtten svg').style.cssText = 'display: inline-block;';
        document.querySelector('.leaveTableLoader').classList.add('active');

        let form_data = new FormData(form);
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
})();