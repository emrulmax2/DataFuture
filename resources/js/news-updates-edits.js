import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";
import tippy, { roundArrow } from "tippy.js";

(function(){
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

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    //const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#successModal .successCloser").attr('data-action', 'none');
        $("#successModal .successCloser").attr('data-red', '');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            if($(this).attr('data-red') != ''){
                window.location.href = $(this).attr('data-red');
            }else{
                successModal.hide();
                window.location.reload();
            }
        }else{
            successModal.hide();
        }
    })

    let addEditor;
    if($("#addEditor").length > 0){
        const el = document.getElementById('addEditor');
        ClassicEditor.create(el).then((editor) => {
            addEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }


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
    var attendance_semester = new TomSelect('#attendance_semester', tomOptionsMul);
    var course = new TomSelect('#course', tomOptionsMul);

    var group = new TomSelect('#group', tomOptionsMul);
        group.clear(true);
        group.disable();

    var student_type = new TomSelect('#student_type', tomOptionsMul);
    var group_student_status = new TomSelect('#group_student_status', tomOptionsMul);
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


    $('.groupSearchBodyToggler').on('click', function(e){
        e.preventDefault();
        let $theToggler = $(this);

        if($theToggler.hasClass('active')){
            $('#for_all_students').prop('checked', true);
            $theToggler.removeClass('active');
            $theToggler.siblings('.groupSearchBody').fadeOut('fast', function(){
                intake_semester.clear(true);
                attendance_semester.clear(true);
                course.clear(true);
                group.clear(true);
                student_type.clear(true);
                group_student_status.clear(true);
                evening_weekend.clear(true);
            });
        }else{
            $('#saveNewsUpdts').attr('disabled', 'disabled');
            $('#for_all_students').prop('checked', false);
            $theToggler.addClass('active');
            $theToggler.siblings('.groupSearchBody').fadeIn();
        }
        $('.groupSearchCount').fadeOut().html('');
        $('.studentSearchResult').fadeOut().html('');
    });

    $('#for_all_students').on('change', function(){
        let $theCheckBox = $(this);
        let $theToggler = $('.groupSearchBodyToggler');

        if($theCheckBox.prop('checked')){
            if($theToggler.hasClass('active')){
                $('.groupSearchCount').fadeOut().html('');
                $('.studentSearchResult').fadeOut().html('');
                $theToggler.removeClass('active');
                $theToggler.siblings('.groupSearchBody').fadeOut('fast', function(){
                    intake_semester.clear(true);
                    attendance_semester.clear(true);
                    course.clear(true);
                    group.clear(true);
                    student_type.clear(true);
                    group_student_status.clear(true);
                    evening_weekend.clear(true);
                });
            }
            $('#saveNewsUpdts').removeAttr('disabled');
        }
    });

    $('#newsUpdateCreateForm #newsUpdateDocument').on('change', function(){
        var inputs = document.getElementById('newsUpdateDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>'+name+'</div>';
        }

        $('#newsUpdateCreateForm .newsUpdateDocumentNames').fadeIn().html(html);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#groupStudentSearch').on('click', function(e){
        e.preventDefault();
        let $theButton = $(this);
        const form = document.getElementById('newsUpdateCreateForm');

        $theButton.attr('disabled', 'disabled');
        $theButton.find('svg.theLoader').fadeIn();

        let form_data = new FormData(form);
        axios({
            method: 'post',
            url: route('news.updates.find.students'),
            data: form_data,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        }).then((response) => {
            $theButton.removeAttr('disabled');
            $theButton.find('svg.theLoader').fadeOut();
            if (response.status == 200) {
                if(response.data.count > 0){
                    $('.studentSearchResult').fadeIn('fast', function(){
                        $('.groupSearchCount').fadeIn().html(response.data.count+' Students found.')
                        $(this).html(response.data.html);
                    });
                    $('#saveNewsUpdts').removeAttr('disabled');
                }else{
                    $('.groupSearchCount').fadeOut().html('')
                    $('.studentSearchResult').fadeIn('fast', function(){
                        $(this).html('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> No student found.</div>');
                    });
                    $('#saveNewsUpdts').attr('disabled', 'disabled');
                }

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch((error) => {
            $('#saveNewsUpdts').attr('disabled', 'disabled');
            $theButton.removeAttr('disabled');
            $theButton.find('svg.theLoader').fadeOut();
            if (error.response) {
                console.log('error');
            }
        });
    })

    $(document).on('click', '.removeStd', function(e){
        e.preventDefault();
        $(this).parent('.singleStudent').remove();
    });

    $('#newsUpdateCreateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('newsUpdateCreateForm');
    
        document.querySelector('#saveNewsUpdts').setAttribute('disabled', 'disabled');
        document.querySelector("#saveNewsUpdts svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#newsUpdateCreateForm input#newsUpdateDocument')[0].files[0]);
        form_data.append("content", addEditor.getData());
        axios({
            method: "post",
            url: route('news.updates.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveNewsUpdts').removeAttribute('disabled');
            document.querySelector("#saveNewsUpdts svg").style.cssText = "display: none;";

            if (response.status == 200) {

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.message);
                    $("#successModal .successCloser").attr('data-action', 'RELOAD').attr('data-red', response.data.red);
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.href = response.data.red
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveNewsUpdts').removeAttribute('disabled');
            document.querySelector("#saveNewsUpdts svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#newsUpdateCreateForm .${key}`).addClass('border-danger');
                        $(`#newsUpdateCreateForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Delete Document
    $(document).on('click', '.deleteDoc', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delte this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELDOCS');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELDOCS'){
            axios({
                method: 'post',
                url: route('news.updates.document.delete'),
                data: {row_id : recordID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD').attr('data-red', '');
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
    });

    $(document).on('click', '.downloadDoc', function(e){
        e.preventDefault();
        let $theLink = $(this);
        let docId = $theLink.attr('data-docid');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('news.updates.document.download'), 
            data: {row_id : docId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });

})();