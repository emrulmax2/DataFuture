import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';

(function () {
    let tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        maxOptions: null,
        allowEmptyOption: true,
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

    let tomOptionsMul = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: 'Remove this item',
            },
        },
    };

    var intake_semester = new TomSelect('#intake_semester', tomOptionsMul);
    var attendance_semester = new TomSelect(
        '#attendance_semester',
        tomOptionsMul
    );

    var course = new TomSelect('#course', tomOptionsMul);

    var group = new TomSelect('#group', tomOptionsMul);
    group.clear(true);
    group.disable();

    var student_type = new TomSelect('#student_type', tomOptionsMul);
    var group_student_status = new TomSelect(
        '#group_student_status',
        tomOptionsMul
    );
    var evening_weekend = new TomSelect('#evening_weekend', tomOptions);

    var is_self_funded = new TomSelect('#is_self_funded', tomOptions);

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
            let is_self_funds = is_self_funded.getValue();

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
                    is_self_funded : is_self_funds,
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
    /* ---------- Student Data Report screen (26 Nov 2026, user's student-data-report design) ----------
       Same requests as before: the search posts #studentGroupSearchForm to report.student.data.total,
       the export posts the ticked column names + studentIds to report.student.data.excel. */
    const csrfHeader = () => ({ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') });
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const plural = (n, word) => `${n.toLocaleString()} ${word}${n === 1 ? '' : 's'}`;

    const filterSelects = [intake_semester, attendance_semester, course, group, is_self_funded, evening_weekend, student_type, group_student_status];
    const singleSelects = [is_self_funded, evening_weekend];
    // Each filter's options as the page loaded them. The cascade above narrows the lists; Reset puts them back.
    const initialOptions = new Map(
        filterSelects.map((ts) => [
            ts,
            Object.values(ts.options)
                .sort((a, b) => (a.$order || 0) - (b.$order || 0))
                .map((o) => ({ value: o.value, text: o.text })),
        ])
    );

    // Empty-state text ("All intakes"…) on the control; the dropdown's search box keeps "Search Here...".
    filterSelects.forEach((ts) => (ts.control.dataset.placeholder = $(ts.input).data('placeholder') || ''));

    let studentCount = null; // null = no current search result
    let foundIds = []; // student ids of the current result (sent with the export, as before)
    localStorage.removeItem('studentIdsList2024'); // left over from the old screen

    /* --- filters --- */
    function filterIsSet(ts) {
        const v = ts.getValue();
        return Array.isArray(v) ? v.filter((x) => x !== '').length > 0 : v !== '' && v != null;
    }
    function syncGroupHint() {
        $('#sdrGroupHint').prop('hidden', !group.isDisabled);
    }
    function syncFilters() {
        let n = 0;
        filterSelects.forEach((ts) => {
            const set = filterIsSet(ts);
            $(ts.input).closest('[data-sdr-filter]').toggleClass('is-set', set);
            if (set) n++;
        });
        $('#sdrFilterCount').text(n ? `${n} filter${n > 1 ? 's' : ''} applied` : 'No filters applied');
        syncGroupHint();
    }
    // The cascade enables Master Group without a change event: keep its hint in step.
    ['enable', 'disable'].forEach((method) => {
        const original = group[method].bind(group);
        group[method] = (...args) => {
            const result = original(...args);
            syncGroupHint();
            return result;
        };
    });
    filterSelects.forEach((ts) =>
        ts.on('change', () => {
            syncFilters();
            // A result found with other filters is stale: search again before exporting.
            if (studentCount !== null) {
                foundIds = [];
                setResult(null, 'Filters changed. Search again to update the count.');
            }
        })
    );

    $('.resetSearch').on('click', function () {
        resetGroupSearch();
    });

    function resetGroupSearch() {
        filterSelects.forEach((ts) => {
            ts.clear(true);
            ts.clearOptions();
            initialOptions.get(ts).forEach((o) => ts.addOption(o));
            if (singleSelects.includes(ts)) ts.setValue('', true);
        });
        group.disable();
        $('#groupSearchStatus').val('0');
        syncFilters();
        foundIds = [];
        setResult(null);
    }

    function setResult(n, message) {
        studentCount = n;
        const $res = $('#sdrResult').removeClass('is-warn is-error');
        $res.find('.sdr-dot').toggleClass('is-on', n !== null && n > 0);
        $('#sdrResultText').html(
            n === null
                ? esc(message || 'Run a search to see how many students match.')
                : `<b>${n.toLocaleString()}</b> student${n === 1 ? '' : 's'} found`
        );
        $('#sdrStatStudents, #sdrMobileStudents').text(n === null ? '–' : n.toLocaleString());
        syncPicker();
    }
    function setResultMessage(kind, message) {
        setResult(null, message);
        $('#sdrResult').addClass(kind === 'error' ? 'is-error' : 'is-warn');
    }

    $('#studentGroupSearchForm').on('submit', function (event) {
        event.preventDefault();
        const btn = document.getElementById('studentGroupSearchSubmitBtn');
        btn.disabled = true;
        btn.classList.add('is-busy');
        foundIds = [];
        setResult(null, 'Searching…');

        axios({
            method: 'post',
            url: route('report.student.data.total'),
            data: new FormData(this),
            headers: csrfHeader(),
        })
            .then((response) => {
                if (response.status == 200) {
                    foundIds = Array.isArray(response.data.student_ids) ? response.data.student_ids : [];
                    setResult(parseInt(response.data.all_rows, 10) || 0);
                } else {
                    setResult(null);
                }
            })
            .catch((error) => {
                foundIds = [];
                const status = error.response ? error.response.status : 0;
                if (status == 302) setResultMessage('warn', 'Choose at least one filter, then search.');
                else if (status == 422) setResultMessage('error', 'Oops! Something went wrong.');
                else setResultMessage('error', 'No data found. Please try again.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.classList.remove('is-busy');
            });
    });

    /* --- column picker --- */
    const groupsEl = document.getElementById('studentExcelForm');
    const groupEls = () => Array.from(groupsEl.querySelectorAll('[data-sdr-group]'));
    const fieldBoxes = (root) => Array.from(root.querySelectorAll('.sdr-field input'));
    const visibleBoxes = (root) => Array.from(root.querySelectorAll('.sdr-field:not([hidden]) input'));
    const fieldLabel = (box) => box.nextElementSibling.textContent;
    fieldBoxes(groupsEl).forEach((box, i) => (box.dataset.i = i));

    function syncPicker() {
        let total = 0;
        let html = '';
        groupEls().forEach((el) => {
            const boxes = fieldBoxes(el);
            const on = boxes.filter((b) => b.checked);
            total += on.length;
            el.classList.toggle('has', on.length > 0);
            el.querySelector('.sdr-count').textContent = `${on.length}/${boxes.length}`;
            const vis = visibleBoxes(el);
            const visOn = vis.filter((b) => b.checked).length;
            const all = el.querySelector('[data-sdr-all]');
            all.checked = vis.length > 0 && visOn === vis.length;
            all.indeterminate = visOn > 0 && visOn < vis.length;
            if (on.length) {
                html +=
                    `<h4>${esc(el.dataset.title)}</h4><div class="sdr-chips">` +
                    on.map((b) => `<span class="sdr-chip">${esc(fieldLabel(b))}<button type="button" data-rm="${b.dataset.i}" aria-label="Remove ${esc(fieldLabel(b))}">×</button></span>`).join('') +
                    '</div>';
            }
        });
        $('#sdrSelection').html(
            html ||
                '<div class="sdr-empty"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/></svg><div>No columns selected yet.<br>Tick fields to build your export.</div></div>'
        );
        $('#sdrStatCols, #sdrMobileCols').text(total);
        $('#sdrClearAll').prop('disabled', total === 0);
        const vis = visibleBoxes(groupsEl);
        $('#sdrSelectAll').text(vis.length && vis.every((b) => b.checked) ? 'Deselect all' : 'Select all');

        const busy = $('[data-sdr-export]').first().hasClass('is-busy');
        const ready = total > 0 && studentCount > 0 && foundIds.length > 0;
        $('[data-sdr-export]').prop('disabled', !ready || busy);
        $('#sdrWhy').text(
            ready
                ? `${plural(total, 'column')} × ${plural(studentCount, 'student')}`
                : studentCount === null
                  ? 'Run a search first'
                  : studentCount === 0
                    ? 'No students match these filters'
                    : 'Select at least one column'
        );
    }

    groupsEl.addEventListener('submit', (e) => e.preventDefault());
    groupsEl.addEventListener('change', (e) => {
        if (e.target.matches('[data-sdr-all]')) {
            visibleBoxes(e.target.closest('[data-sdr-group]')).forEach((b) => (b.checked = e.target.checked));
        }
        syncPicker();
    });
    groupsEl.addEventListener('click', (e) => {
        const toggle = e.target.closest('.sdr-g-toggle');
        if (!toggle) return;
        const closed = toggle.closest('[data-sdr-group]').classList.toggle('closed');
        toggle.setAttribute('aria-expanded', String(!closed));
        syncExpand();
    });
    function syncExpand() {
        $('#sdrExpandAll').text(groupsEl.querySelectorAll('[data-sdr-group]:not(.closed)').length ? 'Collapse all' : 'Expand all');
    }
    $('#sdrExpandAll').on('click', function () {
        const close = groupsEl.querySelectorAll('[data-sdr-group]:not(.closed)').length > 0;
        groupEls().forEach((g) => {
            g.classList.toggle('closed', close);
            g.querySelector('.sdr-g-toggle').setAttribute('aria-expanded', String(!close));
        });
        syncExpand();
    });
    $('#sdrSelectAll').on('click', function () {
        const vis = visibleBoxes(groupsEl);
        const on = !vis.every((b) => b.checked);
        vis.forEach((b) => (b.checked = on));
        syncPicker();
    });
    $('#sdrClearAll').on('click', function () {
        fieldBoxes(groupsEl).forEach((b) => (b.checked = false));
        syncPicker();
    });
    $('#sdrSelection').on('click', '[data-rm]', function () {
        const box = groupsEl.querySelector(`.sdr-field input[data-i="${this.dataset.rm}"]`);
        if (box) box.checked = false;
        syncPicker();
    });

    $('#sdrFind').on('input', function () {
        const q = this.value.trim().toLowerCase();
        let any = false;
        groupEls().forEach((g) => {
            const whole = q && g.dataset.title.toLowerCase().includes(q);
            let hit = false;
            g.querySelectorAll('.sdr-field').forEach((f) => {
                const span = f.querySelector('span');
                const text = span.textContent;
                const i = q ? text.toLowerCase().indexOf(q) : -1;
                const show = !q || whole || i > -1;
                f.hidden = !show;
                hit = hit || show;
                span.innerHTML = i > -1 ? esc(text.slice(0, i)) + '<mark>' + esc(text.slice(i, i + q.length)) + '</mark>' + esc(text.slice(i + q.length)) : esc(text);
            });
            g.hidden = !hit;
            any = any || hit;
            if (q && hit) {
                g.classList.remove('closed');
                g.querySelector('.sdr-g-toggle').setAttribute('aria-expanded', 'true');
            }
        });
        $('#sdrNone').prop('hidden', any);
        $('#sdrNoneQ').text(this.value);
        syncExpand();
        syncPicker();
    });

    /* --- export --- */
    function toast(message) {
        const t = document.getElementById('sdrToast');
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }

    $('[data-sdr-export]').on('click', function (e) {
        e.preventDefault();
        // Ticked columns in the old page's order: the server lays out the sheet in the order the names arrive.
        const ticked = fieldBoxes(groupsEl)
            .filter((b) => b.checked)
            .sort((a, b) => a.dataset.order - b.dataset.order);
        if (!ticked.length || !foundIds.length) return;

        const formData = new FormData();
        ticked.forEach((b) => formData.append(b.name, b.value));
        formData.append('studentIds', foundIds.join(','));

        const $buttons = $('[data-sdr-export]').addClass('is-busy').prop('disabled', true);
        toast(`Exporting ${plural(ticked.length, 'column')} for ${plural(studentCount, 'student')}…`);

        axios({
            method: 'post',
            url: route('report.student.data.excel'),
            data: formData,
            headers: csrfHeader(),
            responseType: 'blob',
        })
            .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'student_data_report_download.xlsx');
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => window.URL.revokeObjectURL(url), 1000);
                toast('Excel downloaded.');
            })
            .catch((error) => {
                console.log(error);
                toast('The export failed. Please try again.');
            })
            .finally(() => {
                $buttons.removeClass('is-busy');
                syncPicker();
            });
    });

    // Narrow report (container query hides the panel's button): show the bottom bar instead.
    const panelFoot = document.getElementById('sdrPanelFoot');
    const mobileBar = document.getElementById('sdrMobileBar');
    const syncMobileBar = () => (mobileBar.hidden = getComputedStyle(panelFoot).display !== 'none');
    if (window.ResizeObserver) new ResizeObserver(syncMobileBar).observe(document.getElementById('studentDataReport'));
    syncMobileBar();

    syncFilters();
    syncPicker();
    syncExpand();


    function resetList() {
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
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
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
})();
