
import TomSelect from "tom-select";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

import dayjs from "dayjs";
import Litepicker from "litepicker";

import IMask from 'imask';


("use strict");
/* The academic-qualification list.
 *
 * A plain table rather than a Tabulator grid: the design asks for one, and an
 * applicant lists a handful of qualifications, so remote paging, sorting and
 * the export toolbar were machinery with nothing to do. The module keeps its
 * `init()` so every existing call site - the Yes/No toggle, the add, edit and
 * delete handlers - is unchanged, and it reads the same endpoint, asking for
 * every row at once (`size: true`).
 */
var educationQualTable = (function () {
    var _esc = function (v) {
        if (v === null || v === undefined || v === '') return '';
        return $('<i></i>').text(v).html();
    };
    var _cell = function (v) {
        var s = _esc(v);
        return s === '' ? '&mdash;' : s;
    };

    var _render = function (rows) {
        var $host = $('#educationQualTable');
        var $wrap = $host.closest('.educationQualificationTableWrap');

        /* The design shows no empty table and no "no records" placeholder:
           with nothing to list there is only the Add button. */
        if (!rows || rows.length === 0) {
            $host.empty();
            $wrap.removeClass('has-rows').hide();
            return;
        }

        var html = '<table class="apd-table">'
            + '<thead><tr>'
            + '<th>Qualification</th><th>Awarding body</th><th>Subjects</th>'
            + '<th>Result</th><th>Awarded</th><th class="apd-table__act"></th>'
            + '</tr></thead><tbody>';

        $.each(rows, function (i, r) {
            var acts = r.deleted_at
                ? '<button type="button" data-id="' + _esc(r.id) + '" class="restore_btn apd-linkbtn">Restore</button>'
                : '<button type="button" data-id="' + _esc(r.id) + '" data-tw-toggle="modal" data-tw-target="#editQualificationModal" class="edit_btn apd-linkbtn">Edit</button>'
                  + ' <span class="apd-linkbtn__sep">&middot;</span> '
                  + '<button type="button" data-id="' + _esc(r.id) + '" class="delete_btn apd-linkbtn apd-linkbtn--danger">Remove</button>';

            html += '<tr>'
                + '<td>' + _cell(r.highest_academic) + '</td>'
                + '<td>' + _cell(r.awarding_body) + '</td>'
                + '<td>' + _cell(r.subjects) + '</td>'
                + '<td>' + _cell(r.result) + '</td>'
                + '<td>' + _cell(r.degree_award_date) + '</td>'
                + '<td class="apd-table__act">' + acts + '</td>'
                + '</tr>';
        });

        $host.html(html + '</tbody></table>');
        $wrap.addClass('has-rows').show();
    };

    var _tableGen = function () {
        var $host = $('#educationQualTable');
        if ($host.length === 0) return;

        var applicantId = $host.attr('data-applicant') || '0';

        $.ajax({
            url: route('applicant.qualification.list'),
            type: 'GET',
            data: { applicantId: applicantId, status: 1, size: true },
            success: function (res) { _render(res && res.data ? res.data : []); },
            error: function () { _render([]); },
        });
    };

    return {
        init: function () {
            _tableGen();
        },
    };
})();
/* The employment-history list.
 *
 * The plain table the design asks for, in place of the Tabulator grid - same
 * reasoning as the qualification list: a handful of rows never needed remote
 * paging, sorting or an export toolbar. `init()` is unchanged, so the
 * employment-status toggle and the add, edit and delete handlers all still
 * call it, and it reads the same endpoint with every row at once.
 */
