import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
// var agentTableId = (function () {
//     var _tableGen = function () {
//         // Setup Tabulator
//         let querystr = $("#query-Agent").val() != "" ? $("#query-Agent").val() : "";
//         let status = $("#status-Agent").val() != "" ? $("#status-Agent").val() : "";
//         let agentId = $('#addressModal [name=id]').val()
//         let tableContent = new Tabulator("#agentTableId", {
//             ajaxURL: route("agent-user.termlist",agentId),
//             ajaxParams: { querystr: querystr, status: status, id: agentId},
//             ajaxFiltering: true,
//             ajaxSorting: true,
//             printAsHtml: true,
//             printStyled: true,
//             pagination: "remote",
//             paginationSize: 10,
//             paginationSizeSelector: [true, 5, 10, 20, 30, 40],
//             layout: "fitColumns",
//             responsiveLayout: "collapse",
//             placeholder: "No matching records found",
//             columnDefaults:{
//             resizable:true,
//             },
//             columns: [
//                 {
//                     title: "Serial",
//                     field: "sl",
//                     width: "180",
//                     headerSort: false,
//                 },
//                 ,
//                 {
//                     title: "Term Name",
//                     field: "term",
//                     headerHozAlign: "left",
//                     headerSort: false,
//                 },
//                 {
//                     title: "Total Applicants",
//                     field: "ApplicantCount",
//                     headerHozAlign: "left",
//                     headerSort: false,
//                     hozAlign: "center",
//                     headerHozAlign: "center",
                    
//                 },
//                 {
//                     title: "Total Students",
//                     field: "StudentCount",
//                     headerHozAlign: "left",
//                     headerSort: false,
//                     hozAlign: "center",
//                     headerHozAlign: "center",
//                     headerSort: false,
                    
//                 },
//             ],
//             rowFormatter:function(row){
//                 //create and style holder elements
//                var holderEl = document.createElement("div");
//                var tableEl = document.createElement("div");
        
//                holderEl.style.boxSizing = "border-box";
//                //holderEl.style.padding = "10px 30px 10px 10px";
//                //holderEl.style.borderTop = "1px solid #333";
//                //holderEl.style.borderBotom = "1px solid #333";
               
        
//                //tableEl.style.border = "1px solid #333";
        
//                holderEl.appendChild(tableEl);
        
//                row.getElement().appendChild(holderEl);
        
//                var subTable = new Tabulator(tableEl, {
//                    layout:"fitColumns",
//                    pagination: "local",
//                    paginationSize: 10,
//                    paginationSizeSelector: [true, 5, 10, 20, 30, 40],
//                    data:row.getData()._children,
//                    columns:[
//                    {title:"Name", field:"name"},
//                    {title:"Gender", field:"gender"},
//                    {title:"Status", field:"status"},
//                    {title:"Mobile", field:"mobile"},
//                     {
//                         title: "Actions",
//                         field: "id",
//                         headerSort: false,
//                         hozAlign: "center",
//                         headerHozAlign: "center",
//                         width: "180",
//                         download: false,
//                         formatter(cell, formatterParams) {                        
//                             var btns = "";
//                             if (cell.getData().status == "Applicant") {
//                                 btns +='<a href="'+route('agent-user.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
//                             } else {
//                                 btns +='<a href="'+route('agent-user.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
//                             }
                            
//                             return btns;
//                         },
//                     },
//                    ]
//                })
//             },
//             renderComplete() {
//                 createIcons({
//                     icons,
//                     "stroke-width": 1.5,
//                     nameAttr: "data-lucide",
//                 });
//             },
//         });

//         // Export
//         $("#tabulator-export-csv").on("click", function (event) {
//             tableContent.download("csv", "data.csv");
//         });

//         $("#tabulator-export-json").on("click", function (event) {
//             tableContent.download("json", "data.json");
//         });

//         $("#tabulator-export-xlsx").on("click", function (event) {
//             window.XLSX = xlsx;
//             tableContent.download("xlsx", "data.xlsx", {
//                 sheetName: "Agent List",
//             });
//         });

//         $("#tabulator-export-html").on("click", function (event) {
//             tableContent.download("html", "data.html", {
//                 style: true,
//             });
//         });

