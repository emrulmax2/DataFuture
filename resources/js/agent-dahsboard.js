import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var applicantApplicantionList = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        let tableContent = new Tabulator("#applicantApplicantionList", {
            ajaxURL: route("agent.dashboard.applications.list"),
            ajaxParams: {},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "sl",
                    width: "110",
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "DOB",
                    field: "dob",
                    headerHozAlign: "left",
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                },
                {
                    title: "Submission Date",
                    field: "submission_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().submission_date == '') {
                            btns += '<a href="'+route('agent.application',cell.getData().applicationCheck)+'" class="btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                        }else{
                            btns += '<a href="'+route('agent.application.show', cell.getData().id)+'" class="btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                        }
                        
                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
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
        init: function () {
            _tableGen();
        },
    };
})();

var applicantionCustonList = (function () {
    var _tableGenList1 = function (responseData) {
        // Setup Tabulator

        let dataset = responseData
        let totalApplicant = dataset.length
        
        let html = `<div class="report-box-2 intro-y mt-5 mb-7">
                            <div class="box p-5">
                                <div class="flex items-center">
                                    Total Active Application
                                </div>
                                <div class="text-2xl font-medium mt-2">${totalApplicant}</div>
                            </div>
                        </div>`;

        $("#total-application").html(html)

        let htmlRecents = "";

        $(dataset).each(function(index,data) { 
                if( data.mobile_verified_at && data.email_verified_at ) {
                    htmlRecents +=`<div data-applicationid="${ data.id }" 
                                    data-email-verified="${(data.email_verified_at ? 1:0)}" 
                                    data-email="${ data.email }" 
                                    data-mobile="${ data.mobile }" 
                                    data-mobile-verified="${ (data.mobile_verified_at ? 1:0) }"  
                                    class="newapplicant-modal" style="inline-block">`
                } else {
                    htmlRecents +=`<div data-tw-target="#confirmModal" data-tw-toggle="modal" 
                                    data-applicationid="${ data.id }" 
                                    data-email-verified="${(data.email_verified_at ? 1:0)}" 
                                    data-email="${data.email}" 
                                    data-mobile="${data.mobile}" 
                                    data-mobile-verified="${ (data.mobile_verified_at ? 1:0)}"  
                                    class="newapplicant-modal" style="inline-block">`
                }
                htmlRecents +=`<div  class="intro-y module-details_1 ">
                            
                            <div class="box px-4 py-4 mb-3 flex items-center zoom-in">`;
                            if( !data.mobile_verified_at && !data.email_verified_at ) {
                                htmlRecents +=`<div data-tw-target="#confirmDeleteModal" data-tw-toggle="modal" 
                                                data-id="${ $recentapplicant.id }" 
                                                title="Do you want to remove this item?" 
                                                class="tooltip w-5 h-5 flex items-center justify-center absolute rounded-full text-white bg-danger right-0 top-0 -mr-2 -mt-2 delete_btn">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </div>`
                            }
                            htmlRecents +=`<div class="ml-4 mr-auto">
                                    <div class="font-medium">`

                                        if( !data.mobile_verified_at && !data.email_verified_at ) {
                                        htmlRecents +=`<a href="${ route("agent.application",data.id) }" style="inline-block">${data.first_name} ${data.last_name}</a>`
                                        } else {
                                            htmlRecents +=`${data.first_name} ${data.last_name}`
                                        }
                                    htmlRecents +=`</div>
                                    <div class="text-slate-500 text-xs mt-0.5 ">`
                                        if(data.email_verified_at) {
                                            htmlRecents +=`<i data-lucide="check-circle" class="w-4 h-4 mr-1 text-success inline-flex"></i> Email verified`
                                            } else {
                                            htmlRecents +=`<i data-lucide="x-circle" class="w-4 h-4 mr-1 text-danger inline-flex"></i> Email not verified`
                                        }
                                    htmlRecents +=`</div>
                                    <div class="text-slate-500 text-xs mt-0.5 ">`

                                        if(data.mobile_verified_at) {
                                            htmlRecents +=`<i data-lucide="check-circle" class="w-4 h-4 mr-1 text-success inline-flex"></i> Mobile verified`
                                        } else {
                                            htmlRecents +=`<i data-lucide="x-circle" class="w-4 h-4 mr-1 text-danger inline-flex"></i> Mobile not verified`
                                        }

                                htmlRecents +=`</div>
                            </div>`;
                            if(data.mobile_verified_at && data.email_verified_at ) {
                                htmlRecents +=`<a href="${ route("agent.application",data.id) }" class="btn btn-sm btn-success w-28 mr-2 mb-2 text-white">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Apply Now
                                    </a>`
                            } else {
                                htmlRecents +=`<div class="rounded-full text-lg bg-warning text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">
                                        <i data-lucide="alert-circle" class="w-4 h-4 m-auto text-white"></i>
                                    </div>`;
                            }
                            htmlRecents +=`</div>
                        </div>
                    </div>`;
        })
        
        $("#applicant-list").html(htmlRecents)
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        })
    };
    return {
        init: function (responseData) {
            _tableGenList1(responseData);
        },
    };
})();

