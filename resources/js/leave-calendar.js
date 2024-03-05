import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {},
            remove_button: {
                title: "Remove this item",
            },
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    var employee_filter = new TomSelect('#employee', tomOptions);


    $('#leaveCalendarFilterForm #department, #leaveCalendarFilterForm #employee, #leaveCalendarFilterForm #month, #leaveCalendarFilterForm #year').on('change', function(){
        var $form = $('#leaveCalendarFilterForm');

        var $department = $('#department', $form);
        var department = $department.val();

        var $employee = $('#employee', $form);
        var employee = $employee.val();

        var $month = $('#month', $form);
        var month = $month.val();

        var $year = $('#year', $form);
        var year = $year.val();

        var $theWrap = $('.leaveCalendarWrap');
        var $table = $theWrap.find('table.leaveCalendarTable');
        var $theLoader = $('.leaveTableLoader');

        $theLoader.addClass('active');
        $form.find('select').attr('readonly', 'readonly');
        $form.find('button').attr('disabled', 'disabled');

        axios({
            method: "post",
            url: route('hr.portal.filter.leave.calendar'),
            data: {department : department, employee : employee, month : month, year : year},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $theLoader.removeClass('active');
            $form.find('select').removeAttr('readonly');
            $form.find('button').removeAttr('disabled');

            if (response.status == 200) {
                var res = response.data.res;
                $table.html(res);
            } 
        }).catch(error => {
            if(error.response){
                if(error.response.status == 422){
                    $theLoader.removeClass('active');
                    $form.find('select').removeAttr('readonly');
                    $form.find('button').removeAttr('disabled');

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

        var $department = $('#department', $form);
        var department = $department.val();

        var $employee = $('#employee', $form);
        var employee = $employee.val();

        var $theWrap = $('.leaveCalendarWrap');
        var $table = $theWrap.find('table.leaveCalendarTable');
        var $theLoader = $('.leaveTableLoader');

        $theLoader.addClass('active');
        $form.find('select').attr('readonly', 'readonly');
        $form.find('button').attr('disabled', 'disabled');

        axios({
            method: "post",
            url: route('hr.portal.navigate.leave.calendar'),
            data: {department : department, employee : employee, theMonthStatus : theMonthStatus, thedate : thedate},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $theLoader.removeClass('active');
            $form.find('select').removeAttr('readonly');
            $form.find('button').removeAttr('disabled');

            if (response.status == 200) {
                var res = response.data.res;
                var date = response.data.date;

                $table.html(res);
                $form.find('button').attr('data-date', date);
            } 
        }).catch(error => {
            if(error.response){
                if(error.response.status == 422){
                    $theLoader.removeClass('active');
                    $form.find('select').removeAttr('readonly');
                    $form.find('button').removeAttr('disabled');

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