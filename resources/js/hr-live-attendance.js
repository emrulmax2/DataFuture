import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

import dayjs from "dayjs";
import Litepicker from "litepicker";


(function(){
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
        if($('#liveAttendanceTable').length > 0){
            var departement = $('#liveAttendanceDept').val();
            var date = $('#liveAttendanceDate').val();
            var emp = $('#liveAttendanceEmp').val();

            $('.leaveTableLoader').addClass('active');
            axios({
                method: "post",
                url: route('hr.portal.live.attedance.ajax'),
                data: {departement : departement, date : date, emp : emp},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('.leaveTableLoader').removeClass('active');
                if (response.status == 200) {
                    let res = response.data.res;
                    $('.theDateHolder').html(res.the_date);
                    $('#liveAttendanceTable tbody').html(res.htm);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                $('.leaveTableLoader').removeClass('active');
                if (error.response) {
                    console.log('error');
                }
            });
        }
    });

    $('#liveAttendanceDept').on('change', function(e){
        if($('#liveAttendanceTable').length > 0){
            var departement = $('#liveAttendanceDept').val();
            var date = $('#liveAttendanceDate').val();
            var emp = $('#liveAttendanceEmp').val();

            $('.leaveTableLoader').addClass('active');
            axios({
                method: "post",
                url: route('hr.portal.live.attedance.ajax'),
                data: {departement : departement, date : date, emp : emp},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('.leaveTableLoader').removeClass('active');
                if (response.status == 200) {
                    let res = response.data.res;
                    $('.theDateHolder').html(res.the_date);
                    $('#liveAttendanceTable tbody').html(res.htm);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                $('.leaveTableLoader').removeClass('active');
                if (error.response) {
                    console.log('error');
                }
            });
        }
    });

    $('#liveAttendanceEmp').on('keyup', function(e){
        var departement = $('#liveAttendanceDept').val();
        var date = $('#liveAttendanceDate').val();
        var emp = $('#liveAttendanceEmp').val();

        $('.leaveTableLoader').addClass('active');
        axios({
            method: "post",
            url: route('hr.portal.live.attedance.ajax'),
            data: {departement : departement, date : date, emp : emp},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('.leaveTableLoader').removeClass('active');
            if (response.status == 200) {
                let res = response.data.res;
                $('.theDateHolder').html(res.the_date);
                $('#liveAttendanceTable tbody').html(res.htm);

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error => {
            $('.leaveTableLoader').removeClass('active');
            if (error.response) {
                console.log('error');
            }
        });
    });

})();