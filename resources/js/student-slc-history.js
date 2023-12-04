import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";


(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const addRegistrationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addRegistrationModal"));

    const addRegistrationModalEl = document.getElementById('addRegistrationModal')
    addRegistrationModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addRegistrationModal .acc__input-error').html('');
        $('#addRegistrationModal .modal-body select').val('');
        $('#addRegistrationModal .modal-body input:not([type="checkbox"])').val('');
        $('#addRegistrationModal .modal-body input[type="checkbox"]').prop('checked', false);

        $('#addRegistrationModal .confirmAttendanceArea').fadeOut('fast', function(){
            $('#addRegistrationModal .confirmAttendanceArea').removeClass('opened');
            $('#addRegistrationModal input[name="instance_fees"]').val('').attr('readonly', 'readonly');
            $('#addRegistrationModal select[name="self_funded_year"]').val('');
            $('#addRegistrationModal select[name="session_term"]').html('<option value="">Please Select</option>').attr('readonly');

            $('#addRegistrationForm select[name="attendance_code_id"]').val('');
        });
    });


    $('#addRegistrationForm #confirm_attendance').on('change', function(){
        if($(this).prop('checked')){
            var academic_year_id = $('#addRegistrationForm [name="academic_year_id"]').val();
            var course_creation_instance_id = $('#addRegistrationForm [name="course_creation_instance_id"]').val();
            var studen_id = $('#addRegistrationForm input[name="studen_id"]').val();

            axios({
                method: "post",
                url: route('student.get.registration.confirmation.details'),
                data: {studen_id : studen_id, academic_year_id : academic_year_id, course_creation_instance_id : course_creation_instance_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                var fees = response.data.fees;
                var session_term_html = response.data.session_term_html;

                $('#addRegistrationForm .confirmAttendanceArea').fadeIn('fast', function(){
                    $('#addRegistrationForm .confirmAttendanceArea').addClass('opened');
                    $('#addRegistrationForm input[name="instance_fees"]').val(fees).removeAttr('readonly');
                    if(academic_year_id > 0 && academic_year_id != ''){
                        $('#addRegistrationForm select[name="self_funded_year"]').val(academic_year_id);
                    }else{
                        $('#addRegistrationForm select[name="self_funded_year"]').val('');
                    }
                    if(session_term_html != ''){
                        $('#addRegistrationForm select[name="session_term"]').html(session_term_html).removeAttr('readonly');
                    }else{
                        $('#addRegistrationForm select[name="session_term"]').html('<option value="">Please Select</option>').removeAttr('readonly');
                    }
                });
            }).catch(error => {
                if (error.response.status == 422) {
                    console.log('error');
                }
            });
        }else{
            $('#addRegistrationForm .confirmAttendanceArea').fadeOut('fast', function(){
                $('#addRegistrationForm .confirmAttendanceArea').removeClass('opened');
                $('#addRegistrationForm input[name="instance_fees"]').val('').attr('readonly', 'readonly');
                $('#addRegistrationForm select[name="self_funded_year"]').val('');
                $('#addRegistrationForm select[name="session_term"]').html('<option value="">Please Select</option>').attr('readonly');

                $('#addRegistrationForm select[name="attendance_code_id"]').val('');
            });
        }
    });


    $('#addRegistrationForm [name="course_creation_instance_id"]').on('change', function(){
        var $select = $(this);
        var academic_year_id = $('#addRegistrationForm [name="academic_year_id"]').val();
        var course_creation_instance_id = $select.val();
        var studen_id = $('#addRegistrationForm input[name="studen_id"]').val();

        if(course_creation_instance_id > 0 && course_creation_instance_id != ''){
            if($('#addRegistrationForm .confirmAttendanceArea').hasClass('opened')){
                axios({
                    method: "post",
                    url: route('student.get.registration.confirmation.details'),
                    data: {studen_id : studen_id, academic_year_id : academic_year_id, course_creation_instance_id : course_creation_instance_id},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    var fees = response.data.fees;
                    var session_term_html = response.data.session_term_html;

                    
                    $('#addRegistrationForm input[name="instance_fees"]').val(fees);
                    if(session_term_html != ''){
                        $('#addRegistrationForm select[name="session_term"]').html(session_term_html).removeAttr('readonly');
                    }else{
                        $('#addRegistrationForm select[name="session_term"]').html('<option value="">Please Select</option>').removeAttr('readonly');
                    }
                }).catch(error => {
                    if (error.response.status == 422) {
                        console.log('error');
                    }
                });
            }
        }else{
            if($('#addRegistrationForm .confirmAttendanceArea').hasClass('opened')){
                $('#addRegistrationForm input[name="instance_fees"]').val('');
                $('#addRegistrationForm select[name="session_term"]').html('<option value="">Please Select</option>');
            }
        }
    });


    $('#addRegistrationForm [name="academic_year_id"]').on('change', function(){
        var $academic_year_id = $(this);
        var academic_year_id = $academic_year_id.val();

        if($('#addRegistrationForm .confirmAttendanceArea').hasClass('opened')){
            if(academic_year_id > 0 && academic_year_id != ''){
                $('#addRegistrationForm select[name="self_funded_year"]').val(academic_year_id);
            }else{
                $('#addRegistrationForm select[name="self_funded_year"]').val('');
            }
        }
    });


})();