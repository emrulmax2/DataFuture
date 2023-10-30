import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

("use strict");
var employeeWorkingPatternDaysListTable = (function () {
    var _tableGen = function (employeeWorkingPatternId = 0) {
        // Setup Tabulator
        let tableID = 'patternDetailsTable_'+employeeWorkingPatternId;

        let tableContent = new Tabulator("#"+tableID, {
            ajaxURL: route("employee.pattern.details.list"),
            ajaxParams: { employeeWorkingPatternId: employeeWorkingPatternId },
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
                    title: "Day Name",
                    field: "day_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Start Time",
                    field: "start",
                    headerHozAlign: "left",
                },
                {
                    title: "End Time",
                    field: "end",
                    headerHozAlign: "left",
                },
                {
                    title: "Paid Break",
                    field: "paid_br",
                    headerHozAlign: "left",
                },
                {
                    title: "Unpaid Break",
                    field: "unpaid_br",
                    headerHozAlign: "left",
                },
                {
                    title: "Day Hour",
                    field: "total",
                    headerHozAlign: "left",
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
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
        init: function (employeeWorkingPatternId) {
            _tableGen(employeeWorkingPatternId);
        },
    };
})();

var hideCollapsibleIcon = function(cell, formatterParams, onRendered){ 
    return '<span class="chellIconWrapper inline-flex">+</span>';
};

var employeePatternListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let status = $("#status-EWP").val() != "" ? $("#status-EWP").val() : "";
        let employee_id = $("#employeePatternListTable").attr('data-employee');

        let tableContent = new Tabulator("#employeePatternListTable", {
            ajaxURL: route("employee.pattern.list"),
            ajaxParams: { employee_id: employee_id, status: status },
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
            height:"auto",
            columns: [
                {
                    formatter: hideCollapsibleIcon, 
                    align: "left", 
                    title: "&nbsp;", 
                    width: "100",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, row, formatterParams){
                        const employeeWorkingPatternId = row.getData().id;
                        let holderWrapEl = document.getElementById('subTableWrap_'+employeeWorkingPatternId);
                        let termTableID = 'patternDetailsTable_'+employeeWorkingPatternId;

                        if(row.getElement().classList.contains('active')){
                            row.getElement().classList.remove('active');
                            row.getElement().querySelector('.chellIconWrapper').innerHTML = '+';
                            holderWrapEl.style.display = 'none';
                        }else{
                            row.getElement().classList.add('active');
                            row.getElement().querySelector('.chellIconWrapper').innerHTML = '-';
                            holderWrapEl.style.display = 'block';
                            holderWrapEl.style.width = '100%';

                            if($('#'+termTableID).length > 0){
                                employeeWorkingPatternDaysListTable.init(employeeWorkingPatternId)
                            }
                        }     
                    }
                },
                {
                    title: "Effective From",
                    field: "effective_from",
                    headerHozAlign: "left",
                },
                {
                    title: "End To",
                    field: "end_to",
                    headerHozAlign: "left",
                },
                {
                    title: "Contracted Hour",
                    field: "contracted_hour",
                    headerHozAlign: "left",
                },
                {
                    title: "Salary",
                    field: "salary",
                    headerHozAlign: "left",
                },
                {
                    title: "Hourly Rate",
                    field: "hourly_rate",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return cell.getData().active == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "280",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            if(cell.getData().has_days == 0){
                                btns += '<button data-id="' +cell.getData().id +'" data-contracted="'+cell.getData().contracted_hour+'" data-tw-toggle="modal"  data-tw-target="#addCalendarModal" class="addPatternDayInfo btn btn-linkedin text-white btn-rounded ml-1 p-0 px-4 w-auto h-9"><i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Calendar</button>';
                            }else{
                                btns += '<button data-id="' +cell.getData().id +'" data-contracted="'+cell.getData().contracted_hour+'" data-tw-toggle="modal"  data-tw-target="#editCalendarModal" class="editPatternDayInfo btn btn-success text-white btn-rounded ml-1 p-0 px-4 w-auto h-9"><i data-lucide="pencil" class="w-4 h-4 mr-2"></i> Edit Calendar</button>';
                            }
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editEmployeeWorkingPatternModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +='<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowFormatter: function(row, e) {
                const employeeWorkingPatternId = row.getData().id;
                const has_days = row.getData().has_days;

                var holderEl = document.createElement("div");
                holderEl.setAttribute('class', "pt-3 px-5 pb-5 overflow-x-auto scrollbar-hidden subTableWrap_"+employeeWorkingPatternId);
                holderEl.setAttribute('id', "subTableWrap_"+employeeWorkingPatternId);
                holderEl.style.display = "none";
                holderEl.style.boxSizing = "border-box";
                //holderEl.style.borderTop = "1px solid #e5e7eb";


                if(has_days > 0){
                    var tableEl = document.createElement("div");
                    tableEl.setAttribute('class', "table-report table-report--tabulator subTable"+employeeWorkingPatternId);
                    tableEl.setAttribute('id', "patternDetailsTable_"+employeeWorkingPatternId);
                    tableEl.setAttribute('data-employeeWorkingPatternId', employeeWorkingPatternId);

                    holderEl.appendChild(tableEl);
                }else{
                    holderEl.innerHTML = '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp;No details found under this pattern.</div>';
                }

                row.getElement().appendChild(holderEl);
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
        $("#tabulator-export-csv-EWP").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EWP").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EWP").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Groups Details",
            });
        });

        $("#tabulator-export-html-EWP").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EWP").on("click", function (event) {
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
    if ($("#employeePatternListTable").length) {
        // Init Table
        employeePatternListTable.init();

        // Filter function
        function filterHTMLFormEWP() {
            employeePatternListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-EWP")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormEWP();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-EWP").on("click", function (event) {
            filterHTMLFormEWP();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EWP").on("click", function (event) {
            $("#query-EWP").val("");
            $("#status-EWP").val("1");
            filterHTMLFormEWP();
        });
    }

    const addEmployeeWorkingPatternModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmployeeWorkingPatternModal"));
    const editEmployeeWorkingPatternModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmployeeWorkingPatternModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addCalendarModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCalendarModal"));
    const editCalendarModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCalendarModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    let confModalDelTitle = 'Are you sure?';

    const addEmployeeWorkingPatternModalEl = document.getElementById('addEmployeeWorkingPatternModal')
    addEmployeeWorkingPatternModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addEmployeeWorkingPatternModal .acc__input-error').html('');
        $('#addEmployeeWorkingPatternModal .modal-body input').val('');
        $('#addEmployeeWorkingPatternModal .modal-body select').val('');
        $('#addEmployeeWorkingPatternModal .modal-footer input[name="active"]').prop('checked', true);
    });

    const editEmployeeWorkingPatternModalEl = document.getElementById('editEmployeeWorkingPatternModal')
    editEmployeeWorkingPatternModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editEmployeeWorkingPatternModal .acc__input-error').html('');
        $('#editEmployeeWorkingPatternModal .modal-body input').val('');
        $('#editEmployeeWorkingPatternModal .modal-body select').val('');
        $('#editEmployeeWorkingPatternModal .modal-footer input[name="active"]').prop('checked', false);
        $('#editEmployeeWorkingPatternModal .modal-footer input[name="id"]').val('0');
    });

    const addCalendarModalEl = document.getElementById('addCalendarModal')
    addCalendarModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addCalendarModal .weekDayStat').prop('checked', false);
        $('#addCalendarModal table tbody .errorRow').fadeIn('fast');
        $('#addCalendarModal table tbody .patternRow').remove();

        $('#addCalendarModal input[name="employee_working_pattern_id"]').val('0');
        $('#addCalendarModal input[name="contracted_hour"]').val('0');
        $('#addCalendarModal input[name="weekTotal"]').val('');
    });

    const editCalendarModalEl = document.getElementById('editCalendarModal')
    editCalendarModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editCalendarModal .weekDayStat').prop('checked', false);
        $('#editCalendarModal table tbody .errorRow').fadeIn('fast');
        $('#editCalendarModal table tbody .patternRow').remove();

        $('#editCalendarModal input[name="employee_working_pattern_id"]').val('0');
        $('#editCalendarModal input[name="contracted_hour"]').val('0');
        $('#editCalendarModal input[name="weekTotal"]').val('');
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

    if($('input[name="contracted_hour"]').length > 0){
        var maskOptions = {
            mask: '00:00'
        };
        $('input[name="contracted_hour"]').each(function(){
            var mask = IMask(this, maskOptions);
        })
    }

    $('#addEmployeeWorkingPatternForm [name="contracted_hour"], #addEmployeeWorkingPatternForm [name="salary"]').on('keyup', function(){
        var contractedHour = $('#addEmployeeWorkingPatternForm [name="contracted_hour"]').val();
        var salary = $('#addEmployeeWorkingPatternForm [name="salary"]').val();

        if(contractedHour.length == 5 && salary > 0){
            var hrmnArray = contractedHour.split(':');
            var hr = parseInt(hrmnArray[0], 10);
            var mn = parseInt(hrmnArray[1], 10) / 60;
            var hrmn = (hr + mn);

            var hrRate = (salary / hrmn);
            $('#addEmployeeWorkingPatternForm [name="hourly_rate"]').val(hrRate.toFixed(2));
        }else{
            $('#addEmployeeWorkingPatternForm [name="hourly_rate"]').val('');
        }
    });

    $('#addEmployeeWorkingPatternForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addEmployeeWorkingPatternForm');
    
        document.querySelector('#saveEWP').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEWP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.pattern.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveEWP').removeAttribute('disabled');
            document.querySelector("#saveEWP svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addEmployeeWorkingPatternModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee\'s working pattern successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });   
                
                setTimeout(function(){
                    successModal.hide();
                }, 5000)
            }
            employeePatternListTable.init();
        }).catch(error => {
            document.querySelector('#saveEWP').removeAttribute('disabled');
            document.querySelector("#saveEWP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addEmployeeWorkingPatternForm .${key}`).addClass('border-danger');
                        $(`#addEmployeeWorkingPatternForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#employeePatternListTable').on('click', '.edit_btn', function(){
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("employee.pattern.edit"),
            data: {editId: editId},
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;
                $('#editEmployeeWorkingPatternModal [name="effective_from"]').val(dataset.effective_from ? dataset.effective_from : '');
                $('#editEmployeeWorkingPatternModal [name="end_to"]').val(dataset.end_to ? dataset.end_to : '');
                $('#editEmployeeWorkingPatternModal [name="contracted_hour"]').val(dataset.contracted_hour ? dataset.contracted_hour : '');
                $('#editEmployeeWorkingPatternModal [name="salary"]').val(dataset.salary ? dataset.salary : '');
                $('#editEmployeeWorkingPatternModal [name="hourly_rate"]').val(dataset.hourly_rate ? dataset.hourly_rate : '');

                if(dataset.active == 1){
                    $('#editEmployeeWorkingPatternModal [name="active"]').prop('checked', true);
                }else{
                    $('#editEmployeeWorkingPatternModal [name="active"]').prop('checked', false);
                }
                $('#editEmployeeWorkingPatternModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editEmployeeWorkingPatternForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editEmployeeWorkingPatternForm');
    
        document.querySelector('#updateEWP').setAttribute('disabled', 'disabled');
        document.querySelector("#updateEWP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.pattern.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateEWP').removeAttribute('disabled');
            document.querySelector("#updateEWP svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editEmployeeWorkingPatternModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee\'s working pattern successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });   
                
                setTimeout(function(){
                    successModal.hide();
                }, 5000)
            }
            employeePatternListTable.init();
        }).catch(error => {
            document.querySelector('#updateEWP').removeAttribute('disabled');
            document.querySelector("#updateEWP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editEmployeeWorkingPatternForm .${key}`).addClass('border-danger');
                        $(`#editEmployeeWorkingPatternForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();

        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETEEWP'){
            axios({
                method: 'delete',
                url: route('employee.pattern.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Employee Working Pattern successfully deleted!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                employeePatternListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREEWP'){
            axios({
                method: 'post',
                url: route('employee.pattern.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Success!');
                        $('#successModal .successModalDesc').html('Employee Working Pattern Successfully Restored!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                employeePatternListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } 
    })

    // Delete Course
    $('#employeePatternListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record?  If yes, the please click on agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEEWP');
        });
    });

    // Restore Course
    $('#employeePatternListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore this record?  If yes, the please click on agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREEWP');
        });
    });


    /* Word Pattern Details */
    $('#employeePatternListTable').on('click', '.addPatternDayInfo', function(){
        var employeeWorkingPatternId = $(this).attr('data-id');
        var contractedHour = $(this).attr('data-contracted');

        $('#addCalendarModal input[name="employee_working_pattern_id"]').val(employeeWorkingPatternId);
        $('#addCalendarModal input[name="contracted_hour"]').val(contractedHour);
    });

    $('#addCalendarModal .weekDayStat').on('change', function(){
        var $theInput = $(this);
        var dayId = $theInput.val();
        var days = {'1' : 'Mon', '2' : 'Tue', '3' : 'Wed', '4' : 'Thu', '5' : 'Fir', '6' : 'Sat', '7' : 'Sun'};

        if($theInput.prop('checked')){
            var rowHtml = '';
            rowHtml += '<tr data-order="'+dayId+'" class="patternRow patternRow_'+dayId+'" id="patternRow_'+dayId+'">';
                rowHtml += '<td>';
                    rowHtml += days[dayId];
                    rowHtml += '<input type="hidden" value="'+dayId+'" name="pattern['+dayId+'][day]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask startTime" minlength="5" maxlength="5" name="pattern['+dayId+'][start]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask endTime" minlength="5" maxlength="5" name="pattern['+dayId+'][end]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask paidBr" minlength="5" maxlength="5" name="pattern['+dayId+'][paid_br]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask unpaidBr" minlength="5" maxlength="5" name="pattern['+dayId+'][unpaid_br]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask rowTotal" minlength="5" readonly maxlength="5" name="pattern['+dayId+'][total]"/>';
                rowHtml += '</td>';
            rowHtml += '</tr>';

            $('#addCalendarModal table tbody .errorRow').fadeOut('fast');
            $('#addCalendarModal  table tbody').append(rowHtml);

            var maskOptionsNew = {
                mask: '00:00'
            };
            $('.timeMask').each(function(){
                var mask = IMask(this, maskOptions);
            });
            calculateWeekTotal('#editCalendarModal');
        }else{
            $('#addCalendarModal #patternRow_'+dayId).remove();
            if($('#addCalendarModal table tbody .patternRow').length == 0){
                $('#addCalendarModal table tbody .errorRow').fadeIn('fast');
            }
            calculateWeekTotal('#editCalendarModal');
        }

        $('#addCalendarModal table .patternRow').sort((a,b) => $(a).data('order') - $(b).data('order')).appendTo('#addCalendarModal table tbody');
    });

    $('#addCalendarModal').on('keyup change paste', '.startTime, .endTime, .paidBr, .unpaidBr', function (e) {
        var startTime = $(this).closest('tr.patternRow').find('.startTime').val();
        var endTime = $(this).closest('tr.patternRow').find('.endTime').val();
        var unPaidBreak = $(this).closest('tr.patternRow').find('.unpaidBr').val();
        var paidBreak = $(this).closest('tr.patternRow').find('.paidBr').val();

        if (startTime.length == 5 && endTime.length == 5 && unPaidBreak.length == 5) {
            var unPaidBreak = (unPaidBreak != '') ? convert_time_to_second(unPaidBreak) : 0;
            
            var workHourInSecond = (new Date(get_current_date() + " " + endTime + ':00').getTime() - new Date(get_current_date() + " " + startTime + ':00').getTime()) / 1000;
            
            var totalWorkHourInSecond = (unPaidBreak > 0 ? (workHourInSecond) - (parseInt(unPaidBreak, 10) * 60) : workHourInSecond);
            var totalWorkHour = totalWorkHourInSecond / 60;
            totalWorkHour = hour_minute_formate(totalWorkHour);
            $(this).closest('tr.patternRow').find('.rowTotal').val(totalWorkHour);
        }else{
            $(this).closest('tr.patternRow').find('.rowTotal').val('');
        }

        calculateWeekTotal('#addCalendarModal')
    });

    function calculateWeekTotal(modalId){
        var contractedHourStr = $(modalId+' input[name="contracted_hour"]').val();
        var contractedHour = 0;
        if(contractedHourStr != ''){
            var hourArr = contractedHourStr.split(':');
            contractedHour = (parseInt(hourArr[0], 10) * 60) + parseInt(hourArr[1], 10);
        }
        var weekTotal = 0;
        if($(modalId+' .rowTotal').length > 0){
            $(modalId+' .rowTotal').each(function (e) {
                var daytotal = $(this).val();
                if (daytotal != '') {
                    var time = daytotal.split(':');
                    var timeInMin = (parseInt(time[0], 10) * 60) + parseInt(time[1], 10);
                    weekTotal += timeInMin;
                }
            });
            if(contractedHour < weekTotal){
                $(modalId+' tfoot .weekTotal').val('');
                $(modalId+' form .overAlert').remove();
                $(modalId+' form .modal-content').prepend('<div class="alert overAlert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Week total hour can not be grater than contracted hour.</div>')
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $(modalId+' form .overAlert').remove();
                }, 5000)
            }else{
                $(modalId+' tfoot .weekTotal').val(hour_minute_formate(weekTotal));
            }
        }else{
            $(modalId+' tfoot .weekTotal').val('');
        }
    }

    function convert_time_to_second(minSec) {
        var time = minSec.split(':');
        var timeInSec = (parseInt(time[0], 10) * 60) + parseInt(time[1], 10);
        return  timeInSec;
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

    
    $('#addCalendarForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addCalendarForm');
    
        document.querySelector('#saveEWPD').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEWPD svg").style.cssText ="display: inline-block;";

        let errs = 0;
        $('#addCalendarForm .rowTotal').each(function(){
            if($(this).val() == '' || $(this).val() == '00:00'){
                errs += 1;
            }
        })
        if($('#addCalendarForm .weekTotal').val() == ''){
            errs += 1;
        }
        if($('#addCalendarForm .weekDayStat:checked').length == 0){
            errs += 1;
        }

        if(errs > 0){
            document.querySelector('#saveEWPD').removeAttribute('disabled');
            document.querySelector("#saveEWPD svg").style.cssText = "display: none;";

            $('#addCalendarForm .overAlert').remove();
            $('#addCalendarForm .modal-content').prepend('<div class="alert overAlert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Validation error found. Please fill out the form correctly.</div>')
        
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(function(){
                $('#addCalendarForm .overAlert').remove();
            }, 5000);
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('employee.pattern.details.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveEWPD').removeAttribute('disabled');
                document.querySelector("#saveEWPD svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addCalendarModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Employee\'s working pattern details successfully inserted.');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });   
                    
                    setTimeout(function(){
                        successModal.hide();
                    }, 5000)
                }
                employeePatternListTable.init();
            }).catch(error => {
                document.querySelector('#saveEWPD').removeAttribute('disabled');
                document.querySelector("#saveEWPD svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html( "Oops!" );
                            $("#warningModal .warningModalDesc").html('Something went wrong. Please try latter or contact with the administrator');
                        });   
                    
                        setTimeout(function(){
                            warningModal.hide();
                        }, 5000)
                        console.log('error');
                    }
                }
            });
        }
    });

    $('#employeePatternListTable').on('click', '.editPatternDayInfo', function(){
        var employeeWorkingPatternId = $(this).attr('data-id');
        var contractedHour = $(this).attr('data-contracted');

        axios({
            method: "post",
            url: route("employee.pattern.details.edit"),
            data: {employeeWorkingPatternId: employeeWorkingPatternId},
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;

                if(dataset.days.length > 0){
                    for (let i = 0; i < dataset.days.length; i++) {
                        var theDay = dataset.days[i];
                        $('#editCalendarModal #edit_weekDays_'+theDay).prop('checked', true);
                    }
                    if(dataset.html != ''){
                        $('#edit_staff_pay_info_table .errorRow').fadeOut('fast');
                        $('#edit_staff_pay_info_table tbody').append(dataset.html);

                        var maskOptionsNew = {
                            mask: '00:00'
                        };
                        $('.timeMask').each(function(){
                            var mask = IMask(this, maskOptions);
                        });
                    }else{
                        $('#edit_staff_pay_info_table .errorRow').fadeIn('fast', function(){
                            $('span', this).html('Rows not found!');
                        });
                    }
                    if(dataset.weektotal == '' || dataset.weektotal == '00:00'){
                        $('#editCalendarModal tfoot .weekTotal').val('');
                    }else{
                        $('#editCalendarModal tfoot .weekTotal').val(dataset.weektotal);
                    }
                }else{
                    $('#edit_staff_pay_info_table .errorRow').fadeIn('fast', function(){
                        $('span', this).html('Rows not found!');
                    });
                    $('#editCalendarModal tfoot .weekTotal').val('');
                }

                $('#editCalendarModal input[name="employee_working_pattern_id"]').val(employeeWorkingPatternId);
                $('#editCalendarModal input[name="contracted_hour"]').val(contractedHour);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editCalendarModal .weekDayStat').on('change', function(){
        var $theInput = $(this);
        var dayId = $theInput.val();
        var days = {'1' : 'Mon', '2' : 'Tue', '3' : 'Wed', '4' : 'Thu', '5' : 'Fir', '6' : 'Sat', '7' : 'Sun'};

        if($theInput.prop('checked')){
            var rowHtml = '';
            rowHtml += '<tr data-order="'+dayId+'" class="patternRow patternRow_'+dayId+'" id="patternRow_'+dayId+'">';
                rowHtml += '<td>';
                    rowHtml += days[dayId];
                    rowHtml += '<input type="hidden" value="'+dayId+'" name="pattern['+dayId+'][day]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask startTime" minlength="5" maxlength="5" name="pattern['+dayId+'][start]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask endTime" minlength="5" maxlength="5" name="pattern['+dayId+'][end]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask paidBr" minlength="5" maxlength="5" name="pattern['+dayId+'][paid_br]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask unpaidBr" minlength="5" maxlength="5" name="pattern['+dayId+'][unpaid_br]"/>';
                rowHtml += '</td>';
                rowHtml += '<td>';
                    rowHtml += '<input type="text" placeholder="00:00" class="form-control w-full timeMask rowTotal" minlength="5" readonly maxlength="5" name="pattern['+dayId+'][total]"/>';
                rowHtml += '</td>';
            rowHtml += '</tr>';

            $('#editCalendarModal table tbody .errorRow').fadeOut('fast');
            $('#editCalendarModal  table tbody').append(rowHtml);

            var maskOptionsNew = {
                mask: '00:00'
            };
            $('.timeMask').each(function(){
                var mask = IMask(this, maskOptions);
            });
            calculateWeekTotal('#editCalendarModal');
        }else{
            $('#editCalendarModal #patternRow_'+dayId).remove();
            if($('#editCalendarModal table tbody .patternRow').length == 0){
                $('#editCalendarModal table tbody .errorRow').fadeIn('fast');
            }
            calculateWeekTotal('#editCalendarModal');
        }

        $('#editCalendarModal table .patternRow').sort((a,b) => $(a).data('order') - $(b).data('order')).appendTo('#editCalendarModal table tbody');
    });

    $('#editCalendarModal').on('keyup change paste', '.startTime, .endTime, .paidBr, .unpaidBr', function (e) {
        var startTime = $(this).closest('tr.patternRow').find('.startTime').val();
        var endTime = $(this).closest('tr.patternRow').find('.endTime').val();
        var unPaidBreak = $(this).closest('tr.patternRow').find('.unpaidBr').val();
        var paidBreak = $(this).closest('tr.patternRow').find('.paidBr').val();

        if (startTime.length == 5 && endTime.length == 5 && unPaidBreak.length == 5) {
            var unPaidBreak = (unPaidBreak != '') ? convert_time_to_second(unPaidBreak) : 0;
            
            var workHourInSecond = (new Date(get_current_date() + " " + endTime + ':00').getTime() - new Date(get_current_date() + " " + startTime + ':00').getTime()) / 1000;
            
            var totalWorkHourInSecond = (unPaidBreak > 0 ? (workHourInSecond) - (parseInt(unPaidBreak, 10) * 60) : workHourInSecond);
            var totalWorkHour = totalWorkHourInSecond / 60;
            totalWorkHour = hour_minute_formate(totalWorkHour);
            $(this).closest('tr.patternRow').find('.rowTotal').val(totalWorkHour);
        }else{
            $(this).closest('tr.patternRow').find('.rowTotal').val('');
        }

        calculateWeekTotal('#editCalendarModal');
    });

    $('#editCalendarForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editCalendarForm');
    
        document.querySelector('#updateEWPD').setAttribute('disabled', 'disabled');
        document.querySelector("#updateEWPD svg").style.cssText ="display: inline-block;";

        let errs = 0;
        $('#editCalendarForm .rowTotal').each(function(){
            if($(this).val() == '' || $(this).val() == '00:00'){
                errs += 1;
            }
        })
        if($('#editCalendarForm .weekTotal').val() == ''){
            errs += 1;
        }
        if($('#editCalendarForm .weekDayStat:checked').length == 0){
            errs += 1;
        }

        if(errs > 0){
            document.querySelector('#updateEWPD').removeAttribute('disabled');
            document.querySelector("#updateEWPD svg").style.cssText = "display: none;";

            $('#editCalendarForm .overAlert').remove();
            $('#editCalendarForm .modal-content').prepend('<div class="alert overAlert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Validation error found. Please fill out the form correctly.</div>')
        
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(function(){
                $('#editCalendarForm .overAlert').remove();
            }, 5000);
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('employee.pattern.details.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateEWPD').removeAttribute('disabled');
                document.querySelector("#updateEWPD svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    editCalendarModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Employee\'s working pattern details successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });   
                    
                    setTimeout(function(){
                        successModal.hide();
                    }, 5000)
                }
                employeePatternListTable.init();
            }).catch(error => {
                document.querySelector('#updateEWPD').removeAttribute('disabled');
                document.querySelector("#updateEWPD svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html( "Oops!" );
                            $("#warningModal .warningModalDesc").html('Something went wrong. Please try latter or contact with the administrator');
                        });   
                    
                        setTimeout(function(){
                            warningModal.hide();
                        }, 5000)
                        console.log('error');
                    }
                }
            });
        }
    });

})();