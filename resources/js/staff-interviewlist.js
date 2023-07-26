import xlsx from "xlsx";
import { createElement, createIcons, icons,Minus,Plus } from "lucide";
import Tabulator from "tabulator-tables";
import { constant } from "lodash";

("use strict");

let checkBoxAll = '<div data-tw-merge class="flex items-cente mt-2"><input  id="checkbox-all" value="" data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50" />\
<label data-tw-merge for="checkbox-all" class="cursor-pointer ml-2">Applicant</label>\
</div>' 

const minusIcon = createElement(Minus)
minusIcon.setAttribute('stroke-width', '1.5')

const plusIcon = createElement(Plus)
plusIcon.setAttribute('stroke-width', '1.5')
// our object array
var dataStudents = [];

var interviewListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#interviewList", {
            dataTree:true,
            //dataTreeCollapseElement:minusIcon,
            //dataTreeExpandElement:plusIcon,
            ajaxURL: route("applicant.interview.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            //ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            //columnDefs: [ { orderable: false, targets: [0,2], }],
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Serial",
                    field: "sl",
                    width: "180",
                    //sortable:false,
                },
                {
                    title: "Applicant No.",
                    field: "applicant_number",
                    headerHozAlign: "left",
                },
                {
                    title: "Date",
                    field: "date",
                    headerHozAlign: "left",
                    
                    headerSort:false,
                },
                {
                    title: "Name",
                    field: "name",
                    headerSort:false,
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerHozAlign: "left",
                    
                    headerSort:false,
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    
                    headerSort:false,
                },
                {
                    title: "Sart Time - End Time",
                    field: "time",
                    headerHozAlign: "left",
                    
                    headerSort:false,
                },
                {
                    title: "Result",
                    field: "result",
                    headerHozAlign: "left",
                }
                ,{
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    formatter(cell, formatterParams) {                        
                        var btns = ""; 
                        
                        btns += '<button aria-expanded="false" data-tw-toggle="modal" data-id="' + 
                                        cell.getData().id 
                                        + '" data-tw-target="#callLockModal"  class="profile-lock__button transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&:hover:not(:disabled)]:bg-slate-100 [&:hover:not(:disabled)]:border-slate-100 [&:hover:not(:disabled)]:dark:border-darkmode-300/80 [&:hover:not(:disabled)]:dark:bg-darkmode-300/80 mb-2 mr-1"><i data-lucide="lock" class="stroke-1.5 h-5 w-5"></i></button>';

                            //     btns += '<div class="dropdown">\
                            //     <button aria-expanded="false" data-tw-toggle="dropdown" data-tw-merge class="dropdown-toggle transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&:hover:not(:disabled)]:bg-slate-100 [&:hover:not(:disabled)]:border-slate-100 [&:hover:not(:disabled)]:dark:border-darkmode-300/80 [&:hover:not(:disabled)]:dark:bg-darkmode-300/80 mb-2 mr-1"><i data-lucide="more-vertical" width="24" height="24" class="stroke-1.5 h-5 w-5"></i></button>\
                            //     <div class="dropdown-menu w-40">\
                            //         <ul class="dropdown-content">\
                            //             <li>\
                            //                 <div class="dropdown-header">Options</div>\
                            //             </li>\
                            //             <li>\
                            //                 <hr class="dropdown-divider">\
                            //             </li>\
                            //             <li>\
                            //                 <a href="javascript:void(0)" data-id="' + 
                            //                 cell.getData().id 
                            //                 + '"class="dropdown-item interview-start hover-bg-success hover-text-white">\
                            //                     <i data-lucide="alarm-clock" class="w-4 h-4 mr-2"></i> Start Interview\
                            //                 </a>\
                            //             </li>\
                            //             <li>\
                            //                 <a href="javascript:void(0)" data-id="' + 
                            //                 cell.getData().id 
                            //                 + '"class="dropdown-item interview-end hover-bg-success hover-text-white">\
                            //                     <i data-lucide="alarm-clock-off" class="w-4 h-4 mr-2"></i> End interview\
                            //                 </a>\
                            //             </li>\
                            //             <li>\
                            //                 <a href="javascript:void(0)" data-id="' + 
                            //                 cell.getData().id 
                            //                 + '" data-tw-toggle="modal" data-tw-target="#editModal" class="dropdown-item interview-result hover-bg-success hover-text-white">\
                            //                     <i data-lucide="activity" class="w-4 h-4 mr-2"></i> Update Result\
                            //                 </a>\
                            //             </li>\
                            //             <li>\
                            //                 <a href="javascript:void(0)" data-id="' + 
                            //                 cell.getData().id 
                            //                 + '" class="dropdown-item interview-taskend hover-bg-success hover-text-white">\
                            //                     <i data-lucide="archive" class="w-4 h-4 mr-2"></i> Finish Task\
                            //                 </a>\
                            //             </li>\
                            //         </ul>\
                            //     </div>\
                            // </div>'
                        return btns;
                    },
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });               

            },
        });
        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Tasks List",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
            tableContent.print();
        });

    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
const editModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));


const lockModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#callLockModal"));

//