(function () {
    if($('#applicantApplicantionList').length > 0){
        applicantApplicantionList.init();
    }
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addDeteilsModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const confirmDeleteModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmDeleteModal"));
    $('.save').on('click', function(e){
        e.preventDefault();

        let tthis = $(this);
        let parentForm = tthis.parents('form');
        let formID = parentForm.attr('id');
        const form = document.getElementById(formID);
        let rurl = $("#"+formID+" input[name=url]").val();

        tthis.attr('disabled', 'disabled');
        $("svg",tthis).css("display", "inline-block");


        let form_data = new FormData(form);
        axios({
            method: "post",
            url: rurl,
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            tthis.removeAttr('disabled');
            $("svg",tthis).css("display", "none");

            if (response.status == 200) {

                tthis.removeAttr('disabled');
                $("svg",tthis).css("display", "none");

                addModal.hide();
                succModal.show();
                confirmModal.hide();
                applicantionCustonList.init(response.data);
                if(response.data.email_verified_at) {
                    
                    $("#modal-emailverified").hide()
                    
                }
                if(response.data.mobile_verified_at) {
                    $("#modal-mobileverified").hide()
                }
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Success!");
                    $("#successModal .successModalDesc").html('Valid applicantion');
                });
                setTimeout(function(){
                    succModal.hide();
                }, 1000);        
                location.reload();
            }
            applicantApplicantionList.init();
        }).catch(error => {
            
            tthis.removeAttr('disabled');
            $("svg",tthis).css("display", "none");

            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#${formID} .${key}`).addClass('border-danger')
                        $(`#${formID}  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.resend-mobile').on('click', function(e) {

        e.preventDefault();

        let tthis = $(this);
        let parentForm = tthis.parents('form');
        let formID = parentForm.attr('id');
        const form = document.getElementById(formID);
        let id = $('input[name="id"]').val()
     
        let rurl = route("agent.apply.update",id) ;

        tthis.attr('disabled', 'disabled');
        $("svg",tthis).css("display", "inline-block");


        let form_data = new FormData(form);
        axios({
            method: "post",
            url: rurl,
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            tthis.removeAttr('disabled');
            $("svg",tthis).css("display", "none");

            if (response.status == 200) {

                tthis.removeAttr('disabled');
                $("svg",tthis).css("display", "none");

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("OTP SEND!");
                    $("#successModal .successModalDesc").html('new otp sent to your mobile.');
                });
                setTimeout(function() {
                    succModal.hide();
                }, 1200);        

                applicantionCustonList.init(response.data);
            }

        }).catch(error => {
            
            tthis.removeAttr('disabled');
            $("svg",tthis).css("display", "none");

            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#${formID} .${key}`).addClass('border-danger')
                        $(`#${formID}  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.resend-email').on('click', function(e) {

        e.preventDefault();

        let tthis = $(this);
        let parentForm = tthis.parents('form');
        let formID = parentForm.attr('id');
        const form = document.getElementById(formID);
        let id = $('input[name="id"]').val()
        
        let rurl = route("agent.apply.update",id) ;

        tthis.attr('disabled', 'disabled');
        $("svg",tthis).css("display", "inline-block");


        let form_data = new FormData(form);
  
        axios({
            method: "post",
            url: rurl,
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            tthis.removeAttr('disabled');
            $("svg",tthis).css("display", "none");

            if (response.status == 200) {

                tthis.removeAttr('disabled');
                $("svg",tthis).css("display", "none");
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Email Sent!");
                    $("#successModal .successModalDesc").html('Successful Email Verification Code Send');
                });
                
                setTimeout(function(){
                    succModal.hide();
                }, 1200); 

                applicantionCustonList.init(response.data);
                       
            }
        }).catch(error => {
            
            tthis.removeAttr('disabled');
            $("svg",tthis).css("display", "none");

            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#${formID} .${key}`).addClass('border-danger')
                        $(`#${formID}  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $(".newapplicant-modal").on('click',function(e){ 
        let tthis = $(this)
        var eVerified = tthis.data('email-verified');
        var mVerified = tthis.data('mobile-verified')

        $('#confirmModal #horizontal-email').html(tthis.data('email'));
        $('#confirmModal #horizontal-mobile').html(tthis.data('mobile'));

        $("input[name='id']").val(tthis.data('applicationid'))
        $("input.id").val(tthis.data('applicationid'))

        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            if(mVerified) {
                $("#confirmModal #modal-mobileverified").hide()
            } else {
                $("#confirmModal #modal-mobileverified").show()
            }
            if(eVerified) {
                $("#confirmModal #modal-emailverified").hide()
            } else {
                $("#confirmModal #modal-emailverified").show()
            }
            eVerified =undefined
            mVerified =undefined
        });
        
    })

    // Confirm Modal Action
    $('#confirmDeleteModal .agreeWith').on('click', function(){

        let tthis= $(this);
        let recordID = tthis.attr('data-id');
        let action = tthis.attr('data-action');
        $("svg",tthis).css("display", "inline-block");
        $('#confirmDeleteModal button').attr('disabled', 'disabled');

        axios({
            method: 'delete',
            url: route('agent.apply.destory', recordID),
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#confirmDeleteModal button').removeAttr('disabled');
                $("svg",tthis).css("display", "none");
                confirmDeleteModal.hide();

                succModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Done!');
                    $('#successModal .successModalDesc').html('Applicant successfully deleted!');
                });
            }
            
            setTimeout(function() {
                succModal.hide();
            }, 1200);    
            applicantionCustonList.init(response.data);

        }).catch(error =>{
            console.log(error)
        });
        
    })

    // Delete Course
    $('#applicant-list .delete_btn').on('click', function(){
        
        let tthis = $(this);
        let rowID = tthis.attr('data-id');
        let confModalDelTitle = "Delete Applicant?"
        
        confirmDeleteModal.show();
        document.getElementById('confirmDeleteModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmDeleteModal .confModTitle').html(confModalDelTitle);
            $('#confirmDeleteModal .confModDesc').html('Do you really want to delete these record? If yes, then please click on agree btn.');
            $('#confirmDeleteModal .agreeWith').attr('data-id', rowID);
            $('#confirmDeleteModal .agreeWith').attr('data-action', 'DELETE');
        });
    });
})();