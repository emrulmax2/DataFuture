import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";

(function(){
    let tomOptions = {
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

    
    let academicYearTom = new TomSelect('#academic_year_id', tomOptions);
    let semesterTom= new TomSelect('#semester_id', tomOptions);
    let courseTom = new TomSelect('#course_id', tomOptions);
    let venue_id = new TomSelect('#venue_id', tomOptions);

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
    });

    const editStudentCourseChangeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentCourseChangeModal"));
    const editStudentCourseDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentCourseDetailsModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));

    const editStudentCourseChangeModalEl = document.getElementById('editStudentCourseChangeModal')
    editStudentCourseChangeModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editStudentCourseChangeModal .acc__input-error').html('')
        $('#editStudentCourseChangeModal .semesterWrap').fadeOut('fast', function(){
            semesterTom.clear();
            semesterTom.disable();
        })
        $('#editStudentCourseChangeModal .courseWrap').fadeOut('fast', function() {

            courseTom.clear();

            courseTom.disable();

        })

        $('#editStudentCourseChangeModal .venueWrap').fadeOut('fast', function() {

            venue_id.clear();

            venue_id.disable();

        })

        $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeOut('fast', function(){
            $('[name="full_time"]', this).prop('checked', false);
        })
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#editStudentCourseDetailsForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editStudentCourseDetailsForm');
    
        document.querySelector('#savePCP').setAttribute('disabled', 'disabled');
        document.querySelector("#savePCP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.course.details'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#savePCP').removeAttribute('disabled');
                document.querySelector("#savePCP svg").style.cssText = "display: none;";

                editStudentCourseDetailsModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Course & Programme Details successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#savePCP').removeAttribute('disabled');
            document.querySelector("#savePCP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editStudentCourseDetailsForm .${key}`).addClass('border-danger');
                        $(`#editStudentCourseDetailsForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#editStudentCourseChangeModal [name="academic_year_id"]').on('change', function(e){
        var $academic_year_id = $(this);
        var academic_year_id = $academic_year_id.val();
        $academic_year_id.parent().siblings('label').find('svg.loading').removeClass('hidden');

        if(academic_year_id > 0){
            $('#editStudentCourseChangeModal .semesterWrap').fadeOut('fast', function(){
                semesterTom.clear(true);
                semesterTom.disable();
            })
            $('#editStudentCourseChangeModal .courseWrap').fadeOut('fast', function(){
                courseTom.clear(true);
                courseTom.disable();
            })
            
            $('#editStudentCourseChangeModal .venueWrap').fadeOut('fast', function(){
                venue_id.clear(true);
                venue_id.disable();
            })
            
            $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeOut('fast', function(){
                $('#editStudentCourseChangeModal .eveningWeekendWrap [name="full_time"]').prop('checked', false);
            })
            
            axios({
                method: "post",
                url: route('student.get.semesters.by.academic'),
                data: {academic_year_id : academic_year_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $(`#editStudentCourseChangeForm  .error-academic_year_id`).removeClass('border-danger');
                    $(`#editStudentCourseChangeForm  .error-academic_year_id`).html('');
                    $academic_year_id.parent().siblings('label').find('svg.loading').addClass('hidden');
                    $('.semesterWrap').fadeIn('fast', function(){
                        semesterTom.enable();
                        semesterTom.clearOptions();
                        $.each(response.data.res, function(index, semester){
                            semesterTom.addOption({
                                value: semester.id,
                                text: semester.name
                            });
                        })
                    })
                }
            }).catch(error => {
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editStudentCourseChangeForm .${key}`).addClass('border-danger');
                            $(`#editStudentCourseChangeForm  .error-${key}`).html(val);
                        }
                        $academic_year_id.parent().siblings('label').find('svg.loading').addClass('hidden');
                    } else {
                        console.log('error');
                    }
                }
            });
        }else{
            $academic_year_id.parent().siblings('label').find('svg.loading').addClass('hidden');
            $('#editStudentCourseChangeModal .semesterWrap').fadeOut('fast', function(){
                semesterTom.clear();
                semesterTom.disable();
            })
            $('#editStudentCourseChangeModal .courseWrap').fadeOut('fast', function(){
                courseTom.clear();
                courseTom.disable();
            })
            
            $('#editStudentCourseChangeModal .venueWrap').fadeOut('fast', function(){
                venue_id.clear(true);
                venue_id.disable();
            })
            
            $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeOut('fast', function(){
                $('#editStudentCourseChangeModal .eveningWeekendWrap [name="full_time"]').prop('checked', false);
            })
        }
    });

    $('#editStudentCourseChangeModal [name="semester_id"]').on('change', function(e){
        var $semester_id = $(this);
        var semester_id = $semester_id.val();
        var academic_year_id = $('#editStudentCourseChangeModal [name="academic_year_id"]').val();
        $semester_id.parent().siblings('label').find('svg.loading').removeClass('hidden');

        if(semester_id > 0){
            $('#editStudentCourseChangeModal .courseWrap').fadeOut('fast', function(){
                courseTom.clear();
                courseTom.disable();
            })
            
            $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeOut('fast', function(){
                $('#editStudentCourseChangeModal .eveningWeekendWrap [name="full_time"]').prop('checked', false);
            })
            axios({
                method: "post",
                url: route('student.get.courses.by.academic.semester'),
                data: {academic_year_id : academic_year_id, semester_id : semester_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $semester_id.parent().siblings('label').find('svg.loading').addClass('hidden');
                    $('.courseWrap').fadeIn('fast', function(){
                        courseTom.enable();
                        courseTom.clearOptions();
                        $.each(response.data.res, function(index, course){
                            courseTom.addOption({
                                value: course.course_creation_id,
                                text: course.name
                            });

                        })
                    })
                }
            }).catch(error => {
                if (error.response) {
                    if (error.response.status == 422) {
                        $semester_id.parent().siblings('label').find('svg.loading').addClass('hidden');
                    } else {
                        console.log('error');
                    }
                }
            });
        }else{
            $semester_id.parent().siblings('label').find('svg.loading').addClass('hidden');
            $('#editStudentCourseChangeModal .courseWrap').fadeOut('fast', function(){
                courseTom.clear();
                courseTom.disable();
            })
            
            $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeOut('fast', function(){
                $('#editStudentCourseChangeModal .eveningWeekendWrap [name="full_time"]').prop('checked', false);
            })
        }
    });


    $('#editStudentCourseChangeForm').on('submit', function(e){

        e.preventDefault();
        const form = document.getElementById('editStudentCourseChangeForm');
    
        document.querySelector('#saveSCR').setAttribute('disabled', 'disabled');
        document.querySelector("#saveSCR svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.assigned.new.course'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveSCR').removeAttribute('disabled');
            document.querySelector("#saveSCR svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editStudentCourseChangeModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student successfully assigned to new course.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);    
            }
        }).catch(error => {
            document.querySelector('#saveSCR').removeAttribute('disabled');
            document.querySelector("#saveSCR svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editStudentCourseChangeForm .${key}`).addClass('border-danger');
                        $(`#editStudentCourseChangeForm  .error-${key}`).html(val);
                    }
                }else if(error.response.status == 304){
                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Oops!" );
                        $("#errorModal .errorModalDesc").html(error.response.data.msg);
                    });   
                
                    setTimeout(function(){
                        errorModal.show();
                    }, 2000);  
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#course_id').on('change', function(e){

        $('.courseLoading').show();
        let SelectedValue = $(this).val();
        if(SelectedValue=="") {
            $('#editStudentCourseChangeModal .venueWrap').fadeOut('fast', function(){
                $('.courseLoading').hide();
                venue_id.clear(true);
                venue_id.clearOptions();
            })
            
            $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeOut('fast', function(){
                $('#editStudentCourseChangeModal .eveningWeekendWrap [name="full_time"]').prop('checked', false);
            })
        }else
            axios({
                method: "get",
                url: route("course.creation.edit", SelectedValue),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let venues = response.data.venues;
                        venue_id.clear();
                        venue_id.enable();
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

                        $('#editStudentCourseChangeModal .venueWrap').fadeIn('fast', function(){
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
        let $theCourseCreation = $('#course_id');

        let venue_id = $theVenue.val();
        let course_creation_id = $theCourseCreation.val();

        if(venue_id > 0 && course_creation_id > 0){
            axios({
                method: "post",
                url: route("student.get.evening.weekend.status"),
                data: {course_creation_id : course_creation_id, venue_id : venue_id},
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    if(response.data.weekends == 1){
                        $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeIn('fast', function(){
                            $('[name="full_time"]', this).prop('checked', false);
                        })
                    }else{
                        $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeOut('fast', function(){
                            $('[name="full_time"]', this).prop('checked', false);
                        })
                    }
                }
            }).catch((error) => {
                $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeOut('fast', function(){
                    $('[name="full_time"]', this).prop('checked', false);
                })
                console.log(error);
            });
        }else{
            $('#editStudentCourseChangeModal .eveningWeekendWrap').fadeOut('fast', function(){
                $('[name="full_time"]', this).prop('checked', false);
            })
        }
    })

})();