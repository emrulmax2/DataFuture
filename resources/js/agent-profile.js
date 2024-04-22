import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var agentTableId = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-Agent").val() != "" ? $("#query-Agent").val() : "";
        let status = $("#status-Agent").val() != "" ? $("#status-Agent").val() : "";
        let agentId = $('#addressModal [name=id]').val()
        let tableContent = new Tabulator("#agentTableId", {
            ajaxURL: route("agent-user.termlist",agentId),
            ajaxParams: { querystr: querystr, status: status, id: agentId},
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
            columnDefaults:{
            resizable:true,
            },
            columns: [
                {
                    title: "Serial",
                    field: "sl",
                    width: "180",
                    headerSort: false,
                },
                ,
                {
                    title: "Term Name",
                    field: "term",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Total Applicants",
                    field: "ApplicantCount",
                    headerHozAlign: "left",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    
                },
                {
                    title: "Total Students",
                    field: "StudentCount",
                    headerHozAlign: "left",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    headerSort: false,
                    
                },
            ],
            rowFormatter:function(row){
                //create and style holder elements
               var holderEl = document.createElement("div");
               var tableEl = document.createElement("div");
        
               holderEl.style.boxSizing = "border-box";
               //holderEl.style.padding = "10px 30px 10px 10px";
               //holderEl.style.borderTop = "1px solid #333";
               //holderEl.style.borderBotom = "1px solid #333";
               
        
               //tableEl.style.border = "1px solid #333";
        
               holderEl.appendChild(tableEl);
        
               row.getElement().appendChild(holderEl);
        
               var subTable = new Tabulator(tableEl, {
                   layout:"fitColumns",
                   pagination: "local",
                   paginationSize: 10,
                   paginationSizeSelector: [true, 5, 10, 20, 30, 40],
                   data:row.getData()._children,
                   columns:[
                   {title:"Name", field:"name"},
                   {title:"Gender", field:"gender"},
                   {title:"Status", field:"status"},
                   {title:"Mobile", field:"mobile"},
                    {
                        title: "Actions",
                        field: "id",
                        headerSort: false,
                        hozAlign: "center",
                        headerHozAlign: "center",
                        width: "180",
                        download: false,
                        formatter(cell, formatterParams) {                        
                            var btns = "";
                            if (cell.getData().status == "Applicant") {
                                btns +='<a href="'+route('agent-user.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                            } else {
                                btns +='<a href="'+route('agent-user.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                            }
                            
                            return btns;
                        },
                    },
                   ]
               })
            },
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
                sheetName: "Agent List",
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
    
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
    if($('#agentTableId').length > 0){
        // Init Table
        agentTableId.init();
        //const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        //const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    }
    /*Address Modal*/
    if($('#addressModal').length > 0){
        const addressModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addressModal"));

        const addressModalEl = document.getElementById('addressModal')
        addressModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addressModal .acc__input-error').html('');
            $('#addressModal .modal-body input').val('');
            $('#addressModal input[name="prfix"]').val('');
            $('#addressModal input[name="place"]').val('');
        });

        $('.addressPopupToggler').on('click', function(e){
            e.preventDefault();

            var $btn = $(this);
            var $wrap = $btn.parents('.addressWrap');
            var $addressFieldPrefix = $btn.siblings('.address_prfix_field').val();

            var wrap_id = '#'+$wrap.attr('id');
            $('#addressModal input[name="place"]').val(wrap_id);
            $('#addressModal .modal-body input').val('');
            $('#addressModal input[name="prfix"]').val($addressFieldPrefix);
        });

        $('#addressForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addressForm');
            var $form = $(this);
            var wrapid = $('input[name="place"]', $form).val();
            var prfix = $('input[name="prfix"]', $form).val();

            document.querySelector('#insertAddress').setAttribute('disabled', 'disabled');
            document.querySelector('#insertAddress svg').style.cssText = 'display: inline-block;';

            var err = 0;
            $('input', $form).each(function(){
                var $input = $(this);
                var name = $input.attr('name');
                if(name != 'address_line_2' && $input.val() == ''){
                    err += 1;
                }
            })
            
            if(err > 0){
                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';

                $form.find('.mod-error').remove();
                $form.find('.modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Please fill out all required fields.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $form.find('.mod-error').remove();
                }, 2000);
            }else{
                var htmls = '';
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_1', $form).val()+'</span><br/>';
                htmls += '<input type="hidden" name="'+prfix+'address_line_1" value="'+$('#student_address_address_line_1', $form).val()+'"/>';
                if($('#student_address_address_line_2', $form).val() != ''){
                    htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_2', $form).val()+'</span><br/>';
                    htmls += '<input type="hidden" name="'+prfix+'address_line_2" value="'+$('#student_address_address_line_2', $form).val()+'"/>';
                }
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_city', $form).val()+'</span>, ';
                htmls += '<input type="hidden" name="'+prfix+'city" value="'+$('#student_address_city', $form).val()+'"/>';
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_postal_zip_code', $form).val()+'</span>,<br/>';
                htmls += '<input type="hidden" name="'+prfix+'post_code" value="'+$('#student_address_postal_zip_code', $form).val()+'"/>';
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_country', $form).val()+'</span><br/>';
                htmls += '<input type="hidden" name="'+prfix+'country" value="'+$('#student_address_country', $form).val()+'"/>';

                document.querySelector('#insertAddress').removeAttribute('disabled');
                document.querySelector('#insertAddress svg').style.cssText = 'display: none;';

                addressModal.hide();
                $(wrapid+' .addresses').fadeIn().addClass('active').html(htmls);
                $(wrapid +' button.addressPopupToggler span').html('Update Address');
            }
        });
    }

    // Update Course Data
    $("#editModalForm").on("submit", function (e) {

        e.preventDefault();
        let editId = $('#editModal input[name="id"]').val();

        const form = document.getElementById("editModalForm");

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
                editModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Data successfully updated.');
                });
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
                    editModal.hide();

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