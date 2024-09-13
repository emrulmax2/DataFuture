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

        var academic_year = new TomSelect('#academic_year', groupTomOptionsMul);
        var intake_semester = new TomSelect('#intake_semester', groupTomOptionsMul);
        var attendance_semester = new TomSelect('#attendance_semester', groupTomOptionsMul);
            attendance_semester.clear()
            attendance_semester.disable();
        var course = new TomSelect('#course', groupTomOptionsMul);
            course.clear(true)
            course.disable();
        var group = new TomSelect('#group', groupTomOptionsMul);
            group.clear(true)
            group.disable();
        var term_status = new TomSelect('#term_status', groupTomOptionsMul);
        var student_type = new TomSelect('#student_type', groupTomOptionsMul);
        var group_student_status = new TomSelect('#group_student_status', groupTomOptionsMul);

        academic_year.on('item_add', function(e) {
            let academicList = academic_year.getValue();
            
            if(academicList.length > 0 ){
                axios({
                    method: "post",
                    url: route('student.get.term.by.academics'),
                    data: { academic_years : academicList },
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var res = response.data.res;
                        attendance_semester.enable();
                        $.each(res, function(index, row) {
                            attendance_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        attendance_semester.refreshOptions();
                    }
                }).catch(error => {
                    if (error.response) {

                        attendance_semester.enable();
                        attendance_semester.clear();
                        attendance_semester.clearOptions();
                        console.log('error');

                    }
                });
            }else{
                attendance_semester.clear(true)
                attendance_semester.clearOptions();
                attendance_semester.disable();
            }
        })
        academic_year.on('item_remove', function(e) {

            let academicList = academic_year.getValue();
            
            attendance_semester.clear(true)
            attendance_semester.clearOptions();
            attendance_semester.disable();
            if(academicList.length > 0 ){
                axios({
                    method: "post",
                    url: route('student.get.term.by.academics'),
                    data: { academic_years : academicList },
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var res = response.data.res;
                        attendance_semester.enable();
                        $.each(res, function(index, row) {
                            attendance_semester.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        attendance_semester.refreshOptions();
                    }
                }).catch(error => {
                    if (error.response) {

                        attendance_semester.enable();
                        attendance_semester.clear();
                        attendance_semester.clearOptions();
                        console.log('error');

                    }
                });
            }else{
                attendance_semester.clear(true)
                attendance_semester.clearOptions();
                attendance_semester.disable();
            }
        })
        attendance_semester.on('item_add', function(e) {

            let List1 = attendance_semester.getValue();
            let List2 = academic_year.getValue();
            course.clear(true)
            course.disable();
            course.clearOptions();

            if(List1.length > 0 ) {
                
                axios({
                    method: "post",
                    url: route('student.get.coureses.by.terms'),
                    data: { academic_years : List2, term_declaration_ids :  List1},
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

                        course.enable();
                        course.clear();
                        course.clearOptions();
                        console.log('error');

                    }

                });

                /* catch the Status */
                let List3 = course.getValue();
                let List4 = group.getValue();
                group_student_status.clear(true)
                group_student_status.disable();
                group_student_status.clearOptions();

                if(List1.length > 0 ) {

                    axios({
                        method: "post",
                        url: route('student.get.status.by.groups'),
                        data: { academic_years : List2, term_declaration_ids :  List1 ,  courses : List3, groups: List4 },
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

                } else {

                    group_student_status.clear(true)
                    group_student_status.clearOptions();
                    group_student_status.disable();

                }
                /* End catch the Status */
            }else{
                group_student_status.clear(true)
                group_student_status.clearOptions();
                group_student_status.disable();
            }
        })

        attendance_semester.on('item_remove', function(e) {
            let List1 = attendance_semester.getValue();
            let List2 = academic_year.getValue();
            
            course.clear(true)
            course.disable();
            course.clearOptions();
            
            if(List1.length > 0 ){
                axios({
                    method: "post",
                    url: route('student.get.coureses.by.terms'),
                    data: { academic_years : List2, term_declaration_ids :  List1},
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

                        course.enable();
                        course.clear();
                        course.clearOptions();
                        console.log('error');

                    }
                });
            }else{
                group.clear(true)
                group.clearOptions();
                group.disable();
            }
        })

        course.on('item_add', function(e) {
            let List1 = attendance_semester.getValue();
            let List2 = academic_year.getValue();
            let List3 = course.getValue();
            
            group.clear(true)
            group.disable();
            group.clearOptions();

            if(List1.length > 0 ){
                axios({
                    method: "post",
                    url: route('student.get.groups'),
                    data: { academic_years : List2, term_declaration_ids :  List1 ,  courses : List3},
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

                        group.enable();
                        group.clear();
                        group.clearOptions();
                        group.log('error');

                    }
                });
            }else{
                group.clear(true)
                group.clearOptions();
                group.disable();
            }
        })

        course.on('item_remove', function(e) {
            let List1 = attendance_semester.getValue();
            let List2 = academic_year.getValue();
            let List3 = course.getValue();
            
            group.clear(true)
            group.disable();
            group.clearOptions();

            if(List1.length > 0 ){
                axios({
                    method: "post",
                    url: route('student.get.groups'),
                    data: { academic_years : List2, term_declaration_ids :  List1 ,  courses : List3},
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

                        group.enable();
                        group.clear();
                        group.clearOptions();
                        group.log('error');

                    }
                });
            }else{
                group.clear(true)
                group.clearOptions();
                group.disable();
            }
        })


        course.on('item_add', function(e) {
            let List1 = attendance_semester.getValue();
            let List2 = academic_year.getValue();
            let List3 = course.getValue();
            let List4 = group.getValue();
            
            group.clear(true)
            group.disable();
            group.clearOptions();

            if(List1.length > 0 ){
                axios({
                    method: "post",
                    url: route('student.get.groups'),
                    data: { academic_years : List2, term_declaration_ids :  List1 ,  courses : List3},
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

                        group.enable();
                        group.clear();
                        group.clearOptions();
                        group.log('error');

                    }
                });
            }else{
                group.clear(true)
                group.clearOptions();
                group.disable();
            }
        })

        group.on('item_add', function(e) {
            let List1 = attendance_semester.getValue();
            let List2 = academic_year.getValue();
            let List3 = course.getValue();
            let List4 = group.getValue();
            group_student_status.clear(true)
            group_student_status.disable();
            group_student_status.clearOptions();

            if(List1.length > 0 ) {
                axios({
                    method: "post",
                    url: route('student.get.status.by.groups'),
                    data: { academic_years : List2, term_declaration_ids :  List1 ,  courses : List3, groups: List4 },
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
            }else{
                group_student_status.clear(true)
                group_student_status.clearOptions();
                group_student_status.disable();
            }
        })
        group.on('item_remove', function(e) {
            let List1 = attendance_semester.getValue();
            let List2 = academic_year.getValue();
            let List3 = course.getValue();
            let List4 = group.getValue();
            group_student_status.clear(true)
            group_student_status.disable();
            group_student_status.clearOptions();

            if(List1.length > 0 ) {

                axios({
                    method: "post",
                    url: route('student.get.status.by.groups'),
                    data: { academic_years : List2, term_declaration_ids :  List1 ,  courses : List3, groups: List4 },
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

            }else{

                group_student_status.clear(true)
                group_student_status.clearOptions();
                group_student_status.disable();

            }
        });

    }
})()