import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var agentTableId = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-Agent").val() != "" ? $("#query-Agent").val() : "";
        let status = $("#status-Agent").val() != "" ? $("#status-Agent").val() : "";

        let tableContent = new Tabulator("#agentTableId", {
            ajaxURL: route("agent-user.list"),
            ajaxParams: { querystr: querystr, status: status},
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
                    field: "id",
                    width: "180",
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Organization",
                    field: "organization",
                    headerHozAlign: "left",
                },
                {
                    title: "Code",
                    field: "code",
                    headerHozAlign: "left",
                },
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
                        if (cell.getData().deleted_at == null) {
                           // btns +='<a href="'+route('agent-user.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editAgentModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
    if($('#agentTableId').length > 0){
        // Init Table
        agentTableId.init();

        // Filter function
        function filterHTMLForm() {
            agentTableId.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-Agent")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-Agent").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-Agent").on("click", function (event) {
            $("#query-Agent").val("");
            $("#status-Agent").val("1");
            filterHTMLForm();
        });


        const addAgentModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAgentModal"));
        const editAgentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAgentModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescription = 'Do you really want to re-store these records? Click agree to continue.';

        const addAgentModalEl = document.getElementById('addAgentModal')
        addAgentModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addAgentModal .acc__input-error').html('');
            $('#addAgentModal input').val('');
            $('#addAgentModal select').val('');
        });
        
        const editAgentModalEl = document.getElementById('editAgentModal')
        editAgentModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editAgentModal .acc__input-error').html('');
            $('#editAgentModal input').val('');
            $('#editAgentModal select').val('');
            $('#editAgentModal input[name="id"]').val('0');
        });


        $('#agentTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        $('#agentTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record?');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
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
                    url: route('agent-user.destroy', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Agent data successfully deleted.');
                        });
                    }
                    agentTableId.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('agent-user.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Agent data Successfully Restored!');
                        });
                    }
                    agentTableId.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })


        $("#agentTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("agent-user.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editAgentModal input[name="first_name"]').val(dataset.first_name ? dataset.first_name : '');
                    $('#editAgentModal input[name="last_name"]').val(dataset.last_name ? dataset.last_name : '');
                    $('#editAgentModal input[name="organization"]').val(dataset.organization ? dataset.organization : '');
                    $('#editAgentModal input[name="code"]').val(dataset.code ? dataset.code : '');
                    $('#editAgentModal input[name="email"]').val(dataset.agent_user.email ? dataset.agent_user.email : '');
                    $('#editAgentModal input[name="id"]').val(editId);
                    if(dataset.agent_user.email_verified_at==null) {
                        let verificationList = $("#verificationEmail");
                            verificationList.removeClass('text-success');
                            verificationList.addClass('text-danger');
                            verificationList.html('<i data-lucide="x-circle" class="w-4 h-4  ml-2 mr-1  "></i>Unverifed');
                    } else {
                        let verificationList = $("#verificationEmail");
                            verificationList.removeClass('text-danger');
                            verificationList.addClass('text-success');
                            verificationList.html('<i data-lucide="check-circle" class="w-4 h-4  ml-2 mr-1  "></i>Verified');
                    }
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#editAgentForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('editAgentForm');
        
            document.querySelector('#updateAgent').setAttribute('disabled', 'disabled');
            document.querySelector("#updateAgent svg").style.cssText ="display: inline-block;";
            let agent = $("#editAgentForm input[name=id]").val();
            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('agent-user.update',agent),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateAgent').removeAttribute('disabled');
                document.querySelector("#updateAgent svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    editAgentModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Agent data successfully updated.');
                    });                
                        
                }
                agentTableId.init();
            }).catch(error => {
                document.querySelector('#updateAgent').removeAttribute('disabled');
                document.querySelector("#updateAgent svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAgentForm .${key}`).addClass('border-danger')
                            $(`#editAgentForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#addAgentForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addAgentForm');
        
            document.querySelector('#saveAgent').setAttribute('disabled', 'disabled');
            document.querySelector("#saveAgent svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('agent-user.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveAgent').removeAttribute('disabled');
                document.querySelector("#saveAgent svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addAgentModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Course creation data successfully inserted.');
                    });                
                        
                }
                agentTableId.init();
            }).catch(error => {
                document.querySelector('#saveAgent').removeAttribute('disabled');
                document.querySelector("#saveAgent svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addAgentForm .${key}`).addClass('border-danger')
                            $(`#addAgentForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#password').on('keyup', function(e) {
            let totalText = this.value
            let strenghtTips = checkPasswordStrength(totalText)
            const box1 = document.getElementById('strength-1');
            const box2 = document.getElementById('strength-2');
            const box3 = document.getElementById('strength-3');
            const box4 = document.getElementById('strength-4');

            switch (strenghtTips) {
                case 1:
                        box1.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                        box1.classList.add('bg-danger');
                        break;
                case 2: 
                        box2.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                        box2.classList.add('bg-warning');
                        break;
                case 3: 
                        box3.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                        box3.classList.add('bg-success');
                        break;
                case 4: 
                case 5: 
                case 6: 
                case 7: 
                case 8: 
                case 9: 
                        box4.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                        box4.classList.add('bg-success');
                        break;
                default:
                        box1.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                        box2.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                        box3.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                        box4.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                        
                        box1.classList.add('bg-slate-100','dark:bg-darkmode-800');
                        box2.classList.add('bg-slate-100','dark:bg-darkmode-800');
                        box3.classList.add('bg-slate-100','dark:bg-darkmode-800');
                        box4.classList.add('bg-slate-100','dark:bg-darkmode-800');
                        break;
            }
        })
    }
})()