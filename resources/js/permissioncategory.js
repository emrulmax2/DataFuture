import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var table = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#permissioncategoryTableId", {
            ajaxURL: route("permissioncategory.list"),
            ajaxParams: { querystr: querystr, status: status },
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
                    title: "Name",
                    field: "name",
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
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '" data-tw-toggle="modal" data-tw-target="#editPermissionModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Permission Category Details",
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
    if ($("#permissioncategoryTableId").length) {
        // Init Table
        table.init();

        // Filter function
        function filterHTMLForm() {
            table.init();
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
        const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPermissionModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPermissionModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addModalEl = document.getElementById('addPermissionModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addPermissionModal .acc__input-error').html('');
            $('#addPermissionModal input').val('');
        });
        
        const editModalEl = document.getElementById('editPermissionModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editPermissionModal .acc__input-error').html('');
            $('#editPermissionModal input').val('');
            $('#editPermissionModal input[name="id"]').val('0');
        });

        const confirmModalEl = document.getElementById('confirmModal')
        confirmModalEl.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .agreeWith').attr('data-id', '0');
            $('#confirmModal .agreeWith').attr('data-action', 'none');
        });

        $('#addPermissionForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addPermissionForm');
        
            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector("#save svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('permissioncategory.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#save').removeAttribute('disabled');
                    document.querySelector("#save svg").style.cssText = "display: none;";
                    addModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Success!");
                        $("#successModal .successModalDesc").html('Data Inserted');
                    });         
                }
                table.init();
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addPermissionForm .${key}`).addClass('border-danger')
                            $(`#addPermissionForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#permissioncategoryTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("permissioncategory.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editPermissionModal input[name="name"]').val(dataset.name ? dataset.name : '');

                    $('#editPermissionModal input[name="id"]').val(editId);
                }
            })
            .catch((error) => {
                console.log(error);
            });
        });

        // Update Course Data
        $("#editPermissionForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editPermissionModal input[name="id"]').val();

            const form = document.getElementById("editPermissionForm");

            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector('#update svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("permissioncategory.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#update").removeAttribute("disabled");
                    document.querySelector("#update svg").style.cssText = "display: none;";
                    editModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Success!");
                        $("#successModal .successModalDesc").html('Data Updated');
                    });
                }
                table.init();
            }).catch((error) => {
                document.querySelector("#update").removeAttribute("disabled");
                document.querySelector("#update svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editPermissionForm .${key}`).addClass('border-danger')
                            $(`#editPermissionForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html("Oops!");
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
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('permissioncategory.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Academic year successfully deleted!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('permissioncategory.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Academic Year Data Successfully Restored!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#permissioncategoryTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#permissioncategoryTableId').on('click', '.restore_btn', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record?');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }
})();