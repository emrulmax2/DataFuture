import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var docRolePermissionTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#docRolePermissionTable", {
            ajaxURL: route("site.settings.doc.role.permission.list"),
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
                    title: "Create",
                    field: "create",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        return (cell.getData().create == 1 ? '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-2 text-white py-0 rounded-0">No</span>');
                    }
                },
                {
                    title: "Read",
                    field: "read",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        return (cell.getData().read == 1 ? '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-2 text-white py-0 rounded-0">No</span>');
                    }
                },
                {
                    title: "Update",
                    field: "update",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        return (cell.getData().update == 1 ? '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-2 text-white py-0 rounded-0">No</span>');
                    }
                },
                {
                    title: "Delete",
                    field: "delete",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        return (cell.getData().delete == 1 ? '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-2 text-white py-0 rounded-0">No</span>');
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editPermissionModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
    if ($("#docRolePermissionTable").length) {
        // Init Table
        docRolePermissionTable.init();

        // Filter function
        function filterHTMLForm() {
            docRolePermissionTable.init();
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

        const addPermissionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPermissionModal"));
        const editPermissionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPermissionModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addPermissionModalEl = document.getElementById('addPermissionModal')
        addPermissionModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addPermissionModal .acc__input-error').html('');
            $('#addPermissionModal .modal-body input:not([type="checkbox"])').val('');
            $('#addPermissionModal .modal-body input[type="checkbox"]').prop('checked', false);
        });
        
        const editPermissionModalEl = document.getElementById('editPermissionModal')
        editPermissionModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editPermissionModal .acc__input-error').html('');
            $('#addPermissionModal .modal-body input:not([type="checkbox"])').val('');
            $('#addPermissionModal .modal-body input[type="checkbox"]').prop('checked', false);
            $('#editPermissionModal input[name="id"]').val('0');
        });

        $('#addPermissionForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('addPermissionForm');
        
            document.querySelector('#saveRole').setAttribute('disabled', 'disabled');
            document.querySelector("#saveRole svg").style.cssText ="display: inline-block;";

            var checkedLength = $('#addPermissionForm input[type="checkbox"]:checked').length;
            if(checkedLength == 0){
                $form.find('.modError').remove();
                $('.modal-content', $form).prepend('<div class="modError alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> You must checked at least one permission checkbox.</div>');
                createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
                
                setTimeout(function(){
                    $form.find('.modError').remove();
                }, 2000);

                document.querySelector('#saveRole').removeAttribute('disabled');
                document.querySelector("#saveRole svg").style.cssText = "display: none;";
            }else{
                let form_data = new FormData(form);
                axios({
                    method: "post",
                    url: route('site.settings.doc.role.permission.store'),
                    data: form_data,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    document.querySelector('#saveRole').removeAttribute('disabled');
                    document.querySelector("#saveRole svg").style.cssText = "display: none;";
                    
                    if (response.status == 200) {
                        addPermissionModal.hide();

                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Document role and permission successfully stored.');
                        }); 
                        
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    docRolePermissionTable.init();
                }).catch(error => {
                    document.querySelector('#saveRole').removeAttribute('disabled');
                    document.querySelector("#saveRole svg").style.cssText = "display: none;";
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(error.response.data.errors)) {
                                $(`#addPermissionForm .${key}`).addClass('border-danger');
                                $(`#addPermissionForm  .error-${key}`).html(val);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });
            }
        });

        $("#docRolePermissionTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "post",
                url: route("site.settings.doc.role.permission.edit"),
                data: {row_id : editId},
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;

                    $('#editPermissionModal input[name="display_name"]').val(dataset.display_name ? dataset.display_name : '');
                    $('#editPermissionModal input[name="type"]').val(dataset.type ? dataset.type : '');

                    if(dataset.create == 1){
                        $('#editPermissionModal [name="create"]').prop('checked', true);
                    }else{
                        $('#editPermissionModal [name="create"]').prop('checked', false);
                    }
                    if(dataset.read == 1){
                        $('#editPermissionModal [name="read"]').prop('checked', true);
                    }else{
                        $('#editPermissionModal [name="read"]').prop('checked', false);
                    }
                    if(dataset.update == 1){
                        $('#editPermissionModal [name="update"]').prop('checked', true);
                    }else{
                        $('#editPermissionModal [name="update"]').prop('checked', false);
                    }
                    if(dataset.delete == 1){
                        $('#editPermissionModal [name="delete"]').prop('checked', true);
                    }else{
                        $('#editPermissionModal [name="delete"]').prop('checked', false);
                    }
                    
                    $('#editPermissionModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        
        $("#editPermissionForm").on("submit", function (e) {
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editPermissionForm');
        
            document.querySelector('#updateRole').setAttribute('disabled', 'disabled');
            document.querySelector("#updateRole svg").style.cssText ="display: inline-block;";

            var checkedLength = $('#editPermissionForm input[type="checkbox"]:checked').length;
            if(checkedLength == 0){
                $form.find('.modError').remove();
                $('.modal-content', $form).prepend('<div class="modError alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> You must checked at least one permission checkbox.</div>');
                createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
                
                setTimeout(function(){
                    $form.find('.modError').remove();
                }, 2000);

                document.querySelector('#updateRole').removeAttribute('disabled');
                document.querySelector("#updateRole svg").style.cssText = "display: none;";
            }else{
                let form_data = new FormData(form);
                axios({
                    method: "post",
                    url: route('site.settings.doc.role.permission.update'),
                    data: form_data,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    document.querySelector('#updateRole').removeAttribute('disabled');
                    document.querySelector("#updateRole svg").style.cssText = "display: none;";
                    
                    if (response.status == 200) {
                        editPermissionModal.hide();

                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Document role and permission successfully updated.');
                        }); 
                        
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    docRolePermissionTable.init();
                }).catch(error => {
                    document.querySelector('#updateRole').removeAttribute('disabled');
                    document.querySelector("#updateRole svg").style.cssText = "display: none;";
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(error.response.data.errors)) {
                                $(`#editPermissionForm .${key}`).addClass('border-danger');
                                $(`#editPermissionForm  .error-${key}`).html(val);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });
            }
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
                    url: route('site.settings.doc.role.permission.destory', recordID),
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
                    docRolePermissionTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('site.settings.doc.role.permission.restore', recordID),
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
                    docRolePermissionTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })


        // Delete Course
        $('#docRolePermissionTable').on('click', '.delete_btn', function(){
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
        $('#docRolePermissionTable').on('click', '.restore_btn', function(){
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