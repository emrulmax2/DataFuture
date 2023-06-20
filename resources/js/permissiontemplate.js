import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var permissionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-01").val() != "" ? $("#query-01").val() : "";
        let status = $("#status-01").val() != "" ? $("#status-01").val() : "";
        let role = $("#permissiontemplateTableId").attr('data-roleid') != "" ? $("#permissiontemplateTableId").attr('data-roleid') : "0";
        let permissioncategory = $("#permissioncategory-01").val() != "" ? $("#permissioncategory-01").val() : "";
        let department = $("#department-01").val() != "" ? $("#department-01").val() : "";

        let tableContent = new Tabulator("#permissiontemplateTableId", {
            ajaxURL: route("permissiontemplate.list"),
            ajaxParams: { querystr: querystr, status: status, role: role, permissioncategory: permissioncategory, department: department},
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
                    width: "180",
                },
                {
                    title: "Permission Category",
                    field: "permission_category_id",
                    headerHozAlign: "left",
                },
                {
                    title: "Department",
                    field: "department_id",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                },
                {
                    title: "Read",
                    field: "R",
                    headerHozAlign: "left",
                },
                {
                    title: "Write",
                    field: "W",
                    headerHozAlign: "left",
                },
                {
                    title: "Delete",
                    field: "D",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#permissiontemplateEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
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
                sheetName: "Permissions",
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
    if ($("#permissiontemplateTableId").length) {
        // Init Table
        permissionListTable.init();

        // Filter function
        function filterHTMLForm() {
            permissionListTable.init();
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
            $("#status").val("");
            filterHTMLForm();
        });

        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        let confModalDelTitle = 'Are you sure?';

        const addModalEl = document.getElementById('permissiontemplateAddModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#permissiontemplateAddModal .acc__input-error').html('');
            $('#permissiontemplateAddModal input:not([type="hidden"]):not([type="checkbox"])').val('');
            $('#permissiontemplateAddModal input[type="checkbox"]').prop('checked', false);
            $('#permissiontemplateAddModal select').val('');
        });
        
        const editModalEl = document.getElementById('permissiontemplateEditModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#permissiontemplateEditModal .acc__input-error').html('');
            $('#permissiontemplateEditModal input:not([type="hidden"]):not([type="checkbox"])').val('');
            $('#permissiontemplateEditModal input[name="id"]').val('0');
            $('#permissiontemplateEditModal input[type="checkbox"]').prop('checked', false);
            $('#permissiontemplateEditModal select').val('');
        });

        $('#permissiontemplateAddForm').on('submit', function(e){
            e.preventDefault();
            const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#permissiontemplateAddModal"));
            const form = document.getElementById('permissiontemplateAddForm');
        
            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector("#save svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('permissiontemplate.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#save').removeAttribute('disabled');
                    document.querySelector("#save svg").style.cssText = "display: none;";
                    $('#permissiontemplateAddForm #permission_category_id').val('');
                    $('#permissiontemplateAddForm #department_id').val('');
                    $('#permissiontemplateAddForm input[type="text"]').val('');
                    addModal.hide();
                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(
                                "Success!"
                            );
                            $("#successModal .successModalDesc").html('Data Inserted');
                        });                
                        
                }
                permissionListTable.init();
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#permissiontemplateAddForm .${key}`).addClass('border-danger')
                            $(`#permissiontemplateAddForm  .error-${key}`).html(val)
                        }
                        $('#permissiontemplateAddForm #permission_category_id').val('');
                        $('#permissiontemplateAddForm #department_id').val('');
                        $('#permissiontemplateAddForm input[type="text"]').val('');
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#permissiontemplateTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("permissiontemplate.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#permissiontemplateEditModal select[name="permission_category_id"]').val(dataset.permission_category_id ? dataset.permission_category_id : '');
                        $('#permissiontemplateEditModal select[name="department_id"]').val(dataset.department_id ? dataset.department_id : '');
                        $('#permissiontemplateEditModal input[name="type"]').val(dataset.type ? dataset.type : '');
                        $('#permissiontemplateEditModal input[name="role_id"]').val(dataset.role_id ? dataset.role_id : '');
                        
                        if(dataset.R == 1){
                            document.querySelector('#permissiontemplateEditModal #R').checked = true;
                        }else{
                            document.querySelector('#permissiontemplateEditModal #R').checked = false;
                        }
                        
                        if(dataset.W == 1){
                            document.querySelector('#permissiontemplateEditModal #W').checked = true;
                        }else{
                            document.querySelector('#permissiontemplateEditModal #W').checked = false;
                        }

                        if(dataset.D == 1){
                            document.querySelector('#permissiontemplateEditModal #D').checked = true;
                        }else{
                            document.querySelector('#permissiontemplateEditModal #D').checked = false;
                        }

                        $('#permissiontemplateEditModal input[name="id"]').val(editId);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#permissiontemplateEditForm").on("submit", function (e) {
            let editId = $('#permissiontemplateEditModal input[name="role_id"]').val();
            const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#permissiontemplateEditModal"));
            const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

            e.preventDefault();
            const form = document.getElementById("permissiontemplateEditForm");

            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector('#update svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("permissiontemplate.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        document.querySelector("#update").removeAttribute("disabled");
                        document.querySelector("#update svg").style.cssText = "display: none;";
                        editModal.hide();

                        succModal.show();
                        document.getElementById("successModal")
                            .addEventListener("shown.tw.modal", function (event) {
                                $("#successModal .successModalTitle").html(
                                    "Success!"
                                );
                                $("#successModal .successModalDesc").html('Data Updated');
                            });
                    }
                    permissionListTable.init();
                })
                .catch((error) => {
                    document
                        .querySelector("#update")
                        .removeAttribute("disabled");
                    document.querySelector("#update svg").style.cssText =
                        "display: none;";
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(error.response.data.errors)) {
                                $(`#permissiontemplateEditForm .${key}`).addClass('border-danger')
                                $(`#permissiontemplateEditForm  .error-${key}`).html(val)
                            }
                        }else if (error.response.status == 304) {
                            editModal.hide();

                            let message = error.response.statusText;
                            succModal.show();
                            document.getElementById("successModal")
                                .addEventListener("shown.tw.modal", function (event) {
                                    $("#successModal .successModalTitle").html(
                                        "No Data Change!"
                                    );
                                    $("#successModal .successModalDesc").html(message);
                                });
                        } else {
                            console.log("error");
                        }
                    }
                });
        });

        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('permissiontemplate.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Data Deleted!');
                        });
                    }
                    permissionListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('permissiontemplate.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Data Successfully Restored!');
                        });
                    }
                    permissionListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

         // Delete Course
         $('#permissiontemplateTableId').on('click', '.delete_btn', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record?');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#permissiontemplateTableId').on('click', '.restore_btn', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let dataID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record?');
                $('#confirmModal .agreeWith').attr('data-id', dataID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }
})();