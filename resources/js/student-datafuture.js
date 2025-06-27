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

    let semester_id = new TomSelect('#semester_id', tomOptionsSDF);
    let DISABILITY_IDS = new TomSelect('#DISABILITY_IDS', tomOptionsSDFMul);

    let SSI_disall_id = new TomSelect('#SSI_disall_id', tomOptionsSDF);
    let SSI_exchind_id = new TomSelect('#SSI_exchind_id', tomOptionsSDF);
    let SSI_locsdy_id = new TomSelect('#SSI_locsdy_id', tomOptionsSDF);
    let SSI_mode_id = new TomSelect('#SSI_mode_id', tomOptionsSDF);
    let SSI_mstufee_id = new TomSelect('#SSI_mstufee_id', tomOptionsSDF);
    let SSI_notact_id = new TomSelect('#SSI_notact_id', tomOptionsSDF);
    let SSI_priprov_id = new TomSelect('#SSI_priprov_id', tomOptionsSDF);
    let SSI_sselig_id = new TomSelect('#SSI_sselig_id', tomOptionsSDF);
    let SSI_qual_id = new TomSelect('#SSI_qual_id', tomOptionsSDF);
    let SSI_heapespop_id = new TomSelect('#SSI_heapespop_id', tomOptionsSDF);

    let tomOptionsSDFNew = {
        ...tomOptionsSDF,
        allowEmptyOption: false,
        plugins: {
            ...tomOptionsSDF.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    }
    let term_declaration_id = new TomSelect('#terms_declaration_id', tomOptionsSDFNew);

    if($('.dfReportWrap .df-datepicker').length > 0){
        $('.dfReportWrap .df-datepicker').each(function(){
            new Litepicker({
                element: this,
                ...stdDFLitepicker,
            });
        })
    }

    if($('#editStudentStuloadModal .df-datepicker').length > 0){
        stdDFLitepicker.format = 'DD-MM-YYYY';
        $('#editStudentStuloadModal .df-datepicker').each(function(){
            new Litepicker({
                element: this,
                ...stdDFLitepicker,
            });
        })
    }

    if($('#xmlExportModal').length > 0){
        stdDFLitepicker.format = 'DD-MM-YYYY';
        let theFormDate = new Litepicker({
            element: document.getElementById('from_date'),
            ...stdDFLitepicker,
        });
        
        
        let theToDate = new Litepicker({
            element: document.getElementById('to_date'),
            ...stdDFLitepicker,
        });

        theFormDate.on('selected', (date1, date2) => {
            theToDate.setOptions({
                showWeekNumbers: false,
                minDate: theFormDate.getDate(),
                //startDate: theFormDate.getDate()
            });
        });
    }

    $('#terms_declaration_id').on('change', function(){
        let $theTermDec = $(this);
        let termDecs = $theTermDec.val();

        let $from_date = $('#from_date');
        let $to_date = $('#to_date');

        if(termDecs.length > 0){
            $from_date.val('');
            $to_date.val('');
        }
    })

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addHesaInstanceModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHesaInstanceModal"));
    const editStudentStuloadModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentStuloadModal"));
    const xmlExportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#xmlExportModal"));

    const addHesaInstanceModalEl = document.getElementById('addHesaInstanceModal')
    addHesaInstanceModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addHesaInstanceModal .acc__input-error').html('');
        $('#addHesaInstanceModal .modal-body .instanceListWrap').fadeOut('fast', function(){
            $('#addHesaInstanceModal .modal-body .instanceListWrap .table tbody').html('');
        });
        
        semester_id.clear(true);
    });

    const editStudentStuloadModalEl = document.getElementById('editStudentStuloadModal')
    editStudentStuloadModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editStudentStuloadModal .acc__input-error').html('');
        $('#editStudentStuloadModal .modal-body input:not([type="checkbox"])').val('');
        $('#editStudentStuloadModal input[name="id"]').val('0');
        
        SSI_disall_id.clear(true);
        SSI_exchind_id.clear(true);
        SSI_locsdy_id.clear(true);
        SSI_mode_id.clear(true);
        SSI_mstufee_id.clear(true);
        SSI_notact_id.clear(true);
        SSI_priprov_id.clear(true);
        SSI_sselig_id.clear(true);
        SSI_qual_id.clear(true);
        SSI_heapespop_id.clear(true);
    });

    const xmlExportModalEl = document.getElementById('xmlExportModal')
    xmlExportModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#xmlExportModal .acc__input-error').html('');
        $('#xmlExportModal .modal-body input)').val('');
        
        term_declaration_id.clear(true);
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
        document.querySelector("#saveDFBTN .theLoader").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.datafuture.store', student_id),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveDFBTN').removeAttribute('disabled');
            document.querySelector("#saveDFBTN .theLoader").style.cssText = "display: none;";

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
            document.querySelector("#saveDFBTN .theLoader").style.cssText = "display: none;";
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

    $(document).on('click', '.editStudentLoadBtn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var stuload_id = $theBtn.attr('data-id');
        var student_id = $theBtn.attr('data-student-id');

        axios({
            method: "POST",
            url: route('student.datafuture.get.stuload.information', student_id),
            data: {stuload_id : stuload_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            let row = response.data.row;

            $('#editStudentStuloadModal [name="gross_fee"]').val(row.gross_fee ? row.gross_fee : '');
            $('#editStudentStuloadModal [name="netfee"]').val(row.netfee ? row.netfee : '');
            $('#editStudentStuloadModal [name="periodstart"]').val(row.periodstart ? row.periodstart : '');
            $('#editStudentStuloadModal [name="periodend"]').val(row.periodend ? row.periodend : '');
            $('#editStudentStuloadModal [name="yearprg"]').val(row.yearprg ? row.yearprg : '');
            $('#editStudentStuloadModal [name="yearstu"]').val(row.yearstu ? row.yearstu : '');
            $('#editStudentStuloadModal [name="comdate"]').val(row.comdate ? row.comdate : '');
            $('#editStudentStuloadModal [name="comdate"]').val(row.comdate ? row.comdate : '');
            $('#editStudentStuloadModal [name="enddate"]').val(row.enddate ? row.enddate : '');
            SSI_disall_id.addItem(row.disall_id);
            SSI_exchind_id.addItem(row.exchind_id);
            SSI_locsdy_id.addItem(row.locsdy_id);
            SSI_mode_id.addItem(row.mode_id);
            SSI_mstufee_id.addItem(row.mstufee_id);
            SSI_notact_id.addItem(row.notact_id);
            SSI_priprov_id.addItem(row.priprov_id);
            SSI_sselig_id.addItem(row.sselig_id);
            SSI_qual_id.addItem(row.qual_id);
            SSI_heapespop_id.addItem(row.heapespop_id);

            $('#editStudentStuloadModal [name="id"]').val(stuload_id);
        }).catch(error => {
            console.log('error');
        });
    });

    $('#editStudentStuloadForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('editStudentStuloadForm');
        var student_id = $('[name="student_id"]', $form).val();
    
        document.querySelector('#saveStuloadBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveStuloadBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.datafuture.update.hesa.instances', student_id),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveStuloadBtn').removeAttribute('disabled');
            document.querySelector("#saveStuloadBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editStudentStuloadModal.hide();
                
                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student stuload information successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveStuloadBtn').removeAttribute('disabled');
            document.querySelector("#saveStuloadBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editStudentStuloadForm .${key}`).addClass('border-danger');
                        $(`#editStudentStuloadForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $(document).on('click', '.deleteStudentLoadBtn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var stuload_id = $theBtn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Session for the student? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', stuload_id);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETESTL');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETESTL'){
            axios({
                method: 'delete',
                url: route('student.datafuture.destory.student.stuload', student),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student course session successfully deleted.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    });


    
    $('#xmlExportForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('xmlExportForm');
    
        document.querySelector('#xmlDownBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#xmlDownBtn .theLoader").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            //url: route('reports.datafuture.single.student'),
            url: route('reports.datafuture.multiple.student'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#xmlDownBtn').removeAttribute('disabled');
            document.querySelector("#xmlDownBtn .theLoader").style.cssText = "display: none;";

            if (response.status == 200) {
                console.log(response.data);
            }
        }).catch(error => {
            document.querySelector('#xmlDownBtn').removeAttribute('disabled');
            document.querySelector("#xmlDownBtn .theLoader").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#xmlExportForm .${key}`).addClass('border-danger');
                        $(`#xmlExportForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
})()