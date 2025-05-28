import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var employeePenssionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-PNS").val() != "" ? $("#query-PNS").val() : "";
        let status = $("#status-PNS").val() != "" ? $("#status-PNS").val() : "";
        let employee_id = $("#employeePenssionListTable").attr('data-employee');

        let tableContent = new Tabulator("#employeePenssionListTable", {
            ajaxURL: route("employee.penssion.list"),
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
                    title: "Scheme",
                    field: "penssion",
                    headerHozAlign: "left",
                },
                {
                    title: "Date Joined",
                    field: "joining_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Date Left",
                    field: "date_left",
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
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editEmpPenssionModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
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
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
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
        $("#tabulator-export-csv-PNS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-PNS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-PNS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Groups Details",
            });
        });

        $("#tabulator-export-html-PNS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-PNS").on("click", function (event) {
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
    if ($("#employeePenssionListTable").length) {
        // Init Table
        employeePenssionListTable.init();

        // Filter function
        function filterHTMLFormPNS() {
            employeePenssionListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PNS")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormPNS();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-PNS").on("click", function (event) {
            filterHTMLFormPNS();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PNS").on("click", function (event) {
            $("#query-PNS").val("");
            $("#status-PNS").val("1");
            filterHTMLFormPNS();
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
    const addEmpPenssionModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmpPenssionModal"));
    const editEmpPenssionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmpPenssionModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    let confModalDelTitle = 'Are you sure?';

    const addEmpPenssionModalEl = document.getElementById('addEmpPenssionModal')
    addEmpPenssionModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addModal .acc__input-error').html('');
        $('#addModal .modal-body input').val('');
        $('#addModal .modal-body select').val('');
    });

    const editEmpPenssionModalEl = document.getElementById('editEmpPenssionModal')
    editEmpPenssionModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addModal .acc__input-error').html('');
        $('#addModal .modal-body input').val('');
        $('#addModal .modal-body select').val('');
        $('#addModal .modal-footer input[name="id"]').val('0');
    });

    $('#addEmpPenssionForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addEmpPenssionForm');
    
        document.querySelector('#saveEPS').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEPS svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.penssion.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveEPS').removeAttribute('disabled');
            document.querySelector("#saveEPS svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                document.querySelector('#saveEPS').removeAttribute('disabled');
                document.querySelector("#saveEPS svg").style.cssText = "display: none;";
                
                addEmpPenssionModal.hide();
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Success!" );
                    $("#successModal .successModalDesc").html('Employee penssion scheme details successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });                
                    
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            employeePenssionListTable.init();
        }).catch(error => {
            document.querySelector('#saveEPS').removeAttribute('disabled');
            document.querySelector("#saveEPS svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addEmpPenssionForm .${key}`).addClass('border-danger')
                        $(`#addEmpPenssionForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#employeePenssionListTable').on('click', '.edit_btn', function(){
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("employee.penssion.edit"),
            data: {editId: editId},
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;
                $('#editEmpPenssionModal [name="employee_info_penssion_scheme_id"]').val(dataset.employee_info_penssion_scheme_id ? dataset.employee_info_penssion_scheme_id : '');
                $('#editEmpPenssionModal [name="joining_date"]').val(dataset.joining_date ? dataset.joining_date : '');
                $('#editEmpPenssionModal [name="date_left"]').val(dataset.date_left ? dataset.date_left : '');

                $('#editEmpPenssionModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editEmpPenssionForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editEmpPenssionForm');
    
        document.querySelector('#updateEPS').setAttribute('disabled', 'disabled');
        document.querySelector("#updateEPS svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.penssion.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateEPS').removeAttribute('disabled');
            document.querySelector("#updateEPS svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                document.querySelector('#updateEPS').removeAttribute('disabled');
                document.querySelector("#updateEPS svg").style.cssText = "display: none;";
                
                editEmpPenssionModal.hide();
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Success!" );
                    $("#successModal .successModalDesc").html('Employee penssion scheme details successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });                
                    
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            employeePenssionListTable.init();
        }).catch(error => {
            document.querySelector('#updateEPS').removeAttribute('disabled');
            document.querySelector("#updateEPS svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editEmpPenssionForm .${key}`).addClass('border-danger')
                        $(`#editEmpPenssionForm  .error-${key}`).html(val)
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
        if(action == 'DELETEEPS'){
            axios({
                method: 'delete',
                url: route('employee.penssion.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Employee Penssion Scheme details successfully deleted!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                employeePenssionListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREEPS'){
            axios({
                method: 'post',
                url: route('employee.penssion.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Success!');
                        $('#successModal .successModalDesc').html('Employee Penssion Scheme details Successfully Restored!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                employeePenssionListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } 
    })

    // Delete Course
    $('#employeePenssionListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record?  If yes, the please click on agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEEPS');
        });
    });

    // Restore Course
    $('#employeePenssionListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore this record?  If yes, the please click on agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREEPS');
        });
    });

})();