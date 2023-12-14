import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import moment from 'moment';

("use strict");
var attendanceListTable = (function () {
    var _tableGen = function (form) {
 
         //let form_data = new FormData(form);
         
         
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
                                    <div class="pt-5 px-5 flex items-center">
                                        <div class="ml-0 mr-auto">
                                            <div class="text-base font-medium truncate w-full relative">${ data.module } </div>
                                            <div class="text-slate-400 mt-1">${ data.course }</div>
                                            <div class="text-slate-400 mt-1">Schedule - ${ data.start_time } at ${ data.venue } - ${ data.room }</div>
                                        </div>
                                        <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-12 h-10 inline-flex justify-center items-center">${ data.group }</div>
                                        
                                    </div>
                                    <div class="mt-5 px-5 pb-5 flex font-medium justify-center">`;
                                    if(data.attendance_information!=null) {
                                        if(data.end_time==null) {
                                            html +=`<a data-attendanceinfo="${ data.attendance_information.id }" data-id="${ data.id }" href="`;
                                            html+= route('tutor-dashboard.attendance',[data.tutor_id,data.id])
                                            html +=`" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Feed Attendance</a>
                                            <a data-attendanceinfo="${ data.attendance_information.id }" data-id="${ data.id }" data-tw-toggle="modal" data-tw-target="#endClassModal" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">End Class</a>`
                                        } else {
                                            html +=`<a href="`; 
                                            html += route('tutor-dashboard.attendance',[data.tutor_id,data.id])
                                            html +=`"  data-attendanceinfo="${ data.attendance_information.id }" data-id="${ data.id }" class="start-punch transition duration-200 btn btn-sm btn-success text-white py-2 px-3 "><i data-lucide="view" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>View Feed</a>`
                                        }
                                    } else {
                                        html +=`<a data-tw-toggle="modal" data-id="${ data.id }" data-tw-target="#editPunchNumberDeteilsModal" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Start Class</a>`
                                    }
                                    html +=`</div>
                                </div>
                            </div>`;
                    })
                    $('#todays-classlist').html(html)      
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
        
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
        $('.save').on('click', function (e) {
            e.preventDefault();

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
                            location.href= route("tutor-dashboard.attendance",[res.data.tutor ,res.data.plandate])
                        }, 1000);

                    } else if(xhr.status == 200){
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
                    
                    if(jqXHR.status == 422){
                        for (const [key, val] of Object.entries(jqXHR.responseJSON.errors)) {
                            $(`#${formID} .${key}`).addClass('border-danger');
                            $(`#${formID}  .error-${key}`).html(val);
                        }
                    }else if(jqXHR.status == 303){

                        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                            $("#confirmModal .confModTitle").html("End Class!");
                            $("#confirmModal .confModDesc").html('Do you want to End Class.');
                        });   
                        confirmModal.show();
                        editPunchNumberDeteilsModal.hide();

                    }else if(jqXHR.status == 302)
                    {
                        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
                            $("#confirmModal .confModTitle").html("Different Tutor ?");
                            $("#confirmModal .confModDesc").html('Please Put a note Below, why are you taking this class?');
                        });  
                        editPunchNumberDeteilsModal.hide();
                        confirmModal.show();
                    }else if(jqXHR.status == 304)
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
    }
    
})();
