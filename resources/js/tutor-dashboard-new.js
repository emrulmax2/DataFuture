import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import moment from 'moment';

("use strict");
var attendanceListTable = (function () {
    var _tableGen = function (form) {
 
        $.ajax({
            method: 'GET',
            url: route("tutor-dashboard.list"),
            data: form,
            dataType: 'json',
            async: false,
            contentType: false,
            cache: false,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            success: function(res, textStatus, xhr) {
                let dataSet = res.data
                let html = ""
                if(xhr.status == 200){
                    $(dataSet).each((index, data)=>{
                        //console.log(value)
                        
                        html +=`<div class="mt-5 intro-x">
                                <div class="box zoom-in">
                                    <div class="pt-5 px-5">
                                        <div class="rounded bg-success text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center min-w-10 px-3 py-0.5 mb-2">${ data.group }</div>
                                        <div class="ml-0 mr-auto">
                                            <div class="text-base font-medium truncate w-full relative">${ data.module } </div>
                                            <div class="text-slate-400 mt-1">${ data.course }</div>
                                            <div class="text-slate-400 mt-1">Schedule - ${ data.start_time } to ${ data.end_time } at ${ data.venue } - ${ data.room }</div>
                                        </div>
                                    </div>
                                    <div class="mt-5 px-5 pb-5 flex font-medium justify-center">`;
                                    if(data.attendance_information != null) {
                                        if(data.feed_given != 1) {
                                            html +=`<a data-attendanceinfo="${ data.attendance_information.id }" data-id="${ data.id }" href="`;
                                            html+= route('tutor-dashboard.attendance',[data.tutor_id,data.id])
                                            html +=`" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Feed Attendance</a>`;
                                            
                                        } else {
                                            html +=`<a href="`; 
                                            html += route('tutor-dashboard.attendance',[data.tutor_id,data.id])
                                            html +=`"  data-attendanceinfo="${ data.attendance_information.id }" data-id="${ data.id }" class="start-punch transition duration-200 btn btn-sm btn-success text-white py-2 px-3 "><i data-lucide="view" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>View Feed</a>`;
                                            if(data.feed_given == 1 && data.attendance_information.end_time == null){
                                                html += `<a data-attendanceinfo="${ data.attendance_information.id }" data-id="${ data.id }" data-tw-toggle="modal" data-tw-target="#endClassModal" class="start-punch transition duration-200 btn btn-sm btn-danger text-white py-2 px-3 ml-1"><i data-lucide="x-circle" class="stroke-1.5 mr-2 h-4 w-4"></i>End Class</a>`;
                                            }
                                        }
                                    } else {
                                        if(data.showClass == 1){
                                            html +=`<a data-tw-toggle="modal" data-id="${ data.id }" data-tw-target="#editPunchNumberDeteilsModal" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Start Class</a>`
                                        }else if(data.showClass == 2){
                                            html += '<div class="alert alert-pending-soft show flex items-start" role="alert">\
                                                        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Class Start Button appears 15 minutes before the scheduled time.\
                                                    </div>';
                                        }
                                    }
                                    html +=`</div>
                                </div>
                            </div>`;
                    })
                    $('#todays-classlist').html(html);
                    setTimeout(function(){
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 200);
                    $(".start-punch").on("click", function (event) {
            
                        let data = $(this).data('id');   
                        document.getElementById('employee_punch_number').focus();
                        console.log(data);
                        //let url = route('attendance.infomation.save');
            
                        $(".plan-datelist").val(data);
            
                    });    
                }
                
            },
            error: function (jqXHR, textStatus, errorThrown) {
                
                    console.log(textStatus+' => '+errorThrown);
                
                
            }
        });
    };
    return {
        init: function (form = []) {
            _tableGen(form);
        },
    };
})();


(function(){

    if($('#tutorDashboard').length > 0){
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
            element: document.getElementById('tutor-calendar-date'),
            ...dateOption,
            setup: (picker) => {
                picker.on('selected', (date) => {
                    
                    let tutorData  = $("input[name='tutor_id']").val()
                    let customDate =  moment(date.dateInstance).format('DD-MM-YYYY');
    
                    let form = {
                        "id": tutorData,
                        "plan_date": customDate
                    }    
                    attendanceListTable.init(form);
                    
                });
            }
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
        const endClassModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#endClassModal"));
        
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
                    $theBtn.removeAttr('disabled');
                    $theBtn.find('svg').fadeOut();

                    $('.acc__input-error', parentForm).html('');
                    if(xhr.status == 206){
                        //update Alert
                        editPunchNumberDeteilsModal.hide();
                        startClassConfirmModal.hide();
                        successModal.show();
                        confirmModal.hide();
                        errorModal.hide()
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html("Congratulations!");
                            $("#successModal .successModalDesc").html('Data updated.');
                        });                
                        
                        setTimeout(function(){
                            successModal.hide();
                            location.href= route("tutor-dashboard.attendance",[res.data.tutor ,res.data.plandate])
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
                        startClassConfirmModal.hide();
                        successModal.show();
                        confirmModal.hide();
                        errorModal.hide()
                        endClassModal.hide();
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
                        startClassConfirmModal.hide();
                        confirmModal.show();
                    }else if(jqXHR.status == 444)
                    {
                        document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                            $("#errorModal .errorModalTitle").html("Wrong Punch Number");
                            $("#errorModal .errorModalDesc").html('It is not your punch number');
                        });  
                        editPunchNumberDeteilsModal.hide();
                        startClassConfirmModal.hide();
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
                        startClassConfirmModal.hide();
                        errorModal.show();
                        setTimeout(function(){
                            errorModal.hide();
                            editPunchNumberDeteilsModal.show();
                        }, 1000);
                    }else if(jqXHR.status == 322)
                    {
                        endClassModal.hide();
                        startClassConfirmModal.hide();
                        errorModal.show();
                        document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                            $("#errorModal .errorModalTitle").html("Oops!");
                            $("#errorModal .errorModalDesc").html('You are out of College. Please return to college to end your class');
                        });  
                        
                        setTimeout(function(){
                            errorModal.hide();
                        }, 2000);
                    }else{
                        console.log(textStatus+' => '+errorThrown);
                    }
                    
                }
            });
            
        });

        $('.term-select').on('click', function (e) {
            e.preventDefault();
            let tthis = $(this)
            let btnSvg = $("#selected-term svg")
            let selectedText = $("#selected-term span")
            let termname = tthis.text()
            let instanceTermId = tthis.data('instance_term_id')
            let tutorId = tthis.data('tutor_id')
            btnSvg.eq(0).hide()
            btnSvg.eq(1).show()
            axios({
                method: "get",
                url: route("tutor-dashboard.tutor.modulelist",[instanceTermId,tutorId]),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    selectedText.html(termname)
                    
                    $(".term-select").removeClass('dropdown-active')
                    $("#term-"+instanceTermId).addClass('dropdown-active')
                    //termDropdown.hide()
                    $("#TermBox").html("")
                    btnSvg.eq(1).hide()
                    btnSvg.eq(0).show()
                        //console.log(dataset)
                        //update Alert
                        let html = ""
                        

                        $(dataset.current_term[instanceTermId]).each(function(index, dataSet){
                            
                            $(dataSet).each(function(index, data){
                                //console.log(data.id)
                            html +=`<div id="totalmodule-${data.id}" class="report-box-2 intro-y mt-5 mb-7 ">
                                    <div class="box p-5">
                                        <div class="flex items-center">
                                            Total No of Modules
                                        </div>
                                        <div class="text-2xl font-medium mt-2">${data.total_modules}</div>
                                    </div>
                                </div>`
                            })
                            
                        })
                        $(dataset.module_data[instanceTermId]).each(function(index, dataSet){
                            $(dataSet).each(function(index, data){
                                console.log(data)
                                html +=`<a href="${ route('tutor-dashboard.plan.module.show',data.id) }" target="_blank" style="inline-block">
                                        <div id="moduleset-${data.id}" class="intro-y module-details_${data.id} ">
                                            <div class="box px-4 py-4 mb-3 zoom-in">
                                                <div class="rounded bg-success text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center ml-4 min-w-10 px-3 py-0.5 mb-2">${ data.group }</div>
                                                <div class="ml-4 mr-auto">
                                                    <div class="font-medium">${ data.module }</div>
                                                    <div class="text-slate-500 text-xs mt-0.5">${ data.course }</div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>`

                            })
                        })

                        $("#TermBox").html(html)
                        

                }
            }).catch((error) => {
                btnSvg.eq(1).hide()
                btnSvg.eq(0).show()
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addSmtpForm .${key}`).addClass('border-danger')
                            $(`#addSmtpForm  .error-${key}`).html(val)
                        }
                    }else if(error.response.status == 303){

                        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                            $("#confirmModal .confModTitle").html("End Class!");
                            $("#confirmModal .confModDesc").html('Do you want to End Class.');
                        });   
                        confirmModal.show();
                        editPunchNumberDeteilsModal.hide();

                    } else {
                        console.log('error');
                    }
                }
            });
            
        });
    }
    
})();
