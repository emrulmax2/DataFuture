import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {createApp} from 'vue'
import Toastify from "toastify-js";
import IMask from 'imask';

("use strict");
var admissionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let search = $("#term_declaration_id").val() != "" ? $("#term_declaration_id").val() : "";

        let tableContent = new Tabulator("#requestListTable", {
            ajaxURL: route("students.document-request-form.list"),
            ajaxParams: { search: search},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 50,100,200,500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                
                {
                    title: "S/N",
                    field: "sl",
                    headerHozAlign: "left",
                    sortable: false,
                    width: 80,
                },
                {
                    title: "Document Request Form Name",
                    field: "id",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="block">';
                                
                                html += '<div class="inline-block relative" style="top: -5px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().name+'</div>';
                                    html += '<div class="text-slate-500 text-sm whitespace-nowrap"">'+cell.getData().service_type+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Request/Comments",
                    field: "id",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="block">';
                                
                                html += '<div class="inline-block relative" style="top: 5px;">';
                                    html += '<div class="text-slate-600 text-sm whitespace-nowrap"">'+cell.getData().description+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Last Updated at",
                    field: "updated_at",
                    headerHozAlign: "center",
                    width: 200,
                    hozAlign: "center",
                    formatter(cell, formatterParams) {
                        //implement hr format date time
                        var html = '<div class="block">';
                            html += '<div class="inline-block relative" style="top: 5px;">';
                                html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().updated_at+'</div>';
                            html += '</div>';
                        html += '</div>';
                        return html;
                     
                    }

                },
                {
                    title: "Email Sent ?",
                    field: "email_status",
                    headerHozAlign: "center",
                    width: 120,
                    hozAlign: "center",
                    formatter(cell, formatterParams) {
                        //implement hr format date time
                        var html = '<div class="block">';
                            html += '<div class="inline-block relative" style="top: 5px;">';
                            if(cell.getData().email_status == "Pending") {
                                html += '<div data-tw-merge class="rounded bg-danger text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center ml-4 min-w-10 px-3 py-0.5 mb-2">No</div>';
                            } else if(cell.getData().email_status == "Sent") {
                                html += '<div data-tw-merge class="rounded bg-success text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center ml-4 min-w-10 px-3 py-0.5 mb-2">Yes</div>';
                            }
                            html += '</div>';
                        html += '</div>';
                        return html;
                     
                    }

                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "center",
                    width: 120,
                    hozAlign: "center",
                    formatter(cell, formatterParams) {
                        //implement pending formatter with colored badge background
                        var html = '<div class="block">';
                                html += '<div class="inline-block relative" style="top: 2px;">';
                                    if(cell.getData().status == "Pending") {
                                    html += '<button data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-1 px-3  font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-pending border-pending text-white dark:border-pending rounded-full mb-2 mr-1 w-24 ">'+cell.getData().status+'</button>';
                                    } else if(cell.getData().status == "Approved") {
                                        html += '<button data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-1 px-3  font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-success border-success text-slate-900 dark:border-success rounded-full mb-2 mr-1 w-24 ">'+cell.getData().status+'</button>';
                                    } else if(cell.getData().status == "Rejected") {
                                        html += '<button data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-1 px-3  font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-danger border-danger text-white dark:border-danger rounded-full mb-2 mr-1 w-24">'+cell.getData().status+'</button>';
                                    }else if(cell.getData().status == "In Progress") {
                                        html += '<button data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-1 px-3  font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-pending border-pending text-white dark:border-pending rounded-full mb-2 mr-1 w-24 ">'+cell.getData().status+'</button>';
                                    }
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }

                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
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

        // Export
        $("#tabulator-export-csv-ADM").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-ADM").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-ADM").on("click", function (event) {
            // window.XLSX = xlsx;
            // tableContent.download("xlsx", "data.xlsx", {
            //     sheetName: "Admission Details",
            // });
            event.preventDefault();
            $('#tabulator-export-xlsx-ADM #excel-loading').show();
            let semesters = $("#semesters-ADM").val() != "" ? $("#semesters-ADM").val() : "";
            let courses = $("#courses-ADM").val() != "" ? $("#courses-ADM").val() : "";
            let statuses = $("#statuses-ADM").val() != "" ? $("#statuses-ADM").val() : "";
            let refno = $("#refno-ADM").val() != "" ? $("#refno-ADM").val() : "";
            let firstname = $("#firstname-ADM").val() != "" ? $("#firstname-ADM").val() : "";
            let lastname = $("#lastname-ADM").val() != "" ? $("#lastname-ADM").val() : "";
            let dob = $("#dob-ADM").val() != "" ? $("#dob-ADM").val() : "";
            let agents = $("#agents-ADM").val() != "" ? $("#agents-ADM").val() : "";
            let email = $("#email-ADM").val() != "" ? $("#email-ADM").val() : "";
            let phone = $("#phone-ADM").val() != "" ? $("#phone-ADM").val() : "";
            
            axios({
                method: "get",
                url: route("admission.export"),
                params:{ semesters: semesters, courses: courses, statuses: statuses, refno: refno, firstname: firstname, lastname: lastname, dob: dob, agents: agents,email:email,phone:phone},
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                responseType: 'blob',
            })
            .then((response) => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'admission_download.xlsx'); 
                    document.body.appendChild(link);
                    link.click();
                    $('#tabulator-export-xlsx-ADM #excel-loading').hide();
            })
            .catch((error) => {
                    console.log(error);
            });
        });

        $("#tabulator-export-html-ADM").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-ADM").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){

    const succModal = tailwind.Modal.getOrCreateInstance(document.getElementById("successModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.getElementById("errorModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.getElementById("confirmModal"));

    
        document.getElementById("confirmModal").addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .agreeWith').attr('data-id', '0');
            $('#confirmModal .agreeWith').attr('data-action', 'none');
        });

    let admissionDatepickerOpt = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        maxDate: new Date(),
        dropdowns: {
            minYear: 1900,
            maxYear: null,
            months: true,
            years: true,
        },
    };

    $('.admissionDatepicker').each(function(){
        new Litepicker({
            element: this,
            ...admissionDatepickerOpt,
        });
    })

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    if($('.phoneMask').length > 0){
        $('.phoneMask').each(function(){
            IMask(
                this, {
                  mask: '00000000000'
                }
            )
        })
    }
    
    //var employment_status = new TomSelect('#employment_status', tomOptions);

    $('.addmissionLccTom').each(function(){
        if ($(this).attr("multiple") !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: "Remove this item",
                    },
                }
            };
        }
        new TomSelect(this, tomOptions);
    });

    if($('#requestListTable').length > 0){
        let multiTomOpt = {
            ...tomOptions,
            plugins: {
                ...tomOptions.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };
        var semestersADM = new TomSelect('#term_declaration_id', multiTomOpt);


        // Init Table
        admissionListTable.init();

        // Filter function
        function filterHTMLFormADM() {
            admissionListTable.init();
        }

        // On submit filter form


        // On click go button
        $("#tabulator-html-filter-go-ADM").on("click", function (event) {
            filterHTMLFormADM();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-ADM").on("click", function (event) {
            semestersADM.clear(true);

            $("#refno-ADM").val('');
            filterHTMLFormADM();
        });

        // semestersADM.on('change',function(event){
            
        //     axios({
        //         method: "get",
        //         url: route("course.creation.coursesbysemester"),
        //         params:{ semesters:event },
        //         headers: {
        //             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        //         },
        //         responseType: 'json',
        //     })
        //     .then((response) => {
        //         let courseList = response.data
        //         $(courseList).each(function(index,course) {
        //             coursesADM.addOption({value:course.id,text:course.name})
        //           });
        //     })
        //     .catch((error) => {
        //             console.log(error);
        //     });
        // })
    }

    // $('#agentRulesForm').on('submit', function(e){
    //     e.preventDefault();
    //     const form = document.getElementById('agentRulesForm');

 
    
    //     document.querySelector('#saveRuleBtn').setAttribute('disabled', 'disabled');
    //     document.querySelector("#saveRuleBtn svg").style.cssText ="display: inline-block;";

    //     let form_data = new FormData(form);
    //     axios({
    //         method: "post",
    //         url: route('students.document-request-form.store'),
    //         data: form_data,
    //         headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    //     }).then(response => {
    //         document.querySelector('#saveRuleBtn').removeAttribute('disabled');
    //         document.querySelector("#saveRuleBtn svg").style.cssText = "display: none;";
            
    //         if (response.status == 200) {
    //             console.log(response);
    //             agentRulesModal.hide();
                
    //             succModal.show();
    //             admissionListTable.init();
    //             //$viewBtn.removeClass('hidden');

    //             document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
    //                 $("#successModal .successModalTitle").html("Congratulation!");
    //                 $("#successModal .successModalDesc").html(response.data.message);
    //             });                
                    
    //         }
    //     }).catch(error => {
    //         document.querySelector('#saveRuleBtn').removeAttribute('disabled');
    //         document.querySelector("#saveRuleBtn svg").style.cssText = "display: none;";
    //         if (error.response) {
    //             if (error.response.status == 422) {
    //                 for (const [key, val] of Object.entries(error.response.data.errors)) {
    //                     $(`#agentRulesForm .${key}`).addClass('border-danger')
    //                     $(`#agentRulesForm  .error-${key}`).html(val)
    //                 }
    //             } else {
    //                 console.log('error');
    //             }
    //         }
    //     });
    // });
    
    $(".cancelOrder").on("click", function (event) {
        event.preventDefault();
        const $this = $(this);
        const orderId = $this.attr("data-order_id");
        const action = "DELETE";
        const title = "Are you sure?";
        const desc = "Are you sure you want to delete this order ? This action cannot be undone.";
      
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .modal-title").html(title);
            $("#confirmModal .modal-desc").html(desc);
            $('#confirmModal .agreeWith').attr('data-id', orderId);
            $('#confirmModal .agreeWith').attr('data-action', action);
        });
        confirmModal.show();
    });
// Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('students.order.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Order cancelled!');
                        });
                    }
                    window.location.reload();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('academicyears.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Academic Year Data Successfully Restored!');
                        });
                    }
                    location.reload();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })


        if($('#success-notification-toggle').length>0) {
            $("#success-notification-toggle").on("click", function () {
                Toastify({
                    node: $("#success-notification-content")
                        .clone()
                        .removeClass("hidden")[0],
                    duration: -1,
                    newWindow: true,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                }).showToast();
            });
            $("#success-notification-toggle").trigger('click');
        }
        if($('#error-notification-toggle').length>0) {

            $("#error-notification-toggle").on("click", function () {
                Toastify({
                    node: $("#error-notification-content")
                        .clone()
                        .removeClass("hidden")[0],
                    duration: -1,
                    newWindow: true,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                }).showToast();
            });
            $("#error-notification-toggle").trigger('click')
        }

})();

