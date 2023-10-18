import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

var roleListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#roleTableId", {
            ajaxURL: route("roles.list"),
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
                    title: "Display Name",
                    field: "display_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "type",
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
                            btns +='<a href="'+route('roles.show', cell.getData().id)+'" class="edit_btn btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editRoleModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
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
                sheetName: "Roles Details",
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
    if ($("#roleTableId").length) {
        // Init Table
        roleListTable.init();

        // Filter function
        function filterHTMLForm() {
            roleListTable.init();
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

        const addRoleModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addRoleModal"));
        const editRoleModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editRoleModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

        let confModalDelTitle = 'Are you sure?';

        const addRoleModalEl = document.getElementById('addRoleModal')
        addRoleModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addRoleModal .acc__input-error').html('');
            $('#addRoleModal input[type="text"]').val('');
            $('#addRoleModal select').val('');
        });
        
        const editRoleModalEl = document.getElementById('editRoleModal')
        editRoleModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editRoleModal .acc__input-error').html('');
            $('#editRoleModal input[type="text"]').val('');
            $('#editRoleModal select').val('');
            $('#editRoleModal input[name="id"]').val('0');
        });

        const confirmModal = document.getElementById('confirmModal');
        confirmModal.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .roomAgreeWith').attr('data-id', '0');
            $('#confirmModal .roomAgreeWith').attr('data-action', 'none');
        });


        // Delete Room
        $('#roleTableId').on('click', '.delete_btn', function(){
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

        $('#roleTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let academicyearID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record?');
                $('#confirmModal .agreeWith').attr('data-id', academicyearID);
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
                    url: route('roles.destory', recordID),
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
                    roleListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('roles.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Bank Holiday Data Successfully Restored!');
                        });
                    }
                    roleListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $("#roleTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("roles.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editRoleModal input[name="display_name"]').val(dataset.display_name ? dataset.display_name : '');
                    $('#editRoleModal select[name="type"]').val(dataset.type ? dataset.type : '');

                    $('#editRoleModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#editRoleForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('editRoleForm');

            $('#editRoleForm').find('input').removeClass('border-danger')
            $('#editRoleForm').find('.acc__input-error').html('')

            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector('#update svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('roles.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#update').removeAttribute('disabled');
                document.querySelector('#update svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    editRoleModal.hide();
                    roleListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Role data successfully updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#update').removeAttribute('disabled');
                document.querySelector('#update svg').style.cssText = 'display: none;';
                if(error.response){
                    if(error.response.status == 422){
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editRoleForm .${key}`).addClass('border-danger')
                            $(`#editRoleForm  .error-${key}`).html(val)
                        }
                    }else{
                        console.log('error');
                    }
                }
            });
        });

        $('#addRoleForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addRoleForm');

            $('#addRoleForm').find('input').removeClass('border-danger')
            $('#addRoleForm').find('.acc__input-error').html('')

            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector('#save svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('roles.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector('#save svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    addRoleModal.hide();
                    roleListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Role data successfully inserted.');
                    });
                }               
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector('#save svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addRoleForm .${key}`).addClass('border-danger')
                            $(`#addRoleForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });
    }
})()