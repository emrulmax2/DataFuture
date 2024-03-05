import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var employeeBankListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-BNK").val() != "" ? $("#query-BNK").val() : "";
        let status = $("#status-BNK").val() != "" ? $("#status-BNK").val() : "";
        let employee_id = $("#employeeBankListTable").attr('data-employee');

        let tableContent = new Tabulator("#employeeBankListTable", {
            ajaxURL: route("employee.bank.list"),
            ajaxParams: { employee_id: employee_id, querystr: querystr, status: status },
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
                    title: "Beneficiary Name",
                    field: "beneficiary",
                    headerHozAlign: "left",
                },
                {
                    title: "Sort Code",
                    field: "sort_code",
                    headerHozAlign: "left",
                },
                {
                    title: "A/C No",
                    field: "ac_no",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().active == 1 ? 'Checked' : '')+' value="'+cell.getData().active+'" type="checkbox" class="status_updater form-check-input"> </div>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editBankModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button>';
                            btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                            if(cell.getData().active == 1){
                                btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#addBankModal" type="button" class="btn-rounded btn btn-facebook text-white p-0 w-9 h-9 ml-1"><i data-lucide="refresh-ccw" class="w-4 h-4"></i></button>';
                            }
                        }  else if (cell.getData().deleted_at != null) {
                            btns +='<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-BNK").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-BNK").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-BNK").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Groups Details",
            });
        });

        $("#tabulator-export-html-BNK").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-BNK").on("click", function (event) {
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
    if ($("#employeeBankListTable").length) {
        // Init Table
        employeeBankListTable.init();

        // Filter function
        function filterHTMLFormBNK() {
            employeeBankListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-BNK")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormBNK();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-BNK").on("click", function (event) {
            filterHTMLFormBNK();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-BNK").on("click", function (event) {
            $("#query-BNK").val("");
            $("#status-BNK").val("3");
            filterHTMLFormBNK();
        });
    }

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const addBankModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBankModal"));
    const editBankModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editBankModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    let confModalDelTitle = 'Are you sure?';

    const addBankModalEl = document.getElementById('addBankModal')
    addBankModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addBankModal .acc__input-error').html('');
        $('#addBankModal .modal-body input').val('');
        $('#addBankModal .modal-body select').val('');
        $('#addBankModal input[name="active"]').prop('checked', true);
    });

    const editBankModalEl = document.getElementById('editBankModal')
    editBankModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editBankModal .acc__input-error').html('');
        $('#editBankModal .modal-body input').val('');
        $('#editBankModal .modal-body select').val('');
        $('#editBankModal input[name="active"]').prop('checked', false);
        $('#editBankModal input[name="id"]').val('0');
    });

    $('#addBankForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addBankForm');
    
        document.querySelector('#saveEBNK').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEBNK svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.bank.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveEBNK').removeAttribute('disabled');
            document.querySelector("#saveEBNK svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                document.querySelector('#saveEBNK').removeAttribute('disabled');
                document.querySelector("#saveEBNK svg").style.cssText = "display: none;";
                
                addBankModal.hide();
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Success!" );
                    $("#successModal .successModalDesc").html('Employee bank details successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });                
                    
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            employeeBankListTable.init();
        }).catch(error => {
            document.querySelector('#saveEBNK').removeAttribute('disabled');
            document.querySelector("#saveEBNK svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addBankForm .${key}`).addClass('border-danger')
                        $(`#addBankForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#employeeBankListTable').on('click', '.edit_btn', function(){
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("employee.bank.edit"),
            data: {editId: editId},
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;
                $('#editBankModal [name="beneficiary"]').val(dataset.beneficiary ? dataset.beneficiary : '');
                $('#editBankModal [name="sort_code"]').val(dataset.sort_code ? dataset.sort_code : '');
                $('#editBankModal [name="ac_no"]').val(dataset.ac_no ? dataset.ac_no : '');

                if(dataset.active == 1){
                    $('#editBankModal input[name="active"]').prop('checked', true);
                }else{
                    $('#editBankModal input[name="active"]').prop('checked', false);
                }

                $('#editBankModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editBankForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editBankForm');
    
        document.querySelector('#updateEBNK').setAttribute('disabled', 'disabled');
        document.querySelector("#updateEBNK svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.bank.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateEBNK').removeAttribute('disabled');
            document.querySelector("#updateEBNK svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                document.querySelector('#updateEBNK').removeAttribute('disabled');
                document.querySelector("#updateEBNK svg").style.cssText = "display: none;";
                
                editBankModal.hide();
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Success!" );
                    $("#successModal .successModalDesc").html('Employee bank details successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });                
                    
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            employeeBankListTable.init();
        }).catch(error => {
            document.querySelector('#updateEBNK').removeAttribute('disabled');
            document.querySelector("#updateEBNK svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editBankForm .${key}`).addClass('border-danger')
                        $(`#editBankForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();

        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETEBNK'){
            axios({
                method: 'delete',
                url: route('employee.bank.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Employee Bank details successfully deleted!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                employeeBankListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREBNK'){
            axios({
                method: 'post',
                url: route('employee.bank.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Success!');
                        $('#successModal .successModalDesc').html('Employee Bank details Successfully Restored!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                employeeBankListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTATBNK'){
            axios({
                method: 'post',
                url: route('employee.bank.changestatus', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Success!');
                        $('#successModal .successModalDesc').html('Employee Bank details status successfully updated!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                employeeBankListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    //Change Status
    $('#employeeBankListTable').on('click', '.status_updater', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATBNK');
        });
    });

    // Delete Course
    $('#employeeBankListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record?  If yes, the please click on agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEBNK');
        });
    });

    // Restore Course
    $('#employeeBankListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore this record?');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREBNK');
        });
    });

})();