//         // Print
//         $("#tabulator-print").on("click", function (event) {
//             tableContent.print();
//         });
//     };
//     return {
//         init: function () {
//             _tableGen();
//         },
//     };
// })();
var applicantApplicantionList = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        let id = $('#addressForm input[name="id"]').val()
        let application_no = $("#application_no").val() != "" ? $("#application_no").val() : "";
        let applicantEmail = $("#applicantEmail").val() != "" ? $("#applicantEmail").val() : "";
        let applicantPhone = $("#applicantPhone").val() != "" ? $("#applicantPhone").val() : "";
        let querystr = $("#query-CNTR").val() != "" ? $("#query-CNTR").val() : "";

        let semesters = $("#semesters").val() != "" ? $("#semesters").val() : [];
        let courses = $("#courses").val() != "" ? $("#courses").val() : [];
        let statuses = $("#statuses").val() != "" ? $("#statuses").val() : [];
        let agents = $("#agents").val() != "" ? $("#agents").val() : [];

        let tableContent = new Tabulator("#applicantApplicantionList", {

            ajaxURL: route("agent-user.query.list",id),

            ajaxParams: {  refno: application_no, email:applicantEmail, phone:applicantPhone, semesters: semesters, statuses:statuses, courses:courses, agents:agents, querystr:querystr },

            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 10,20, 30, 40,50],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "application_no",
                    width: "180",
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
                    headerSort:false,
                    headerHozAlign: "left",
                    width: "100"
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
                    title: "RF code",
                    field: "referral_code",
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

                            btns += '';
                        
                        }else{

                            btns += '<a href="'+route('admission.show', cell.getData().id)+'" class="btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="eye-off" class="lucide lucide-eye-off w-4 h-4"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" x2="22" y1="2" y2="22"></line></svg></a>';
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
function checkPasswordStrength(password) {
    // Initialize variables
    let strength = 0;
    let tips = "";
    //let lowUpperCase = document.querySelector(".low-upper-case i");

    //let number = document.querySelector(".one-number i");
    //let specialChar = document.querySelector(".one-special-char i");
    //let eightChar = document.querySelector(".eight-character i");

    //If password contains both lower and uppercase characters
    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
        strength += 1;
        //lowUpperCase.classList.remove('fa-circle');
        //lowUpperCase.classList.add('fa-check');
    } else {
        //lowUpperCase.classList.add('fa-circle');
        //lowUpperCase.classList.remove('fa-check');
    }
    //If it has numbers and characters
    if (password.match(/([0-9])/)) {
        strength += 1;
        //number.classList.remove('fa-circle');
        //number.classList.add('fa-check');
    } else {
        //number.classList.add('fa-circle');
        //number.classList.remove('fa-check');
    }
    //If it has one special character
    if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
        strength += 1;
        //specialChar.classList.remove('fa-circle');
        //specialChar.classList.add('fa-check');
    } else {
        //specialChar.classList.add('fa-circle');
        //specialChar.classList.remove('fa-check');
    }
    //If password is greater than 7
    if (password.length > 7) {
        strength += 1;
        //eightChar.classList.remove('fa-circle');
        //eightChar.classList.add('fa-check');
    } else {
        //eightChar.classList.add('fa-circle');
        //eightChar.classList.remove('fa-check');   
    }
   
    // Return results
    if (strength < 2) {
        return strength;
    } else if (strength === 2) {
        return strength;
    } else if (strength === 3) {
        return strength;
    } else {
        return strength;
    }
}
(function () {

    if($('#applicantApplicantionList').length > 0){
        applicantApplicantionList.init();
        let tomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Search Here...',
            persist: false,
            create: false,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };

        let tomOptionsMul = {
            ...tomOptions,
            plugins: {
                ...tomOptions.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };

        
        var semesters = new TomSelect('#semesters', tomOptionsMul);
        var courses = new TomSelect('#courses', tomOptionsMul);

        var statuses = new TomSelect('#statuses', tomOptionsMul);
        var agents = new TomSelect('#agents', tomOptionsMul);

            // Filter function
            function filterHTMLForm() {
                applicantApplicantionList.init();
            }
            // On click go button
            $("#studentGroupSearchSubmitBtn").on("click", function (event) {
                filterHTMLForm();
            });
            // On reset filter form
            
        
    }
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const editContactModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editContactModal"));
    // if($('#agentTableId').length > 0){
    //     // Init Table
    //     agentTableId.init();
    //     //const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    //     //const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    // }
    /*Address Modal*/
    if($('#addressModal').length > 0){
        const addressModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addressModal"));

        const addressModalEl = document.getElementById('addressModal')
        addressModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addressModal .acc__input-error').html('');
            $('#addressModal .modal-body input').val('');
            $('#addressModal input[name="address_id"]').val('0');
        });

        $('.addressPopupToggler').on('click', function(e){
            e.preventDefault();

            var $btn = $(this);
            var $wrap = $btn.parents('.addressWrap');
            var $addressIdField = $btn.siblings('.address_id_field');

            var wrap_id = '#'+$wrap.attr('id');
            var address_id = $addressIdField.val();
            if(address_id > 0){
                axios({
                    method: "post",
                    url: route('address.get'),
                    data: {address_id : address_id},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var dataset = response.data.res;
                        
                        $('#addressModal #student_address_address_line_1').val(dataset.address_line_1 ? dataset.address_line_1 : '');
                        $('#addressModal #student_address_address_line_2').val(dataset.address_line_2 ? dataset.address_line_2 : '');
                        $('#addressModal #student_address_city').val(dataset.city ? dataset.city : '');
                        $('#addressModal #student_address_state_province_region').val(dataset.state ? dataset.state : '');
                        $('#addressModal #student_address_postal_zip_code').val(dataset.post_code ? dataset.post_code : '');
                        $('#addressModal #student_address_country').val(dataset.country ? dataset.country : '');

                        $('#addressModal input[name="place"]').val(wrap_id);
                        $('#addressModal input[name="address_id"]').val(address_id);
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('#addressModal input[name="place"]').val(wrap_id);
                $('#addressModal .modal-body input').val('');
                $('#addressModal input[name="address_id"]').val('0');
            }
        });

        $('#addressForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addressForm');
            var $form = $(this);
            //var wrapid = $('input[name="place"]', $form).val();
            var address_id = $('input[name="address_id"]', $form).val();

            
            //var htmls = '';
            //var post_code = $('#student_address_postal_zip_code', $form).val();
            // htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_1', $form).val()+'</span><br/>';
            // if($('#student_address_address_line_2', $form).val() != ''){
            //     htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_2', $form).val()+'</span><br/>';
            // }
            // htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_city', $form).val()+'</span>, ';
            // if($('#student_address_state_province_region', $form).val() != ''){
            //     htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_state_province_region', $form).val()+'</span>, <br/>';
            // }else{
            //     htmls += '<br/>';
            // }
            // htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_postal_zip_code', $form).val()+'</span>,<br/>';
            // htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_country', $form).val()+'</span><br/>';

            document.querySelector('#insertAddress').setAttribute('disabled', 'disabled');
            document.querySelector('#insertAddress svg').style.cssText = 'display: inline-block;';

            let agentId = $('#addressForm input[name="id"]').val();
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('agent-user.address.store',agentId),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {

                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    //var dataset = response.data.res;
                    //var newAddressId = (dataset.id ? dataset.id : 0);
                    addressModal.hide();
                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Data successfully updated.');
                    });
                    location.reload();
                }
                
            }).catch(error => {

                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';
                if(error.response){
                    if(error.response.status == 422){
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addressForm .${key}`).addClass('border-danger')
                            $(`#addressForm  .error-${key}`).html(val)
                        }
                    }else{
                        console.log('error');
                    }
                }
            });
        });
    };
    /*Resend Verification Modal*/
    if($('#resendverification-staff').length > 0) {

        $("#resendverification-staff").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#resendverification-staff input[name="id"]').val();

            const form = document.getElementById("resendverification-staff");

            document.querySelector('#resend-mail-agent').setAttribute('disabled', 'disabled');
            document.querySelector('#resend-mail-agent .theSend').style.cssText = 'display: none;';
            document.querySelector('#resend-mail-agent .theLoading').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url:  route('agent.verification.send.from.staff', editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#resend-mail-agent").removeAttribute("disabled");
                    document.querySelector("#resend-mail-agent svg.theLoading").style.cssText = "display: none;";
                    document.querySelector("#resend-mail-agent svg.theSend").style.cssText = "display: inline-block;";
                    succModal.show();
                    
                    $("#successModal .successModalTitle").html("Email Sent!");
                    $("#successModal .successModalDesc").html('Verification email successfully sent.');
                    
                    location.reload();
                }
            }).catch((error) => {
                document.querySelector("#resend-mail-agent").removeAttribute("disabled");
                document.querySelector("#resend-mail-agent svg.theLoading").style.cssText = "display: none;";
                document.querySelector("#resend-mail-agent svg.theSend").style.cssText = "display: inline-block;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editForm .${key}`).addClass('border-danger')
                            $(`#editForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            
                            $("#successModal .successModalTitle").html("Oops!");
                            $("#successModal .successModalDesc").html(message);
                        });
                    } else {
                        console.log("error");
                    }
                }
            });
        });
    }
    // Update Contact Data
    $("#editContactModal").on("submit", function (e) {

        e.preventDefault();
        let editId = $('#editContactModal input[name="id"]').val();

        const form = document.getElementById("editContactModalForm");

        document.querySelector('#update').setAttribute('disabled', 'disabled');
        document.querySelector('#update svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("agent-user.update", editId),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#update").removeAttribute("disabled");
                document.querySelector("#update svg").style.cssText = "display: none;";
                editContactModal.hide();
                $("#successModal .successModalTitle").html("Congratulations!");
                $("#successModal .successModalDesc").html('Data successfully updated.');
                succModal.show();
   
                location.reload();
            }
        }).catch((error) => {
            document.querySelector("#update").removeAttribute("disabled");
            document.querySelector("#update svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editForm .${key}`).addClass('border-danger')
                        $(`#editForm  .error-${key}`).html(val)
                    }
                }else if (error.response.status == 304) {
                    editContactModal.hide();

                    let message = error.response.statusText;
                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Oops!");
                        $("#successModal .successModalDesc").html(message);
                    });
                } else {
                    console.log("error");
                }
            }
        });
    });
})()