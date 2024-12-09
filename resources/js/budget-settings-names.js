import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select"; 
import tippy, { roundArrow } from "tippy.js";
 
("use strict");
var budgetNameListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#budgetNameListTable", {
            ajaxURL: route("budget.settings.name.list"),
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
                    width: "80",
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        return '<div class="whitespace-normal">'+cell.getData().name+(cell.getData().code != '' ? ' ('+cell.getData().code+')' : '')+'</div>';
                    }
                },
                {
                    title: "Holders",
                    field: "holders_html",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) { 
                        return '<div style="white-space: normal;">'+cell.getData().holders_html+'</div>';
                    }
                },
                {
                    title: "Requesters",
                    field: "requester_html",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) { 
                        return '<div style="white-space: normal;">'+cell.getData().requester_html+'</div>';
                    }
                },
                {
                    title: "Approvers",
                    field: "approvers_html",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) { 
                        return '<div style="white-space: normal;">'+cell.getData().approvers_html+'</div>';
                    }
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    width: "120",
                    formatter(cell, formatterParams){
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().active == 1 ? 'Checked' : '')+' value="'+cell.getData().active+'" type="checkbox" class="status_updater form-check-input"> </div>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "120",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editBudgetNameModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                $(".tabltooltip").each(function () {
                    let tipyyoptions = {
                        content: $(this).attr("alt"),
                    };
                    tippy(this, {
                        arrow: roundArrow,
                        animation: "shift-away",
                        ...tipyyoptions,
                    });
                })
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
            $(".tabltooltip").each(function () {
                let tipyyoptions = {
                    content: $(this).attr("alt"),
                };
                tippy(this, {
                    arrow: roundArrow,
                    animation: "shift-away",
                    ...tipyyoptions,
                });
            })
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
                sheetName: "Title Details",
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

(function () {
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        create: false,
        allowEmptyOption: false,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let tomOptionsMull = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: 'Remove this item',
            },
        },
    };

    
    let budget_holder_ids = new TomSelect('#budget_holder_ids', tomOptionsMull);
    let budget_requester_ids = new TomSelect('#budget_requester_ids', tomOptionsMull);
    let budget_approver_ids = new TomSelect('#budget_approver_ids', tomOptionsMull);
    
    let edit_budget_holder_ids = new TomSelect('#edit_budget_holder_ids', tomOptionsMull);
    let edit_budget_requester_ids = new TomSelect('#edit_budget_requester_ids', tomOptionsMull);
    let edit_budget_approver_ids = new TomSelect('#edit_budget_approver_ids', tomOptionsMull);

    // Tabulator
    budgetNameListTable.init();
    

    // Filter function
    function filterHTMLForm() {
        budgetNameListTable.init();
    }

    // On submit filter form
    $("#tabulatorFilterForm")[0].addEventListener(
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
    $("#tabulator-html-filter-go").on("click", function (event) {
        filterHTMLForm();
    });

    // On reset filter form
    $("#tabulator-html-filter-reset").on("click", function (event) {
        $("#query").val("");
        $("#status").val("1");
        filterHTMLForm();
    });

    const addBudgetNameModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBudgetNameModal"));
    const editBudgetNameModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editBudgetNameModal"));
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const addBudgetNameModalEl = document.getElementById('addBudgetNameModal')
    addBudgetNameModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addBudgetNameModal .acc__input-error').html('');
        $('#addBudgetNameModal .modal-body input:not([type="checkbox"])').val('');
        $('#addBudgetNameModal input[name="active"]').prop('checked', true);

        budget_holder_ids.clear(true);
        budget_requester_ids.clear(true);
        budget_approver_ids.clear(true);
    });
    
    const editBudgetNameModalEl = document.getElementById('editBudgetNameModal')
    editBudgetNameModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editBudgetNameModal .acc__input-error').html('');
        $('#editBudgetNameModal .modal-body input:not([type="checkbox"])').val('');
        $('#editBudgetNameModal input[name="id"]').val('0');
        $('#editBudgetNameModal input[name="active"]').prop('checked', false);

        edit_budget_holder_ids.clear(true);
        edit_budget_requester_ids.clear(true);
        edit_budget_approver_ids.clear(true);
    });

    $('#addBudgetNameForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addBudgetNameForm');
    
        document.querySelector('#saveNameBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveNameBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('budget.settings.name.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveNameBtn').removeAttribute('disabled');
            document.querySelector("#saveNameBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addBudgetNameModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Budget Name Successfully inserted.');
                });  
                
                setTimeout(() => {
                    succModal.hide();
                }, 2000);
            }
            budgetNameListTable.init();
        }).catch(error => {
            document.querySelector('#saveNameBtn').removeAttribute('disabled');
            document.querySelector("#saveNameBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addBudgetNameForm .${key}`).addClass('border-danger');
                        $(`#addBudgetNameForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#budgetNameListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("budget.settings.name.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                $('#editBudgetNameModal input[name="name"]').val(dataset.name ? dataset.name : '');
                $('#editBudgetNameModal input[name="code"]').val(dataset.code ? dataset.code : '');
                
                $('#editBudgetNameModal input[name="id"]').val(editId);

                if(dataset.active == 1){
                    $('#editBudgetNameModal input[name="active"]').prop('checked', true);
                }else{
                    $('#editBudgetNameModal input[name="active"]').prop('checked', false);
                }

                if(dataset.holders){
                    $.each(dataset.holders, function(index, row) {
                        edit_budget_holder_ids.addItem(row.user_id);
                    });
                }else{
                    edit_budget_holder_ids.clear(true);
                }
                if(dataset.requesters){
                    $.each(dataset.requesters, function(index, row) {
                        edit_budget_requester_ids.addItem(row.user_id);
                    });
                }else{
                    edit_budget_requester_ids.clear(true);
                }
                if(dataset.approvers){
                    $.each(dataset.approvers, function(index, row) {
                        edit_budget_approver_ids.addItem(row.user_id);
                    });
                }else{
                    edit_budget_approver_ids.clear(true);
                }
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    // Update Course Data
    $("#editBudgetNameForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById("editBudgetNameForm");

        document.querySelector('#updateNameBtn').setAttribute('disabled', 'disabled');
        document.querySelector('#updateNameBtn svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route("budget.settings.name.update"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#updateNameBtn").removeAttribute("disabled");
                document.querySelector("#updateNameBtn svg").style.cssText = "display: none;";
                editBudgetNameModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Budget Name data successfully updated.');
                });
                
                setTimeout(() => {
                    succModal.hide();
                }, 2000);
            }
            budgetNameListTable.init();
        }).catch((error) => {
            document.querySelector("#updateNameBtn").removeAttribute("disabled");
            document.querySelector("#updateNameBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editBudgetNameForm .${key}`).addClass('border-danger')
                        $(`#editBudgetNameForm  .error-${key}`).html(val)
                    }
                }else {
                    console.log("error");
                }
            }
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
                url: route('budget.settings.name.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                budgetNameListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('budget.settings.name.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                    });
                
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                budgetNameListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTAT'){
            axios({
                method: 'post',
                url: route('budget.settings.name.update.status', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Record status successfully updated!');
                    });
                
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                budgetNameListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    // Delete Course
    $('#budgetNameListTable').on('click', '.status_updater', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTAT');
        });
    });

    // Delete Course
    $('#budgetNameListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Restore Course
    $('#budgetNameListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
        });
    });

})();