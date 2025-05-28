import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var assetTypeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#assetTypeListTable", {
            ajaxURL: route("site.settings.asset.type.list"),
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
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editAssetTypeModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Status Details",
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
    if ($("#assetTypeListTable").length) {
        // Init Table
        assetTypeListTable.init();

        // Filter function
        function filterHTMLForm() {
            assetTypeListTable.init();
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

        const addAssetTypeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAssetTypeModal"));
        const editAssetTypeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAssetTypeModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addAssetTypeModalEl = document.getElementById('addAssetTypeModal')
        addAssetTypeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addAssetTypeModal .acc__input-error').html('');
            $('#addAssetTypeModal .modal-body input:not([type="checkbox"])').val('');
            $('#addAssetTypeModal input[name="active"]').prop('checked', true);
        });
        
        const editAssetTypeModalEl = document.getElementById('editAssetTypeModal')
        editAssetTypeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editAssetTypeModal .acc__input-error').html('');
            $('#editAssetTypeModal .modal-body input:not([type="checkbox"])').val('');
            $('#editAssetTypeModal input[name="id"]').val('0');
            $('#editAssetTypeModal input[name="active"]').prop('checked', false);
        });

        $('#addAssetTypeForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addAssetTypeForm');
        
            document.querySelector('#saveAssetType').setAttribute('disabled', 'disabled');
            document.querySelector("#saveAssetType svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('site.settings.asset.type.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveAssetType').removeAttribute('disabled');
                document.querySelector("#saveAssetType svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addAssetTypeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Assets Type Item Successfully inserted.');
                    }); 
                    
                    setTimeout(function(){
                        succModal.hide();
                    }, 2000);
                }
                assetTypeListTable.init();
            }).catch(error => {
                document.querySelector('#saveAssetType').removeAttribute('disabled');
                document.querySelector("#saveAssetType svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addAssetTypeForm .${key}`).addClass('border-danger');
                            $(`#addAssetTypeForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#assetTypeListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "post",
                url: route("site.settings.asset.type.edit"),
                data: {row_id : editId},
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editAssetTypeModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    
                    if(dataset.active == 1){
                        $('#editAssetTypeModal [name="active"]').prop('checked', true);
                    }else{
                        $('#editAssetTypeModal [name="active"]').prop('checked', false);
                    }
                    
                    $('#editAssetTypeModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        
        $("#editAssetTypeForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("editAssetTypeForm");

            document.querySelector('#updateAssetType').setAttribute('disabled', 'disabled');
            document.querySelector('#updateAssetType svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route("site.settings.asset.type.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateAssetType").removeAttribute("disabled");
                    document.querySelector("#updateAssetType svg").style.cssText = "display: none;";
                    editAssetTypeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Assets Type Item data successfully updated.');
                    });
                    
                    setTimeout(function(){
                        succModal.hide();
                    }, 2000);
                }
                assetTypeListTable.init();
            }).catch((error) => {
                document.querySelector("#updateAssetType").removeAttribute("disabled");
                document.querySelector("#updateAssetType svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAssetTypeForm .${key}`).addClass('border-danger')
                            $(`#editAssetTypeForm  .error-${key}`).html(val)
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
                    url: route('site.settings.asset.type.destory', recordID),
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
                    
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    assetTypeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('site.settings.asset.type.restore', recordID),
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
                    
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    assetTypeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTAT'){
                axios({
                    method: 'post',
                    url: route('site.settings.asset.type.update.status', recordID),
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
                    
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    assetTypeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#assetTypeListTable').on('click', '.status_updater', function(){
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
        $('#assetTypeListTable').on('click', '.delete_btn', function(){
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
        $('#assetTypeListTable').on('click', '.restore_btn', function(){
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
    }
})();