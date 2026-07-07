import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';
import IMask from 'imask';

('use strict');
var liveStudentsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let form_data = $('#studentSearchForm').serialize();
        let hasCommunication = ($('#liveStudentsListTable').attr('data-coummunication') ? $('#liveStudentsListTable').attr('data-coummunication') : 0);
        let liveStudentsTotalRows = 0;

        let formatStudentNumber = function (value) {
            return new Intl.NumberFormat('en-GB').format(parseInt(value || 0, 10));
        };

        let updateLiveStudentsFooter = function (table) {
            let totalRows = liveStudentsTotalRows || parseInt($('#unsignedResultCount').attr('data-total') || 0, 10);
            let footer = $('#liveStudentsListTable .tabulator-footer');

            if (!footer.length) {
                return;
            }

            if (!footer.find('.student-live-footer-range').length) {
                footer.prepend('<span class="student-live-footer-range"></span>');
            }

            let pageSize = parseInt(table.getPageSize(), 10) || totalRows || 0;
            let page = parseInt(table.getPage(), 10) || 1;
            let startRow = totalRows > 0 ? ((page - 1) * pageSize) + 1 : 0;
            let endRow = totalRows > 0 ? Math.min(page * pageSize, totalRows) : 0;

            footer.find('.student-live-footer-range').html(
                'Showing <strong>' + formatStudentNumber(startRow) + '&ndash;' + formatStudentNumber(endRow) + '</strong> of <strong>' + formatStudentNumber(totalRows) + '</strong>'
            );
            footer.find('.tabulator-page[data-page="first"]').html('&laquo; First');
            footer.find('.tabulator-page[data-page="prev"]').html('&lsaquo; Prev');
            footer.find('.tabulator-page[data-page="next"]').html('Next &rsaquo;');
            footer.find('.tabulator-page[data-page="last"]').html('Last &raquo;');
        };

        let tableContent = new Tabulator('#liveStudentsListTable', {
            ajaxURL: route('student.list'),
            ajaxParams: { form_data: form_data },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: 'remote',
            paginationSize: 50,
            paginationSizeSelector: [25, 50, 100, 250, 500],
            paginationButtonCount: 3,
            layout: 'fitColumns',
            responsiveLayout: 'collapse',
            virtualDom: false,
            placeholder: 'No matching records found',
            selectable: (hasCommunication == 1 ? true : false),
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "left", 
                    headerHozAlign: "left",
                    width: 64,
                    headerSort: false, 
                    download: false,
                    visible: (hasCommunication == 1 ? true : false),
                    cellClick:function(e, cell){
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: 'Reg. No',
                    field: 'registration_no',
                    headerHozAlign: 'left',
                    width: 216,
                    formatter(cell, formatterParams) {
                        let firstName = cell.getData().first_name || '';
                        let lastName = cell.getData().last_name || '';
                        let initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
                        initials = initials != '' ? initials : 'ST';
                        let photoUrl = cell.getData().photo_url || '';
                        let html = '<div class="student-live-reg-cell">';
                        html += '<span class="student-live-avatar">';
                        if (photoUrl != '') {
                            html += '<img alt="' + firstName + '" src="' + photoUrl + '">';
                        } else {
                            html += initials;
                        }
                        html += '</span>';
                        html += '<span class="student-live-reg-no">' + cell.getData().registration_no + '</span>';
                        html += '</div>';
                        html += '<input type="hidden" class="student_ids" name="student_ids[]" value="'+cell.getData().id+'"/>';
                        return html;
                    },
                    cellClick:function(e, cell){
                        let theRow = cell.getRow();
                        window.open(theRow.getData().url, '_blank');
                    }
                },
                {
                    title: 'Name',
                    field: 'first_name',
                    headerHozAlign: 'left',
                    width: 274,
                    formatter(cell, formatterParams) {
                        let firstName = cell.getData().first_name || '';
                        let lastName = cell.getData().last_name || '';
                        return '<div class="student-live-name-cell"><span>' + firstName + '</span> <em>' + lastName + '</em></div>';
                    },
                    cellClick:function(e, cell){
                        let theRow = cell.getRow();
                        window.open(theRow.getData().url, '_blank');
                    }
                },
                {
                    title: '',
                    field: 'full_time',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    width: 72,
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        var html = '<div class="student-live-indicators">';
                        let flagHtml = cell.getData().flag_html || '';
                        if (flagHtml != '') {
                            html += '<span class="student-live-indicator student-live-indicator-flag">' + flagHtml + '</span>';
                        }
                        if (cell.getData().multi_agreement_status > 1) {
                            html += '<span class="student-live-indicator student-live-indicator-alert"><i data-lucide="alert-octagon" class="w-4 h-4"></i></span>';
                        }
                        if (cell.getData().due > 1) {
                            let dueTone = cell.getData().due == 2 ? 'success' : cell.getData().due == 3 ? 'warning' : 'danger';
                            html += '<span class="student-live-indicator student-live-indicator-due is-' + dueTone + '"><i data-lucide="badge-pound-sterling" class="w-4 h-4"></i></span>';
                        }
                        html += '<span class="student-live-indicator student-live-indicator-mode ' + (cell.getData().full_time == 1 ? 'is-day' : 'is-evening') + '">';
                        if (cell.getData().full_time == 1)
                            html +=
                                '<i data-lucide="sun" class="w-4 h-4"></i>';
                        else
                            html +=
                                '<i data-lucide="moon" class="w-4 h-4"></i>';

                        html += '</span>';
                        if (cell.getData().disability == 1)
                            html +=
                                '<span class="student-live-indicator student-live-indicator-accessibility"><i data-lucide="accessibility" class="w-4 h-4"></i></span>';

                        html += '</div>';
                        return html;
                    },
                    cellClick:function(e, cell){
                        let theRow = cell.getRow();
                        window.open(theRow.getData().url, '_blank');
                    }
                },
                {
                    title: 'Semester',
                    field: 'semester',
                    headerSort: false,
                    headerHozAlign: 'left',
                    width: 173,
                    cellClick:function(e, cell){
                        let theRow = cell.getRow();
                        window.open(theRow.getData().url, '_blank');
                    }
                },
                {
                    title: 'Course',
                    field: 'course',
                    headerSort: false,
                    headerHozAlign: 'left',
                    variableHeight: true,
                    cssClass: 'student-live-course-col',
                    formatter(cell, formatterParams) {
                        let course = cell.getData().course || '';
                        return '<div class="student-live-course-cell" title="' + course + '">' + course + '</div>';
                    },
                    cellClick:function(e, cell){
                        let theRow = cell.getRow();
                        window.open(theRow.getData().url, '_blank');
                    }
                },
                {
                    title: 'Status',
                    field: 'status_id',
                    headerHozAlign: 'left',
                    width: 142,
                    formatter(cell, formatterParams) {
                        let status = cell.getValue() || '';
                        return '<span class="student-live-status-pill"><span></span>' + status + '</span>';
                    },
                    cellClick:function(e, cell){
                        let theRow = cell.getRow();
                        window.open(theRow.getData().url, '_blank');
                    }
                },
            ],
            ajaxResponse: function (url, params, response) {
                var total_rows =
                    response.all_rows && response.all_rows > 0
                        ? response.all_rows
                        : 0;
                liveStudentsTotalRows = total_rows;

                if (total_rows > 0) {
                    $('#unsignedResultCount').removeClass('hidden');

                    $('#unsignedResultCount')
                        .attr('data-total', total_rows)
                        .html(formatStudentNumber(total_rows) + ' students found');
                } else {
                    $('#unsignedResultCount').addClass('hidden').attr('data-total', '0').html('');
                }

                return response;
            },
            renderComplete() {
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });
                updateLiveStudentsFooter(this);

                $(document).find('.autoFillDropdown').html('').fadeOut();
                $(document)
                    .find('.flagLinks')
                    .each(function () {
                        $(this).attr('href', 'javascript:void(0);');
                    });
            },
            rowSelectionChanged:function(data, rows){
                if(rows.length > 0){
                    $('#communicationBtnsArea').css('display', 'flex').hide().fadeIn();
                    $('#studentSelectedCount')
                        .removeClass('hidden')
                        .attr('data-count', rows.length)
                        .html('<span></span>' + rows.length + ' selected');
                }else{
                    $('#communicationBtnsArea').fadeOut();
                    $('#studentSelectedCount')
                        .addClass('hidden')
                        .attr('data-count', 0)
                        .html('');
                }
            },
            // rowClick: function (e, row) {
            //     window.open(row.getData().url, '_blank');
            // },
        });

        // Redraw table onresize
        window.addEventListener('resize', () => {
            tableContent.redraw();
            createIcons({
                icons,
                'stroke-width': 1.5,
                nameAttr: 'data-lucide',
            });
        });

        // Export
        $('#tabulator-export-csv-LSD').on('click', function (event) {
            tableContent.download('csv', 'data.csv');
        });

        $('#tabulator-export-json-LSD').on('click', function (event) {
            tableContent.download('json', 'data.json');
        });

        $('#tabulator-export-xlsx-LSD').on('click', function (event) {
            window.XLSX = xlsx;
            tableContent.download('xlsx', 'data.xlsx', {
                sheetName: 'Students Details',
            });
        });

        $('#tabulator-export-html-LSD').on('click', function (event) {
            tableContent.download('html', 'data.html', {
                style: true,
            });
        });

        // Print
        $('#tabulator-print-LSD').on('click', function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    var dobMaskOptions = {
        mask: '00-00-0000',
    };
    var student_dob_mask = IMask(
        document.getElementById('student_dob'),
        dobMaskOptions
    );

    let tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        dropdownParent: 'body',
        dropdownClass: 'ts-dropdown lcc-tom-dropdown',
        persist: false,
        create: true,
        allowEmptyOption: true,
        maxOptions: null,
        onDelete: function (values) {
            return confirm(
                values.length > 1
                    ? 'Are you sure you want to remove these ' +
                          values.length +
                          ' items?'
                    : 'Are you sure you want to remove "' + values[0] + '"?'
            );
        },
    };

    $('.lccTom').each(function () {
        if ($(this).attr('multiple') !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: 'Remove this item',
                    },
                },
            };
        }
        new TomSelect(this, tomOptions);
    });

    if ($('#liveStudentsListTable').length > 0) {
        let tomOptionsMul = {
            ...tomOptions,
            plugins: {
                ...tomOptions.plugins,
                remove_button: {
                    title: 'Remove this item',
                },
            },
        };
        var student_status = new TomSelect('#student_status', tomOptionsMul);
        //var academic_year = new TomSelect('#academic_year', tomOptionsMul);
        var intake_semester = new TomSelect('#intake_semester', tomOptionsMul);
        var attendance_semester = new TomSelect(
            '#attendance_semester',
            tomOptionsMul
        );
        //attendance_semester.clear()
        //attendance_semester.disable();
        var course = new TomSelect('#course', tomOptionsMul);
        //course.clear(true)
        //course.disable();
        var group = new TomSelect('#group', tomOptionsMul);
        group.clear(true);
        group.disable();
        //var term_status = new TomSelect('#term_status', tomOptionsMul);
        var student_type = new TomSelect('#student_type', tomOptionsMul);
        var group_student_status = new TomSelect(
            '#group_student_status',
            tomOptionsMul
        );
        var evening_weekend = new TomSelect('#evening_weekend', tomOptions);

        intake_semester.on('change', function () {
            let intakeSemester = intake_semester.getValue();

            if (intakeSemester.length > 0) {
                let intake_semesters = intake_semester.getValue();
                let attendance_semesters = attendance_semester.getValue();
                let courses = course.getValue();
                let groups = group.getValue();
                let student_types = student_type.getValue();
                let group_student_statuses = group_student_status.getValue();
                let evening_weekends = evening_weekend.getValue();
                //student.get.all.student.type

                axios({
                    method: 'post',
                    url: route('student.get.all.student.type'),
                    data: {
                        academic_years: '',
                        term_declaration_ids: attendance_semesters,
                        intake_semesters: intake_semesters,
                        courses: courses,
                        groups: groups,
                        group_student_statuses: group_student_statuses,
                        student_types: student_types,
                        evening_weekends: evening_weekends,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                }).then((response) => {
                    if (response.status == 200) {
                        let res = response.data.res;

                        student_type.clearOptions();
                        student_type.enable();

                        attendance_semester.clearOptions();
                        attendance_semester.enable();

                        course.clearOptions();
                        course.enable();

                        group.clearOptions();
                        group.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        group_student_status.clearOptions();
                        group_student_status.enable();

                        evening_weekend.clearOptions();
                        evening_weekend.enable();

                        $.each(res.intake_semester, function (index, row) {
                            intake_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //intake_semester.setValue(intake_semesters);
                        $.each(res.attendance_semester, function (index, row) {
                            attendance_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.course, function (index, row) {
                            course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //course.setValue(courses);

                        $.each(res.group_student_status, function (index, row) {
                            group_student_status.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group_student_status.setValue(group_student_statuses);

                        $.each(res.evening_weekend, function (index, row) {
                            evening_weekend.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //evening_weekend.setValue(evening_weekends);

                        $.each(res.group, function (index, row) {
                            group.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group.setValue(groups);
                        $.each(res.student_type, function (index, row) {
                            student_type.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                    }
                });
            }
        });

        attendance_semester.on('change', function (e) {
            let attendanceSemester = attendance_semester.getValue();

            if (attendanceSemester.length > 0) {
                let intake_semesters = intake_semester.getValue();
                let attendance_semesters = attendance_semester.getValue();
                let courses = course.getValue();
                let groups = group.getValue();
                let student_types = student_type.getValue();
                let group_student_statuses = group_student_status.getValue();
                let evening_weekends = evening_weekend.getValue();

                axios({
                    method: 'post',
                    url: route('student.get.all.student.type'),
                    data: {
                        academic_years: '',
                        term_declaration_ids: attendance_semesters,
                        intake_semesters: intake_semesters,
                        courses: courses,
                        groups: groups,
                        group_student_statuses: group_student_statuses,
                        student_types: student_types,
                        evening_weekends: evening_weekends,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                }).then((response) => {
                    if (response.status == 200) {
                        let res = response.data.res;

                        intake_semester.clearOptions();
                        intake_semester.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        course.clearOptions();
                        course.enable();

                        group.clearOptions();
                        group.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        group_student_status.clearOptions();
                        group_student_status.enable();

                        evening_weekend.clearOptions();
                        evening_weekend.enable();

                        $.each(res.intake_semester, function (index, row) {
                            intake_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //intake_semester.setValue(intake_semesters);
                        $.each(res.attendance_semester, function (index, row) {
                            attendance_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.course, function (index, row) {
                            course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //course.setValue(courses);

                        $.each(res.group_student_status, function (index, row) {
                            group_student_status.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group_student_status.setValue(group_student_statuses);

                        $.each(res.evening_weekend, function (index, row) {
                            evening_weekend.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //evening_weekend.setValue(evening_weekends);

                        $.each(res.group, function (index, row) {
                            group.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group.setValue(groups);
                        $.each(res.student_type, function (index, row) {
                            student_type.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                    }
                });
            }
        });

        course.on('change', function (e) {
            let coursee = course.getValue();

            if (coursee.length > 0) {
                let intake_semesters = intake_semester.getValue();
                let attendance_semesters = attendance_semester.getValue();
                let courses = course.getValue();
                let groups = group.getValue();
                let student_types = student_type.getValue();
                let group_student_statuses = group_student_status.getValue();
                let evening_weekends = evening_weekend.getValue();

                axios({
                    method: 'post',
                    url: route('student.get.all.student.type'),
                    data: {
                        academic_years: '',
                        term_declaration_ids: attendance_semesters,
                        intake_semesters: intake_semesters,
                        courses: courses,
                        groups: groups,
                        group_student_statuses: group_student_statuses,
                        student_types: student_types,
                        evening_weekends: evening_weekends,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                }).then((response) => {
                    if (response.status == 200) {
                        let res = response.data.res;

                        intake_semester.clearOptions();
                        intake_semester.enable();

                        attendance_semester.clearOptions();
                        attendance_semester.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        group.clearOptions();
                        group.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        group_student_status.clearOptions();
                        group_student_status.enable();

                        evening_weekend.clearOptions();
                        evening_weekend.enable();

                        $.each(res.intake_semester, function (index, row) {
                            intake_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.attendance_semester, function (index, row) {
                            attendance_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.course, function (index, row) {
                            course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //course.setValue(courses);

                        $.each(res.group_student_status, function (index, row) {
                            group_student_status.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group_student_status.setValue(group_student_statuses);

                        $.each(res.evening_weekend, function (index, row) {
                            evening_weekend.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //evening_weekend.setValue(evening_weekends);

                        $.each(res.group, function (index, row) {
                            group.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group.setValue(groups);
                        $.each(res.student_type, function (index, row) {
                            student_type.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                    }
                });
            }
        });

        group.on('change', function (e) {
            let groups = group.getValue();

            if (groups.length > 0) {
                let intake_semesters = intake_semester.getValue();
                let attendance_semesters = attendance_semester.getValue();
                let courses = course.getValue();
                let groups = group.getValue();
                let student_types = student_type.getValue();
                let group_student_statuses = group_student_status.getValue();
                let evening_weekends = evening_weekend.getValue();

                axios({
                    method: 'post',
                    url: route('student.get.all.student.type'),
                    data: {
                        academic_years: '',
                        term_declaration_ids: attendance_semesters,
                        intake_semesters: intake_semesters,
                        courses: courses,
                        groups: groups,
                        group_student_statuses: group_student_statuses,
                        student_types: student_types,
                        evening_weekends: evening_weekends,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                }).then((response) => {
                    if (response.status == 200) {
                        let res = response.data.res;

                        intake_semester.clearOptions();
                        intake_semester.enable();

                        attendance_semester.clearOptions();
                        attendance_semester.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        group_student_status.clearOptions();
                        group_student_status.enable();

                        evening_weekend.clearOptions();
                        evening_weekend.enable();

                        course.clearOptions();
                        course.enable();

                        $.each(res.intake_semester, function (index, row) {
                            intake_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.attendance_semester, function (index, row) {
                            attendance_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.course, function (index, row) {
                            course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //course.setValue(courses);

                        $.each(res.group_student_status, function (index, row) {
                            group_student_status.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group_student_status.setValue(group_student_statuses);

                        $.each(res.evening_weekend, function (index, row) {
                            evening_weekend.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //evening_weekend.setValue(evening_weekends);

                        $.each(res.group, function (index, row) {
                            group.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group.setValue(groups);
                        $.each(res.student_type, function (index, row) {
                            student_type.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                    }
                });
            }
        });
        student_type.on('change', function (e) {
            let group_student_statuses = group_student_status.getValue();

            if (group_student_statuses.length > 0) {
                let intake_semesters = intake_semester.getValue();
                let attendance_semesters = attendance_semester.getValue();
                let courses = course.getValue();
                let groups = group.getValue();
                let student_types = student_type.getValue();
                let group_student_statuses = group_student_status.getValue();
                let evening_weekends = evening_weekend.getValue();
                //student.get.all.student.type

                axios({
                    method: 'post',
                    url: route('student.get.all.student.type'),
                    data: {
                        academic_years: '',
                        term_declaration_ids: attendance_semesters,
                        intake_semesters: intake_semesters,
                        courses: courses,
                        groups: groups,
                        group_student_statuses: group_student_statuses,
                        student_types: student_types,
                        evening_weekends: evening_weekends,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                }).then((response) => {
                    if (response.status == 200) {
                        let res = response.data.res;

                        evening_weekend.clearOptions();
                        evening_weekend.enable();

                        intake_semester.clearOptions();
                        intake_semester.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        attendance_semester.clearOptions();
                        attendance_semester.enable();

                        course.clearOptions();
                        course.enable();

                        group.clearOptions();
                        group.enable();

                        group_student_status.clearOptions();
                        group_student_status.enable();

                        $.each(res.intake_semester, function (index, row) {
                            intake_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.attendance_semester, function (index, row) {
                            attendance_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.course, function (index, row) {
                            course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.group_student_status, function (index, row) {
                            group_student_status.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group_student_status.setValue(group_student_statuses);

                        $.each(res.evening_weekend, function (index, row) {
                            evening_weekend.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //evening_weekend.setValue(evening_weekends);

                        $.each(res.group, function (index, row) {
                            group.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.student_type, function (index, row) {
                            student_type.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                    }
                });
            }
        });

        group_student_status.on('change', function (e) {
            let group_student_statuses = group_student_status.getValue();

            if (group_student_statuses.length > 0) {
                let intake_semesters = intake_semester.getValue();
                let attendance_semesters = attendance_semester.getValue();
                let courses = course.getValue();
                let groups = group.getValue();
                let student_types = student_type.getValue();
                let group_student_statuses = group_student_status.getValue();
                let evening_weekends = evening_weekend.getValue();
                //student.get.all.student.type

                axios({
                    method: 'post',
                    url: route('student.get.all.student.type'),
                    data: {
                        academic_years: '',
                        term_declaration_ids: attendance_semesters,
                        intake_semesters: intake_semesters,
                        courses: courses,
                        groups: groups,
                        group_student_statuses: group_student_statuses,
                        student_types: student_types,
                        evening_weekends: evening_weekends,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                }).then((response) => {
                    if (response.status == 200) {
                        let res = response.data.res;

                        evening_weekend.clearOptions();
                        evening_weekend.enable();

                        intake_semester.clearOptions();
                        intake_semester.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        attendance_semester.clearOptions();
                        attendance_semester.enable();

                        course.clearOptions();
                        course.enable();

                        group.clearOptions();
                        group.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        $.each(res.intake_semester, function (index, row) {
                            intake_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.attendance_semester, function (index, row) {
                            attendance_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.course, function (index, row) {
                            course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.group_student_status, function (index, row) {
                            group_student_status.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group_student_status.setValue(group_student_statuses);

                        $.each(res.evening_weekend, function (index, row) {
                            evening_weekend.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //evening_weekend.setValue(evening_weekends);

                        $.each(res.group, function (index, row) {
                            group.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.student_type, function (index, row) {
                            student_type.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                    }
                });
            }
        });

        evening_weekend.on('change', function (e) {
            let evening_weekends = evening_weekend.getValue();

            if (evening_weekends.length > 0) {
                let intake_semesters = intake_semester.getValue();
                let attendance_semesters = attendance_semester.getValue();
                let courses = course.getValue();
                let groups = group.getValue();
                let student_types = student_type.getValue();
                let group_student_statuses = group_student_status.getValue();
                let evening_weekends = evening_weekend.getValue();
                //student.get.all.student.type

                axios({
                    method: 'post',
                    url: route('student.get.all.student.type'),
                    data: {
                        academic_years: '',
                        term_declaration_ids: attendance_semesters,
                        intake_semesters: intake_semesters,
                        courses: courses,
                        groups: groups,
                        group_student_statuses: group_student_statuses,
                        student_types: student_types,
                        evening_weekends: evening_weekends,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                }).then((response) => {
                    if (response.status == 200) {
                        let res = response.data.res;

                        intake_semester.clearOptions();
                        intake_semester.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        attendance_semester.clearOptions();
                        attendance_semester.enable();

                        course.clearOptions();
                        course.enable();

                        group.clearOptions();
                        group.enable();

                        student_type.clearOptions();
                        student_type.enable();

                        group_student_status.clearOptions();
                        group_student_status.enable();

                        $.each(res.intake_semester, function (index, row) {
                            intake_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //intake_semester.setValue(intake_semesters);
                        $.each(res.attendance_semester, function (index, row) {
                            attendance_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });

                        $.each(res.course, function (index, row) {
                            course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //course.setValue(courses);

                        $.each(res.group_student_status, function (index, row) {
                            group_student_status.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group_student_status.setValue(group_student_statuses);

                        $.each(res.evening_weekend, function (index, row) {
                            evening_weekend.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //evening_weekend.setValue(evening_weekends);

                        $.each(res.group, function (index, row) {
                            group.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        //group.setValue(groups);
                        $.each(res.student_type, function (index, row) {
                            student_type.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        student_type.setValue(student_types);
                    }
                });
            }
        });
        // Reset Tom Select
        function resetStudentIDSearch() {
            $('#registration_no').val('');
        }

        function resetStudentSearch() {
            student_status.clear(true);
            $('#studentSearchStatus').val('0');
            $(
                '#student_id, #student_name, #student_dob, #student_abr, #student_ssn, #student_uhn, #student_mobile, #student_email, #student_post_code'
            ).val('');
            student_dob_mask.updateValue('');
        }

        function resetGroupSearch() {
            //academic_year.clear(true);
            intake_semester.clear(true);
            attendance_semester.clear(true);
            course.clear(true);
            group.clear(true);

            //term_status.clear(true);
            student_type.clear(true);
            group_student_status.clear(true);
            $('#evening_weekend').val('');
            $('#groupSearchStatus').val('0');
        }

        /* Start List Table Inits */
        //liveStudentsListTable.init();

        function filterStudentListTable() {
            liveStudentsListTable.init();
        }

        $('#registration_no').on('keypress', function (e) {
            var keycode = e.keyCode || e.which;
            if (keycode == 13) {
                $(this).blur();
                $('#studentIDSearchBtn').trigger('click');
            }
        });
        $(
            '#student_id, #student_name, #student_dob, #student_abr, #student_ssn, #student_uhn, #student_mobile, #student_email, #student_post_code, #application_no'
        ).on('keypress', function (e) {
            var keycode = e.keyCode || e.which;
            if (keycode == 13) {
                filterStudentListTable();
                $(this).blur();
                $(
                    '#student_id, #student_name, #student_dob, #student_abr, #student_ssn, #student_uhn, #student_mobile, #student_email, #student_post_code, #application_no'
                ).val('');
                student_dob_mask.updateValue('');
            }
        });

        $(
            '#studentIDSearchBtn, #studentIDSearchSubmitBtn, #studentSearchSubmitBtn'
        ).on('click', function (event) {
            filterStudentListTable();
            $(
                '#student_id, #student_name, #student_dob, #student_abr, #student_ssn, #student_uhn, #student_mobile, #student_email, #student_post_code, #application_no'
            ).val('');
            student_dob_mask.updateValue('');
        });
        $('#studentGroupSearchSubmitBtn').on('click', function (event) {
            filterStudentListTable();
        });

        $('#resetStudentSearch').on('click', function (event) {
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();
            location.reload();
            //filterStudentListTable();

            $('#communicationBtnsArea').fadeOut();
            $('#liveStudentsListTable')
                .html('')
                .removeClass('tabulator')
                .removeAttr('tabulator-layout')
                .removeAttr('role');
        });
        /* End List Table Inits */

        const studentSearchAccordion = tailwind.Accordion.getOrCreateInstance(
            document.querySelector('#studentSearchAccordion')
        );
        $('#advanceSearchToggle').on('click', function (e) {
            e.preventDefault();
            $('#studentSearchAccordionWrap').slideToggle();
            $('#studentIDSearchBtn').fadeToggle();
            $('.studentIdSearchWrap').fadeToggle();
            studentSearchAccordion.toggle();
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            //filterStudentListTable();
            $('#communicationBtnsArea').fadeOut();
            $('#liveStudentsListTable')
                .html('')
                .removeClass('tabulator')
                .removeAttr('tabulator-layout')
                .removeAttr('role');
        });

        $('#studentSearchBtn').on('click', function () {
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            if ($(this).hasClass('collapsed')) {
                $('#studentSearchStatus').val(1);
                $('#groupSearchStatus').val(0);
            } else {
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(0);
            }

            //filterStudentListTable();
            $('#communicationBtnsArea').fadeOut();
            $('#liveStudentsListTable')
                .html('')
                .removeClass('tabulator')
                .removeAttr('tabulator-layout')
                .removeAttr('role');
        });

        $('#studentGroupSearchBtn').on('click', function () {
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            if ($(this).hasClass('collapsed')) {
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(1);
            } else {
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(0);
            }

            //filterStudentListTable();
            $('#communicationBtnsArea').fadeOut();
            $('#liveStudentsListTable')
                .html('')
                .removeClass('tabulator')
                .removeAttr('tabulator-layout')
                .removeAttr('role');
        });

        $('.registration_no').on('keyup', function () {
            var $theInput = $(this);
            var SearchVal = $theInput.val();

            if (SearchVal.length >= 3) {
                axios({
                    method: 'post',
                    url: route('student.filter.id'),
                    data: { SearchVal: SearchVal },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                })
                    .then((response) => {
                        if (response.status == 200) {
                            $theInput
                                .siblings('.autoFillDropdown')
                                .html(response.data.htm)
                                .fadeIn();
                        }
                    })
                    .catch((error) => {
                        if (error.response) {
                            console.log('error');
                            $theInput
                                .siblings('.autoFillDropdown')
                                .html('')
                                .fadeOut();
                        }
                    });
            } else {
                $theInput.siblings('.autoFillDropdown').html('').fadeOut();
            }
        });

        $('.autoFillDropdown').on(
            'click',
            'li a:not(".disable")',
            function (e) {
                e.preventDefault();
                var registration_no = $(this).attr('href');
                $(this)
                    .parent('li')
                    .parent('ul.autoFillDropdown')
                    .siblings('.registration_no')
                    .val(registration_no);
                $(this)
                    .parent('li')
                    .parent('.autoFillDropdown')
                    .html('')
                    .fadeOut();
            }
        );
    }
})();
