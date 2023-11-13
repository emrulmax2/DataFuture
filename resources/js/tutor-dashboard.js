import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var attendanceListTable = (function () {
    var _tableGen = function (form) {
        // Setup Tabulator
        
        
        let tableContent = new Tabulator("#tutorClassList", {
            ajaxURL: route("tutor-dashboard.list"),
            ajaxParams: form,
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            headerSort:false,
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    formatter: "responsiveCollapse",
                    width: 40,
                    minWidth: 30,
                    hozAlign: "center",
                    resizable: false,
                    headerSort: false,
                },
                {
                    title: "COURSE & MODULE",
                    minWidth: 200,
                    responsive: 0,
                    field: "course",
                    vertAlign: "middle",
                    headerHozAlign: "left",
                    print: false,
                    download: false,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().course
                            }</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().module
                            }</div>
                        </div>`;
                    },
                },
                {
                    title: "GROUP",
                    field: "group",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    width:100,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().group
                            }</div>
                        </div>`;
                    },
                },
                {
                    title: "TIME",
                    field: "time",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:"center",
                    width:150,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().start_time
                            } TO </div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().end_time
                            }</div>
                        </div>`;
                    },
                },
                {
                    title: "ROOM",
                    field: "venue",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    width:200,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().venue
                            }</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().room
                            }</div>
                        </div>`;
                    },
                },

                {
                    title: "VR",
                    field: "virtual_room",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    headerSort:false,
                    width:80,
                    formatter(cell, formatterParams) {       
                        return '<a href="'+cell.getData().virtual_room+'" target="_blank"  class="btn-primary btn text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="video" class="w-4 h-4"></i></a>';
                    },
                },
                
                {
                    title: "LECTURE TYPE",
                    field: "lecture_type",
                    vertAlign: "middle",
                    hozAlign:  "center",
                    headerHozAlign: "center",
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().lecture_type
                            }</div>
                        </div>`;
                    },
                },
                
                {
                    title: "CAPTURED BY AND AT",
                    field: "captured_by",
                    vertAlign: "middle",
                    hozAlign:  "center",
                    headerHozAlign: "center",
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().captured_by
                            }</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().captured_at
                            }</div>
                        </div>`;
                    },
                },
                
                {
                    title: "STATUS",
                    field: "status",
                    width: 150,
                    vertAlign: "middle",
                    hozAlign:  "center",
                    headerHozAlign: "center",
                    formatter(cell, formatterParams) {
                        let dropdown = [];
                        let attendanceInformation = cell.getData().attendance_information
                        if(attendanceInformation!=null) {
                            if(attendanceInformation.end_time==null) { 
                            dropdown =`<div data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-success text-success dark:border-success [&amp;:hover:not(:disabled)]:bg-success/10 mb-2 mr-1  w-24">Class Started</div>`;
                            } else {
                                dropdown =`<div data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-primary text-primary dark:border-primary [&amp;:hover:not(:disabled)]:bg-primary/10 mb-2 mr-1  w-24 ">Class Ended</div>`;  
                            }
                        }else {
                            dropdown =`<div class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-pending text-pending dark:border-pending [&amp;:hover:not(:disabled)]:bg-pending/10 mb-2 mr-1  w-24 ">Pending</div>`;
                        
                        }
                        return dropdown;
                    },
                },
                {
                    title: "ACTIONS",
                    minWidth: 200,
                    field: "actions",
                    responsive: 1,
                    hozAlign: "center",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    print: false,
                    download: false,
                    formatter(cell, formatterParams) {
                        let dropdown = [];
                        
                        let attendanceInformation = cell.getData().attendance_information
                        if(attendanceInformation!=null) {
                            if(attendanceInformation.end_time==null) { 
                                
                                    dropdown =`<a data-attendanceinfo="${
                                        attendanceInformation.id
                                    }" data-id="${
                                        cell.getData().id
                                    }" href="${
                                        cell.getData().tutor_id
                                    }/attendance/${
                                        cell.getData().id
                                    }" class="start-punch transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-2 w-32"><i data-lucide="activity" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>
                                    Feed Attendance</a>`;
                                
                                dropdown +=`<button data-tw-toggle="modal" data-attendanceinfo="${
                                    attendanceInformation.id
                                }" data-id="${
                                    cell.getData().id
                                }" data-tw-target="#endClassModal" class="start-punch transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-danger border-danger text-white dark:border-danger mb-2 mr-2 w-32  "><i data-lucide="clock" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>
                                End Class</button>`;
                            } else {
                                dropdown =`<a href="${
                                    cell.getData().tutor_id
                                }/attendance/${
                                    cell.getData().id
                                }"  data-attendanceinfo="${
                                    attendanceInformation.id
                                }" data-id="${
                                    cell.getData().id
                                }" data-tw-target="#viewFeed" class="start-punch transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-2 w-32 "><i data-lucide="view" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>
                                View Feed</a>`;
                            }
                        }else {

                             dropdown =`<button data-tw-toggle="modal" data-id="${
                                cell.getData().id
                            }" data-tw-target="#editPunchNumberDeteilsModal" class="start-punch transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-success border-success text-slate-900 dark:border-success mb-2 mr-2 w-32 "><i data-lucide="clock" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>
                            Start Class</button>`;
                            
                        }
                        return dropdown;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                $(".start-punch").on("click", function (event) {
                    let data = $(this).data('id');   
                    document.getElementById('employee_punch_number').focus();
                    console.log(data);

                    //let url = route('attendance.infomation.save');

                    $(".plan-datelist").val(data);

                });
            },
            rowClick:function(e, row){
                //window.open(row.getData().url, '_blank');
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
    };
    return {
        init: function (form = []) {
            _tableGen(form);
        },
    };
})();


(function(){
    if($('#tutorClassList').length > 0){

        let tutorData  = $("input[name='tutor_id']").val()
        let dateSearch  = $("input[name='current_hidden_date']").val()
        let form = {
            "id": tutorData,
            "plan_date": dateSearch
        }    

        filterAttendanceListTable(form)

        function filterAttendanceListTable(form) {
            attendanceListTable.init(form);
        }
        $(".start-punch").on("click", function (event) {
            
            document.getElementById('employee_punch_number').focus();
        });
        $("#planDateSearchBtn").on("click", function (event) {
            event.preventDefault()
            let tutorData  = $("input[name='tutor_id']").val()
            let dateSearch  = $("input[name='plan_date']").val()
            let form = {
                "id": tutorData,
                "plan_date": dateSearch
            }    
            filterAttendanceListTable(form)
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
