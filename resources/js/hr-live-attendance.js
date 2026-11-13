import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

import dayjs from "dayjs";
import Litepicker from "litepicker";
import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";

import tippy, { roundArrow } from "tippy.js";


(function(){
    const senMailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#senMailModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#successModal .successCloser').on('click', function(e){
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    let mailEditor;
    if($("#mailEditor").length > 0){
        const el = document.getElementById('mailEditor');
        ClassicEditor.create(el).then((editor) => {
            mailEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    let tomOptions = {
        dropdownParent: 'body',
        dropdownClass: 'ts-dropdown lcc-tom-float',
        
        plugins: {
            dropdown_input: {},
            remove_button: {
                title: "Remove this item",
            },
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    var ccEmail = new TomSelect('#cc_email', tomOptions);

    function initLiveAttendanceRows() {
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });

        setTimeout(() => {
            $('#liveAttendanceTable tbody').find('.hr-live-tip-trigger').each(function(){
                let contentEl = null;

                // tippy moves the content node out of the document and into its own popper,
                // so take it back off any existing instance before that instance is destroyed.
                if (this._tippy) {
                    contentEl = (this._tippy.props.content instanceof Element ? this._tippy.props.content : null);
                    this._tippy.destroy();
                }

                if (contentEl === null && $(this).data("tooltip-content") !== undefined) {
                    contentEl = $($(this).data("tooltip-content"))[0] || null;
                }

                if (contentEl === null) {
                    return;
                }

                $(this).removeAttr("title");

                tippy(this, {
                    content: contentEl,
                    arrow: roundArrow,
                    animation: "shift-away",
                    theme: 'light hr-live',
                    placement: 'left',
                    interactive: false,
                    maxWidth: 300,
                });
            })
        }, 10);
    }

    let activeStatusFilter = '';

    function applyLiveStatusFilter() {
        const $rows = $('#liveAttendanceTable tbody tr.hr-live-row');
        let shown = 0;

        let stripe = 0;
        let stripeDept = null;
        $rows.each(function(){
            const visible = (activeStatusFilter === '' || $(this).attr('data-status-group') === activeStatusFilter);

            $(this).toggleClass('hr-live-row--filtered-out', !visible)
                   .removeClass('hr-live-row--even hr-live-row--odd');

            if (visible) {
                // grouped by department: the stripes start again under each heading
                const rowDept = (this.getAttribute('data-department') || '').trim();
                if (groupByDept && rowDept !== stripeDept) {
                    stripe = 0;
                    stripeDept = rowDept;
                }
                $(this).addClass(stripe % 2 === 0 ? 'hr-live-row--even' : 'hr-live-row--odd');
                stripe++;
                shown++;
            }
        });

        $('#liveAttendanceTable tbody tr.hr-live-filter-empty').remove();
        if ($rows.length > 0 && shown === 0) {
            $('#liveAttendanceTable tbody').not('.hr-live-group').first().append('<tr class="hr-live-empty-row hr-live-filter-empty"><td colspan="4">No staff match the current filters.</td></tr>');
        }

        $('.hr-live-stat').each(function(){
            const isActive = (($(this).attr('data-live-filter') || '') === activeStatusFilter);

            $(this).toggleClass('hr-live-stat--active', isActive)
                   .toggleClass('hr-live-stat--muted', activeStatusFilter !== '' && !isActive)
                   .attr('aria-pressed', isActive ? 'true' : 'false');
        });

        if ($rows.length > 0) {
            $('#liveShownCount').text(shown);
            $('#liveCountAll').text($rows.length);
        }

        updateDepartmentHeadings();
    }

    /* By Department (user, 11 Nov 2026): one box per department — its own <tbody> with a header button
       that opens / closes it, the column headings, then its staff (still A–Z by name). Departments A–Z,
       "No department" last; a department with no row showing (e.g. under a status chip) is hidden. Only
       the rows already on the page are moved (nothing is reloaded); turned off, they go back to the one
       list in their first order. The choice is remembered in this browser; every box starts open. */
    const GROUP_BY_DEPT_KEY = 'hr-live-group-by-dept';
    // departments closed by the user (each box open by default, any can be closed);
    // kept while the list reloads (date, department, search), all open again on a new page load
    const collapsedDepts = new Set();
    const GROUP_ICON = '<span class="hr-live-group-toggle__icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg></span>';
    const GROUP_CHEVRON = '<span class="hr-live-group-toggle__chevron" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></span>';
    let groupByDept = false;
    try { groupByDept = window.localStorage.getItem(GROUP_BY_DEPT_KEY) === '1'; } catch (e) { groupByDept = false; }

    function applyDepartmentGrouping() {
        const $table = $('#liveAttendanceTable');
        const $main = $table.children('tbody').not('.hr-live-group').first(); // the list's own tbody
        const $rows = $table.find('tbody tr.hr-live-row');
        const deptOf = (el) => (el.getAttribute('data-department') || '').trim();
        const orderOf = (el) => parseInt(el.getAttribute('data-order'), 10) || 0;

        // the order the list came in (A–Z by name), kept to go back to when grouping is turned off
        $rows.each(function(i){
            if (this.getAttribute('data-order') === null) {
                this.setAttribute('data-order', i);
            }
        });

        // every row back into the list's own tbody, in that order; then the boxes go
        const list = $rows.get().sort((a, b) => orderOf(a) - orderOf(b));
        list.forEach((el) => $main.append(el));
        $table.children('tbody.hr-live-group').remove();

        $table.toggleClass('hr-live-table--grouped', groupByDept);
        $table.closest('.hr-live-table-card').toggleClass('hr-live-table-card--grouped', groupByDept);
        $('#liveGroupByDept').toggleClass('hr-live-tool-btn--active', groupByDept).attr('aria-pressed', groupByDept ? 'true' : 'false');
        if (!groupByDept) {
            return;
        }

        const grouped = list.slice().sort((a, b) => {
            const da = deptOf(a);
            const db = deptOf(b);
            if (da !== db) {
                if (da === '') return 1;
                if (db === '') return -1;
                return da.localeCompare(db, undefined, { sensitivity: 'base' });
            }
            return orderOf(a) - orderOf(b);
        });
        // the column headings, as the table's own header shows them
        const $headCells = $table.children('thead').find('tr').first().children();

        let $box = null;
        let current = null;
        grouped.forEach((el) => {
            if ($box === null || deptOf(el) !== current) {
                current = deptOf(el);
                $box = $('<tbody class="hr-live-group"></tbody>').attr('data-department', current);
                const $heading = $('<tr class="hr-live-group-row"><td colspan="4"><button type="button" class="hr-live-group-toggle" aria-expanded="true">' + GROUP_ICON + '<span class="hr-live-group-row__name"></span><span class="hr-live-group-row__count"></span>' + GROUP_CHEVRON + '</button></td></tr>');
                $heading.find('.hr-live-group-row__name').text(current !== '' ? current : 'No department');
                $box.append($heading).append($('<tr class="hr-live-group-colhead"></tr>').append($headCells.clone()));
                $table.append($box);
            }
            $box.append(el);
        });

        applyDepartmentCollapse();
    }

    // a closed box keeps its header (with its count); its column headings and staff are hidden
    function applyDepartmentCollapse() {
        $('#liveAttendanceTable tbody.hr-live-group').each(function(){
            const closed = collapsedDepts.has($(this).attr('data-department') || '');
            $(this).toggleClass('hr-live-group--collapsed', closed)
                   .find('.hr-live-group-toggle').attr('aria-expanded', closed ? 'false' : 'true')
                   .attr('title', (closed ? 'Open ' : 'Close ') + ($(this).find('.hr-live-group-row__name').text() || 'this department'));
        });
    }

    $('#liveAttendanceTable').on('click', '.hr-live-group-toggle', function(){
        const dept = $(this).closest('tbody.hr-live-group').attr('data-department') || '';
        if (collapsedDepts.has(dept)) {
            collapsedDepts.delete(dept);
        } else {
            collapsedDepts.add(dept);
        }
        applyDepartmentCollapse();
    });

    // each box: how many of its staff are showing (status chips); none → the box is hidden
    function updateDepartmentHeadings() {
        $('#liveAttendanceTable tbody.hr-live-group').each(function(){
            const showing = $(this).find('tr.hr-live-row').not('.hr-live-row--filtered-out').length;

            $(this).toggleClass('hr-live-group--empty', showing === 0)
                   .find('.hr-live-group-row__count').text(showing + ' staff');
        });
    }

    $('#liveGroupByDept').on('click', function(){
        groupByDept = !groupByDept;
        try { window.localStorage.setItem(GROUP_BY_DEPT_KEY, groupByDept ? '1' : '0'); } catch (e) { /* not remembered */ }
        applyDepartmentGrouping();
        applyLiveStatusFilter();
    });

    $('.hr-live-stats').on('click', '.hr-live-stat', function(){
        const filterKey = $(this).attr('data-live-filter') || '';

        activeStatusFilter = (activeStatusFilter === filterKey ? '' : filterKey);
        applyLiveStatusFilter();
    });

    function updateLiveAttendanceMeta(meta) {
        if (!meta) {
            return;
        }

        $('#liveCountWorking').text(meta.working ?? 0);
        $('#liveCountAbsent').text(meta.absent ?? 0);
        $('#liveCountOff').text(meta.off ?? 0);
        $('#liveCountLeave').text(meta.leave ?? 0);
        $('#liveCountUnscheduled').text(meta.unscheduled ?? 0);
        $('#liveCountAll').text(meta.shownCount ?? 0);
        $('#liveShownCount').text(meta.shownCount ?? 0);
        $('#liveTotalCount').text(meta.totalCount ?? 0);
    }

    function renderLiveAttendanceResponse(res) {
        $('.theDateHolder').html(res.the_date);
        $('#liveAttendanceTable tbody.hr-live-group').remove(); // By Department boxes (rebuilt below)
        $('#liveAttendanceTable tbody').html(res.htm);
        updateLiveAttendanceMeta(res.meta);
        initLiveAttendanceRows();
        applyDepartmentGrouping();
        applyLiveStatusFilter();
    }

    const senMailModalEl = document.getElementById('senMailModal')
    senMailModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#senMailModal .acc__input-error').html('');
        $('#senMailModal .modal-body input#sendMailsDocument').val('');
        $('#senMailModal .modal-body input, #senMailModal .modal-body select').val('');
        $('#senMailModal [name="to_email"]').val('').removeAttr('readonly');
        $('#senMailModal .sendMailsDocumentNames').html('').fadeOut();
        mailEditor.setData('');
        ccEmail.clear(true);
    });

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
    initLiveAttendanceRows();
    applyDepartmentGrouping();
    applyLiveStatusFilter();

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
                    renderLiveAttendanceResponse(res);
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
                    renderLiveAttendanceResponse(res);
                }
            }).catch(error => {
                $('.leaveTableLoader').removeClass('active');
                if (error.response) {
                    console.log('error');
                }
            });
        }
    });

    let liveAjaxRequest = null;
    $('#liveAttendanceEmp').on('keyup', function(e){
        var departement = $('#liveAttendanceDept').val();
        var date = $('#liveAttendanceDate').val();
        var emp = $('#liveAttendanceEmp').val();

        $('.leaveTableLoader').addClass('active');
        liveAjaxRequest = $.ajax({
            type: 'POST',
            data: {departement : departement, date : date, emp : emp},
            url: route('hr.portal.live.attedance.ajax'),
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            beforeSend : function()    {           
                if(liveAjaxRequest != null) {
                    liveAjaxRequest.abort();
                }
                $('.leaveTableLoader').addClass('active');
            },
            success: function(data) {
                $('.leaveTableLoader').removeClass('active');

                let res = data.res;
                renderLiveAttendanceResponse(res);
            },
            error:function(e){
                $('.leaveTableLoader').removeClass('active');
                console.log('Error');
            }
        });

        /*axios({
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

                setTimeout(() => {
                    $('#liveAttendanceTable tbody').find('.tooltip').each(function(){
                        let thTippyOptions = {
                            dropdownParent: 'body',
                            dropdownClass: 'ts-dropdown lcc-tom-float',
                            
                            content: $(this).attr("title"),
                        };
                        if ($(this).data("tooltip-content") !== undefined) {
                            thTippyOptions.content = $($(this).data("tooltip-content"))[0];
                        }
                
                        $(this).removeAttr("title");
                
                        tippy(this, {
                            arrow: roundArrow,
                            animation: "shift-away",
                            theme: 'light',
                            placement: 'top-start',
                            ...thTippyOptions,
                        });
                    })
                }, 10);
            }
        }).catch(error => {
            $('.leaveTableLoader').removeClass('active');
            if (error.response) {
                console.log('error');
            }
        });*/
    });

    $('#senMailModal #sendMailsDocument').on('change', function(){
        var inputs = document.getElementById('sendMailsDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>'+name+'</div>';
        }

        $('#senMailModal .sendMailsDocumentNames').fadeIn().html(html);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#liveAttendanceTable').on('click', '.sendMailBtn', function(e){
        let $theLink = $(this);
        let employee_id = $theLink.attr('data-id');

        axios({
            method: "post",
            url: route('hr.portal.live.get.employee.mail'),
            data: {employee_id : employee_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                var emails = response.data.emails;
                if(emails != ''){
                    $('#senMailModal [name="to_email"]').val(emails).attr('readonly', 'readonly');
                }else{
                    $('#senMailModal [name="to_email"]').val('').removeAttr('readonly');
                }

                $('#senMailModal [name="employee_id"]').val(employee_id);
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
        
    });

    $('#senMailForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('senMailForm');
    
        document.querySelector('#sentMailBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sentMailBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#senMailForm input#sendMailsDocument')[0].files[0]); 
        form_data.append("mail_body", mailEditor.getData());
        axios({
            method: "post",
            url: route('hr.portal.live.attedance.sent.mail'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sentMailBtn').removeAttribute('disabled');
            document.querySelector("#sentMailBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                senMailModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Mail successfylly sent to selected employee.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#sentMailBtn').removeAttribute('disabled');
            document.querySelector("#sentMailBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#senMailForm .${key}`).addClass('border-danger');
                        $(`#senMailForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})();
