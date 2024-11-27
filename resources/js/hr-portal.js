import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import IMask from 'imask';

("use strict");
var employeeListTable = (function () {
    var _tableGen = function () {
        
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#employeeListTable", {
            ajaxURL: route("employee.list"),
            ajaxParams: { querystr: querystr, status: status },
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
                    field: "first_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                    html += '<img alt="'+cell.getData().first_name+'" class="rounded-full shadow" src="'+cell.getData().photourl+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -5px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().first_name+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().ejt_name != '' ? cell.getData().ejt_name : 'Unknown')+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Department",
                    field: "dpt_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Work Type",
                    field: "ewt_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Work Number",
                    field: "empt_works_number",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        if(cell.getData().status == 1){
                            return '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Active</span>';
                        }else if(cell.getData().status == 2){
                            return '<span class="btn inline-flex btn-pending w-auto px-2 text-white py-0 rounded-0">Temporary</span>';
                        }else if(cell.getData().status == 4){
                            return '<span class="btn inline-flex btn-warning w-auto px-2 text-white py-0 rounded-0">Submitted</span>';
                        }else{
                            return '<span class="btn inline-flex btn-danger w-auto px-2 text-white py-0 rounded-0">Inactive</span>';
                        }
                    }
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowClick:function(e, row){
                if((row.getData().locked_profile == 0 || row.getData().emp_can_access_all == 1) && (row.getData().status == 1 || row.getData().status == 0 || row.getData().status == 4)){
                    window.open(row.getData().url, '_blank');
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

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    if ($("#employeeListTable").length) {
        employeeListTable.init();
        

        // Filter function
        function filterTitleHTMLForm() {
            employeeListTable.init();
        }

        $("#tabulatorFilterForm #query").on('keypress', function(e){
            var key = e.keyCode || e.which;
            if(key === 13){
                e.preventDefault(); // Ensure it is only this code that runs
    
                filterTitleHTMLForm();
            }
        })

        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterTitleHTMLForm();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const absentUpdateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#absentUpdateModal"));
    const empNewLeaveRequestModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#empNewLeaveRequestModal"));
    const addTempEmployeeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTempEmployeeModal"));

    const absentUpdateModalEl = document.getElementById('absentUpdateModal')
    absentUpdateModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#absentUpdateModal .modal-body select').val('');
        $('#absentUpdateModal .modal-body input').val('');
        $('#absentUpdateModal .modal-body textarea').val('');

        $('#absentUpdateModal input[name="employee_id"]').html('0');
        $('#absentUpdateModal input[name="minutes"]').html('0');
        $('#absentUpdateForm input[name="hour"]').attr('data-todayhour', '00:00').val('00:00').attr('readonly', 'readonly');
        $('#absentUpdateModal .modal-body').find('.formLeaveError').remove();
    });

    const empNewLeaveRequestModalEl = document.getElementById('empNewLeaveRequestModal')
    empNewLeaveRequestModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#empNewLeaveRequestModal .modal-body').html('');
        $('#empNewLeaveRequestModal [name="employee_leave_id"]').html('0');
    });

    const addTempEmployeeModalEl = document.getElementById('addTempEmployeeModal')
    addTempEmployeeModalEl.addEventListener('hide.tw.modal', function(event){
        $('#addTempEmployeeModal .modal-body input').val('');
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

    if($('.timeMask').length > 0){
        var maskOptions = {
            mask: '00:00'
        };
        $('.timeMask').each(function(){
            var mask = IMask(this, maskOptions);
        })
    }


    $('.absentToday').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var employee = $this.attr('data-emloyee');
        var minute = $this.attr('data-minute');
        var hourminute = $this.attr('data-hour-min');
        var the_date = $this.attr('data-date');

        axios({
            method: "post",
            url: route('hr.portal.check.pending.leave'),
            data: {employee : employee, the_date : the_date},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            
            
            if (response.status == 200) {
                let suc = response.data.suc;
                let msg = response.data.msg;

                if(suc == 2){
                    $('#absentUpdateForm .modal-body').find('.formLeaveError').remove();
                    $('#absentUpdateForm .modal-body').prepend('<div class="alert formLeaveError alert-danger-soft show flex items-start mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-4"></i><div>'+msg+'</div></div>');
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }else{
                    $('#absentUpdateForm .modal-body').find('.formLeaveError').remove();
                    document.querySelector('#updateAbsent').removeAttribute('disabled');
                }
                
                //$('#absentUpdateForm input[name="hour"]').val(hourminute);
                $('#absentUpdateForm input[name="hour"]').attr('data-todayhour', hourminute);
                $('#absentUpdateForm input[name="employee_id"]').val(employee)
                $('#absentUpdateForm input[name="minutes"]').val(minute);

                setTimeout(function(){
                    //$('#absentUpdateForm .modal-body').find('.formLeaveError').remove();
                }, 5000);
            }
        }).catch(error => {
            document.querySelector('#updateAbsent').removeAttribute('disabled');
            if (error.response) {
                console.log('error');
            }
        });
    });
    $('#absentUpdateForm [name="leave_type"]').on('change', function(){
        if($(this).val() == 5){
            $('#absentUpdateForm input[name="hour"]').val($('#absentUpdateForm input[name="hour"]').attr('data-todayhour')).removeAttr('readonly');
        }else{
            $('#absentUpdateForm input[name="hour"]').val('00:00').attr('readonly', 'readonly');
        }
    });

    $('#absentUpdateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('absentUpdateForm');
    
        document.querySelector('#updateAbsent').setAttribute('disabled', 'disabled');
        document.querySelector("#updateAbsent svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('hr.portal.update.absent'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateAbsent').removeAttribute('disabled');
            document.querySelector("#updateAbsent svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                absentUpdateModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Absent details successfully updated .');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000)
            }
        }).catch(error => {
            document.querySelector('#updateAbsent').removeAttribute('disabled');
            document.querySelector("#updateAbsent svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#absentUpdateForm .${key}`).addClass('border-danger');
                        $(`#absentUpdateForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    /* Pending Leave Request Action Start */
    $('.actPendingHoliday').on('click', function(e){
        e.preventDefault();
        var employee_leave_id = $(this).attr('data-leave');

        empNewLeaveRequestModal.show();
        axios({
            method: "post",
            url: route('employee.holiday.get.leave'),
            data: {employee_leave_id : employee_leave_id},
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
    /* Pending Leave Request Action End */


    
    $("#addTempEmployeeForm").on("submit", function (e) {
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById("addTempEmployeeForm");

        document.querySelector('#tempEmpBtn').setAttribute('disabled', 'disabled');
        document.querySelector('#tempEmpBtn svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: "POST",
            url: route("hr.portal.create.temporary.employee"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#tempEmpBtn").removeAttribute("disabled");
                document.querySelector("#tempEmpBtn svg").style.cssText = "display: none;";
                if(response.data.suc == 2){
                    $('#addTempEmployeeModal .modal-content .employeeError').remove();
                    $('#addTempEmployeeModal .modal-content').prepend('<div class="alert employeeError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try again later or contact with the administrator.</div>');
                }else if(response.data.suc == 3){
                    $('#addTempEmployeeModal .modal-content .employeeError').remove();
                    $('#addTempEmployeeModal .modal-content').prepend('<div class="alert employeeError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Email address already exist. Please try with a new one.</div>');
                }else{
                    addTempEmployeeModal.hide();
                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Success!");
                        $("#successModal .successModalDesc").html('Employee successfull saved and email sent to the employee.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });

                    setTimeout(() => {
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }
        })
        .catch((error) => {
            document.querySelector("#tempEmpBtn").removeAttribute("disabled");
            document.querySelector("#tempEmpBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addTempEmployeeForm .${key}`).addClass('border-danger')
                        $(`#addTempEmployeeForm  .error-${key}`).html(val)
                    }
                }else {
                    console.log("error");
                }
            }
        });
    });

})();