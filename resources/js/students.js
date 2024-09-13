import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";


("use strict");
var liveStudentsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let form_data = $('#studentSearchForm').serialize();

        let tableContent = new Tabulator("#liveStudentsListTable", {
            ajaxURL: route("student.list"),
            ajaxParams: { form_data: form_data},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [50, 100, 250],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Reg. No",
                    field: "registration_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                                    html += '<img alt="'+cell.getData().first_name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -13px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().registration_no+'</div>';
                                    
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                },
                {
                    title: "",
                    field: "full_time",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {  
                        let day=false;
                        if(cell.getData().full_time==1) 
                            day = 'text-slate-900' 
                        else  
                            day = 'text-amber-600'
                        var html = '<div class="flex">';
                                html += '<div class="w-8 h-8 '+day+' intro-x inline-flex">';
                                if(cell.getData().full_time==1)
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg>';
                                else
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>';
                                
                                html += '</div>';
                            if(cell.getData().disability==1)
                                html += '<div class="inline-flex intro-x " style="color:#9b1313"><i data-lucide="accessibility" class="w-6 h-6"></i></div>';
                            
                            html += '</div>';
                            createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide"});

                        return html;
                    }
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                $(document).find('.autoFillDropdown').html('').fadeOut();
            },
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
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
        $("#tabulator-export-csv-LSD").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-LSD").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-LSD").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Students Details",
            });
        });

        $("#tabulator-export-html-LSD").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-LSD").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var liveGroupListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let form_data = $('#studentSearchForm').serialize();
        let tableContent = new Tabulator("#liveStudentsListTable", {
            ajaxURL: route("student.list.by.groupsearch"),
            ajaxParams: { form_data: form_data},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [50, 100, 250],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Reg. No",
                    field: "registration_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                                    html += '<img alt="'+cell.getData().first_name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -13px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().registration_no+'</div>';
                                    
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                },
                {
                    title: "",
                    field: "full_time",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {  
                        let day=false;
                        if(cell.getData().full_time==1) 
                            day = 'text-slate-900' 
                        else  
                            day = 'text-amber-600'
                        var html = '<div class="flex">';
                                html += '<div class="w-8 h-8 '+day+' intro-x inline-flex">';
                                if(cell.getData().full_time==1)
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg>';
                                else
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>';
                                
                                html += '</div>';
                            if(cell.getData().disability==1)
                                html += '<div class="inline-flex intro-x " style="color:#9b1313"><i data-lucide="accessibility" class="w-6 h-6"></i></div>';
                            
                            html += '</div>';
                            createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide"});

                        return html;
                    }
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                }
            ],
            ajaxResponse:function(url, params, response){
                
                var total_rows = (response.all_rows && response.all_rows > 0 ? response.all_rows : 0);
                if(total_rows > 0){
                    $('#unsignedResultCount').attr('data-total', total_rows).html(total_rows+' students found');
                }else{
                    $('#unsignedResultCount').attr('data-total', '0').html('');
                }

                return response;
            },
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                $(document).find('.autoFillDropdown').html('').fadeOut();
            },
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
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
        $("#tabulator-export-csv-LSD").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-LSD").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-LSD").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Students Details",
            });
        });

        $("#tabulator-export-html-LSD").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-LSD").on("click", function (event) {
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
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };


    $('.lccTom').each(function(){
        if ($(this).attr("multiple") !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: "Remove this item",
                    },
                }
            };
        }
        new TomSelect(this, tomOptions);
    })

    if($('#liveStudentsListTable').length > 0){
        let tomOptionsMul = {
            ...tomOptions,
            plugins: {
                ...tomOptions.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };
        var student_status = new TomSelect('#student_status', tomOptionsMul);
        var academic_year = new TomSelect('#academic_year', tomOptionsMul);
        var intake_semester = new TomSelect('#intake_semester', tomOptionsMul);
        var attendance_semester = new TomSelect('#attendance_semester', tomOptionsMul);
            attendance_semester.clear()
            attendance_semester.disable();
        var course = new TomSelect('#course', tomOptionsMul);
            course.clear(true)
            course.disable();
        var group = new TomSelect('#group', tomOptionsMul);
            group.clear(true)
            group.disable();
        var term_status = new TomSelect('#term_status', tomOptionsMul);
        var student_type = new TomSelect('#student_type', tomOptionsMul);
        var group_student_status = new TomSelect('#group_student_status', tomOptionsMul);
        ///course.list.by.academic.term
        academic_year.on('item_add', function(e) {
            let academicList = academic_year.getValue();
            
            //attendance_semester.clear(true)
            //attendance_semester.clearOptions();
            //attendance_semester.disable();
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
        })
        // Reset Tom Select
        function resetStudentIDSearch(){
            $('#registration_no').val('');
        }

        function resetStudentSearch(){
            student_status.clear(true);
            $('#studentSearchStatus').val('0');
            $('#student_id, #student_name, #student_dob #student_abr, #student_ssn, #student_uhn, #student_mobile, #student_email, #student_post_code').val('');
        }

        function resetGroupSearch() {
            academic_year.clear(true);
            intake_semester.clear(true);
            attendance_semester.clear(true);
            course.clear(true); 
            group.clear(true); 
            
            term_status.clear(true);
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
            function filterStudentGroupTable() {
                liveGroupListTable.init();
            }
            
            $("#studentIDSearchBtn, #studentIDSearchSubmitBtn, #studentSearchSubmitBtn").on("click", function (event) {
                filterStudentListTable();
            });
            $("#studentGroupSearchSubmitBtn").on("click", function (event) {
                var $academic_year = $('#academic_year');
                var $intake_semester = $('#intake_semester');
                var $termDeclaration = $('#attendance_semester');
                var $course = $('#course');

                let List1 = attendance_semester.getValue();
                let List2 = academic_year.getValue();
                let List3 = course.getValue();
                let List4 = group.getValue();

                if(List1.length>0 && List2.length>0 ){
                    filterStudentGroupTable();
                }else{
                    if($academic_year.val() != ''){
                        $academic_year.siblings('.acc__input-error').html('This field is required.')
                    }else{
                        $academic_year.siblings('.acc__input-error').html('')
                    }
                    
                    if($termDeclaration.val() != ''){
                        $termDeclaration.siblings('.acc__input-error').html('This field is required.')
                    }else{
                        $termDeclaration.siblings('.acc__input-error').html('')
                    }
                    // if($course.val() != ''){
                    //     $course.siblings('.acc__input-error').html('This field is required.')
                    // }else{
                    //     $course.siblings('.acc__input-error').html('')
                    // }
                }
            });

            $("#resetStudentSearch").on("click", function (event) {
                resetStudentSearch();
                resetGroupSearch();
                resetStudentIDSearch();

                //filterStudentListTable();
                $('#liveStudentsListTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
            });
        /* End List Table Inits */


        
        const studentSearchAccordion = tailwind.Accordion.getOrCreateInstance(document.querySelector("#studentSearchAccordion"));
        $('#advanceSearchToggle').on('click', function(e){
            e.preventDefault();
            $('#studentSearchAccordionWrap').slideToggle();
            $('#studentIDSearchBtn').fadeToggle();
            studentSearchAccordion.toggle();
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            //filterStudentListTable();
            $('#liveStudentsListTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
        });

        $('#studentSearchBtn').on('click', function(){
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            if($(this).hasClass('collapsed')){
                $('#studentSearchStatus').val(1);
                $('#groupSearchStatus').val(0);
            }else{
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(0);
            }

            //filterStudentListTable();
            $('#liveStudentsListTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
        });

        $('#studentGroupSearchBtn').on('click', function(){
            resetStudentSearch();
            resetGroupSearch();
            resetStudentIDSearch();

            if($(this).hasClass('collapsed')){
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(1);
            }else{
                $('#studentSearchStatus').val(0);
                $('#groupSearchStatus').val(0);
            }

            //filterStudentListTable();
            $('#liveStudentsListTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
        });

        $('.registration_no').on('keyup', function(){
            var $theInput = $(this);
            var SearchVal = $theInput.val();

            if(SearchVal.length >= 3){
                axios({
                    method: "post",
                    url: route('student.filter.id'),
                    data: {SearchVal : SearchVal},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $theInput.siblings('.autoFillDropdown').html(response.data.htm).fadeIn();
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                        $theInput.siblings('.autoFillDropdown').html('').fadeOut();
                    }
                });
            }else{
                $theInput.siblings('.autoFillDropdown').html('').fadeOut();
            }
        });

        $('.autoFillDropdown').on('click', 'li a:not(".disable")', function(e){
            e.preventDefault();
            var registration_no = $(this).attr('href');
            $(this).parent('li').parent('ul.autoFillDropdown').siblings('.registration_no').val(registration_no);
            $(this).parent('li').parent('.autoFillDropdown').html('').fadeOut();
        });
    }
})();