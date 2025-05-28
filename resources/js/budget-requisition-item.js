import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select"; 

("use strict");
var requisitionItemListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        var requisition_id = $("#requisitionItemListTable").attr('data-requisition');
        let querystr = $("#query-RI").val() != "" ? $("#query-RI").val() : "";
        let status = $("#status-RI").val() != "" ? $("#status-RI").val() : "";

        let tableContent = new Tabulator("#requisitionItemListTable", {
            ajaxURL: route("budget.management.req.item.list"),
            ajaxParams: { requisition_id: requisition_id, querystr: querystr, status: status },
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
                    title: "Description",
                    field: "description",
                    headerHozAlign: "left",
                },
                {
                    title: "Price",
                    field: "price",
                    headerHozAlign: "left",
                },
                {
                    title: "Quantity",
                    field: "quantity",
                    headerHozAlign: "left",
                },
                {
                    title: "Total",
                    field: "total",
                    headerHozAlign: "left",
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editRequisitionItemModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();


(function(){
    requisitionItemListTable.init();

    function filterRIHTMLForm() {
        requisitionItemListTable.init();
    }

    // On submit filter form
    $("#tabulatorFilterForm-RI")[0].addEventListener(
        "keypress",
        function (event) {
            let keycode = event.keyCode ? event.keyCode : event.which;
            if (keycode == "13") {
                event.preventDefault();
                filterRIHTMLForm();
            }
        }
    );

    // On click go button
    $("#tabulator-html-filter-go-RI").on("click", function (event) {
        filterRIHTMLForm();
    });

    // On reset filter form
    $("#tabulator-html-filter-reset-RI").on("click", function (event) {
        $("#query-RI").val("");
        $("#status-RI").val("1");
        filterRIHTMLForm();
    });

    const addRequisitionItemModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addRequisitionItemModal"));
    const editRequisitionItemModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editRequisitionItemModal"));

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#successModal .successCloser').attr('data-action', 'NONE');
    });

    const addRequisitionItemModalEl = document.getElementById('addRequisitionItemModal')
    addRequisitionItemModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addRequisitionItemModal .acc__input-error').html('');
        $('#addRequisitionItemModal .modal-body input:not([type="checkbox"])').val('');
    });

    const editRequisitionItemModalEl = document.getElementById('editRequisitionItemModal')
    editRequisitionItemModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editRequisitionItemModal .acc__input-error').html('');
        $('#editRequisitionItemModal .modal-body input:not([type="checkbox"])').val('');
        $('#editRequisitionItemModal input[name="id"]').val('0');
    });

    $('#addRequisitionItemModal [name="quantity"], #addRequisitionItemModal [name="price"]').on('keyup paste', function(){
        var theQuantity = ($('#addRequisitionItemModal [name="quantity"]').val() != '' ? $('#addRequisitionItemModal [name="quantity"]').val() * 1 : 0);
        var thePrice = ($('#addRequisitionItemModal [name="price"]').val() != '' ? $('#addRequisitionItemModal [name="price"]').val() * 1 : 0);
        var theTotal = thePrice * theQuantity;

        if(theTotal > 0){
            $('#addRequisitionItemModal [name="total"]').val(theTotal)
        }else{
            $('#addRequisitionItemModal [name="total"]').val('')
        }
    });

    $('#editRequisitionItemModal [name="quantity"], #editRequisitionItemModal [name="price"]').on('keyup paste', function(){
        var theQuantity = ($('#editRequisitionItemModal [name="quantity"]').val() != '' ? $('#editRequisitionItemModal [name="quantity"]').val() * 1 : 0);
        var thePrice = ($('#editRequisitionItemModal [name="price"]').val() != '' ? $('#editRequisitionItemModal [name="price"]').val() * 1 : 0);
        var theTotal = thePrice * theQuantity;

        if(theTotal > 0){
            $('#editRequisitionItemModal [name="total"]').val(theTotal)
        }else{
            $('#editRequisitionItemModal [name="total"]').val('')
        }
    });

    $('#addRequisitionItemForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addRequisitionItemForm');
    
        document.querySelector('#saveItemBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveItemBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('budget.management.req.item.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveItemBtn').removeAttribute('disabled');
            document.querySelector("#saveItemBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addRequisitionItemModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Requisition item Successfully inserted.');
                });  
                
                setTimeout(() => {
                    succModal.hide();
                }, 2000);
            }
            requisitionItemListTable.init();
        }).catch(error => {
            document.querySelector('#saveItemBtn').removeAttribute('disabled');
            document.querySelector("#saveItemBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addRequisitionItemForm .${key}`).addClass('border-danger');
                        $(`#addRequisitionItemForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#requisitionItemListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("budget.management.req.item.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                $('#editRequisitionItemModal input[name="description"]').val(dataset.description ? dataset.description : '');
                $('#editRequisitionItemModal input[name="quantity"]').val(dataset.quantity ? dataset.quantity : '');
                $('#editRequisitionItemModal input[name="price"]').val(dataset.price ? dataset.price : '');
                $('#editRequisitionItemModal input[name="total"]').val(dataset.total ? dataset.total : '');
                $('#editRequisitionItemModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editRequisitionItemForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editRequisitionItemForm');
    
        document.querySelector('#updateItemBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#updateItemBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('budget.management.req.item.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateItemBtn').removeAttribute('disabled');
            document.querySelector("#updateItemBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editRequisitionItemModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Requisition item Successfully updated.');
                });  
                
                setTimeout(() => {
                    succModal.hide();
                }, 2000);
            }
            requisitionItemListTable.init();
        }).catch(error => {
            document.querySelector('#updateItemBtn').removeAttribute('disabled');
            document.querySelector("#updateItemBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editRequisitionItemForm .${key}`).addClass('border-danger');
                        $(`#editRequisitionItemForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
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
        if(action == 'DELETERI'){
            axios({
                method: 'delete',
                url: route('budget.management.req.item.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                }
                requisitionItemListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORERI'){
            axios({
                method: 'post',
                url: route('budget.management.req.item.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                    });
                }
                requisitionItemListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTATRI'){
            axios({
                method: 'post',
                url: route('budget.management.req.item.update.status', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record status successfully updated!');
                    });
                }
                requisitionItemListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    // Delete Course
    $('#requisitionItemListTable').on('click', '.status_updater', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATRI');
        });
    });

    // Delete Course
    $('#requisitionItemListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETERI');
        });
    });

    // Restore Course
    $('#requisitionItemListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORERI');
        });
    });

})()