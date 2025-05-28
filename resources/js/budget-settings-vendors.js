import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var budgetVendorListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#budgetVendorListTable", {
            ajaxURL: route("budget.settings.vendors.list"),
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
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Email",
                    field: "email",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Address",
                    field: "address",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().address+'</div>';
                    }
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
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "120",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editBudgetVendorModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
    // Tabulator
    budgetVendorListTable.init();
    

    // Filter function
    function filterHTMLForm() {
        budgetVendorListTable.init();
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

    const addBudgetVendorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBudgetVendorModal"));
    const editBudgetVendorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editBudgetVendorModal"));
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const addBudgetVendorModalEl = document.getElementById('addBudgetVendorModal')
    addBudgetVendorModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addBudgetVendorModal .acc__input-error').html('');
        $('#addBudgetVendorModal .modal-body input:not([type="checkbox"])').val('');
        $('#addBudgetVendorModal .modal-body textarea').val('');
        $('#addBudgetVendorModal input[name="active"]').prop('checked', true);
    });
    
    const editBudgetVendorModalEl = document.getElementById('editBudgetVendorModal')
    editBudgetVendorModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editBudgetVendorModal .acc__input-error').html('');
        $('#editBudgetVendorModal .modal-body input:not([type="checkbox"])').val('');
        $('#editBudgetVendorModal input[name="id"]').val('0');
        $('#editBudgetVendorModal .modal-body textarea').val('');
        $('#editBudgetVendorModal input[name="active"]').prop('checked', false);
    });

    $('#addBudgetVendorForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addBudgetVendorForm');
    
        document.querySelector('#saveVenBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveVenBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('budget.settings.vendors.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveVenBtn').removeAttribute('disabled');
            document.querySelector("#saveVenBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addBudgetVendorModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Vendor Successfully inserted.');
                });  
                
                setTimeout(() => {
                    succModal.hide();
                }, 2000);
            }
            budgetVendorListTable.init();
        }).catch(error => {
            document.querySelector('#saveVenBtn').removeAttribute('disabled');
            document.querySelector("#saveVenBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addBudgetVendorForm .${key}`).addClass('border-danger');
                        $(`#addBudgetVendorForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#budgetVendorListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("budget.settings.vendors.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                $('#editBudgetVendorModal input[name="name"]').val(dataset.name ? dataset.name : '');
                $('#editBudgetVendorModal input[name="email"]').val(dataset.email ? dataset.email : '');
                $('#editBudgetVendorModal input[name="phone"]').val(dataset.phone ? dataset.phone : '');
                $('#editBudgetVendorModal [name="address"]').val(dataset.address ? dataset.address : '');
                
                $('#editBudgetVendorModal input[name="id"]').val(editId);

                if(dataset.active == 1){
                    $('#editBudgetVendorModal input[name="active"]').prop('checked', true);
                }else{
                    $('#editBudgetVendorModal input[name="active"]').prop('checked', false);
                }
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    // Update Course Data
    $("#editBudgetVendorForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById("editBudgetVendorForm");

        document.querySelector('#updateVenBtn').setAttribute('disabled', 'disabled');
        document.querySelector('#updateVenBtn svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route("budget.settings.vendors.update"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#updateVenBtn").removeAttribute("disabled");
                document.querySelector("#updateVenBtn svg").style.cssText = "display: none;";
                editBudgetVendorModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Vendor data successfully updated.');
                });
                
                setTimeout(() => {
                    succModal.hide();
                }, 2000);
            }
            budgetVendorListTable.init();
        }).catch((error) => {
            document.querySelector("#updateVenBtn").removeAttribute("disabled");
            document.querySelector("#updateVenBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editBudgetVendorForm .${key}`).addClass('border-danger')
                        $(`#editBudgetVendorForm  .error-${key}`).html(val)
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
                url: route('budget.settings.vendors.destory', recordID),
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
                budgetVendorListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('budget.settings.vendors.restore', recordID),
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
                budgetVendorListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTAT'){
            axios({
                method: 'post',
                url: route('budget.settings.vendors.update.status', recordID),
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
                budgetVendorListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    // Delete Course
    $('#budgetVendorListTable').on('click', '.status_updater', function(){
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
    $('#budgetVendorListTable').on('click', '.delete_btn', function(){
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
    $('#budgetVendorListTable').on('click', '.restore_btn', function(){
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