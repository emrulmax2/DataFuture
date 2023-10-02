import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var studentEmploymentHistoryTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let student_id = $("#studentEmploymentHistoryTable").attr('data-student') != "" ? $("#studentEmploymentHistoryTable").attr('data-student') : "0";
        let querystr = $("#query-SEH").val() != "" ? $("#query-SEH").val() : "";
        let status = $("#status-SEH").val() != "" ? $("#status-SEH").val() : "";

        let tableContent = new Tabulator("#studentEmploymentHistoryTable", {
            ajaxURL: route("student.employment.list"),
            ajaxParams: { student_id: student_id, querystr: querystr, status: status},
            ajaxFiltering: true,
            ajaxSorting: true,
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
                    title: "#ID",
                    field: "id",
                    width: "80",
                },
                {
                    title: "Company",
                    field: "company_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "company_phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Position",
                    field: "position",
                    headerHozAlign: "left",
                },
                {
                    title: "Start",
                    field: "start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "End",
                    field: "end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Address",
                    field: "address",
                    headerHozAlign: "left",
                    width: "180",
                    formatter(cell, formatterParams) {   
                        return '<div class="whitespace-nowrap">'+cell.getData().address+'</div>';
                    }
                },
                {
                    title: "Contact Person",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Position",
                    field: "contact_position",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "contact_phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editEmployementHistoryModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-SEH").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-SEH").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-SEH").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Venues Details",
            });
        });

        $("#tabulator-export-html-SEH").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-SEH").on("click", function (event) {
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
    if($('#studentEmploymentHistoryTable').length > 0){
        if($('#studentEmploymentHistoryTable').hasClass('activeTable')){
            studentEmploymentHistoryTable.init();
        }

        // Filter function
        function filterHTMLFormSEH() {
            studentEmploymentHistoryTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-SEH").on("click", function (event) {
            filterHTMLFormSEH();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-SEH").on("click", function (event) {
            $("#query-SEH").val("");
            $("#status-SEH").val("1");
            filterHTMLFormSEH();
        });
    }


    const editStudentEmpStatusModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentEmpStatusModal"));
    const addEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmployementHistoryModal"));
    const editEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmployementHistoryModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });
    
    let tomOptionsNew = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    
    var employment_status = new TomSelect('#student_employment_status', tomOptionsNew);

    /* Update Employment Status */
    $('#editStudentEmpStatusForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editStudentEmpStatusForm');
    
        document.querySelector('#updateSES').setAttribute('disabled', 'disabled');
        document.querySelector("#updateSES svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.employment.status.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#updateSES').removeAttribute('disabled');
                document.querySelector("#updateSES svg").style.cssText = "display: none;";
                
                editStudentEmpStatusModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student employment history status successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 5000);
            }
        }).catch(error => {
            document.querySelector('#updateSES').removeAttribute('disabled');
            document.querySelector("#updateSES svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editStudentEmpStatusForm .${key}`).addClass('border-danger');
                        $(`#editStudentEmpStatusForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    /* Update Employment Status */


    $('#addEmployementHistoryForm input[name="continuing"]').on('change', function(){
        if($(this).prop('checked')){
            $('#addEmployementHistoryForm input[name="end_date"]').val('').attr('disabled', 'disabled');
        }else{
            $('#addEmployementHistoryForm input[name="end_date"]').val('').removeAttr('disabled');
        }
    })

    $('#editEmployementHistoryModal input[name="continuing"]').on('change', function(){
        if($(this).prop('checked')){
            $('#editEmployementHistoryModal input[name="end_date"]').val('').attr('disabled', 'disabled');
        }else{
            $('#editEmployementHistoryModal input[name="end_date"]').val('').removeAttr('disabled');
        }
    })


    $('#addEmployementHistoryForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addEmployementHistoryForm');
    
        document.querySelector('#saveEmpHistory').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEmpHistory svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        let studentId = $('[name="student_id"]', $form).val();
        axios({
            method: "post",
            url: route('student.employment.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => { 
            if (response.status == 200) {
                document.querySelector('#saveEmpHistory').removeAttribute('disabled');
                document.querySelector("#saveEmpHistory svg").style.cssText = "display: none;";

                addEmployementHistoryModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Employment History date successfully added.');
                });                
                    
            }
            studentEmploymentHistoryTable.init();
        }).catch(error => {
            document.querySelector('#saveEmpHistory').removeAttribute('disabled');
            document.querySelector("#saveEmpHistory svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addEmployementHistoryForm .${key}`).addClass('border-danger');
                        $(`#addEmployementHistoryForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#studentEmploymentHistoryTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("student.employment.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;

                $('#editEmployementHistoryModal input[name="company_name"]').val(dataset.company_name ? dataset.company_name : '');
                $('#editEmployementHistoryModal input[name="company_phone"]').val(dataset.company_phone ? dataset.company_phone : '');
                $('#editEmployementHistoryModal input[name="position"]').val(dataset.position ? dataset.position : '');
                $('#editEmployementHistoryModal input[name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                if(dataset.continuing && dataset.continuing == 1){
                    $('#editEmployementHistoryModal input[name="continuing"]').prop('checked', true);
                    $('#editEmployementHistoryModal input[name="end_date"]').val('').attr('disabled', 'disabled');
                }else{
                    $('#editEmployementHistoryModal input[name="continuing"]').prop('checked', false);
                    $('#editEmployementHistoryModal input[name="end_date"]').val(dataset.end_date ? dataset.end_date : '').removeAttr('disabled');
                }
                
                var address_id = (typeof dataset.address_id !== 'undefined' && dataset.address_id > 0 ? dataset.address_id : 0);
                if(address_id > 0){
                    var address = (typeof dataset.address !== 'undefined' && dataset.address ? dataset.address : []);
                    var htmls = '';
                    htmls += (typeof address.address_line_1 !== 'undefined' && address.address_line_1 ? '<span class="text-slate-600 font-medium">'+address.address_line_1+'</span><br/>' : '');
                    htmls += (typeof address.address_line_2 !== 'undefined' && address.address_line_2 != '' ? '<span class="text-slate-600 font-medium">'+address.address_line_2+'</span><br/>' : '');
                    htmls += (typeof address.city !== 'undefined' && address.city ? '<span class="text-slate-600 font-medium">'+address.city+'</span>, ' : '');
                    htmls += (typeof address.state !== 'undefined' && address.state != '' ? '<span class="text-slate-600 font-medium">'+address.state+'</span>, <br/>' : '<br/>');
                    htmls += (typeof address.post_code !== 'undefined' && dataset.post_code ? '<span class="text-slate-600 font-medium">'+dataset.post_code+'</span>,<br/>' : '');
                    htmls += (typeof address.country !== 'undefined' && dataset.country ? '<span class="text-slate-600 font-medium">'+dataset.country+'</span><br/>' : '');

                    
                    $('#editEmpHistoryAddress .addresses').html(htmls);
                    $('#editEmpHistoryAddress .addressPopupToggler span').html('Update Address');
                    $('#editEmpHistoryAddress .address_id_field').val(address_id);
                }else{
                    $('#editEmpHistoryAddress .addresses').html('<span class="text-warning font-medium">Not set yet!</span>');
                    $('#editEmpHistoryAddress .addressPopupToggler span').html('Add Address');
                    $('#editEmpHistoryAddress .address_id_field').val(address_id);
                }

                $('#editEmployementHistoryModal input[name="contact_name"]').val(dataset.reference[0].name ? dataset.reference[0].name : '');
                $('#editEmployementHistoryModal input[name="contact_position"]').val(dataset.reference[0].position ? dataset.reference[0].position : '');
                $('#editEmployementHistoryModal input[name="contact_phone"]').val(dataset.reference[0].phone ? dataset.reference[0].phone : '');
                $('#editEmployementHistoryModal input[name="contact_email"]').val(dataset.reference[0].email ? dataset.reference[0].email : '');
                $('#editEmployementHistoryModal input[name="ref_id"]').val(dataset.reference[0].id ? dataset.reference[0].id : '');
                
                $('#editEmployementHistoryModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editEmployementHistoryForm").on("submit", function (e) {
        e.preventDefault();
        let editId = $('#editEmployementHistoryForm input[name="id"]').val();
        const form = document.getElementById("editEmployementHistoryForm");

        document.querySelector('#updateEmpHistory').setAttribute('disabled', 'disabled');
        document.querySelector('#updateEmpHistory svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("student.employment.update"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#updateEmpHistory").removeAttribute("disabled");
                document.querySelector("#updateEmpHistory svg").style.cssText = "display: none;";
                editEmployementHistoryModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Employment History data successfully updated.');
                });
            }
            studentEmploymentHistoryTable.init();
        }).catch((error) => {
            document.querySelector("#updateEmpHistory").removeAttribute("disabled");
            document.querySelector("#updateEmpHistory svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editEmployementHistoryForm .${key}`).addClass('border-danger')
                        $(`#editEmployementHistoryForm  .error-${key}`).html(val)
                    }
                }else {
                    console.log("error");
                }
            }
        });
    });

    // Delete Course
    $('#studentEmploymentHistoryTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETESEH');
        });
    });

    // Restore Course
    $('#studentEmploymentHistoryTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORESEH');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETESEH'){
            axios({
                method: 'delete',
                url: route('student.employment.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student employment history successfull deleted.');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 3000)
                }
                studentEmploymentHistoryTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORESEH'){
            axios({
                method: 'post',
                url: route('student.employment.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student employment history successfull restored.');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 3000)
                }
                studentEmploymentHistoryTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })
})();