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

        let tableContent = new Tabulator('#liveStudentsListTable', {
            ajaxURL: route('student.list'),
            ajaxParams: { form_data: form_data },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: 'remote',
            paginationSize: 50,
            paginationSizeSelector: [50, 100, 250, 500],
            layout: 'fitColumns',
            responsiveLayout: 'collapse',
            placeholder: 'No matching records found',
            columns: [
                {
                    title: 'Reg. No',
                    field: 'registration_no',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '<div class="block">';
                        html +=
                            '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                        html +=
                            '<img alt="' +
                            cell.getData().first_name +
                            '" class="rounded-full shadow" src="' +
                            cell.getData().photo_url +
                            '">';
                        html += '</div>';
                        html +=
                            '<div class="inline-block relative" style="top: -13px;">';
                        html +=
                            '<div class="font-medium whitespace-nowrap uppercase">' +
                            cell.getData().registration_no +
                            '</div>';

                        html += '</div>';
                        html += '</div>';
                        return html;
                    },
                },
                {
                    title: 'First Name',
                    field: 'first_name',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Last Name',
                    field: 'last_name',
                    headerHozAlign: 'left',
                },
                {
                    title: '',
                    field: 'full_time',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        let day = false;
                        if (cell.getData().full_time == 1)
                            day = 'text-slate-900';
                        else day = 'text-amber-600';
                        var html = '<div class="flex">';
                        if (cell.getData().flag_html != '') {
                            html += cell.getData().flag_html;
                        }
                        if (cell.getData().multi_agreement_status > 1) {
                            html += '<div class="mr-2 inline-flex  intro-x  ml-auto" style="color:#f59e0b"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="alert-octagon" class="lucide lucide-alert-octagon w-6 h-6"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg></div>';
                        }
                        if (cell.getData().due > 1) {
                            html +=
                                '<div class="mr-2 ' +
                                (cell.getData().due == 2
                                    ? 'text-success'
                                    : cell.getData().due == 3
                                    ? 'text-warning'
                                    : 'text-danger') +
                                '"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="badge-pound-sterling" class="lucide lucide-badge-pound-sterling w-6 h-6"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"></path><path d="M8 12h4"></path><path d="M10 16V9.5a2.5 2.5 0 0 1 5 0"></path><path d="M8 16h7"></path></svg></div>';
                        }
                        html +=
                            '<div class="w-8 h-8 ' +
                            day +
                            ' intro-x inline-flex">';
                        if (cell.getData().full_time == 1)
                            html +=
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg>';
                        else
                            html +=
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>';

                        html += '</div>';
                        if (cell.getData().disability == 1)
                            html +=
                                '<div class="inline-flex intro-x " style="color:#9b1313"><i data-lucide="accessibility" class="w-6 h-6"></i></div>';

                        html += '</div>';
                        createIcons({
                            icons,
                            'stroke-width': 1.5,
                            nameAttr: 'data-lucide',
                        });

                        return html;
                    },
                },
                {
                    title: 'Semester',
                    field: 'semester',
                    headerSort: false,
                    headerHozAlign: 'left',
                },
                {
                    title: 'Course',
                    field: 'course',
                    headerSort: false,
                    headerHozAlign: 'left',
                },
                {
                    title: 'Status',
                    field: 'status_id',
                    headerHozAlign: 'left',
                },
            ],
            ajaxResponse: function (url, params, response) {
                var total_rows =
                    response.all_rows && response.all_rows > 0
                        ? response.all_rows
                        : 0;

                if (total_rows > 0) {
                    $('#unsignedResultCount').removeClass('hidden');

                    $('#unsignedResultCount')
                        .attr('data-total', total_rows)
                        .html(total_rows + ' students found');
                } else {
                    $('#unsignedResultCount').attr('data-total', '0').html('');
                }

                return response;
            },
            renderComplete() {
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });

                $(document).find('.autoFillDropdown').html('').fadeOut();
                $(document)
                    .find('.flagLinks')
                    .each(function () {
                        $(this).attr('href', 'javascript:void(0);');
                    });
            },
            rowClick: function (e, row) {
                window.open(row.getData().url, '_blank');
            },
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
