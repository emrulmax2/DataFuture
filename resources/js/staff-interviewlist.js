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


var interviewListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#interviewList", {
            dataTree:true,
            ajaxURL: route("applicant.interview.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            printAsHtml: true,
            printStyled: true,
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
                        btns += '<button class="profile-lock__button btn btn-secondary w-48 mr-2 mb-2" data-id="'+ cell.getData().id + '" >\
                                    <i data-lucide="eye" class="w-4 h-4 mr-2"></i> View Profile\
                                    <svg width="25" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" stroke="rgb(100,116,139)" class="loading invisible w-4 h-4 ml-2">\
                                    <g fill="none" fill-rule="evenodd" stroke-width="4">\
                                        <circle cx="22" cy="22" r="1">\
                                            <animate attributeName="r"\
                                                begin="0s" dur="1.8s"\
                                                values="1; 20"\
                                                calcMode="spline"\
                                                keyTimes="0; 1"\
                                                keySplines="0.165, 0.84, 0.44, 1"\
                                                repeatCount="indefinite" />\
                                            <animate attributeName="stroke-opacity"\
                                                begin="0s" dur="1.8s"\
                                                values="1; 0"\
                                                calcMode="spline"\
                                                keyTimes="0; 1"\
                                                keySplines="0.3, 0.61, 0.355, 1"\
                                                repeatCount="indefinite" />\
                                        </circle>\
                                        <circle cx="22" cy="22" r="1">\
                                            <animate attributeName="r"\
                                                begin="-0.9s" dur="1.8s"\
                                                values="1; 20"\
                                                calcMode="spline"\
                                                keyTimes="0; 1"\
                                                keySplines="0.165, 0.84, 0.44, 1"\
                                                repeatCount="indefinite" />\
                                            <animate attributeName="stroke-opacity"\
                                                begin="-0.9s" dur="1.8s"\
                                                values="1; 0"\
                                                calcMode="spline"\
                                                keyTimes="0; 1"\
                                                keySplines="0.3, 0.61, 0.355, 1"\
                                                repeatCount="indefinite" />\
                                        </circle>\
                                    </g>\
                                </svg>\
                                </button>';
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
    document.querySelector(".profile-lock__button svg.loading").classList.remove('invisible')
    const data = {
        interviewId : $(this).attr("data-id")
    }
    axios({
        method: "post",
        url: route('applicant.interview.unlock'),
        data: data,
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {
        document.querySelector(".profile-lock__button svg.loading").classList.add('invisible')


        if (response.status == 200) {
            lockModal.hide();

            succModal.show();
            let Data = response.data.ref;
            document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                $("#successModal .successModalTitle").html( "Success!" );
                $("#successModal .successModalDesc").html('Profile Matched.');
            });   
            
            location.href= Data;  
        }
    }).catch(error => {
        document.querySelector(".profile-lock__button svg.loading").classList.add('invisible')
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
                            $("#errorModal .errorModalTitle").html('Invalid Profile!');
                            $("#errorModal .errorModalDesc").html('Interviewer didn\'t match');
                        }); 
                
                        
            } else {
                console.log('error')
            }
        }
    });

});



$(document).on("click", ".interview-result", function (e) { 
        e.preventDefault();
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

            $('#editForm').find('.interview_result__input').removeClass('border-danger')
            $('#editForm').find('.interview_result__input-error').html('')

            e.preventDefault()
            document.querySelector('#update').setAttribute('disabled', 'disabled')
            document.querySelector("#update svg").style.cssText ="display: inline-block;"

            const form = document.getElementById('editForm')
            let form_data = new FormData(form);
            
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