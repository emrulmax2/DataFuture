import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    if($('#studentGroupSearchForm').length > 0){
        let groupTomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Search Here...',
            //persist: false,
            create: false,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };

        let groupTomOptionsMul = {
            ...groupTomOptions,
            plugins: {
                ...groupTomOptions.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };

        var intake_semester = new TomSelect('#intake_semester', groupTomOptionsMul);
        var attendance_semester = new TomSelect('#attendance_semester', groupTomOptionsMul);
            attendance_semester.clear(true);

        var course = new TomSelect('#course', groupTomOptionsMul);
            course.clear(true)
            course.disable();
        var group = new TomSelect('#group', groupTomOptionsMul);
            group.clear(true)
            group.disable();

        var group_student_status = new TomSelect('#group_student_status', groupTomOptionsMul);

        $('#intake_semester').on('change', function(){
            let intakeSemester = intake_semester.getValue();
            attendance_semester.clear(true);
            course.clear(true);
            course.clearOptions();
            course.disable();
            group.clear(true);
            group.clearOptions();
            group.disable();
            group_student_status.clear(true);
            group_student_status.clearOptions();
            group_student_status.disable();

            if(intakeSemester.length > 0){
                axios({
                    method: "post",
                    url: route('student.get.coureses.by.intake.or.term'),
                    data: {intakeSemester : intakeSemester},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var res = response.data.res;
                        course.enable();
                        $.each(res, function(index, row) {
                            course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        course.refreshOptions();
                    }

                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                        course.enable();
                        course.clear(true);
                        course.clearOptions();
                    }

                });
            }
        });

        $('#attendance_semester').on('change', function(){
            let attenSemesters = attendance_semester.getValue();
            intake_semester.clear(true);
            course.clear(true);
            course.clearOptions();
            course.disable();
            group.clear(true);
            group.clearOptions();
            group.disable();
            group_student_status.clear(true);
            group_student_status.clearOptions();
            group_student_status.disable();

            if(attenSemesters.length > 0){
                axios({
                    method: "post",
                    url: route('student.get.coureses.by.intake.or.term'),
                    data: {attenSemesters : attenSemesters},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var res = response.data.res;
                        course.enable();
                        $.each(res, function(index, row) {
                            course.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        course.refreshOptions();
                    }

                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                        course.enable();
                        course.clear(true);
                        course.clearOptions();
                    }

                });

                axios({
                    method: "post",
                    url: route('student.get.status.by.groups'),
                    data: { academic_years : [], term_declaration_ids :  attenSemesters ,  courses : [], groups: [] },
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var res = response.data.res;
                        group_student_status.enable();
                        $.each(res, function(index, row) {
                            group_student_status.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        group.refreshOptions();
                    }

                }).catch(error => {
                    if (error.response) {
                        group_student_status.enable();
                        group_student_status.clear();
                        group_student_status.clearOptions();
                        group_student_status.log('error');
                    }
                });
            }
        });
        

        $('#course').on('change', function(e){
            let courses = course.getValue();
            let attenSemesters = attendance_semester.getValue();
            group.clear(true);
            group.clearOptions();
            group.disable();
            group_student_status.clear(true);
            group_student_status.clearOptions();
            group_student_status.disable();

            if(courses.length > 0){
                axios({
                    method: "post",
                    url: route('student.get.groups.by.course'),
                    data: {attenSemesters : attenSemesters, courses : courses},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var res = response.data.res;
                        group.enable();
                        $.each(res, function(index, row) {
                            group.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        group.refreshOptions();
                    }

                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                        group.enable();
                        group.clear(true);
                        group.clearOptions();
                    }

                });
            }


            axios({
                method: "post",
                url: route('student.get.status.by.groups'),
                data: { academic_years : [], term_declaration_ids :  attenSemesters ,  courses : courses, groups: [] },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    var res = response.data.res;
                    group_student_status.enable();
                    $.each(res, function(index, row) {
                        group_student_status.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    group.refreshOptions();
                }

            }).catch(error => {
                if (error.response) {
                    group_student_status.enable();
                    group_student_status.clear();
                    group_student_status.clearOptions();
                    console.log('error');

                }
            });
        })

        $('#group').on('change', function(){
            let attenSemesters = attendance_semester.getValue();
            let courses = course.getValue();
            let groups = group.getValue();
            group_student_status.clear(true)
            group_student_status.disable();
            group_student_status.clearOptions();

            axios({
                method: "post",
                url: route('student.get.status.by.groups'),
                data: { academic_years : [], term_declaration_ids :  attenSemesters ,  courses : courses, groups: groups },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    var res = response.data.res;
                    group_student_status.enable();
                    $.each(res, function(index, row) {
                        group_student_status.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    group.refreshOptions();
                }

            }).catch(error => {
                if (error.response) {
                    group_student_status.enable();
                    group_student_status.clear();
                    group_student_status.clearOptions();
                    console.log('error');

                }
            });
        });

    }
})()