var employmentHistoryTable = (function () {
    var _esc = function (v) {
        if (v === null || v === undefined || v === '') return '';
        return $('<i></i>').text(v).html();
    };
    var _cell = function (v) {
        var s = _esc(v);
        return s === '' ? '&mdash;' : s;
    };

    var _render = function (rows) {
        var $host = $('#employmentHistoryTable');
        var $wrap = $host.closest('.educationEmploymentTableWrap');

        if (!rows || rows.length === 0) {
            $host.empty();
            $wrap.removeClass('has-rows').hide();
            return;
        }

        var html = '<table class="apd-table">'
            + '<thead><tr>'
            + '<th>Organisation</th><th>Position</th><th>From</th>'
            + '<th>To</th><th>Reference</th><th class="apd-table__act"></th>'
            + '</tr></thead><tbody>';

        $.each(rows, function (i, r) {
            /* The endpoint already substitutes "Continue" for a job with no end
               date; the design words it "Continuing". */
            var to = String(r.continuing) === '1' ? 'Continuing' : _cell(r.end_date);

            var acts = r.deleted_at
                ? '<button type="button" data-id="' + _esc(r.id) + '" class="restore_btn apd-linkbtn">Restore</button>'
                : '<button type="button" data-id="' + _esc(r.id) + '" data-tw-toggle="modal" data-tw-target="#editEmployementHistoryModal" class="edit_btn apd-linkbtn">Edit</button>'
                  + ' <span class="apd-linkbtn__sep">&middot;</span> '
                  + '<button type="button" data-id="' + _esc(r.id) + '" class="delete_btn apd-linkbtn apd-linkbtn--danger">Remove</button>';

            html += '<tr>'
                + '<td>' + _cell(r.company_name) + '</td>'
                + '<td>' + _cell(r.position) + '</td>'
                + '<td>' + _cell(r.start_date) + '</td>'
                + '<td>' + to + '</td>'
                + '<td>' + _cell(r.name) + '</td>'
                + '<td class="apd-table__act">' + acts + '</td>'
                + '</tr>';
        });

        $host.html(html + '</tbody></table>');
        $wrap.addClass('has-rows').show();
    };

    var _tableGen = function () {
        var $host = $('#employmentHistoryTable');
        if ($host.length === 0) return;

        $.ajax({
            url: route('applicant.employment.list'),
            type: 'GET',
            data: { applicantId: $host.attr('data-applicant') || '0', status: 1, size: true },
            success: function (res) { _render(res && res.data ? res.data : []); },
            error: function () { _render([]); },
        });
    };

    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    if($('#educationQualTable').length > 0){
        if($('#educationQualTable').hasClass('activeTable')){
            educationQualTable.init();
        }
    }
    if($('#employmentHistoryTable').length > 0){
        if($('#employmentHistoryTable').hasClass('activeTable')){
            employmentHistoryTable.init();
        }
    }


    const addQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addQualificationModal"));
    const editQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editQualificationModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));

    const addEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmployementHistoryModal"));
    const editEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmployementHistoryModal"));
            

    const addQualificationModalEl = document.getElementById('addQualificationModal')
    addQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addQualificationModal .acc__input-error').html('');
        $('#addQualificationModal .modal-body input').val('');
        $('#addQualificationModal .modal-body select').val('');
    });

    const editQualificationModalEl = document.getElementById('editQualificationModal')
    editQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editQualificationModal .acc__input-error').html('');
        $('#editQualificationModal .modal-body input').val('');
        $('#editQualificationModal .modal-body select').val('');
        $('#editQualificationModal .modal-footer input[name="id"]').val('0');
    });

    const addEmployementHistoryModalEl = document.getElementById('addEmployementHistoryModal')
    addEmployementHistoryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addEmployementHistoryModal .acc__input-error').html('');
        $('#addEmployementHistoryModal .modal-body input').val('').removeAttr('disabled');
        $('#addEmployementHistoryModal .modal-body input[type="checkbox"]').prop('checked', false);
        $('#addEmployementHistoryModal .modal-body select').val('');
        $('#addEmployementHistoryModal .addressWrap').fadeOut().removeClass('active').html('');
    });

    const editEmployementHistoryModalEl = document.getElementById('editEmployementHistoryModal')
    editEmployementHistoryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editEmployementHistoryModal .acc__input-error').html('');
        $('#editEmployementHistoryModal .modal-body input').val('').removeAttr('disabled');
        $('#editEmployementHistoryModal .modal-body input[type="checkbox"]').prop('checked', false);
        $('#editEmployementHistoryModal .modal-body select').val('');
        $('#editEmployementHistoryModal .addressWrap').fadeOut().removeClass('active').html('');
        $('#editEmployementHistoryModal .modal-footer input[name="id"]').val('0');
        $('#editEmployementHistoryModal .modal-footer input[name="ref_id"]').val('0');
    });

    let applicationDatepickerOpt = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        maxDate: new Date(),
        dropdowns: {
            minYear: 1900,
            maxYear: null,
            months: true,
            years: true,
        },
    };

    $('.applicationDatepicker').each(function(){
        new Litepicker({
            element: this,
            ...applicationDatepickerOpt,
        });
    });

    /* Month-only Litepicker for the employment dates (MM-YYYY, which is what
       the column holds). Litepicker has no month mode; as the attendance
       report does, the day grid is hidden and a twelve-button month grid is
       drawn in its place. The input keeps its 00-0000 mask, so typing still
       works, and the picker re-reads the box each time it opens so a value
       set by the edit flow is reflected. */
    ;(function(){
        var $fields = $('.applicationMonthpicker');
        if(!$fields.length) return;

        var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var parse = function(v){
            var m = String(v || '').trim().match(/^(\d{1,2})-(\d{4})$/);
            if(!m) return null;
            return {month: Math.min(Math.max(parseInt(m[1], 10), 1), 12) - 1, year: parseInt(m[2], 10)};
        };
        var fmt = function(p){ return String(p.month + 1).padStart(2, '0') + '-' + p.year; };

        $fields.each(function(){
            var input = this;
            var noFuture = input.hasAttribute('data-no-future');
            var today = new Date();

            var draw = function(picker){
                if(!picker.ui) return;
                picker.ui.classList.add('attendance-month-litepicker', 'apd-month-litepicker');
                var item = picker.ui.querySelector('.month-item');
                if(!item) return;

                var cal = picker.calendars && picker.calendars[0];
                var year = cal ? cal.getFullYear() : today.getFullYear();
                var sel = parse(input.value);

                var grid = item.querySelector('.attendance-month-grid');
                if(!grid){ grid = document.createElement('div'); grid.className = 'attendance-month-grid'; item.appendChild(grid); }
                grid.innerHTML = '';

                MONTHS.forEach(function(name, idx){
                    var b = document.createElement('button');
                    b.type = 'button'; b.className = 'attendance-month-option'; b.textContent = name;
                    if(sel && sel.month === idx && sel.year === year) b.classList.add('is-active');
                    var future = year > today.getFullYear() || (year === today.getFullYear() && idx > today.getMonth());
                    if(noFuture && future){
                        b.classList.add('is-disabled'); b.disabled = true;
                    }else{
                        b.addEventListener('click', function(){
                            picker.setDate(new Date(year, idx, 1));
                            input.value = fmt({month: idx, year: year});
                            /* native event: the IMask on the field listens for it */
                            input.dispatchEvent(new Event('input', {bubbles: true}));
                            picker.hide();
                        });
                    }
                    grid.appendChild(b);
                });

                var yearField = picker.ui.querySelector('.month-item-year');
                if(yearField && !yearField.dataset.apdMonthBound){
                    yearField.dataset.apdMonthBound = '1';
                    yearField.addEventListener('change', function(){
                        var y = parseInt(yearField.value, 10);
                        if(!isNaN(y)){ picker.gotoDate(new Date(y, 0, 1)); draw(picker); }
                    });
                }
            };

            new Litepicker({
                element: input,
                autoApply: true,
                singleMode: true,
                numberOfColumns: 1,
                numberOfMonths: 1,
                showWeekNumbers: false,
                format: 'MM-YYYY',
                switchingMonths: 12,
                maxDate: noFuture ? today : null,
                dropdowns: { minYear: 1950, maxYear: today.getFullYear() + 5, months: false, years: true },
                setup: function(picker){
                    picker.on('show', function(){
                        var sel = parse(input.value);
                        if(sel){
                            picker.setDate(new Date(sel.year, sel.month, 1));
                            picker.gotoDate(new Date(sel.year, sel.month, 1));
                        }else{
                            picker.clearSelection();
                            picker.gotoDate(today);
                        }
                        draw(picker);
                    });
                    picker.on('change:month', function(){ draw(picker); });
                    picker.on('change:year', function(){ draw(picker); });
                },
            });
        });
    })();

    let tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        dropdownParent: 'body',
        dropdownClass: 'ts-dropdown lcc-tom-float',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    var course_creation_id = new TomSelect('#course_creation_id', tomOptions);
    var venue_id = new TomSelect('#venue_id', tomOptions);
    var employment_status = new TomSelect('#employment_status', tomOptions);
    var student_loan = new TomSelect('#student_loan', tomOptions);

    $('.applicationLccTom').each(function(){
        new TomSelect(this, tomOptions);
    })

    if($('.applicationPhoneMask').length > 0){
        $('.applicationPhoneMask').each(function(){
            IMask(
                this, {
                  mask: '00000000000'
                }
            )
        })
    }

    // click on next button
    $('#is_applicant_agree').on('change', function(e){
        if($(this).prop('checked')){
            $('.form-wizard .wizard-fieldset.wizard-last-step .form-wizard-next-btn').removeAttr('disabled');
        }else{
            $('.form-wizard .wizard-fieldset.wizard-last-step .form-wizard-next-btn').attr('disabled', 'disabled');
        }
    });

    /* Clear a field's validation message the moment the applicant corrects it.
       The error handler writes each message into `.error-<field>` and reddens
       `.<field>`, both keyed off the input's name, so the same name is used to
       clear them. Delegated, so it covers fields inside panels that are shown
       later and rows added after load. `disability_id[]` reports as
       `disability_id`, hence the trailing-brackets trim. */
    const clearFieldValidation = function (el) {
        const $el = $(el);
        const $form = $el.closest('.wizard-step-form');
        if (!$form.length) return;

        let key = ($el.attr('name') || $el.attr('id') || '').replace(/\[\]$/, '');
        if (!key) return;

        $form.find('.error-' + key).html('');
        $form.find('.' + key).removeClass('border-danger');
        $el.removeClass('border-danger');
        // Tom Select keeps the original <select>; the red class sits on its wrapper.
        $el.closest('.ts-control').removeClass('border-danger');
    };

    $(document).on('input keyup change', '.wizard-step-form :input', function () {
        clearFieldValidation(this);
    });

    $('.form-wizard-next-btn').on('click', function () {
        var parentFieldset = $(this).parents('.wizard-fieldset');
        var parentForm = $(this).parents('.wizard-step-form');
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-steps .active');
        var next = $(this);
        let nextWizardStep = true;

        /* Form Submission Start*/
        var formID = parentForm.attr('id');
        const form = document.getElementById(formID);
    
        $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).attr('disabled', 'disabled');
        $('.form-wizard-next-btn svg', parentForm).fadeIn();

        let form_data = new FormData(form);
        let applicantId = $('[name="applicant_id"]', parentForm).val();
        let url, redURL;
        if(parentFieldset.index() == 2){
            url = route('applicant.application.store.course');
        }else if(parentFieldset.index() == 3){
            url = route('applicant.application.store.residency_and_criminal_conviction');
        }else if(parentFieldset.index() == 4){
            url = route('applicant.application.store.submission');
            redURL = $('input[name="url"]', parentForm).val();
        }else{
            url = route('applicant.application.store.personal');
        }

        $.ajax({
            method: 'POST',
            url: url,
            data: form_data,
            dataType: 'json',
            async: false,
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            success: function(res, textStatus, xhr){
                $('.acc__input-error', parentForm).html('');
                $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).removeAttr('disabled');
                $('.form-wizard-next-btn svg', parentForm).fadeOut(); 
                if(xhr.status == 200){
                    if(parentFieldset.index() == 1){
                        $(document.body).find('input[name="applicant_id"]').val(res.applicant_id);
                        $('#educationQualTable, #employmentHistoryTable').attr('data-applicant', res.applicant_id);
                        $('#varifiedReferral').attr('data-applicant-id', res.applicant_id);
                    } else if(parentFieldset.index() == 2){
                        $(document.body).find('input[name="applicant_id"]').val(res.applicant_id);
                    } else if(parentFieldset.index() == 3){
                        $('.reviewContentWrap').attr('data-review-id', res.applicant_id);
                    } else if(parentFieldset.index() == 4){
                        window.location.href = redURL;
                    }
                }
                nextWizardStep = true;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).removeAttr('disabled');
                $('.form-wizard-next-btn svg', parentForm).fadeOut();
                if(jqXHR.status == 422){
                    for (const [key, val] of Object.entries(jqXHR.responseJSON.errors)) {
                        $(`#${formID} .${key}`).addClass('border-danger');
                        $(`#${formID}  .error-${key}`).html(val);
                    }
                    /* The messages sit by their fields, which may be well above the
                       button that was pressed: bring the first one into view and put
                       the cursor in its field. */
                    const filled = $(`#${formID} .acc__input-error`).filter(function () { return $(this).text().trim() !== ''; });
                    const firstError = filled.filter(function () { return this.offsetParent !== null; }).first();
                    /* the field block (label, control, message) when the message is visible;
                       an error inside a collapsed section goes to its nearest visible container */
                    let target = firstError.length
                        ? (firstError.closest('.apd-field').get(0) || firstError.parent().get(0))
                        : filled.first().parents().filter(function () { return this.offsetParent !== null; }).first().get(0);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        /* plain inputs get the cursor; Tom Selects are left alone, focusing one opens it */
                        const key = ((firstError.attr('class') || '').match(/error-([\w-]+)/) || [])[1];
                        const control = key ? (document.getElementById(key) || $(`#${formID} .${key}`).get(0)) : null;
                        if (control && !control.tomselect && control.offsetParent !== null && /^(INPUT|TEXTAREA|SELECT)$/.test(control.tagName)) { try { control.focus({ preventScroll: true }); } catch (e) {} }
                    }
                }else{
                    console.log(textStatus+' => '+errorThrown);
                }
                nextWizardStep = false;
            }
        });
        //console.log(nextWizardStep);
        //nextWizardStep = false;
        /* Form Submission End*/
         
        if (nextWizardStep) {
            next.parents('.wizard-fieldset').removeClass("show");
            currentActiveStep.removeClass('active').addClass('activated').next().addClass('active');
            next.parents('.wizard-fieldset').next('.wizard-fieldset').addClass("show");
            $(document).find('.wizard-fieldset').each(function () {
                if ($(this).hasClass('show')) {
                    var activeIndex = $(this).index();
                    var indexCount = 1;
                    $(document).find('.form-wizard-steps .form-wizard-step-item').each(function () {
                        if (activeIndex == indexCount) {
                            $(this).addClass('active');
                        } else {
                            $(this).removeClass('active');
                        }
                        indexCount++;
                    });
                    
                    /* Check If Last Step */
                    var $lastStep = $(this);
                    if($lastStep.hasClass('wizard-last-step') && $('.reviewContentWrap', $lastStep).length > 0){
                        var applicant_id = $('.reviewContentWrap', $lastStep).attr('data-review-id');
                        axios({
                            method: "post",
                            url: route('applicant.application.review'),
                            data: {applicant_id : applicant_id},
                            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                        }).then(response => {
                            if (response.status == 200) {
                                $('.reviewLoader', $lastStep).fadeOut('fast', function(){
                                    $('.reviewContentWrap', $lastStep).fadeIn('fast', function(){
                                        /* native <details> sections: no accordion script needed */
                                        $('.reviewContent', $lastStep).html(response.data.htmls);
                                        createIcons({
                                            icons,
                                            "stroke-width": 1.5,
                                            nameAttr: "data-lucide",
                                        });
                                    })
                                })
                            }
                        }).catch(error => {
                            if (error.response) {
                                console.log('error');
                            }
                        });
                    }
                }
            });
        }
    });
    //click on previous button
    $('.form-wizard-previous-btn').on('click', function () {
        var counter = parseInt($(".wizard-counter").text());
        
        var prev = $(this);
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-steps .active');
        prev.parents('.wizard-fieldset').removeClass("show");
        prev.parents('.wizard-fieldset').prev('.wizard-fieldset').addClass("show");
        currentActiveStep.removeClass('active').prev().removeClass('activated').addClass('active');
        $(document).find('.wizard-fieldset').each(function () {
            if ($(this).hasClass('show')) {
                var activeIndex = $(this).index();
                var indexCount = 1;
                $(document).find('.form-wizard-steps .form-wizard-step-item').each(function () {
                    if (activeIndex == indexCount) {
                        $(this).addClass('active');
                    } else {
                        $(this).removeClass('active');
                    }
                    indexCount++;
                });
            }
        });
    });

    $('#course_creation_id').on('change', function(e){

        $('.courseLoading').show();
        let SelectedValue = $(this).val();
        //woorking all here get the venues
        const $opt = $(this).find('option[value="' + SelectedValue + '"]');
        const optFees = parseFloat($opt.data('fees'));
        const optStart = $opt.data('start');
        if (SelectedValue !== "") {
            $('.courseFee').text(!isNaN(optFees) && optFees > 0 ? '£' + optFees.toLocaleString('en-GB', {maximumFractionDigits: 0}) : '—');
            $('.courseStart').text(optStart ? optStart : '—');
            $('.apd-course-meta').css('display', 'grid');
        }
        if(SelectedValue=="") {
            $('.apd-course-meta').hide();
            $('#selectVenue').fadeOut('fast', function(){
                $('.courseLoading').hide();
            })
        }else
            axios({
                method: "get",
                url: route("applicant.application.course.creation.edit", $('#course_creation_id').val()),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let venues = response.data.venues;
                        venue_id.clear();
                        venue_id.clearOptions();
                    if(venues.length>1) {
                        venue_id.addOption({value:'',text:"Please Select"});
                        venue_id.addItem('');
                    }
                    venues.forEach((e, i) => {
                        if(e.pivot.deleted_at==null) {
                            if(venues.length==1) {
                                venue_id.removeOption('');
                                venue_id.addOption({value:e.id,text:e.name});
                                venue_id.addItem(e.id);
                            } else {
                                venue_id.removeItem(e.id);
                                venue_id.addOption({value:e.id,text:e.name});
                            }
                        }
                    });
                    
                    if(venues.length>0) {
                        
                        $('#selectVenue').removeClass('hidden').css('display', 'grid').hide().fadeIn('fast', function(){
                            $('.courseLoading').hide();
                        })
                    } else {
                        $('.courseLoading').hide();
                    }
                }
            }).catch((error) => {
                console.log(error);
            });
            
    });

    $('#venue_id').on('change', function(e){
        let $theVenue = $(this);
        let $theCourseCreation = $('#course_creation_id');

        let venue_id = $theVenue.val();
        let course_creation_id = $theCourseCreation.val();

        if(venue_id > 0 && course_creation_id > 0){
            axios({
                method: "post",
                url: route("applicant.application.get.evening.weekend.status"),
                data: {course_creation_id : course_creation_id, venue_id : venue_id},
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    if(response.data.weekends == 1){
                        $('.ewHint').text('This campus runs both weekday and evening/weekend timetables.');
                        $('.eveningWeekendWrap').css('display', 'grid').hide().fadeIn('fast', function(){
                            $('[name="full_time"]', this).prop('checked', false).removeClass('onlyWeekends');
                        })
                    }else if(response.data.weekends == 2){
                        $('.ewHint').text('This campus runs evening/weekend classes only.');
                        $('.eveningWeekendWrap').css('display', 'grid').hide().fadeIn('fast', function(){
                            $('[name="full_time"]', this).prop('checked', true).addClass('onlyWeekends');
                        })
                    }else{
                        $('.eveningWeekendWrap').fadeOut('fast', function(){
                            $('[name="full_time"]', this).prop('checked', false).removeClass('onlyWeekends');
                        })
                    }
                }
            }).catch((error) => {
                $('.eveningWeekendWrap').fadeOut('fast', function(){
                    $('[name="full_time"]', this).prop('checked', false).removeClass('onlyWeekends');
                })
                console.log(error);
            });
        }else{
            $('.eveningWeekendWrap').fadeOut('fast', function(){
                $('[name="full_time"]', this).prop('checked', false).removeClass('onlyWeekends');
            })
        }
    });

    $('#full_time').on('click', function(e){
        if($(this).hasClass('onlyWeekends')){
            e.preventDefault();
            e.stopPropagation();
        }
    })

    $(document).on('change', 'input[name="disability_status"]', function(){
        if($('#disability_status').prop('checked')){
            $('.disabilityItems').fadeIn('fast', function(){
                $('.disabilityItems input[type="checkbox"]').prop('checked', false);
                $('.disabilityAllowance').fadeOut();
                $('.disabilityAllowance input[value="0"]').prop('checked', true);
            });
        }else{
            $('.disabilityItems').fadeOut('fast', function(){
                $('.disabilityItems input[type="checkbox"]').prop('checked', false);
                $('.disabilityAllowance').fadeOut();
                $('.disabilityAllowance input[value="0"]').prop('checked', true);
            });
        }
    });

    const toggleCriminalConvictionDetails = () => {
        console.log('toggleCriminalConvictionDetails called');
        const selected = $('input[name="have_you_been_convicted"]:checked').val();
        if (selected === "1") {
            $('.criminalConvictionDetailsWrap').fadeIn('fast');
        } else {
            $('.criminalConvictionDetailsWrap').fadeOut('fast', function(){
                $('#criminal_conviction_details').val('');
            });
        }
    };

    toggleCriminalConvictionDetails();
    $(document).on('change', 'input[name="have_you_been_convicted"]', toggleCriminalConvictionDetails);
    
    $(document).on('change', '.disabilityItems input[type="checkbox"]', function(){
        if($('.disabilityItems input[type="checkbox"]:checked').length > 0){
            $('.disabilityAllowance').fadeIn('fast');
        }else{
            $('.disabilityAllowance').fadeOut('fast', function(){
                $('input[value="0"]', this).prop('checked', true);
            });
        }
    });

    $(document).on('change', 'input[name="is_edication_qualification"]', function(){
        if($('#is_edication_qualification').prop('checked')){
            $('.qualificationAdder').fadeIn('fast');
            $('.educationQualificationTableWrap').fadeIn('fast', function(){
                educationQualTable.init();
            });
        }else{
            $('.qualificationAdder').fadeOut('fast');
            $('.educationQualificationTableWrap').fadeOut('fast', function(){
                educationQualTable.init();
            });
        }
    });

    $('#addQualificationForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addQualificationForm');
    
        document.querySelector('#saveEducationQualification').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEducationQualification svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        let applicantId = $('[name="applicant_id"]', $form).val();
        axios({
            method: "post",
            url: route('applicant.qualification.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#saveEducationQualification').removeAttribute('disabled');
                document.querySelector("#saveEducationQualification svg").style.cssText = "display: none;";

                addQualificationModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html('Qualification added');
                    $("#successModal .successModalDesc").html('It now appears in your qualifications list.');
                });                
                    
            }
            educationQualTable.init();
        }).catch(error => {
            document.querySelector('#saveEducationQualification').removeAttribute('disabled');
            document.querySelector("#saveEducationQualification svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addQualificationForm .${key}`).addClass('border-danger');
                        $(`#addQualificationForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#educationQualTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("applicant.qualification.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editQualificationModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#editQualificationModal input[name="highest_academic"]').val(dataset.highest_academic ? dataset.highest_academic : '');
                    $('#editQualificationModal input[name="awarding_body"]').val(dataset.awarding_body ? dataset.awarding_body : '');
                    $('#editQualificationModal input[name="subjects"]').val(dataset.subjects ? dataset.subjects : '');
                    $('#editQualificationModal input[name="result"]').val(dataset.result ? dataset.result : '');
                    $('#editQualificationModal input[name="degree_award_date"]').val(dataset.degree_award_date ? dataset.degree_award_date : '');
                    
                    $('#editQualificationModal input[name="id"]').val(editId);
                }
            })
            .catch((error) => {
                console.log(error);
            });
    });

    $("#editQualificationForm").on("submit", function (e) {
        e.preventDefault();
        let editId = $('#editQualificationForm input[name="id"]').val();
        const form = document.getElementById("editQualificationForm");

        document.querySelector('#updateEducationQualification').setAttribute('disabled', 'disabled');
        document.querySelector('#updateEducationQualification svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("applicant.qualification.update"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#updateEducationQualification").removeAttribute("disabled");
                document.querySelector("#updateEducationQualification svg").style.cssText = "display: none;";
                editQualificationModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html('Qualification updated');
                    $("#successModal .successModalDesc").html('Your changes have been saved.');
                });
            }
            educationQualTable.init();
        }).catch((error) => {
            document.querySelector("#updateEducationQualification").removeAttribute("disabled");
            document.querySelector("#updateEducationQualification svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editQualificationForm .${key}`).addClass('border-danger')
                        $(`#editQualificationForm  .error-${key}`).html(val)
                    }
                }else if (error.response.status == 304) {
                    editQualificationModal.hide();

                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Oops!");
                        $("#errorModal .errorModalDesc").html('No data change found!');
                    });
                } else {
                    console.log("error");
                }
            }
        });
    });

    // Delete Course
    $('#educationQualTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Remove this qualification?');
            $('#confirmModal .confModDesc').html('This will delete it from your application. You can\u2019t undo this.');
            $('#confirmModal .agreeWith').text('Yes, remove');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Restore Course
    $('#educationQualTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Restore this qualification?');
            $('#confirmModal .confModDesc').html('It will be listed on your application again.');
            $('#confirmModal .agreeWith').text('Yes, restore');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('applicant.qualification.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Removed');
                        $('#successModal .successModalDesc').html('The record has been deleted from your application.');
                    });
                }
                educationQualTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('applicant.qualification.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Restored');
                        $('#successModal .successModalDesc').html('The record is listed on your application again.');
                    });
                }
                educationQualTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    $('#employment_status').on('change', function(){
        if($(this).val() == 'Unemployed' || $(this).val() == 'Contractor' || $(this).val() == 'Consultant' || $(this).val() == 'Office Holder' || $(this).val() == ''){
            $('.employmentHistoryAdder').fadeOut('fast');
            $('.educationEmploymentTableWrap').fadeOut();
        }else{
            $('.employmentHistoryAdder').fadeIn('fast');
            $('.educationEmploymentTableWrap').fadeIn();
            employmentHistoryTable.init();
        }
    });

    $('#addEmployementHistoryForm input[name="continuing"]').on('change', function(){
        if($(this).prop('checked')){
            $('#addEmployementHistoryForm input[name="end_date"]').val('').attr('disabled', 'disabled');
        }else{
            $('#addEmployementHistoryForm input[name="end_date"]').val('').removeAttr('disabled');
        }
    })

    $('#editEmployementHistoryModal input[name="continuing"]').on('change', function(){
        if($(this).prop('checked')){
            $('#editEmployementHistoryModal input[name="end_date"]').val('').attr('disabled', 'disabled');
        }else{
            $('#editEmployementHistoryModal input[name="end_date"]').val('').removeAttr('disabled');
        }
    })


    $('#addEmployementHistoryForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addEmployementHistoryForm');
    
        document.querySelector('#saveEmpHistory').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEmpHistory svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        let applicantId = $('[name="applicant_id"]', $form).val();
        axios({
            method: "post",
            url: route('applicant.employment.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => { 
            if (response.status == 200) {
                document.querySelector('#saveEmpHistory').removeAttribute('disabled');
                document.querySelector("#saveEmpHistory svg").style.cssText = "display: none;";

                addEmployementHistoryModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html('Employment added');
                    $("#successModal .successModalDesc").html('It now appears in your employment history.');
                });                
                    
            }
            employmentHistoryTable.init();
        }).catch(error => {
            document.querySelector('#saveEmpHistory').removeAttribute('disabled');
            document.querySelector("#saveEmpHistory svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addEmployementHistoryForm .${key}`).addClass('border-danger');
                        $(`#addEmployementHistoryForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#employmentHistoryTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("applicant.employment.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;

                $('#editEmployementHistoryModal input[name="company_name"]').val(dataset.company_name ? dataset.company_name : '');
                $('#editEmployementHistoryModal input[name="company_phone"]').val(dataset.company_phone ? dataset.company_phone : '');
                $('#editEmployementHistoryModal input[name="position"]').val(dataset.position ? dataset.position : '');
                $('#editEmployementHistoryModal input[name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                if(dataset.continuing && dataset.continuing == 1){
                    $('#editEmployementHistoryModal input[name="continuing"]').prop('checked', true);
                    $('#editEmployementHistoryModal input[name="end_date"]').val('').attr('disabled', 'disabled');
                }else{
                    $('#editEmployementHistoryModal input[name="continuing"]').prop('checked', false);
                    $('#editEmployementHistoryModal input[name="end_date"]').val(dataset.end_date ? dataset.end_date : '').removeAttr('disabled');
                }

                /* The address is an inline, editable block now, so fill the real
                   inputs rather than painting a read-only summary. */
                $('#editEmployementHistoryModal input[name="employment_address_line_1"]').val(dataset.address_line_1 || '');
                $('#editEmployementHistoryModal input[name="employment_address_line_2"]').val(dataset.address_line_2 || '');
                $('#editEmployementHistoryModal input[name="employment_address_city"]').val(dataset.city || '');
                $('#editEmployementHistoryModal input[name="employment_address_state"]').val(dataset.state || '');
                $('#editEmployementHistoryModal input[name="employment_address_postal_zip_code"]').val(dataset.post_code || '');
                $('#editEmployementHistoryModal input[name="employment_address_country"]').val(dataset.country || 'United Kingdom');

                $('#editEmployementHistoryModal input[name="contact_name"]').val(dataset.reference[0].name ? dataset.reference[0].name : '');
                $('#editEmployementHistoryModal input[name="contact_position"]').val(dataset.reference[0].position ? dataset.reference[0].position : '');
                $('#editEmployementHistoryModal input[name="contact_phone"]').val(dataset.reference[0].phone ? dataset.reference[0].phone : '');
                $('#editEmployementHistoryModal input[name="contact_email"]').val(dataset.reference[0].email ? dataset.reference[0].email : '');
                $('#editEmployementHistoryModal input[name="ref_id"]').val(dataset.reference[0].id ? dataset.reference[0].id : '');
                
                $('#editEmployementHistoryModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editEmployementHistoryForm").on("submit", function (e) {
        e.preventDefault();
        let editId = $('#editEmployementHistoryForm input[name="id"]').val();
        const form = document.getElementById("editEmployementHistoryForm");

        document.querySelector('#updateEmpHistory').setAttribute('disabled', 'disabled');
        document.querySelector('#updateEmpHistory svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("applicant.employment.update"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#updateEmpHistory").removeAttribute("disabled");
                document.querySelector("#updateEmpHistory svg").style.cssText = "display: none;";
                editEmployementHistoryModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html('Employment updated');
                    $("#successModal .successModalDesc").html('Your changes have been saved.');
                });
            }
            employmentHistoryTable.init();
        }).catch((error) => {
            document.querySelector("#updateEmpHistory").removeAttribute("disabled");
            document.querySelector("#updateEmpHistory svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editEmployementHistoryForm .${key}`).addClass('border-danger')
                        $(`#editEmployementHistoryForm  .error-${key}`).html(val)
                    }
                }else if (error.response.status == 304) {
                    editEmployementHistoryModal.hide();

                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Oops!");
                        $("#errorModal .errorModalDesc").html('No data change found!');
                    });
                } else {
                    console.log("error");
                }
            }
        });
    });

    // Delete Course
    $('#employmentHistoryTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Remove this employment entry?');
            $('#confirmModal .confModDesc').html('This will delete it from your application. You can\u2019t undo this.');
            $('#confirmModal .agreeWith').text('Yes, remove');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEEH');
        });
    });

    // Restore Course
    $('#employmentHistoryTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Restore this employment entry?');
            $('#confirmModal .confModDesc').html('It will be listed on your application again.');
            $('#confirmModal .agreeWith').text('Yes, restore');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREEH');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETEEH'){
            axios({
                method: 'delete',
                url: route('applicant.employment.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Removed');
                        $('#successModal .successModalDesc').html('The record has been deleted from your application.');
                    });
                }
                employmentHistoryTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREEH'){
            axios({
                method: 'post',
                url: route('applicant.employment.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Restored');
                        $('#successModal .successModalDesc').html('The record is listed on your application again.');
                    });
                }
                employmentHistoryTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    });

    /* Referral code - the design's flow. One button reads "Verify code" until
       the code checks out and "Change" afterwards; a notice under the field
       carries the result; the box is locked while verified. The verify
       endpoint is the only thing that writes the code (the step save never
       touches the field), so "Change" clears it server-side too. The next
       button is held while an unverified code sits in the box, because that
       code would otherwise be lost silently on save. */
    ;(function(){
        var $input = $('#referral_code');
        if(!$input.length) return;
        var $btn = $input.siblings('#varifiedReferral');
        var $flag = $input.siblings('.is_referral_varified');
        var $notice = $input.closest('.apd-field').find('.apd-referral-notice');
        var $next = $input.closest('form').find('.form-wizard-next-btn');
                /* Resolved when clicked, not at load: a route missing from Ziggy's list
           would otherwise throw here and unbind every handler below. */
        var url = function(name){
            try { return route(name); }
            catch (err) { console.error('referral: route not available -', name, err); return null; }
        };
        var verifyRoute = 'applicant.application.verify.referral.code';
        var clearRoute = 'applicant.application.clear.referral.code';
        var csrf = {headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}};

        var isVerified = function(){ return $btn.hasClass('verified'); };

        var notice = function(tone, html){
            $notice.removeClass('apd-notice apd-notice--ok apd-notice--bad');
            if(!tone){ $notice.html(''); return; }
            $notice.addClass('apd-notice apd-notice--' + tone).html(html);
        };

        var render = function(){
            var code = $.trim($input.val());
            if(isVerified()){
                $input.attr('readonly', 'readonly');
                $btn.text('Change').removeAttr('disabled');
                $next.removeAttr('disabled');
            }else{
                $input.removeAttr('readonly');
                $btn.text('Verify code');
                if(code === ''){ $btn.attr('disabled', 'disabled'); $next.removeAttr('disabled'); }
                else { $btn.removeAttr('disabled'); $next.attr('disabled', 'disabled'); }
            }
        };

        $input.on('input', function(){
            if(isVerified()) return;
            notice('');
            render();
        });

        $btn.on('click', function(e){
            e.preventDefault();
            var applicantId = $btn.attr('data-applicant-id');
            $btn.attr('disabled', 'disabled');

            if(isVerified()){
                var clearUrl = url(clearRoute);
                if(!clearUrl){ notice('bad', 'We could not reach the server just now. Please try again.'); render(); return; }
                axios.post(clearUrl, {applicantId: applicantId}, csrf)
                    .then(function(){
                        $btn.removeClass('verified');
                        $input.val('').attr('data-org', '');
                        $flag.val('0').attr('data-org', '0');
                        notice('');
                        render();
                        $input.trigger('focus');
                    })
                    .catch(function(err){
                        console.error('referral: clear failed', err);
                        notice('bad', 'We could not clear the code just now' + (err && err.response ? ' (' + err.response.status + ')' : '') + '. Please try again.');
                        render();
                    });
                return;
            }

            var code = $.trim($input.val()).toUpperCase();
            var verifyUrl = url(verifyRoute);
            if(!verifyUrl){ notice('bad', 'We could not reach the server just now. Please try again.'); render(); return; }
            axios.post(verifyUrl, {applicantId: applicantId, code: code}, csrf)
                .then(function(response){
                    var msg = (response.data && response.data.msg) || {};
                    if(msg.suc == 1){
                        $btn.addClass('verified');
                        $input.val(msg.code).attr('data-org', msg.code);
                        $flag.val('1').attr('data-org', '1');
                        notice('ok', 'Verified &mdash; ' + $('<i></i>').text(msg.owner || msg.code).html() + '. Your application will be linked to this referrer.');
                    }else{
                        notice('bad', 'We could not find that code. Check it with whoever gave it to you, or leave it blank.');
                    }
                    render();
                })
                .catch(function(err){
                    console.error('referral: verify failed', err);
                    notice('bad', 'We could not check that code just now' + (err && err.response ? ' (' + err.response.status + ')' : '') + '. Please try again.');
                    render();
                });
        });

        render();
    })();

    $('#student_loan').on('change', function(){
        var $this = $(this);
        if($this.val() == 'Others'){
            $('.studentLoanPanel').fadeOut('fast');
            $('.studentLoanEnglandFunding, .studentLoanFundReceipt, .studentLoanApplied')
                .hide().find('input[type="radio"]').prop('checked', false);
            $('.otherFundings').fadeIn('fast', function(){
                $('input', this).val('');
            })
        }else if($this.val() == 'Student Loan'){
            $('.studentLoanPanel').css('display', 'grid').hide().fadeIn('fast');
            $('.studentLoanEnglandFunding, .studentLoanApplied').css('display', 'grid')
                .find('input[type="radio"]').prop('checked', false);
            $('.studentLoanFundReceipt').hide().find('input[type="radio"]').prop('checked', false);
            $('.otherFundings').fadeOut('fast', function(){
                $('input', this).val('');
            })
        }else{
            $('.studentLoanPanel').fadeOut('fast');
            $('.studentLoanEnglandFunding, .studentLoanFundReceipt, .studentLoanApplied')
                .hide().find('input[type="radio"]').prop('checked', false);
            $('.otherFundings').fadeOut('fast', function(){
                $('input', this).val('');
            })
        }
    })

    $(document).on('change', 'input[name="student_finance_england"]', function(){
        if($('#student_finance_england').prop('checked')){
            $('.studentLoanFundReceipt').css('display', 'grid').hide().fadeIn('fast')
                .find('input[type="radio"]').prop('checked', false);
        }else{
            $('.studentLoanFundReceipt').fadeOut('fast', function(){
                $('input[type="radio"]', this).prop('checked', false);
            })
        }
    })

})();