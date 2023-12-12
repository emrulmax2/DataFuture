import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

import dayjs from "dayjs";
import Litepicker from "litepicker";
import 'litepicker/dist/plugins/multiselect';

("use strict");
var manageHolidayListTable = (function () {
    var _tableGen = function (yearid, type) {
        let tableID = '#leaveListTable-'+type+'-'+yearid;
        
        let tableContent = new Tabulator(tableID, {
            ajaxURL: route('hr.portal.holiday.list'),
            ajaxParams: { yearid : yearid, type : type },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<a href="'+cell.getData().url+'" class="flex justify-start items-center">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5">';
                                    html += '<img alt="'+cell.getData().name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div>';
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().name+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().designation != '' ? cell.getData().designation : 'Unknown')+'</div>';
                                html += '</div>';
                            html += '</a>';
                        return html;
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                },
                {
                    title: "Start Date",
                    field: "start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "End Date",
                    field: "end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Title",
                    field: "title",
                    headerHozAlign: "left",
                },
                {
                    title: "Hour",
                    field: "hour",
                    headerHozAlign: "left",
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowClick:function(e, row){
                var type = row.getData().type;
                var id = row.getData().id;
                var yearid = row.getData().yearid;

                
                if(type == 'approved'){
                    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
                    confirmModal.show();
                    document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                        $('#confirmModal .confModTitle').html('Are you sure?');
                        $('#confirmModal .confModDesc').html('Do you really want to reject this day\'s leave hour? Then click on agree to continue.');
                        $('#confirmModal .agreeWith').attr('data-id', id).attr('data-action', 'REJECTLEAVE');
                    });
                }else if(type == 'rejected'){
                    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
                    confirmModal.show();
                    document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                        $('#confirmModal .confModTitle').html('Are you sure?');
                        $('#confirmModal .confModDesc').html('Do you really want to approve this day\'s leave hour? Then click on agree to continue.');
                        $('#confirmModal .agreeWith').attr('data-id', id).attr('data-action', 'APPROVELEAVE');
                    });
                }else{
                    const empNewLeaveRequestModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#empNewLeaveRequestModal"));
                    empNewLeaveRequestModal.show();
                    axios({
                        method: "post",
                        url: route('employee.holiday.get.leave'),
                        data: {employee_leave_id : id},
                        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                    }).then(response => {
                        if (response.status == 200) {
                            $('#empNewLeaveRequestModal .modal-body').html(response.data.res);
                            $('#empNewLeaveRequestModal [name="employee_leave_id"]').val(employee_leave_id);
                        } 
                    }).catch(error => {
                        if(error.response){
                            if(error.response.status == 422){
                                empNewLeaveRequestModal.hide();
                                console.log('error');
                            }
                        }
                    });
                }
            }
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });
    };
    return {
        init: function (yearid, type) {
            _tableGen(yearid, type);
        },
    };
})();


(function(){

    $('.holidayCollapseBtns').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var yearid = $theBtn.attr('data-year');

        if($theBtn.hasClass('collapsed')){
            manageHolidayListTable.init(yearid, 'pending');
            manageHolidayListTable.init(yearid, 'approved');
            manageHolidayListTable.init(yearid, 'rejected');
        }
    });


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const empNewLeaveRequestModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#empNewLeaveRequestModal"));

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

    /* Leave Request Start */
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


    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var row_id = $theBtn.attr('data-id');
        var action = $theBtn.attr('data-action');

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
                console.log(error)
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
                console.log(error)
            });
        }
    })
    /* Leave Request End */
})()