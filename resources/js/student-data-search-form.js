import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

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

    let tomOptionsMul = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var intake_semester = new TomSelect('#intake_semester', tomOptionsMul);
    var attendance_semester = new TomSelect('#attendance_semester', tomOptionsMul);
      
    var course = new TomSelect('#course', tomOptionsMul);

    var group = new TomSelect('#group', tomOptionsMul);
        group.clear(true)
        group.disable();

    var student_type = new TomSelect('#student_type', tomOptionsMul);
    var group_student_status = new TomSelect('#group_student_status', tomOptionsMul);
    var evening_weekend = new TomSelect('#evening_weekend', tomOptions);

    intake_semester.on('change', function(){
        let intakeSemester = intake_semester.getValue();

        if(intakeSemester.length > 0){
        let intake_semesters = intake_semester.getValue();
        let attendance_semesters = attendance_semester.getValue();
        let courses = course.getValue();
        let groups = group.getValue();
        let student_types = student_type.getValue();
        let group_student_statuses = group_student_status.getValue();
        let evening_weekends = evening_weekend.getValue();

        axios({

            method: "post",
            url: route('student.get.all.student.type'),
            data: { academic_years : '' , 
                    term_declaration_ids: attendance_semesters , 
                    intake_semesters: intake_semesters,
                    courses: courses, 
                    groups:groups,
                    group_student_statuses:group_student_statuses,
                    student_types:student_types,
                    evening_weekends:evening_weekends
            },

            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},

        }).then(response => {
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
                
                $.each(res.intake_semester, function(index, row) {
                    intake_semester.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //intake_semester.setValue(intake_semesters);
                $.each(res.attendance_semester, function(index, row) {
                    attendance_semester.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });

                $.each(res.course, function(index, row) {
                    course.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //course.setValue(courses);

                $.each(res.group_student_status, function(index, row) {
                    group_student_status.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //group_student_status.setValue(group_student_statuses);

                $.each(res.evening_weekend, function(index, row) {
                    evening_weekend.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //evening_weekend.setValue(evening_weekends);

                $.each(res.group, function(index, row) {
                    group.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //group.setValue(groups);
                $.each(res.student_type, function(index, row) {
                    student_type.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
            }
        });
        }
    });
    
    attendance_semester.on('change', function(e) {

        let attendanceSemester = attendance_semester.getValue();

        if(attendanceSemester.length > 0 ) {
            
            let intake_semesters = intake_semester.getValue();
            let attendance_semesters = attendance_semester.getValue();
            let courses = course.getValue();
            let groups = group.getValue();
            let student_types = student_type.getValue();
            let group_student_statuses = group_student_status.getValue();
            let evening_weekends = evening_weekend.getValue();
        
            axios({
                method: "post",
                url: route('student.get.all.student.type'),
                data: { academic_years : '' , 
                        term_declaration_ids: attendance_semesters , 
                        intake_semesters: intake_semesters,
                        courses: courses, 
                        groups:groups,
                        group_student_statuses:group_student_statuses,
                        student_types:student_types,
                        evening_weekends:evening_weekends
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
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
                    
                    $.each(res.intake_semester, function(index, row) {
                        intake_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //intake_semester.setValue(intake_semesters);
                    $.each(res.attendance_semester, function(index, row) {
                        attendance_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.course, function(index, row) {
                        course.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //course.setValue(courses);

                    $.each(res.group_student_status, function(index, row) {
                        group_student_status.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //group_student_status.setValue(group_student_statuses);

                    $.each(res.evening_weekend, function(index, row) {
                        evening_weekend.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //evening_weekend.setValue(evening_weekends);

                    $.each(res.group, function(index, row) {
                        group.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //group.setValue(groups);
                    $.each(res.student_type, function(index, row) {
                        student_type.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                }
            });
        }
    })

    course.on('change', function(e) {
        let coursee = course.getValue();
        
        if(coursee.length > 0 ) {
            
            let intake_semesters = intake_semester.getValue();
            let attendance_semesters = attendance_semester.getValue();
            let courses = course.getValue();
            let groups = group.getValue();
            let student_types = student_type.getValue();
            let group_student_statuses = group_student_status.getValue();
            let evening_weekends = evening_weekend.getValue();
        
            axios({
                method: "post",
                url: route('student.get.all.student.type'),
                data: { academic_years : '' , 
                        term_declaration_ids: attendance_semesters , 
                        intake_semesters: intake_semesters,
                        courses: courses, 
                        groups:groups,
                        group_student_statuses:group_student_statuses,
                        student_types:student_types,
                        evening_weekends:evening_weekends
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
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
                    
                    $.each(res.intake_semester, function(index, row) {
                        intake_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    
                    $.each(res.attendance_semester, function(index, row) {
                        attendance_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.course, function(index, row) {
                        course.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //course.setValue(courses);

                    $.each(res.group_student_status, function(index, row) {
                        group_student_status.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //group_student_status.setValue(group_student_statuses);

                    $.each(res.evening_weekend, function(index, row) {
                        evening_weekend.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //evening_weekend.setValue(evening_weekends);

                    $.each(res.group, function(index, row) {
                        group.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //group.setValue(groups);
                    $.each(res.student_type, function(index, row) {
                        student_type.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                }
            });
        }
    })

    group.on('change', function(e) {
        let groups = group.getValue();
        
        if(groups.length > 0 ) {
            
            let intake_semesters = intake_semester.getValue();
            let attendance_semesters = attendance_semester.getValue();
            let courses = course.getValue();
            let groups = group.getValue();
            let student_types = student_type.getValue();
            let group_student_statuses = group_student_status.getValue();
            let evening_weekends = evening_weekend.getValue();
        
            axios({
                method: "post",
                url: route('student.get.all.student.type'),
                data: { academic_years : '' , 
                        term_declaration_ids: attendance_semesters , 
                        intake_semesters: intake_semesters,
                        courses: courses, 
                        groups:groups,
                        group_student_statuses:group_student_statuses,
                        student_types:student_types,
                        evening_weekends:evening_weekends
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
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
                    
                    $.each(res.intake_semester, function(index, row) {
                        intake_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    
                    $.each(res.attendance_semester, function(index, row) {
                        attendance_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.course, function(index, row) {
                        course.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //course.setValue(courses);

                    $.each(res.group_student_status, function(index, row) {
                        group_student_status.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //group_student_status.setValue(group_student_statuses);

                    $.each(res.evening_weekend, function(index, row) {
                        evening_weekend.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //evening_weekend.setValue(evening_weekends);

                    $.each(res.group, function(index, row) {
                        group.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //group.setValue(groups);
                    $.each(res.student_type, function(index, row) {
                        student_type.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                }
            });
        }
    })
    student_type.on('change', function(e){
        let group_student_statuses = group_student_status.getValue();

        if(group_student_statuses.length > 0){
            let intake_semesters = intake_semester.getValue();
            let attendance_semesters = attendance_semester.getValue();
            let courses = course.getValue();
            let groups = group.getValue();
            let student_types = student_type.getValue();
            let group_student_statuses = group_student_status.getValue();
            let evening_weekends = evening_weekend.getValue();
            //student.get.all.student.type

            axios({
                method: "post",
                url: route('student.get.all.student.type'),
                data: { academic_years : '' , 
                        term_declaration_ids: attendance_semesters , 
                        intake_semesters: intake_semesters,
                        courses: courses, 
                        groups:groups,
                        group_student_statuses:group_student_statuses,
                        student_types:student_types,
                        evening_weekends:evening_weekends
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
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

                    
                    
                    $.each(res.intake_semester, function(index, row) {
                        intake_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    
                    $.each(res.attendance_semester, function(index, row) {
                        attendance_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.course, function(index, row) {
                        course.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.group_student_status, function(index, row) {
                        group_student_status.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //group_student_status.setValue(group_student_statuses);

                    $.each(res.evening_weekend, function(index, row) {
                        evening_weekend.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //evening_weekend.setValue(evening_weekends);

                    $.each(res.group, function(index, row) {
                        group.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.student_type, function(index, row) {
                        student_type.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    
                }
            });
        
        }
    }) 
    
    group_student_status.on('change', function(e){

        let group_student_statuses = group_student_status.getValue();

        if(group_student_statuses.length > 0){
            let intake_semesters = intake_semester.getValue();
            let attendance_semesters = attendance_semester.getValue();
            let courses = course.getValue();
            let groups = group.getValue();
            let student_types = student_type.getValue();
            let group_student_statuses = group_student_status.getValue();
            let evening_weekends = evening_weekend.getValue();
            //student.get.all.student.type

            axios({
                method: "post",
                url: route('student.get.all.student.type'),
                data: { academic_years : '' , 
                        term_declaration_ids: attendance_semesters , 
                        intake_semesters: intake_semesters,
                        courses: courses, 
                        groups:groups,
                        group_student_statuses:group_student_statuses,
                        student_types:student_types,
                        evening_weekends:evening_weekends
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
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

                    
                    
                    $.each(res.intake_semester, function(index, row) {
                        intake_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    
                    $.each(res.attendance_semester, function(index, row) {
                        attendance_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.course, function(index, row) {
                        course.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.group_student_status, function(index, row) {
                        group_student_status.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //group_student_status.setValue(group_student_statuses);

                    $.each(res.evening_weekend, function(index, row) {
                        evening_weekend.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //evening_weekend.setValue(evening_weekends);

                    $.each(res.group, function(index, row) {
                        group.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.student_type, function(index, row) {
                        student_type.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    
                }
            });
        
        }
    })
    
    evening_weekend.on('change', function(e){
        let evening_weekends = evening_weekend.getValue();

        if(evening_weekends.length > 0){
        let intake_semesters = intake_semester.getValue();
        let attendance_semesters = attendance_semester.getValue();
        let courses = course.getValue();
        let groups = group.getValue();
        let student_types = student_type.getValue();
        let group_student_statuses = group_student_status.getValue();
        let evening_weekends = evening_weekend.getValue();
        //student.get.all.student.type

        axios({
            method: "post",
            url: route('student.get.all.student.type'),
            data: { academic_years : '' , 
                    term_declaration_ids: attendance_semesters , 
                    intake_semesters: intake_semesters,
                    courses: courses, 
                    groups:groups,
                    group_student_statuses:group_student_statuses,
                    student_types:student_types,
                    evening_weekends:evening_weekends
            },
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
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
                
                $.each(res.intake_semester, function(index, row) {
                    intake_semester.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //intake_semester.setValue(intake_semesters);
                $.each(res.attendance_semester, function(index, row) {
                    attendance_semester.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });

                $.each(res.course, function(index, row) {
                    course.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //course.setValue(courses);

                $.each(res.group_student_status, function(index, row) {
                    group_student_status.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //group_student_status.setValue(group_student_statuses);

                $.each(res.evening_weekend, function(index, row) {
                    evening_weekend.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //evening_weekend.setValue(evening_weekends);

                $.each(res.group, function(index, row) {
                    group.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                //group.setValue(groups);
                $.each(res.student_type, function(index, row) {
                    student_type.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                student_type.setValue(student_types);
            }
        });
        
        }

    })
    $('.resetSearch').on('click', function(e){
        resetGroupSearch();
    })



    function resetGroupSearch() {

        intake_semester.clear(true);
        attendance_semester.clear(true);
        course.clear(true); 
        group.clear(true); 
        
        student_type.clear(true);
        group_student_status.clear(true);
        $('#evening_weekend').val('');
        $('#groupSearchStatus').val('0'); 

        intake_semester.refreshOptions();
        student_type.refreshOptions(false);
        course.refreshOptions(false);
        group.refreshOptions(false);
        student_type.refreshOptions(false);
        group_student_status.refreshOptions(false);
        evening_weekend.refreshOptions(false);

    }
    localStorage.removeItem('studentIdsList2024');
    /* Start List Table Inits */

    $("#studentGroupSearchForm").on("submit", function (event) {
        
        event.preventDefault();
        const form = document.getElementById('studentGroupSearchForm');
        let form_data = new FormData(form);
        document.querySelector('#studentGroupSearchSubmitBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#studentGroupSearchSubmitBtn svg.loadingCall").style.cssText ="display: inline-block;";

        axios({
            method: "post",
            url: route('report.student.data.total'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            document.querySelector('#studentGroupSearchSubmitBtn').removeAttribute('disabled');
            document.querySelector("#studentGroupSearchSubmitBtn svg.loadingCall").style.cssText = "display: none;";
            let total_student = response.data.all_rows;
            
            if (response.status == 200) {
                
                if(total_student>0) 
                    $('#reportTotalRowCount').html('Total Student(s) Found: <div id="totalCount" class="inline-block ml-2"><button class="rounded bg-success text-white cursor-pointer font-medium inline-flex justify-center items-center w-auto mr-2 px-4 py-2">'+total_student+'</button></div>');
                else  
                    $('#reportTotalRowCount').html('Total Student(s) Found: <div id="totalCount" class="inline-block ml-2"><button class="rounded bg-danger text-white cursor-pointer font-medium inline-flex justify-center items-center w-auto mr-2 px-4 py-2">'+total_student+'</button></div>');

                localStorage.setItem('studentIdsList2024', response.data.student_ids);
            }

            
            
        }).catch(error => {

            document.querySelector('#studentGroupSearchSubmitBtn').removeAttribute('disabled');
            document.querySelector("#studentGroupSearchSubmitBtn svg.loadingCall").style.cssText = "display: none;";
            localStorage.setItem('studentIdsList2024', []);
            if (error.response) {
                if (error.response.status == 422) {
                    let total_student = "OOPS! something went wrong.";
                    $('#reportTotalRowCount').html('<button class="rounded bg-danger text-white cursor-pointer font-medium inline-flex justify-center items-center w-auto mr-2 px-4 py-2">'+total_student+'</button>');
                }else if(error.response.status == 302) {
                    let html ='<div role="alert" class="alert relative border rounded-md px-5 py-4 bg-pending border-pending text-white dark:border-pending mb-2 flex items-center"><i data-tw-merge data-lucide="alert-triangle" class="stroke-1.5 w-5 h-5 mr-2  "></i>\
                        Please, select an item from abobe\
                        <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close" type="button" aria-label="Close" class="text-slate-800 py-2 px-3 absolute right-0 my-auto mr-2 btn-close"><i data-tw-merge data-lucide="x" class="stroke-1.5 w-5 h-5 "></i></button>\
                    </div>';
                    
                    $('#reportTotalRowCount').html(html);
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                    
                } else {
                    $('#reportTotalRowCount').html("No Data Found")
                }
            }
            

        });
    });

    $('input[type=checkbox]').on('click',function() {

        let studentIds = localStorage.getItem('studentIdsList2024');
        if(studentIds!=null && studentIds.length>0) {
            if($('input[type=checkbox]').is(':checked')) {
                $('#studentDataReportExcelBtn').removeAttr('disabled');
            }
        }else {
            $('#studentDataReportExcelBtn').attr('disabled','disabled');
        }

    })
    $("#studentDataReportExcelBtn").on("click", function (e) {
        e.preventDefault();
        $("#studentExcelForm").submit();
    })

    $("#studentExcelForm").on("submit", function (event) {

        event.preventDefault();
        let studentIds = localStorage.getItem('studentIdsList2024');
        if(studentIds.length>0) {
            document.querySelector('#studentDataReportExcelBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#studentDataReportExcelBtn svg.loadingCall").style.cssText ="display: inline-block;";

            $('#studentDataReportExcelBtn .loading').show();
            const form = document.getElementById('studentExcelForm');
            let form_data = new FormData(form);
            form_data.append("studentIds", studentIds);
            axios({
                method: "post",
                url: route("report.student.data.excel"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                responseType: 'blob',
            })
            .then((response) => {
                    document.querySelector('#studentDataReportExcelBtn').removeAttribute('disabled');
                    $('#studentDataReportExcelBtn svg.loading').hide();
                    localStorage.removeItem('studentIdsList2024');
                    $('#totalCount').html('');
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'student_data_report_download.xlsx'); 
                    document.body.appendChild(link);
                    link.click();
                    
            })
            .catch((error) => {
                    console.log(error);
            });
            document.querySelector('#studentDataReportExcelBtn').removeAttribute('disabled');
            document.querySelector("#studentDataReportExcelBtn svg.loadingCall").style.cssText ="display: none;";
        }
    });
    
    $("#checkbox-all-personal").on('click',function(e){
        for(let i=1;i<15;i++) {
            $('#checkbox-switch-'+i).trigger('click');
        }
    })
    $("#checkbox-all-course").on('click',function(e){
        for(let i=15;i<24;i++) {
            $('#checkbox-switch-'+i).trigger('click');
        }
    })
    
    $("#checkbox-all-proof").on('click',function(e){
        for(let i=24;i<27;i++) {
            $('#checkbox-switch-'+i).trigger('click');
        }
    })
    
    $("#checkbox-all-address").on('click',function(e){
        for(let i=27;i<36;i++) {
            $('#checkbox-switch-'+i).trigger('click');
        }
    })
    
    $("#checkbox-all-kin").on('click',function(e){
        for(let i=36;i<41;i++) {
            $('#checkbox-switch-'+i).trigger('click');
        }
    })

    
    
    $("#checkbox-all-qual").on('click',function(e){
        for(let i=41;i<43;i++) {
            $('#checkbox-switch-'+i).trigger('click');
        }
    })

    
    
    $("#checkbox-all-ref").on('click',function(e){
        for(let i=43;i<49;i++) {
            $('#checkbox-switch-'+i).trigger('click');
        }
    })
    /* End List Table Inits */

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
                method: "post",
                url: route('student.get.all.student.type'),
                data: { academic_years : '' , 
                        term_declaration_ids: attendance_semesters , 
                        intake_semesters: intake_semesters,
                        courses: courses, 
                        groups:groups,
                        group_student_statuses:group_student_statuses,
                        student_types:student_types,
                        evening_weekends:evening_weekends
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
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

                    
                    
                    $.each(res.intake_semester, function(index, row) {
                        intake_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    
                    $.each(res.attendance_semester, function(index, row) {
                        attendance_semester.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.course, function(index, row) {
                        course.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.group_student_status, function(index, row) {
                        group_student_status.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //group_student_status.setValue(group_student_statuses);

                    $.each(res.evening_weekend, function(index, row) {
                        evening_weekend.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    //evening_weekend.setValue(evening_weekends);

                    $.each(res.group, function(index, row) {
                        group.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });

                    $.each(res.student_type, function(index, row) {
                        student_type.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    
                }
        });
    }
})();