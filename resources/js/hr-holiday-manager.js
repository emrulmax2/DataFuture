import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const refreshHolidayIcons = () => {
    createIcons({
        icons,
        "stroke-width": 1.5,
        nameAttr: "data-lucide",
    });
};

const escapeHtml = (value) => String(value ?? "").replace(/[&<>"'`=\/]/g, (char) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
    "`": "&#096;",
    "=": "&#061;",
    "/": "&#047;",
}[char]));

const leaveInitials = (name) => {
    const clean = String(name || "London Churchill").replace(/^(Mr|Mrs|Ms|Miss|Dr)\.?\s+/i, "").trim();
    const parts = clean.split(/\s+/).filter(Boolean);
    const first = parts[0] || "L";
    const last = parts.length > 1 ? parts[parts.length - 1] : "C";

    return `${first.charAt(0)}${last.charAt(0)}`.toUpperCase();
};

const leavePalette = (seed) => {
    const palettes = [
        ["#7a4fa3", "#fff"],
        ["#137a70", "#fff"],
        ["#2f8f5b", "#fff"],
        ["#c94f7c", "#fff"],
        ["#b5602f", "#fff"],
        ["#2f5fa1", "#fff"],
        ["#a13f6b", "#fff"],
        ["#4a7a2f", "#fff"],
        ["#b3261e", "#fff"],
    ];
    const value = String(seed || "employee");
    let hash = 0;

    for (let index = 0; index < value.length; index += 1) {
        hash = (hash * 31 + value.charCodeAt(index)) >>> 0;
    }

    return palettes[hash % palettes.length];
};

const renderAvatar = (data) => {
    const photoUrl = String(data.photo_url || "");

    if (photoUrl !== "") {
        return `<span class="hm-avatar"><img src="${escapeHtml(photoUrl)}" alt="${escapeHtml(data.name)}"></span>`;
    }

    const palette = leavePalette(data.name);

    return `<span class="hm-avatar" style="background:${palette[0]};color:${palette[1]};">${escapeHtml(leaveInitials(data.name))}</span>`;
};

const renderPersonCell = (data) => `<a href="${escapeHtml(data.url)}" class="hm-person-cell">
    ${renderAvatar(data)}
    <span class="min-w-0">
        <span class="hm-person-name">${escapeHtml(data.name)}</span>
        <span class="hm-person-role">${escapeHtml(data.designation || "Unknown")}</span>
    </span>
</a>`;

const statusText = (data, type) => {
    if (type === "approved") {
        return "Approved";
    }

    if (type === "rejected") {
        return "Rejected";
    }

    return String(data.status || "Approval").replace(/^Request for approval\s*/i, "Approval · ");
};

const renderStatusCell = (data, type) => {
    const shield = Number(data.supervised) === 1 && type === "pending"
        ? '<i data-lucide="shield-check" class="hm-supervised w-4 h-4"></i>'
        : "";

    return `<span class="hm-status-cell">
        <span class="hm-status-chip">${shield}${escapeHtml(statusText(data, type))}</span>
        <span class="hm-status-time">${escapeHtml(data.hour)}</span>
    </span>`;
};

const renderDateCell = (value) => `<span class="hm-date-cell">${escapeHtml(value)}</span>`;

const renderTitleCell = (value) => {
    const label = String(value || "").trim();

    if (label === "") {
        return '<span class="hm-title-cell is-empty">&mdash;</span>';
    }

    return `<span class="hm-title-cell${label.length > 22 ? " is-long" : ""}">${escapeHtml(label)}</span>`;
};

const renderMetaCell = (data, type) => {
    if (type === "pending") {
        return `<span class="hm-meta-cell">
            <span class="hm-meta-main">${escapeHtml(data.created_at)}</span>
            <span class="hm-meta-sub">${escapeHtml(data.created_relative)}</span>
        </span>`;
    }

    return `<span class="hm-meta-cell">
        <span class="hm-meta-main">${escapeHtml(data.approved_by || "Unknown")}</span>
        <span class="hm-meta-sub">${escapeHtml(data.approved_at)}</span>
    </span>`;
};

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
            columnDefaults: {
                headerSortTristate: true,
            },
            placeholder: "No leave records found",
            columns: [
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    widthGrow: 1.5,
                    minWidth: 245,
                    formatter(cell, formatterParams) { 
                        return renderPersonCell(cell.getData());
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    widthGrow: 1.35,
                    minWidth: 230,
                    formatter(cell, formatterParams) { 
                        return renderStatusCell(cell.getData(), type);
                    }
                },
                {
                    title: "Start Date",
                    field: "start_date",
                    headerHozAlign: "left",
                    widthGrow: 1.05,
                    minWidth: 150,
                    formatter(cell, formatterParams) {
                        return renderDateCell(cell.getValue());
                    }
                },
                {
                    title: "End Date",
                    field: "end_date",
                    headerHozAlign: "left",
                    widthGrow: 1.05,
                    minWidth: 150,
                    formatter(cell, formatterParams) {
                        return renderDateCell(cell.getValue());
                    }
                },
                {
                    title: "Title",
                    field: "title",
                    headerHozAlign: "left",
                    widthGrow: 1.15,
                    minWidth: 165,
                    formatter(cell, formatterParams) {
                        return renderTitleCell(cell.getValue());
                    }
                },
                {
                    title: type == 'pending' ? "Request Made" : (type == 'approved' ? "Approved By" : "Rejected By"),
                    field: type == 'pending' ? "created_at" : "approved_by",
                    headerHozAlign: "left",
                    widthGrow: 1.2,
                    minWidth: 180,
                    formatter(cell, formatterParams) { 
                        return renderMetaCell(cell.getData(), type);
                    }
                },
            ],
            renderComplete() {
                refreshHolidayIcons();
            },
            rowClick:function(e, row){
                var type = row.getData().type;
                var id = row.getData().id;
                var yearid = row.getData().yearid;
                var can_auth = row.getData().can_auth;

                if(can_auth == 1){
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
                                $('#empNewLeaveRequestModal [name="employee_leave_id"]').val(id);
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
            }
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            refreshHolidayIcons();
        });
    };
    return {
        init: function (yearid, type) {
            _tableGen(yearid, type);
        },
    };
})();


(function(){
    refreshHolidayIcons();

    const initialiseYearTables = (yearid) => {
        ['pending', 'approved', 'rejected'].forEach((type) => {
            const tableSelector = '#leaveListTable-'+type+'-'+yearid;
            const $table = $(tableSelector);

            if ($table.length > 0 && !$table.data('holiday-loaded')) {
                manageHolidayListTable.init(yearid, type);
                $table.data('holiday-loaded', 1);
            }
        });
    };

    if($('#employeeHolidayAccordion-0').length > 0){
        var yearid = $('#employeeHolidayAccordion-0 .holidayCollapseBtns').attr('data-year');
        if(!$('#employeeHolidayAccordion-0 .holidayCollapseBtns').hasClass('collapsed')){
            initialiseYearTables(yearid);
        }
    }

    $('.holidayCollapseBtns').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var yearid = $theBtn.attr('data-year');

        if($theBtn.hasClass('collapsed')){
            initialiseYearTables(yearid);
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
            
            refreshHolidayIcons();
            
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
