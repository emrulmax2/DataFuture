import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

var hrMachinesListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-HRM").val() != "" ? $("#query-HRM").val() : "";
        let status = $("#status-HRM").val() != "" ? $("#status-HRM").val() : "";

        let tableContent = new Tabulator("#hrMachinesListTable", {
            ajaxURL: route("hr.machine.list"),
            ajaxParams: { querystr: querystr, status: status },
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
                    title: "User Name",
                    field: "username",
                    headerHozAlign: "left",
                },
                {
                    title: "Location",
                    field: "location",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "210",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editMachineModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-HRM").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-HRM").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-HRM").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Roles Details",
            });
        });

        $("#tabulator-export-html-HRM").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-HRM").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    if ($("#hrMachinesListTable").length) {
        // Init Table
        hrMachinesListTable.init();

        // Filter function
        function filterHTMLForm() {
            hrMachinesListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-HRM")[0].addEventListener(
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
        $("#tabulator-html-filter-go-HRM").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-HRM").on("click", function (event) {
            $("#query-HRM").val("");
            $("#status-HRM").val("1");
            filterHTMLForm();
        });
    }

    const addMachineModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addMachineModal"));
    const editMachineModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editMachineModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    let confModalDelTitle = 'Are you sure?';

    const addMachineModalEl = document.getElementById('addMachineModal')
    addMachineModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addMachineModal .acc__input-error').html('');
        $('#addMachineModal .modal-body input').val('');
        $('#addMachineModal .modal-body textarea').val('');
    });
    
    const editMachineModalEl = document.getElementById('editMachineModal')
    editMachineModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editMachineModal .acc__input-error').html('');
        $('#editMachineModal .modal-body input').val('');
        $('#editMachineModal .modal-body textarea').val('');
        $('#editMachineModal input[name="id"]').val('0');
    });

    const confirmModalEl = document.getElementById('confirmModal');
    confirmModalEl.addEventListener('hidden.tw.modal', function(event){
        $('#confirmModal .roomAgreeWith').attr('data-id', '0');
        $('#confirmModal .roomAgreeWith').attr('data-action', 'none');
    });


    $('#addMachineForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addMachineForm');

        $('#addMachineForm').find('input').removeClass('border-danger')
        $('#addMachineForm').find('.acc__input-error').html('')

        document.querySelector('#saveMachine').setAttribute('disabled', 'disabled');
        document.querySelector('#saveMachine svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route('hr.machine.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveMachine').removeAttribute('disabled');
            document.querySelector('#saveMachine svg').style.cssText = 'display: none;';
            
            if (response.status == 200) {
                addMachineModal.hide();
                
                successModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Congratulations!');
                    $('#successModal .successModalDesc').html('HR Machine successfully inserted.');
                });
            }  
            hrMachinesListTable.init();             
        }).catch(error => {
            document.querySelector('#saveMachine').removeAttribute('disabled');
            document.querySelector('#saveMachine svg').style.cssText = 'display: none;';
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addMachineForm .${key}`).addClass('border-danger')
                        $(`#addMachineForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#hrMachinesListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("hr.machine.edit"),
            data: {rowID : editId},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                $('#editMachineModal [name="name"]').val(dataset.name ? dataset.name : '');
                $('#editMachineModal [name="username"]').val(dataset.username ? dataset.username : '');
                $('#editMachineModal [name="location"]').val(dataset.location ? dataset.location : '');

                $('#editMachineModal [name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editMachineForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editMachineForm');

        $('#editMachineForm').find('input').removeClass('border-danger')
        $('#editMachineForm').find('.acc__input-error').html('')

        document.querySelector('#updateMachine').setAttribute('disabled', 'disabled');
        document.querySelector('#updateMachine svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route('hr.machine.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateMachine').removeAttribute('disabled');
            document.querySelector('#updateMachine svg').style.cssText = 'display: none;';
            
            if (response.status == 200) {
                editMachineModal.hide();
                
                successModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Congratulations!');
                    $('#successModal .successModalDesc').html('Holiday year successfully updated.');
                });
            }
            hrMachinesListTable.init(); 
        }).catch(error => {
            document.querySelector('#updateMachine').removeAttribute('disabled');
            document.querySelector('#updateMachine svg').style.cssText = 'display: none;';
            if(error.response){
                if(error.response.status == 422){
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editMachineForm .${key}`).addClass('border-danger')
                        $(`#editMachineForm  .error-${key}`).html(val)
                    }
                }else{
                    console.log('error');
                }
            }
        });
    });

    // Delete Room
    $('#hrMachinesListTable').on('click', '.delete_btn', function(){
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

    $('#hrMachinesListTable').on('click', '.restore_btn', function(){
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
                url: route('hr.machine.destroy', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                }
                hrMachinesListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('hr.machine.restore'),
                data: {recordID : recordID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                    });
                }
                hrMachinesListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    });
    
})()