$(document).on("click", ".profile-lock__button", function (e) { 
    e.preventDefault();
    //interviewId = $(this).attr("data-id");
    document.getElementById('interviewId').value = $(this).attr("data-id");

});
$('#callLockModalForm').on('submit', function(e){
    e.preventDefault();
    const form = document.getElementById('callLockModalForm');

    document.querySelector('#unlock').setAttribute('disabled', 'disabled');
    document.querySelector("#unlock svg.loading").style.cssText ="display: inline-block;";

    let form_data = new FormData(form);
    //form_data.append('file', $('#addUserForm input[name="photo"]')[0].files[0]); 
    axios({
        method: "post",
        url: route('applicant.interview.unlock'),
        data: form_data,
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {
        document.querySelector('#unlock').removeAttribute('disabled');
        document.querySelector("#unlock svg.loading").style.cssText = "display: none;";
        //console.log(response.data.data);
        //return false;

        if (response.status == 200) {
            lockModal.hide();

            succModal.show();
            let Data = response.data.ref;
            //alert(Data);
            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                $("#successModal .successModalTitle").html( "Congratulations!" );
                $("#successModal .successModalDesc").html('Profile Unlocked.');
            });   
            
            location.href= Data;  
        }
        //userListTable.init();
    }).catch(error => {
        document.querySelector('#unlock').removeAttribute('disabled');
        document.querySelector("#unlock svg.loading").style.cssText = "display: none;";
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#callLockModalForm .${key}`).addClass('border-danger');
                    $(`#callLockModalForm  .error-${key}`).html(val);
                }
            } else if (error.response.status == 404) {
                succModal.hide();
                lockModal.hide();
                errorModal.show();
                document.getElementById("errorModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#errorModal .errorModalTitle").html('Invalid Date!');
                            $("#errorModal .errorModalDesc").html('Invalid Date');
                        }); 
                
                        
            } else {
                console.log('error')
            }
        }
    });
});
//


$(document).on("click", ".interview-result", function (e) { 
        e.preventDefault();
        //interviewId = $(this).attr("data-id");
        document.getElementById('id').value = $(this).attr("data-id");;
    
});
$(document).on("click", ".interview-taskend", function (e) { 
            
            e.preventDefault();

            const theId = $(this).attr("data-id");
            console.log(theId);
            axios({
                method: "post",
                url: route('applicant.interview.task.update'),
                data: {
                  id: theId
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {

                if (response.status == 200) {
                    editModal.hide();
                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(response.data.msg);
                            $("#successModal .successModalDesc").html('success');
                        });    
                }

                interviewListTable.init();

            }).catch(error => {
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

});

$(document).on("click", ".interview-start", function (e) { 
            
    e.preventDefault();

    const theId = $(this).attr("data-id");
    console.log(theId);
    axios({
        method: "post",
        url: route('applicant.interview.start'),
        data: {
          id: theId
        },
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {

        if (response.status == 200) {
            editModal.hide();
            succModal.show();
            document.getElementById("successModal")
                .addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html(response.data.msg);
                    $("#successModal .successModalDesc").html('success');
                });    
        }

        interviewListTable.init();

    }).catch(error => {
        
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#${key}`).addClass('border-danger')
                    $(`#error-${key}`).html(val)
                }
            } else {
                console.log('error');
            }
        }
    });

});


$(document).on("click", ".interview-end", function (e) { 
            
    e.preventDefault();

    const theId = $(this).attr("data-id");
    console.log(theId);
    axios({
        method: "post",
        url: route('applicant.interview.end'),
        data: {
          id: theId
        },
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {

        if (response.status == 200) {
            editModal.hide();
            succModal.show();
            document.getElementById("successModal")
                .addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html(response.data.msg);
                    $("#successModal .successModalDesc").html('success');
                });    
        }

        interviewListTable.init();

    }).catch(error => {
        
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#${key}`).addClass('border-danger')
                    $(`#error-${key}`).html(val)
                }
            } else {
                console.log('error');
            }
        }
    });

});


(function () {


    if ($("#interviewList").length) {
        // Init Table
        interviewListTable.init();
        
        // Filter function
        function filterHTMLForm() {
            interviewListTable.init();
        }
        
        $('#editForm').on("submit", function (e) {

            $('#editForm').find('.interview_status__input').removeClass('border-danger')
            $('#editForm').find('.interview_status__input-error').html('')

            e.preventDefault()
            document.querySelector('#update').setAttribute('disabled', 'disabled')
            document.querySelector("#update svg").style.cssText ="display: inline-block;"

            const form = document.getElementById('editForm')
            let form_data = new FormData(form);
            //const user = document.getElementById('interview_status').value;
            
            axios({
                method: "post",
                url: route('applicant.interview.result.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {

                document.querySelector('#update').removeAttribute('disabled');
                document.querySelector("#update svg").style.cssText = "display: none;";
                console.log(response);
                if (response.status == 200) {
                    document.querySelector('#update').removeAttribute('disabled');
                    document.querySelector("#update svg").style.cssText = "display: none;";
                    $('.user__input').val('');
                    editModal.hide();
                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(response.data.msg);
                            $("#successModal .successModalDesc").html('success');
                        });                
                        
                }
                interviewListTable.init();
            }).catch(error => {
                document.querySelector('#assign').removeAttribute('disabled');
                document.querySelector("#assign svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                        $('#interviewerSelectForm #user').val('');
                    } else {
                        console.log('error');
                    }
                }
            });


        });
        
    }
})()