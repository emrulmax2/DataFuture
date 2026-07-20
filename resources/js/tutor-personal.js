import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import moment from 'moment';
import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import TomSelect from "tom-select";


(function(){
    let tomOptionsNote = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        dropdownParent: 'body',
        dropdownClass: 'ts-dropdown lcc-tom-float',
        //persist: true,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    let todayDateOption = {
        ...dateOption,
        format: "DD / MM / YYYY",
    };

    const start_date = new Litepicker({
        element: document.getElementById('personalTutorCalendar'),
        ...todayDateOption,
        setup: (picker) => {
            picker.on('selected', (date) => {
                let personalTutorId  = $('#personalTutorCalendar').attr('data-pt')
                let plan_date =  moment(date.dateInstance).format('DD-MM-YYYY');
                
                axios({
                    method: "post",
                    url: route('pt.get.classes'),
                    data: {personalTutorId : personalTutorId, plan_date : plan_date},
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                }).then((response) => {
                    if (response.status == 200) {
                        $('#todaysClassListWrap').html(response.data.res);

                        setTimeout(function(){
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                        })
                    }
                }).catch((error) => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            });
        }
    });

    let currentRequest = null;
    $('.pt-search-form').on('submit', function(e){
        e.preventDefault();
    });

    $('#registration_no').on('keyup paste change', function(){
        var $theInput = $(this);
        var SearchVal = $theInput.val();

        if(SearchVal.length >= 1){
            $('#viewStudentBtn').find('.svgSearch').css({opacity: 0});
            $('#viewStudentBtn').find('.svgLoader').css({opacity: 1});
            currentRequest = $.ajax({
                type: 'POST',
                data: {SearchVal : SearchVal},
                url: route("pt.student.filter.id"),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                beforeSend : function()    {           
                    if(currentRequest != null) {
                        currentRequest.abort();
                        $('#viewStudentBtn').find('.svgSearch').css({opacity: 0});
                        $('#viewStudentBtn').find('.svgLoader').css({opacity: 1});
                    }
                },
                success: function(data) {
                    $('#viewStudentBtn').find('.svgSearch').css({opacity: 1});
                    $('#viewStudentBtn').find('.svgLoader').css({opacity: 0});
                    $theInput.siblings('.autoFillDropdown').html(data.htm).fadeIn();
                    $theInput.siblings('#profileUrl').val('');
                    $theInput.parent('.autoCompleteField').siblings('#viewStudentBtn').attr('disabled', 'disabled');
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                },
                error:function(e){
                    console.log('Error');
                    $('#viewStudentBtn').find('.svgSearch').css({opacity: 1});
                    $('#viewStudentBtn').find('.svgLoader').css({opacity: 0});
                    $theInput.siblings('.autoFillDropdown').html('').fadeOut();
                    $theInput.siblings('#profileUrl').val('');
                    $theInput.parent('.autoCompleteField').siblings('#viewStudentBtn').attr('disabled', 'disabled');
                }
            });
        }else{
            $('#viewStudentBtn').find('.svgSearch').css({opacity: 1});
            $('#viewStudentBtn').find('.svgLoader').css({opacity: 0});
            $theInput.siblings('.autoFillDropdown').html('').fadeOut();
            $theInput.siblings('#profileUrl').val('');
            $theInput.parent('.autoCompleteField').siblings('#viewStudentBtn').attr('disabled', 'disabled');

            if(currentRequest != null) {
                currentRequest.abort();
            }
        }
    });

    $('.autoFillDropdown').on('click', 'li a:not(".disable")', function(e){
        e.preventDefault();
        var profile_url = $(this).attr('href');
        var label = $(this).attr('data-label');
        window.location.href = profile_url;
    });

    $(document).on("click", ".start-punch", function (event) {
        let data = $(this).data('id');   
        document.getElementById('employee_punch_number').focus();
        console.log(data);
        //let url = route('attendance.infomation.save');
        $(".plan-datelist").val(data);

    }); 

    let note_term_declaration_id = new TomSelect('#note_term_declaration_id', tomOptionsNote);
    let sms_template_id = new TomSelect('#sms_template_id', tomOptionsNote);


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const editPunchNumberDeteilsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPunchNumberDeteilsModal"));
    
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const startClassConfirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#startClassConfirmModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
    const endClassModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#endClassModal"));
    const addNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addNoteModal"));
    const smsSMSModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#smsSMSModal"));

    const ptTermDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#ptTermDropdown"));

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

    const addNoteModalEl = document.getElementById('addNoteModal')
    addNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addNoteModal .acc__input-error').html('');
        $('#addNoteModal input[name="document"]').val('');
        $('#addNoteModal #addNoteDocumentName').html('');
        $('#addNoteModal input[name="student_id"]').val('0');
        $('#addNoteModal input[name="attendance_ids"]').val('');
        $('#addNoteModal .pt-note-student-initials').text('ST');
        $('#addNoteModal .pt-note-student-name').text('Selected student');
        $('#addNoteModal .pt-note-student-reg').text('Student ID');
        $('#addNoteModal .pt-note-student-attendance').text('attendance');

        addEditor.setData('');
        note_term_declaration_id.clear(true);
    });


    const smsSMSModalEl = document.getElementById('smsSMSModal')
    smsSMSModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#smsSMSModal .acc__input-error').html('');
        $('#smsSMSModal input[name="subject"], #smsSMSModal textarea').val('');
        $('#smsSMSModal input[name="student_id"]').val('0');
        $('#smsSMSModal .sms_countr').html('160 / 1');
        $('#smsSMSModal .smsWarning').remove();
        $('#smsSMSModal .pt-sms-student-initials').text('ST');
        $('#smsSMSModal .pt-sms-student-name').text('Selected student');
        $('#smsSMSModal .pt-sms-student-reg').text('Student ID');
        $('#smsSMSModal .pt-sms-student-attendance').text('attendance');
        sms_template_id.clear(true);
    });
    
    //const termDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#term-dropdown"));
    $('.save').on('click', function (e) {
        e.preventDefault();
        let $theBtn = $(this);

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg').fadeIn();

        var parentForm = $(this).parents('form');
        var formID = parentForm.attr('id');
        const form = document.getElementById(formID);
        let url = $("#"+formID+" input[name=url]").val();
        
        let form_data = new FormData(form);
        $.ajax({
            method: 'POST',
            url: url,
            data: form_data,
            dataType: 'json',
            async: false,
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            success: function(res, textStatus, xhr) {
                $('.acc__input-error', parentForm).html('');
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg').fadeOut();

                if(xhr.status == 206){
                    //update Alert
                    editPunchNumberDeteilsModal.hide();
                    endClassModal.hide();
                    successModal.show();
                    confirmModal.hide();
                    errorModal.hide()
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Data updated.');
                    });                
                    
                    setTimeout(function(){
                        successModal.hide();
                        location.href= route("tutor-dashboard.attendance",[res.data.tutor, res.data.plandate, res.data.type])
                    }, 1000);

                }if(xhr.status == 207){
                    //update Alert
                    editPunchNumberDeteilsModal.hide();
                    endClassModal.hide();
                    successModal.hide();
                    startClassConfirmModal.show();
                    errorModal.hide();

                }  else if(xhr.status == 200){
                    //update Alert
                    editPunchNumberDeteilsModal.hide();
                    endClassModal.hide();
                    successModal.show();
                    confirmModal.hide();
                    errorModal.hide()
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Data updated.');
                    });                
                    
                    setTimeout(function(){
                        successModal.hide();
                        location.reload();
                    }, 1000);
                }
                
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('.acc__input-error').html('');
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg').fadeOut();
                
                if(jqXHR.status == 422){
                    for (const [key, val] of Object.entries(jqXHR.responseJSON.errors)) {
                        $(`#${formID} .${key}`).addClass('border-danger');
                        $(`#${formID}  .error-${key}`).html(val);
                    }
                }else if(jqXHR.status == 443){

                    document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                        $("#confirmModal .confModTitle").html("End Class!");
                        $("#confirmModal .confModDesc").html('Do you want to End Class.');
                    });   
                    confirmModal.show();
                    editPunchNumberDeteilsModal.hide();
                    endClassModal.hide();

                }else if(jqXHR.status == 442)
                {
                    document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                        $("#confirmModal .confModTitle").html("Different Tutor ?");
                        $("#confirmModal .confModDesc").html('Please Put a note Below, why are you taking this class?');
                    });  
                    editPunchNumberDeteilsModal.hide();
                    endClassModal.hide();
                    confirmModal.show();
                }else if(jqXHR.status == 444)
                {
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Wrong Punch Number");
                        $("#errorModal .errorModalDesc").html('It is not your punch number');
                    });  
                    editPunchNumberDeteilsModal.hide();
                    endClassModal.hide();
                    errorModal.show();
                    setTimeout(function(){
                        errorModal.hide();
                        editPunchNumberDeteilsModal.show();
                    }, 1000);
                }else if(jqXHR.status == 402)
                {
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Invalid Punch");
                        $("#errorModal .errorModalDesc").html('Invalid Punch Number');
                    });  
                    editPunchNumberDeteilsModal.hide();
                    endClassModal.hide();
                    errorModal.show();
                    setTimeout(function(){
                        errorModal.hide();
                        editPunchNumberDeteilsModal.show();
                    }, 1000);
                }else{
                    console.log(textStatus+' => '+errorThrown);
                }
                
            }
        });
        
    });


    /* On Change The Calendar */
    $("#planClassStatus").on('change',function(){

        var planClassStatus = $(this).val();
        var planCourseId = $('#planCourseId').val();
        
        $('.dailyClassInfoTableWrap .leaveTableLoader').addClass('active');
        axios({
            method: 'post',
            url: route('pt.dashboard.class.info'),
            data: {planClassStatus : planClassStatus, planCourseId : planCourseId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('.dailyClassInfoTableWrap .leaveTableLoader').removeClass('active');
                var res = response.data.res;
                $('#dailyClassInfoTable tbody').html(res.planTable);
                doSthWithLoadedContent();

            }
        }).catch(error =>{
            $('.dailyClassInfoTableWrap .leaveTableLoader').removeClass('active');
            console.log(error)
        });
    });

    doSthWithLoadedContent();
    
    function doSthWithLoadedContent() {
        

        $('input.class-fileupload').on('change', function(){
            let tthis = $(this);
            var classFileUploadFound  = tthis.val();
            var planCourseId = $('#planCourseId').val();
            var plansDateListId = tthis.data('id')*1;
            var planClassStatus = $("#planClassStatus").val();
            $('.dailyClassInfoTableWrap .leaveTableLoader').addClass('active');
            axios({
                method: 'post',
                url: route('pt.dashboard.class.status.update'),
                data: {classFileUploadFound : classFileUploadFound,planCourseId : planCourseId, plansDateListId : plansDateListId, planClassStatus: planClassStatus},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {

                if (response.status == 200) {
                    $('.dailyClassInfoTableWrap .leaveTableLoader').removeClass('active');
                    var res = response.data.res;
                    $('#dailyClassInfoTable tbody').html(res.planTable);
                }
                
            }).catch(error =>{

                $('.dailyClassInfoTableWrap .leaveTableLoader').removeClass('active');
                if (error.response) {
                    if (error.response.status == 422) {
                        
                    }
                }
            });
        });
    }


    /* Student Attendance Tracking Start */
    const theAttendanceDate = new Litepicker({
        element: document.getElementById('theAttendanceDate'),
        ...dateOption
    });

    theAttendanceDate.on('selected', (date) => {
        let theYear = date.getFullYear();
        let theMonth = date.getMonth() + 1;
        let theDay = date.getDate();

        let theDate = theYear+'-'+theMonth+'-'+theDay;
        generateStudentAttendanceTrackingHtml(theDate);
    });

    $(window).on('load', function(){
        generateStudentAttendanceTrackingHtml();
    })

    $('#trackingStatus').on('change', function(){
        generateStudentAttendanceTrackingHtml();
    })

    function setStudentTrackingLoading(isLoading) {
        $('#studentAttendanceTrackingWrap').toggleClass('is-loading', isLoading);
        $('#studentAttendanceTrackingWrap .leaveTableLoader').toggleClass('active', isLoading);
        $('#trackingStatus, #theAttendanceDate').prop('disabled', isLoading);
    }

    function generateStudentAttendanceTrackingHtml(theDate = null){
        let $theStatus = $('#trackingStatus');
        let trackingStatus = $theStatus.val();
        if(theDate == null){
            let $theCalendar = $('#theAttendanceDate');
            theDate = $theCalendar.val();
        }
        const loaderStartedAt = Date.now();
        setStudentTrackingLoading(true);

        function finishStudentTrackingLoading() {
            const remainingLoaderTime = Math.max(0, 260 - (Date.now() - loaderStartedAt));
            setTimeout(function(){
                setStudentTrackingLoading(false);
            }, remainingLoaderTime);
        }

        axios({
            method: "POST",
            url: route('pt.dashboard.get.student.attn.tracking'),
            data: {theDate : theDate, trackingStatus : trackingStatus},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                //console.log(response.data);
                $('#studentTrackingListTable tbody').html(response.data.htm);

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
            finishStudentTrackingLoading();
        }).catch(error => {
            finishStudentTrackingLoading();
            if (error.response) {
                console.log('error');
            }
        });
    }

    $('#addNoteForm').on('change', '#addNoteDocument', function(){
        showFileName('addNoteDocument', 'addNoteDocumentName');
    });

    function showFileName(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = fileInput.files[0].name;
        namePreview.innerText = fileName;
        return false;
    };

    $('#studentTrackingListTable').on('click', '.addNoteBtn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var student_id = $theBtn.attr('data-student');
        var attendance_ids = $theBtn.attr('data-attendanceids');
        var student_name = $theBtn.attr('data-student-name') || 'Selected student';
        var registration = $theBtn.attr('data-registration') || 'Student ID';
        var attendance = $theBtn.attr('data-attendance') || 'attendance';
        var initials = $theBtn.attr('data-initials') || 'ST';

        $('#addNoteModal input[name="student_id"]').val(student_id);
        $('#addNoteModal input[name="attendance_ids"]').val(attendance_ids);
        $('#addNoteModal .pt-note-student-initials').text(initials);
        $('#addNoteModal .pt-note-student-name').text(student_name);
        $('#addNoteModal .pt-note-student-reg').text(registration);
        $('#addNoteModal .pt-note-student-attendance').text(attendance + ' attendance');
    })

    $('#addNoteForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addNoteForm');
    
        document.querySelector('#saveNote').setAttribute('disabled', 'disabled');
        document.querySelector("#saveNote svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addNoteForm input[name="document"]')[0].files[0]); 
        form_data.append("content", addEditor.getData());
        axios({
            method: "post",
            url: route('student.store.note'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveNote').removeAttribute('disabled');
            document.querySelector("#saveNote svg").style.cssText = "display: none;";
            //console.log(response.data.message);
            //return false;

            if (response.status == 200) {
                addNoteModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Note successfully stored.');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 1000);
            }
        }).catch(error => {
            document.querySelector('#saveNote').removeAttribute('disabled');
            document.querySelector("#saveNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addNoteForm .${key}`).addClass('border-danger');
                        $(`#addNoteForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#studentTrackingListTable').on('click', '.addSmsBtn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var student_id = $theBtn.attr('data-student');
        var student_name = $theBtn.attr('data-student-name') || 'Selected student';
        var registration = $theBtn.attr('data-registration') || 'Student ID';
        var attendance = $theBtn.attr('data-attendance') || 'attendance';
        var initials = $theBtn.attr('data-initials') || 'ST';

        $('#smsSMSModal input[name="student_id"]').val(student_id);
        $('#smsSMSModal .pt-sms-student-initials').text(initials);
        $('#smsSMSModal .pt-sms-student-name').text(student_name);
        $('#smsSMSModal .pt-sms-student-reg').text(registration);
        $('#smsSMSModal .pt-sms-student-attendance').text(attendance + ' attendance');
    })

    $('#smsTextArea').on('keyup', function(){
        var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
        if(chars > 0){
            if(chars >= maxlength && maxlength > 0){
                $('#smsSMSModal .modal-content .smsWarning').remove();
                $('#smsSMSModal .pt-note-body').prepend('<div class="smsWarning pt-drawer-alert" role="alert">Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
            }else{
                $('#smsSMSModal .modal-content .smsWarning').remove();
            }
            $('#smsSMSModal .sms_countr').html(remaining +' / '+messages);
        }else{
            $('#smsSMSModal .sms_countr').html('160 / 1');
            $('#smsSMSModal .modal-content .smsWarning').remove();
        }
    });


    $('#smsSMSForm #sms_template_id').on('change', function(){
        var smsTemplateId = $(this).val();
        if(smsTemplateId != ''){
            axios({
                method: "post",
                url: route('student.get.sms.template'),
                data: {smsTemplateId : smsTemplateId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#smsSMSForm #smsTextArea').val(response.data.row.description ? response.data.row.description : '').trigger('keyup');
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            })
        }else{
            $('#smsSMSForm #smsTextArea').val('');
            $('#smsSMSModal .sms_countr').html('160 / 1');
        }
    });

    $('#smsSMSForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('smsSMSForm');
    
        document.querySelector('#sendSMSBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sendSMSBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.send.sms'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#sendSMSBtn').removeAttribute('disabled');
            document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                smsSMSModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.message);
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 1000);
            }
        }).catch(error => {
            document.querySelector('#sendSMSBtn').removeAttribute('disabled');
            document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#smsSMSForm .${key}`).addClass('border-danger');
                        $(`#smsSMSForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    /*Student Attendance Tracking  End*/

    /* Term Data Reload Start*/
    $(document).on('click', '.pt_term_item', function(e){
        e.preventDefault();
        ptTermDropdown.hide();
        var $theBtn = $(this);
        var $theList = $theBtn.closest('.dropdown-content');
        var term_id = $theBtn.attr('data-id');
        var term_name = $theBtn.attr('data-term');

        var $contentWrap = $('.pt_term_content_wrap');
        var $contentArea = $contentWrap.find('.pt_term_content');
        var $myModuleWrap = $('#personalTutormoduleListWrap');
        var $myModuleArea = $myModuleWrap.find('#personalTutormoduleList');
        $contentWrap.find('.leaveTableLoader').addClass('active');
        $myModuleWrap.find('.leaveTableLoader').addClass('active');


        $('.ptTermDropdwnWrap').find('#ptTermDropdown .pt-term-label').html(term_name);
        $theList.find('li .pt_term_item').removeClass('text-primary font-medium is-active');
        $theBtn.addClass('text-primary font-medium is-active');

        axios({
            method: "post",
            url: route('pt.dashboard.get.term.statistics'),
            data: {term_id : term_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $contentWrap.find('.leaveTableLoader').removeClass('active');
            $myModuleWrap.find('.leaveTableLoader').removeClass('active');

            if (response.status == 200) {
                $contentArea.html(response.data.statshtml);
                $myModuleArea.html(response.data.modulhtml);

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error => {
            $contentWrap.find('.leaveTableLoader').removeClass('active');
            $myModuleWrap.find('.leaveTableLoader').removeClass('active');
            if (error.response) {
                $contentArea.html('<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> Something went wrong. Please try again later or contact with the administrator.</div>');
                $myModuleArea.html('<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> Something went wrong. Please try again later or contact with the administrator.</div>');
                console.log('error');

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        });
    })
    /* Term Data Reload End*/
    
})();
