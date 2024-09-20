import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import moment from 'moment';


(function(){

    if($('#personalTutorDashboard').length > 0){
        $("#load-more").on('click',function(e){
            e.preventDefault()
            $('.more').removeClass('hidden');
            $("#load-more").hide()
        });
    }

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

    const start_date = new Litepicker({
        element: document.getElementById('personalTutorCalendar'),
        ...dateOption,
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
    $('#registration_no').on('keyup paste change', function(){
        var $theInput = $(this);
        var SearchVal = $theInput.val();

        if(SearchVal.length >= 3){
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

    $(".start-punch").on("click", function (event) {
        let data = $(this).data('id');   
        document.getElementById('employee_punch_number').focus();
        console.log(data);
        //let url = route('attendance.infomation.save');
        $(".plan-datelist").val(data);

    }); 


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const editPunchNumberDeteilsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPunchNumberDeteilsModal"));
    
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const startClassConfirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#startClassConfirmModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
    
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
                    successModal.hide();
                    startClassConfirmModal.show();
                    errorModal.hide();

                }  else if(xhr.status == 200){
                    //update Alert
                    editPunchNumberDeteilsModal.hide();
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

                }else if(jqXHR.status == 442)
                {
                    document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                        $("#confirmModal .confModTitle").html("Different Tutor ?");
                        $("#confirmModal .confModDesc").html('Please Put a note Below, why are you taking this class?');
                    });  
                    editPunchNumberDeteilsModal.hide();
                    confirmModal.show();
                }else if(jqXHR.status == 444)
                {
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Wrong Punch Number");
                        $("#errorModal .errorModalDesc").html('It is not your punch number');
                    });  
                    editPunchNumberDeteilsModal.hide();
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
    
})();