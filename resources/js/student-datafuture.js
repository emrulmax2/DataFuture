import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";
import Litepicker from "litepicker";

(function(){
    let stdDFLitepicker = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        format: "YYYY-MM-DD",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    let tomOptionsSDF = {
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

    let tomOptionsSDFMul = {
        ...tomOptionsSDF,
        plugins: {
            ...tomOptionsSDF.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    $('.dfReportWrap .df-tom-selects').each(function(){
        new TomSelect(this, tomOptionsSDF);
    });

    $('#editStudentStuloadModal .df-tom-selects').each(function(){
        new TomSelect(this, tomOptionsSDF);
    });
    let semester_id = new TomSelect('#semester_id', tomOptionsSDF);
    let DISABILITY_IDS = new TomSelect('#DISABILITY_IDS', tomOptionsSDFMul);

    if($('.dfReportWrap .df-datepicker').length > 0){
        $('.dfReportWrap .df-datepicker').each(function(){
            new Litepicker({
                element: this,
                ...stdDFLitepicker,
            });
        })
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addHesaInstanceModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHesaInstanceModal"));

    const addHesaInstanceModalEl = document.getElementById('addHesaInstanceModal')
    addHesaInstanceModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addHesaInstanceModal .acc__input-error').html('');
        $('#addHesaInstanceModal .modal-body .instanceListWrap').fadeOut('fast', function(){
            $('#addHesaInstanceModal .modal-body .instanceListWrap .table tbody').html('');
        });
        
        semester_id.clear(true);
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

    $('#addHesaInstanceModal #semester_id').on('change', function(e){
        var $semester = $(this);
        var semester_id = $semester.val();
        var course_id = $('#addHesaInstanceModal [name="course_id"]').val();
        var student_id = $('#addHesaInstanceModal [name="id"]').val();

        axios({
            method: 'post',
            url: route('student.datafuture.get.instances', student_id),
            data: {semester_id : semester_id, course_id : course_id},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        }).then((response) => {
            if (response.status == 200) {
                $('#addHesaInstanceModal .modal-body .instanceListWrap').fadeIn('fast', function(){
                    $('#addHesaInstanceModal .modal-body .instanceListWrap .table tbody').html(response.data.html);
                });

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch((error) => {
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#addHesaInstanceForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('addHesaInstanceForm');
        var student_id = $('[name="id"]', $form).val();
    
        document.querySelector('#saveInstBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveInstBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.datafuture.store.hesa.instances', student_id),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveInstBtn').removeAttribute('disabled');
            document.querySelector("#saveInstBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addHesaInstanceModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student hesa instance successfully created.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveInstBtn').removeAttribute('disabled');
            document.querySelector("#saveInstBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addHesaInstanceForm .${key}`).addClass('border-danger');
                        $(`#addHesaInstanceForm  .error-${key}`).html(val);
                    }
                } else if(error.response.status == 304) {
                    addHesaInstanceModal.hide();

                    warningModal.show(); 
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html("Congratulation!" );
                        $("#warningModal .warningModalDesc").html('Something went wrong. Please try again later or contact with the administrator.');
                    });  
                    
                    setTimeout(function(){
                        warningModal.hide();
                    }, 2000);
                }else {
                    console.log('error');
                }
            }
        });
    });

    $(document).on('change', '.stuloadMethodChecker', function(){
        var $theCheckbox = $(this);
        if($theCheckbox.prop('checked')){
            $theCheckbox.siblings('.form-check-label').html('Auto Load');
        }else{
            $theCheckbox.siblings('.form-check-label').html('Manual Load');
        }
    });


    $('#studentDFForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('studentDFForm');
        var student_id = $('[name="student_id"]', $form).val();
    
        document.querySelector('#saveDFBTN').setAttribute('disabled', 'disabled');
        document.querySelector("#saveDFBTN svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.datafuture.store', student_id),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveDFBTN').removeAttribute('disabled');
            document.querySelector("#saveDFBTN svg").style.cssText = "display: none;";

            if (response.status == 200) {

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student Datafuture data successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveDFBTN').removeAttribute('disabled');
            document.querySelector("#saveDFBTN svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#studentDFForm .${key}`).addClass('border-danger');
                        $(`#studentDFForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    
})()