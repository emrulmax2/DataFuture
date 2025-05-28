import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";


("use strict");
var agentBankListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "1";
        let agent_id = $("#agentBankListTable").attr('data-agent');

        let tableContent = new Tabulator("#agentBankListTable", {
            ajaxURL: route("agent-user.bank.list"),
            ajaxParams: { agent_id: agent_id, querystr: querystr, status: status },
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
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editBankDetailsModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button>';
                            btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                            if(cell.getData().active == 1){
                                //btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#addBankModal" type="button" class="btn-rounded btn btn-facebook text-white p-0 w-9 h-9 ml-1"><i data-lucide="refresh-ccw" class="w-4 h-4"></i></button>';
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
                const actionColumn = this.getColumn("id");
                if (actionColumn) {
                    const currentWidth = actionColumn.getWidth();
                    actionColumn.setWidth(currentWidth - 1);
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Groups Details",
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

(function(){
    if ($("#agentBankListTable").length) {
        agentBankListTable.init();

        function filterHTMLFormBNK() {
            agentBankListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm")[0].addEventListener(
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
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLFormBNK();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterHTMLFormBNK();
        });
    }


    const addBankDetailsModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBankDetailsModal"));
    const editBankDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editBankDetailsModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    let confModalDelTitle = 'Are you sure?';


    const addBankDetailsModalEl = document.getElementById('addBankDetailsModal')
    addBankDetailsModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addBankDetailsModal .acc__input-error').html('');
        $('#addBankDetailsModal .modal-body input').val('');
        $('#addBankDetailsModal .modal-body select').val('');
        $('#addBankDetailsModal input[name="active"]').prop('checked', true);
    });

    const editBankDetailsModalEl = document.getElementById('editBankDetailsModal')
    editBankDetailsModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editBankDetailsModal .acc__input-error').html('');
        $('#editBankDetailsModal .modal-body input').val('');
        $('#editBankDetailsModal .modal-body select').val('');
        $('#editBankDetailsModal input[name="active"]').prop('checked', false);
        $('#editBankDetailsModal [name="id"]').val('0');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });

    $('#addBankDetailsForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addBankDetailsForm');
    
        document.querySelector('#saveABNK').setAttribute('disabled', 'disabled');
        document.querySelector("#saveABNK svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('agent-user.store.bank'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveABNK').removeAttribute('disabled');
            document.querySelector("#saveABNK svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addBankDetailsModal.hide();
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Success!" );
                    $("#successModal .successModalDesc").html('Agent bank details successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });                
                    
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            agentBankListTable.init();
        }).catch(error => {
            document.querySelector('#saveABNK').removeAttribute('disabled');
            document.querySelector("#saveABNK svg").style.cssText = "display: none;";

            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addBankDetailsForm .${key}`).addClass('border-danger')
                        $(`#addBankDetailsForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#agentBankListTable').on('click', '.edit_btn', function(){
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("agent-user.edit.bank"),
            data: {editId: editId},
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;
                $('#editBankDetailsModal [name="beneficiary"]').val(dataset.beneficiary ? dataset.beneficiary : '');
                $('#editBankDetailsModal [name="sort_code"]').val(dataset.sort_code ? dataset.sort_code : '');
                $('#editBankDetailsModal [name="ac_no"]').val(dataset.ac_no ? dataset.ac_no : '');

                if(dataset.active == 1){
                    $('#editBankDetailsModal input[name="active"]').prop('checked', true);
                }else{
                    $('#editBankDetailsModal input[name="active"]').prop('checked', false);
                }

                $('#editBankDetailsModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editBankDetailsForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editBankDetailsForm');
    
        document.querySelector('#updateABNK').setAttribute('disabled', 'disabled');
        document.querySelector("#updateABNK svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('agent-user.update.bank'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateABNK').removeAttribute('disabled');
            document.querySelector("#updateABNK svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editBankDetailsModal.hide();
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Success!" );
                    $("#successModal .successModalDesc").html('Agent bank details successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });                
                    
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            agentBankListTable.init();
        }).catch(error => {
            document.querySelector('#updateABNK').removeAttribute('disabled');
            document.querySelector("#updateABNK svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editBankDetailsForm .${key}`).addClass('border-danger')
                        $(`#editBankDetailsForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });


    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();

        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETEBNK'){
            axios({
                method: 'delete',
                url: route('agent-user.destroy.bank', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Agent Bank details successfully deleted!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                agentBankListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREBNK'){
            axios({
                method: 'post',
                url: route('agent-user.restore.bank', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Success!');
                        $('#successModal .successModalDesc').html('Agent Bank details Successfully Restored!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                agentBankListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTATBNK'){
            axios({
                method: 'post',
                url: route('agent-user.changestatus.bank', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Success!');
                        $('#successModal .successModalDesc').html('Agent Bank details status successfully updated!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                agentBankListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    //Change Status
    $('#agentBankListTable').on('click', '.status_updater', function(){
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
    $('#agentBankListTable').on('click', '.delete_btn', function(){
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
    $('#agentBankListTable').on('click', '.restore_btn', function(){
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