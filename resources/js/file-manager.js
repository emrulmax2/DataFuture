import TomSelect from "tom-select";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var fileVersionHistoryListTable = (function () {
    var _tableGen = function (file_id ) {
        
        let tableContent = new Tabulator('#fileVersionHistoryListTable', {
            ajaxURL: route("file.manager.file.version.history"),
            ajaxParams: { file_id: file_id },
            ajaxConfig:{
                method:"POST",
                headers: {
                    'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')
                },
            },
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
                    title: "Created At",
                    field: "created_at",
                    headerHozAlign: "left",
                },
                {
                    title: "Created By",
                    field: "created_by",
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
                        if(cell.getData().current_version == 1){
                            btns += '<span class="btn btn-success py-0 px-2 text-white rounded-0">Current Version</span>';
                        }else{
                            btns += '<a href="'+cell.getData().download_url+'" download class="downloadDoc relative btn btn-success text-white btn-rounded ml-1 p-0 w-7 h-7"><i data-lucide="cloud-lightning" class="w-3 h-3"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn relative btn btn-linkedin text-white btn-rounded ml-1 p-0 w-7 h-7"><i data-lucide="rotate-cw" class="w-3 h-3"></i></button>';
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
    };
    return {
        init: function ( file_id ) {
            _tableGen( file_id );
        },
    };
})();

(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {},
            remove_button: {
                title: "Remove this item",
            },
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let employeeIDS = ($('#employee_ids').length > 0 ? new TomSelect('#employee_ids', tomOptions) : null);
    let editEmployeeIds = ($('#edit_employee_ids').length > 0 ? new TomSelect('#edit_employee_ids', tomOptions) : null);
    let editFileEmployeeIds = ($('#edit_file_employee_ids').length > 0 ? new TomSelect('#edit_file_employee_ids', tomOptions) : null);
    let reminderEmployeeIds = ($('#reminder_employee_ids').length > 0 ? new TomSelect('#reminder_employee_ids', tomOptions) : null);


    const addFolderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addFolderModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const editFolderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFolderModal"));
    const editFolderPermissionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFolderPermissionModal"));
    const fileReminderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#fileReminderModal"));
    let confModalDelTitle = 'Are you sure?';

    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-id', '0');
        $("#confirmModal .agreeWith").attr('data-action', 'none');
        $('#confirmModal button').removeAttr('disabled');
    });

    $('#successModal .successCloser').on('click', function(e){
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    const addFolderModalEl = document.getElementById('addFolderModal')
    addFolderModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addFolderModal .acc__input-error').html('');
        $('#addFolderModal .modal-body input:not([type="checkbox"])').val('');

        $('#addFolderModal .modal-body input[name="permission_inheritence"]').prop('checked', true);
        $('#addFolderModal .permission_inheritence_label').html('Yes');
        $('#addFolderModal .permissionWrap').fadeOut('fast', function(){
            $('#addFolderModal .folderPermissionTable').find('.permissionEmployeeRow').remove();
            $('#addFolderModal .folderPermissionTable').find('.noticeTr').fadeIn();
            if($('#employee_ids').length > 0){
                employeeIDS.clear(true);
            }
        });
    });

    const editFolderModallEl = document.getElementById('editFolderModal')
    editFolderModallEl.addEventListener('hide.tw.modal', function(event) {
        $('#editFolderModal .acc__input-error').html('');
        $('#editFolderModal .modal-body input:not([type="checkbox"])').val('');
        $('#editFolderModal .modal-footer input[name="id"]').val('0');
    });

    const editFolderPermissionModalEl = document.getElementById('editFolderPermissionModal')
    editFolderPermissionModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editFolderPermissionModal .folderPermissionTable').find('.permissionEmployeeRow').remove();
        $('#editFolderPermissionModal .folderPermissionTable').find('.noticeTr').fadeIn();
        if($('#edit_employee_ids').length > 0){
            editEmployeeIds.clear(true);
        }

        $('#editFolderPermissionModal .modal-footer input[name="id"]').val('0');
    });

    const fileReminderModalEl = document.getElementById('fileReminderModal')
    fileReminderModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#fileReminderModal .acc__input-error').html('');
        $('#fileReminderModal .displayName').html('');
        $('#fileReminderModal .modal-body input:not([type="checkbox"])').val('');
        $('#fileReminderModal .modal-body textarea').val('');
        $('#fileReminderModal .modal-body [name="is_repeat_reminder"]').prop('checked', false);
        $('#fileReminderModal .modal-body [name="is_send_email"]').prop('checked', false);
        $('#fileReminderModal .modal-body .reminderSingleWrap').fadeIn();
        $('#fileReminderModal .modal-body .reminderMultiWrap').fadeOut();

        reminderEmployeeIds.clear(true);
        $('#fileReminderModal .modal-footer input[name="document_info_id"]').val('0');
    });


    if($('#employee_ids').length > 0){
        employeeIDS.on('item_add', function(employee_id, item){
            axios({
                method: "post",
                url: route('file.manager.get.employee.permission.set'),
                data: {employee_id : employee_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('#addFolderModal .leaveTableLoader').removeClass('active');
                if (response.status == 200) {
                    let res = response.data.res;
                    $('#addFolderForm .folderPermissionTable').find('.noticeTr').fadeOut('fast', function(){
                        $('#addFolderForm .folderPermissionTable tbody').append(res);
                    });

                    createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
                }
            }).catch(error => {
                $('#addFolderModal .leaveTableLoader').removeClass('active');
                if (error.response) {
                    console.log('error');
                }
            });
        });
        employeeIDS.on('item_remove', function(employee_id, item){
            let $theTr = $('#addFolderModal #employeeFolderPermission_'+employee_id);
            $theTr.remove();

            var permissionTrLength = $('#addFolderModal .folderPermissionTable').find('.permissionEmployeeRow').length;
            if(permissionTrLength == 0){
                $('#addFolderModal .folderPermissionTable').find('.noticeTr').fadeIn();
            }else{
                $('#addFolderModal .folderPermissionTable').find('.noticeTr').fadeOut();
            }
        });
    }

    if($('#edit_employee_ids').length > 0){
        editEmployeeIds.on('item_add', function(employee_id, item){
            let folder_id = $('#editFolderPermissionModal [name="folder_id"]').val();
            let existRow = $('#editFolderPermissionModal').find('#employeeFolderPermission_'+employee_id).length;
            
            if(existRow == 0){
                axios({
                    method: "post",
                    url: route('file.manager.get.employee.permission.set'),
                    data: {employee_id : employee_id, folder_id : (folder_id > 0 ? folder_id : 0)},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    $('#editFolderPermissionModal .leaveTableLoader').removeClass('active');
                    if (response.status == 200) {
                        let res = response.data.res;
                        $('#editFolderPermissionModal .folderPermissionTable').find('.noticeTr').fadeOut('fast', function(){
                            $('#editFolderPermissionModal .folderPermissionTable tbody').append(res);
                        });

                        createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
                    }
                }).catch(error => {
                    $('#editFolderPermissionModal .leaveTableLoader').removeClass('active');
                    if (error.response) {
                        console.log('error');
                    }
                });
            }
        });
        editEmployeeIds.on('item_remove', function(employee_id, item){
            let $theTr = $('#editFolderPermissionModal #employeeFolderPermission_'+employee_id);
            $theTr.remove();

            var permissionTrLength = $('#editFolderPermissionModal .folderPermissionTable').find('.permissionEmployeeRow').length;
            if(permissionTrLength == 0){
                $('#editFolderPermissionModal .folderPermissionTable').find('.noticeTr').fadeIn();
            }else{
                $('#editFolderPermissionModal .folderPermissionTable').find('.noticeTr').fadeOut();
            }
        });
    }


    $('#addFolderModal').on('change', '.documentRoleAndPermission', function(e){
        let $thePermission = $(this);
        let thePermission = $thePermission.val();
        let $thePermissionRow = $thePermission.closest('.permissionEmployeeRow');
        let employee_id = $thePermissionRow.attr('data-employee');

        axios({
            method: "post",
            url: route('file.manager.get.permission.set'),
            data: {employee_id : employee_id, role_permission_id : thePermission},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#addFolderModal .leaveTableLoader').removeClass('active');
            if (response.status == 200) {
                let res = response.data.res;
                $thePermissionRow.find('.permissionCols').remove();;
                $thePermissionRow.append(res);

                createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            }
        }).catch(error => {
            $('#addFolderModal .leaveTableLoader').removeClass('active');
            if (error.response) {
                console.log('error');
            }
        });
    })

    $('#editFolderPermissionModal').on('change', '.documentRoleAndPermission', function(e){
        let $thePermission = $(this);
        let thePermission = $thePermission.val();
        let $thePermissionRow = $thePermission.closest('.permissionEmployeeRow');
        let employee_id = $thePermissionRow.attr('data-employee');

        axios({
            method: "post",
            url: route('file.manager.get.permission.set'),
            data: {employee_id : employee_id, role_permission_id : thePermission},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#editFolderPermissionModal .leaveTableLoader').removeClass('active');
            if (response.status == 200) {
                let res = response.data.res;
                $thePermissionRow.find('.permissionCols').remove();;
                $thePermissionRow.append(res);

                createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            }
        }).catch(error => {
            $('#editFolderPermissionModal .leaveTableLoader').removeClass('active');
            if (error.response) {
                console.log('error');
            }
        });
    })


    $('#addFolderForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addFolderForm');
        var parent_id = $form.find('input[name="parent_id"]').val();
    
        document.querySelector('#createFolder').setAttribute('disabled', 'disabled');
        document.querySelector("#createFolder svg").style.cssText ="display: inline-block;";

        var userLengt = $('#addFolderModal .folderPermissionTable').find('.permissionEmployeeRow').length;

        if(userLengt == 0 && parent_id == 0){
            $form.find('.modError').remove();
            $('.modal-content', $form).prepend('<div class="modError alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Please add some user and set permissions.</div>');
            
            createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            
            setTimeout(function(){
                $form.find('.modError').remove();
            }, 2000);

            document.querySelector('#createFolder').removeAttribute('disabled');
            document.querySelector("#createFolder svg").style.cssText = "display: none;";
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('file.manager.create.folder'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#createFolder').removeAttribute('disabled');
                document.querySelector("#createFolder svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addFolderModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Document folder successfully created.');
                    }); 
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#createFolder').removeAttribute('disabled');
                document.querySelector("#createFolder svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addFolderForm .${key}`).addClass('border-danger');
                            $(`#addFolderForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    $('.folderWrap').on('dblclick', function(){
        window.location.href = $(this).attr('data-href');
    });

    $('.editFolder').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        axios({
            method: "post",
            url: route('file.manager.edit.folder'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                let row = response.data.res;

                $('#editFolderModal [name="name"]').val(row.name);
                $('#editFolderModal [name="folder_id"]').val(row.id);
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#editFolderForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editFolderForm');
    
        document.querySelector('#updateFolder').setAttribute('disabled', 'disabled');
        document.querySelector("#updateFolder svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('file.manager.update.folder'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateFolder').removeAttribute('disabled');
            document.querySelector("#updateFolder svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editFolderModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Document folder successfully updated.');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateFolder').removeAttribute('disabled');
            document.querySelector("#updateFolder svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editFolderForm .${key}`).addClass('border-danger');
                        $(`#editFolderForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.editPermission').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        axios({
            method: "post",
            url: route('file.manager.edit.folder.permission'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                let row = response.data;
                let employee_ids = row.emp ? row.emp : [];
                $('#editFolderPermissionModal [name="folder_id"]').val(row_id);

                if(row.htm != ''){
                    $('#editFolderPermissionModal .folderPermissionTable').find('.noticeTr').fadeOut('fast', function(){
                        $('#editFolderPermissionModal .folderPermissionTable tbody').append(row.htm);
                    });
    
                    createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
                }else{
                    $('#editFolderPermissionModal .folderPermissionTable').find('.noticeTr').fadeIn();
                }

                setTimeout(function(){
                    if(employee_ids.length > 0){
                        for (var employee_id of employee_ids) {
                            editEmployeeIds.addItem(employee_id, true);
                        }
                    }else{
                        editEmployeeIds.clear(true); 
                    }
                }, 500);
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#editFolderPermissionForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editFolderPermissionForm');
    
        document.querySelector('#updateFolderPermission').setAttribute('disabled', 'disabled');
        document.querySelector("#updateFolderPermission svg").style.cssText ="display: inline-block;";

        var userLengt = $('#editFolderPermissionModal .folderPermissionTable').find('.permissionEmployeeRow').length;

        if(userLengt == 0 && !$('#editFolderPermissionForm #permission_inheritence').prop('checked')){
            $form.find('.modError').remove();
            $('.modal-content', $form).prepend('<div class="modError alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Please add some user and set permissions.</div>');
            
            createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            
            setTimeout(function(){
                $form.find('.modError').remove();
            }, 2000);

            document.querySelector('#updateFolderPermission').removeAttribute('disabled');
            document.querySelector("#updateFolderPermission svg").style.cssText = "display: none;";
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('file.manager.update.folder.permission'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateFolderPermission').removeAttribute('disabled');
                document.querySelector("#updateFolderPermission svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    editFolderPermissionModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Document folder permission successfully updated.');
                    }); 
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#updateFolderPermission').removeAttribute('disabled');
                document.querySelector("#updateFolderPermission svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editFolderPermissionForm .${key}`).addClass('border-danger');
                            $(`#editFolderPermissionForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    $('.deleteFolder').on('click', function(e){
        e.preventDefault();
        let $theLink = $(this);
        var row_id = $theLink.attr('data-id');
        var row_name = $theLink.attr('data-name');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this folder "'+row_name+'"? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-id', row_id);
            $("#confirmModal .agreeWith").attr('data-action', 'DELETEFLDR');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let row_id = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEFLDR'){
            axios({
                method: 'DELETE',
                url: route('file.manager.destroy.folder'),
                data: {row_id : row_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Folder successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    });




    /* File Upload Code Start */
    const addFileModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addFileModal"));
    const editFileModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFileModal"));
    const uploadFileVersionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadFileVersionModal"));
    const fileHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#fileHistoryModal"));
    const editFilePermissionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFilePermissionModal"));
    const addTagModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTagModal"));

    const addFileModalEl = document.getElementById('addFileModal')
    addFileModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addFileModal .acc__input-error').html('');
        $('#addFileModal .modal-body input:not([type="checkbox"]):not([type="radio"])').val('');
        $('#addFileModal .modal-body textarea').val('');
        $('#addFileModal #addDocumentName').html('');
        $('#addFileModal input#file_type_1').prop('checked', true);

        $('#addFileModal .fileTagsWrap').removeClass('opened');
        $('#addFileModal .fileTagsWrap .fileTag').remove();
        $('#addFileModal .fileTagsWrap .tag_search').val('');
        $('#addFileModal .fileTagsWrap .autoFillDropdown').fadeOut().val();
    });

    const editFileModalEl = document.getElementById('editFileModal')
    editFileModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editFileModal .acc__input-error').html('');
        $('#editFileModal .modal-body input:not([type="checkbox"]):not([type="radio"])').val('');
        $('#editFileModal .modal-footer input[name="id"]').val('0');
        $('#editFileModal input#edit_file_type_1').prop('checked', true);

        $('#editFileModal .fileTagsWrap').removeClass('opened');
        $('#editFileModal .fileTagsWrap .fileTag').remove();
        $('#editFileModal .fileTagsWrap .tag_search').val('');
        $('#editFileModal .fileTagsWrap .autoFillDropdown').fadeOut().val();
    });

    const uploadFileVersionModalEl = document.getElementById('uploadFileVersionModal')
    uploadFileVersionModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#uploadFileVersionModal .acc__input-error').html('');
        $('#uploadFileVersionModal .modal-body input:not([type="checkbox"])').val('');
        $('#uploadFileVersionModal .modal-footer input[name="id"]').val('0');
        $('#uploadFileVersionModal #editDocumentName').html('');
    });

    const editFilePermissionModalEl = document.getElementById('editFilePermissionModal')
    editFilePermissionModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editFilePermissionModal .filePermissionTable').find('.permissionEmployeeRow').remove();
        $('#editFilePermissionModal .filePermissionTable').find('.noticeTr').fadeIn();
        if($('#edit_file_employee_ids').length > 0){
            editFileEmployeeIds.clear(true);
        }

        $('#editFilePermissionModal .modal-footer input[name="document_info_id"]').val('0');
    });

    const fileHistoryModalEl = document.getElementById('fileHistoryModal')
    fileHistoryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#fileVersionHistoryListTable').attr('data-fileinfo', '0').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role').html('');
        $('#fileHistoryModal .displayName').html('');
    });

    const addTagModalEl = document.getElementById('addTagModal')
    addTagModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addTagModal .acc__input-error').html('');
        $('#addTagModal input').val('');
    });
    
    $('#addFileModal').on('change', '#addDocument', function(){
        showFileName('addDocument', 'addDocumentName');
    });
    
    $('#uploadFileVersionModal').on('change', '#editDocument', function(){
        showFileName('editDocument', 'editDocumentName');
    });

    function showFileName(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = fileInput.files[0].name;
        namePreview.innerText = fileName;
        return false;
    };

    if($('#edit_file_employee_ids').length > 0){
        editFileEmployeeIds.on('item_add', function(employee_id, item){
            let existRow = $('#editFilePermissionModal').find('#employeeFolderPermission_'+employee_id).length;
            
            if(existRow == 0){
                axios({
                    method: "post",
                    url: route('file.manager.get.employee.permission.set'),
                    data: {employee_id : employee_id},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    $('#editFilePermissionModal .leaveTableLoader').removeClass('active');
                    if (response.status == 200) {
                        let res = response.data.res;
                        $('#editFilePermissionForm .filePermissionTable').find('.noticeTr').fadeOut('fast', function(){
                            $('#editFilePermissionForm .filePermissionTable tbody').append(res);
                        });

                        createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
                    }
                }).catch(error => {
                    $('#editFilePermissionModal .leaveTableLoader').removeClass('active');
                    if (error.response) {
                        console.log('error');
                    }
                });
            }
        });

        editFileEmployeeIds.on('item_remove', function(employee_id, item){
            let $theTr = $('#editFilePermissionModal #employeeFolderPermission_'+employee_id);
            $theTr.remove();

            var permissionTrLength = $('#editFilePermissionModal .filePermissionTable').find('.permissionEmployeeRow').length;
            if(permissionTrLength == 0){
                $('#editFilePermissionModal .filePermissionTable').find('.noticeTr').fadeIn();
            }else{
                $('#editFilePermissionModal .filePermissionTable').find('.noticeTr').fadeOut();
            }
        });
    }

    let currentRequest = null;
    $('.tag_search').on('keyup', function(e){
        e.preventDefault();
        var $theSearch = $(this);
        var $theFilterWrap = $theSearch.parent('.fileTagsWrap');
        var $theDropdown = $theSearch.siblings('.autoFillDropdown');

        var query = $theSearch.val();
        if(query.length >= 3){
            currentRequest = $.ajax({
                type: 'POST',
                data: {querystr : query},
                url: route("file.manager.search.tags"),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                beforeSend : function()    {           
                    if(currentRequest != null) {
                        currentRequest.abort();
                        $theDropdown.fadeOut().html('');
                    }
                },
                success: function(data) {
                    $theFilterWrap.addClass('opened');
                    $theDropdown.fadeIn().html(data.htm);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                },
                error:function(e){
                    console.log('Error');
                }
            });
        }else{
            $theDropdown.fadeOut().html('');
            if(currentRequest != null) {
                currentRequest.abort();
            }
        }
    });

    $(document).on('click', '.addNewTagBtn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var theStr = $theBtn.attr('data-str');

        document.getElementById("addTagModal").addEventListener("shown.tw.modal", function (event) {
            $("#addTagModal input[name='name']").val(theStr);
        }); 
    });

    $(document).on('click', '.selectableTag', function(e){
        e.preventDefault();
        var $theTag = $(this);
        var tagName = $theTag.html();
        var tagId = $theTag.attr('href');

        var html = '';
        html += '<div class="fileTag">';
            html += '<span>'+tagName+'</span>';
            html += '<button type="button" class="removeTag"><i data-lucide="x" class="w-3 h-3"></i></button>';
            html += '<input type="hidden" name="tag_ids[]" value="'+tagId+'"/>';
        html += '</div>';

        $('.fileTagsWrap.opened').prepend(html);
        $('.fileTagsWrap.opened .autoFillDropdown').fadeOut().html('');
        $('.fileTagsWrap.opened .tag_search').val('');

        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    })

    $(document).on('click', '.removeTag', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        $theBtn.parent('.fileTag').remove();
    })

    $('#addTagForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addTagForm');
    
        document.querySelector('#addTag').setAttribute('disabled', 'disabled');
        document.querySelector("#addTag svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('file.manager.store.tags'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#addTag').removeAttribute('disabled');
            document.querySelector("#addTag svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addTagModal.hide();
                var html = response.data.htm;
                if(html != ''){
                    $('.fileTagsWrap.opened').prepend(html);
                    $('.fileTagsWrap.opened .autoFillDropdown').fadeOut().html('');
                    $('.fileTagsWrap.opened .tag_search').val('');
                }
            }
        }).catch(error => {
            document.querySelector('#addTag').removeAttribute('disabled');
            document.querySelector("#addTag svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addTagForm .${key}`).addClass('border-danger');
                        $(`#addTagForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    })

    $('#addFileModal').on('change', '.documentRoleAndPermission', function(e){
        let $thePermission = $(this);
        let thePermission = $thePermission.val();
        let $thePermissionRow = $thePermission.closest('.permissionEmployeeRow');
        let employee_id = $thePermissionRow.attr('data-employee');

        axios({
            method: "post",
            url: route('file.manager.get.permission.set'),
            data: {employee_id : employee_id, role_permission_id : thePermission},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#addFileModal .leaveTableLoader').removeClass('active');
            if (response.status == 200) {
                let res = response.data.res;
                $thePermissionRow.find('.permissionCols').remove();;
                $thePermissionRow.append(res);

                createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            }
        }).catch(error => {
            $('#addFileModal .leaveTableLoader').removeClass('active');
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#editFilePermissionModal').on('change', '.documentRoleAndPermission', function(e){
        let $thePermission = $(this);
        let thePermission = $thePermission.val();
        let $thePermissionRow = $thePermission.closest('.permissionEmployeeRow');
        let employee_id = $thePermissionRow.attr('data-employee');

        axios({
            method: "post",
            url: route('file.manager.get.permission.set'),
            data: {employee_id : employee_id, role_permission_id : thePermission},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#editFilePermissionModal .leaveTableLoader').removeClass('active');
            if (response.status == 200) {
                let res = response.data.res;
                $thePermissionRow.find('.permissionCols').remove();;
                $thePermissionRow.append(res);

                createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            }
        }).catch(error => {
            $('#editFilePermissionModal .leaveTableLoader').removeClass('active');
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#addFileForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addFileForm');
    
        document.querySelector('#uploadFile').setAttribute('disabled', 'disabled');
        document.querySelector("#uploadFile svg").style.cssText ="display: inline-block;";
        
        let form_data = new FormData(form);
        form_data.append('file', $('#addFileForm input[name="document"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('file.manager.upload.file'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#uploadFile').removeAttribute('disabled');
            document.querySelector("#uploadFile svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addFileModal.hide();
                var suc = response.data.suc;

                if(suc == 1){
                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('File successfully uploaded.');
                    }); 
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }else{
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .sarningModalTitle").html( "Oops!" );
                        $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact with site administrator.');
                    }); 
                    
                    setTimeout(function(){
                        warningModal.hide();
                    }, 2000);
                }
            }
        }).catch(error => {
            document.querySelector('#uploadFile').removeAttribute('disabled');
            document.querySelector("#uploadFile svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addFileForm .${key}`).addClass('border-danger');
                        $(`#addFileForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.fileWrap').on('dblclick', function(){
        window.location.href = $(this).attr('data-href');
    });

    $(document).on('click', '.editFile', function(e){
        e.preventDefault();
        let $theLink = $(this);
        let row_id = $theLink.attr('data-id');

        axios({
            method: "post",
            url: route('file.manager.get.file.data'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                var row = response.data.res;
                $('#editFileModal [name="name"]').val(row.display_file_name ? row.display_file_name : '');
                $('#editFileModal [name="expire_at"]').val(row.expire_at ? row.expire_at : '');
                $('#editFileModal [name="description"]').val(row.description ? row.description : '');
                $('#editFileModal [name="id"]').val(row_id);
                if(row.file_type == 2){
                    $('#editFileModal #edit_file_type_2').prop('checked', true);
                }else{
                    $('#editFileModal #edit_file_type_1').prop('checked', true);
                }

                if(row.tags_html != ''){
                    $('#editFileModal .fileTagsWrap').prepend(row.tags_html);
                }else{
                    $('#editFileModal .fileTagsWrap .fileTag').remove();
                }

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#editFileForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editFileForm');
    
        document.querySelector('#updateFile').setAttribute('disabled', 'disabled');
        document.querySelector("#updateFile svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('file.manager.update.file'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateFile').removeAttribute('disabled');
            document.querySelector("#updateFile svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editFileModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('File Information successfully updated.');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateFile').removeAttribute('disabled');
            document.querySelector("#updateFile svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editFileForm .${key}`).addClass('border-danger');
                        $(`#editFileForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $(document).on('click', '.uploadNewVersion', function(e){
        e.preventDefault();
        let $theLink = $(this);
        let row_id = $theLink.attr('data-id');

        $('#uploadFileVersionModal [name="id"]').val(row_id);
    });

    $('#uploadFileVersionForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('uploadFileVersionForm');
    
        document.querySelector('#uploadNV').setAttribute('disabled', 'disabled');
        document.querySelector("#uploadNV svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#uploadFileVersionForm input[name="document"]')[0].files[0]); 
        
        axios({
            method: "post",
            url: route('file.manager.upload.new.version'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#uploadNV').removeAttribute('disabled');
            document.querySelector("#uploadNV svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                uploadFileVersionModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Document new version successfully uploaded.');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#uploadNV').removeAttribute('disabled');
            document.querySelector("#uploadNV svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#uploadFileVersionForm .${key}`).addClass('border-danger');
                        $(`#uploadFileVersionForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $(document).on('click', '.versionHistory', function(e){
        let $theLink = $(this);
        let file_id = $theLink.attr('data-id');
        let file_name = $theLink.attr('data-name');

        $('#fileHistoryModal .displayName').html(file_name);
        $('#fileHistoryModal #fileVersionHistoryListTable').attr('data-fileinfo', file_id);
        
        fileVersionHistoryListTable.init(file_id);

    });

    $('#fileVersionHistoryListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var row_id = $theBtn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Do you really want to restore this version? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-id', row_id);
            $("#confirmModal .agreeWith").attr('data-action', 'RESTOREVER');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let row_id = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'RESTOREVER'){
            axios({
                method: 'POST',
                url: route('file.manager.file.restore.version'),
                data: {row_id : row_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    var document_info_id = response.data.did;
                    fileVersionHistoryListTable.init(document_info_id);

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Document version successfully restored');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'DELETEFILE'){
            axios({
                method: 'delete',
                url: route('file.manager.destroy.file'),
                data: {row_id : row_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Selected file successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    });


    $('.editFilePermission').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        axios({
            method: "post",
            url: route('file.manager.edit.file.permission'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                let row = response.data;
                
                let employee_ids = row.emp ? row.emp : [];
                $('#editFilePermissionModal [name="document_info_id"]').val(row_id);

                if(row.htm != ''){
                    $('#editFilePermissionModal .filePermissionTable').find('.noticeTr').fadeOut('fast', function(){
                        $('#editFilePermissionModal .filePermissionTable tbody').append(row.htm);
                    });
    
                    createIcons({icons,"stroke-width": 1.5, nameAttr: "data-lucide",});
                }else{
                    $('#editFilePermissionModal .filePermissionTable').find('.noticeTr').fadeIn();
                }

                setTimeout(function(){
                    if(employee_ids.length > 0){
                        for (var employee_id of employee_ids) {
                            editFileEmployeeIds.addItem(employee_id, true);
                        }
                    }else{
                        editFileEmployeeIds.clear(true); 
                    }
                }, 500);
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#editFilePermissionForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editFilePermissionForm');
    
        document.querySelector('#updateFilePermission').setAttribute('disabled', 'disabled');
        document.querySelector("#updateFilePermission svg").style.cssText ="display: inline-block;";

        var userLengt = $('#editFilePermissionModal .filePermissionTable').find('.permissionEmployeeRow').length;

        if(userLengt == 0){
            $form.find('.modError').remove();
            $('.modal-content', $form).prepend('<div class="modError alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Please add some user and set permissions.</div>');
            
            createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            
            setTimeout(function(){
                $form.find('.modError').remove();
            }, 2000);

            document.querySelector('#updateFilePermission').removeAttribute('disabled');
            document.querySelector("#updateFilePermission svg").style.cssText = "display: none;";
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('file.manager.update.file.permission'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateFilePermission').removeAttribute('disabled');
                document.querySelector("#updateFilePermission svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    editFilePermissionModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Document permission successfully updated.');
                    }); 
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#updateFilePermission').removeAttribute('disabled');
                document.querySelector("#updateFilePermission svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editFilePermissionForm .${key}`).addClass('border-danger');
                            $(`#editFilePermissionForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    $('.fileReminderBtn').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let row_id = $theBtn.attr('data-id');
        let displayName = $theBtn.attr('data-name');

        $('#fileReminderModal .displayName').html(displayName);
        $('#fileReminderModal [name="document_info_id"]').val(row_id);

        axios({
            method: "post",
            url: route('file.manager.edit.file.reminder'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                var row = response.data.row;
                $('#fileReminderModal [name="subject"]').val(row.subject ? row.subject : '');
                $('#fileReminderModal [name="message"]').val(row.message ? row.message : '');

                if(row.is_repeat_reminder == 1){
                    $('#fileReminderModal [name="is_repeat_reminder"]').prop('checked', true);
                    $('#fileReminderModal .reminderSingleWrap').fadeOut('fast', function(){
                        $('#fileReminderModal [name="single_reminder_date"]').val('');
                    });
                    $('#fileReminderModal .reminderMultiWrap').fadeIn('fast', function(){
                        $('#fileReminderModal [name="frequency"]').val(row.frequency ? row.frequency : '');
                        $('#fileReminderModal [name="repeat_reminder_start"]').val(row.repeat_reminder_start ? row.repeat_reminder_start : '');
                        $('#fileReminderModal [name="repeat_reminder_end"]').val(row.repeat_reminder_end ? row.repeat_reminder_end : '');
                    });
                }else{
                    $('#fileReminderModal [name="is_repeat_reminder"]').prop('checked', false);
                    $('#fileReminderModal .reminderSingleWrap').fadeIn('fast', function(){
                        $('#fileReminderModal [name="single_reminder_date"]').val(row.single_reminder_date ? row.single_reminder_date : '');
                    });
                    $('#fileReminderModal .reminderMultiWrap').fadeOut('fast', function(){
                        $('select', this).val('');
                        $('input', this).val('');
                    });
                }
                if(row.is_send_email == 1){
                    $('#fileReminderModal [name="is_send_email"]').prop('checked', true);
                }else{
                    $('#fileReminderModal [name="is_send_email"]').prop('checked', false);
                }

                var employee_ids = row.employee_ids;
                if(employee_ids.length > 0){
                    for (var employee_id of employee_ids) {
                        reminderEmployeeIds.addItem(employee_id, true);
                    }
                }else{
                    reminderEmployeeIds.clear(true); 
                }

                $('#editFileModal [name="document_info_id"]').val(row_id);
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#fileReminderForm [name="is_repeat_reminder"]').on('change', function(e){
        let $checkBox = $(this);
        if($checkBox.prop('checked')){
            $('#fileReminderForm .reminderSingleWrap').fadeOut('fast', function(){
                $('input', this).val('');
            });
            $('#fileReminderForm .reminderMultiWrap').fadeIn('fast', function(){
                $('select', this).val('');
                $('input', this).val('');
            });
        }else{
            $('#fileReminderForm .reminderSingleWrap').fadeIn('fast', function(){
                $('input', this).val('');
            });
            $('#fileReminderForm .reminderMultiWrap').fadeOut('fast', function(){
                $('select', this).val('');
                $('input', this).val('');
            });
        }
    })

    $('#fileReminderForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('fileReminderForm');
    
        document.querySelector('#saveReminder').setAttribute('disabled', 'disabled');
        document.querySelector("#saveReminder svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('file.manager.store.file.reminder'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveReminder').removeAttribute('disabled');
            document.querySelector("#saveReminder svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                fileReminderModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Document reminder successfully save.');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveReminder').removeAttribute('disabled');
            document.querySelector("#saveReminder svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#fileReminderForm .${key}`).addClass('border-danger');
                        $(`#fileReminderForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.deleteFile').on('click', function(e){
        e.preventDefault();
        let $theLink = $(this);
        var row_id = $theLink.attr('data-id');
        var row_name = $theLink.attr('data-name');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this file "'+row_name+'"? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-id', row_id);
            $("#confirmModal .agreeWith").attr('data-action', 'DELETEFILE');
        });
    });
    /* File Upload Code END */

    /* Common Scripts START */
    $('body').on('contextmenu', '.folderWrap', function(e) {
        let id = $(this).attr('data-id');
        let name = $(this).attr('data-name');
        let create = $(this).attr('data-metac');
        let read = $(this).attr('data-metar');
        let update = $(this).attr('data-metau');
        let del = $(this).attr('data-metad');
        let parent = $(this).attr('data-parent');

        $('.fileDropdown').hide();
        if(create == 1 || update == 1 || read == 1 || del == 1){
            if(create == 1 && update == 1){
                $('.folderDropdown').find('li.editFolderLink').show('fast', function(){
                    $('.folderDropdown').find('li.editFolderLink a').attr('data-id', id);
                })
            }else{
                $('.folderDropdown').find('li.editFolderLink').hide('fast', function(){
                    $('.folderDropdown').find('li.editFolderLink a').attr('data-id', '0');
                })
            }
            if(create == 1 && update == 1 && parent == 0){
                $('.folderDropdown').find('li.editFolderPermissionLink').show('fast', function(){
                    $('.folderDropdown').find('li.editFolderPermissionLink a').attr('data-id', id);
                })
            }else{
                $('.folderDropdown').find('li.editFolderPermissionLink').hide('fast', function(){
                    $('.folderDropdown').find('li.editFolderPermissionLink a').attr('data-id', '0');
                })
            }
            if(del == 1){
                $('.folderDropdown').find('li.deleteFolderLink').show('fast', function(){
                    $('.folderDropdown').find('li.deleteFolderLink a').attr('data-id', id).attr('data-name', name);
                })
            }else{
                $('.folderDropdown').find('li.deleteFolderLink').hide('fast', function(){
                    $('.folderDropdown').find('li.deleteFolderLink a').attr('data-id', '0').attr('data-name', '');
                })
            }

            $('.folderDropdown').css({
                display: "block",
                left: e.clientX+'px',
                top: e.clientY+'px'
            });
        }
        return false;
   });
   $('html').on('click', function() {
        $('.folderDropdown').hide();
        $('.fileDropdown').hide();
    });
    $('.folderDropdown li a').on('click', function(e){
        var  f = $(this);
    });


    $('body').on('contextmenu', '.fileWrap', function(e) {
        let id = $(this).attr('data-id');
        let name = $(this).attr('data-name');
        let create = $(this).attr('data-metac');
        let read = $(this).attr('data-metar');
        let update = $(this).attr('data-metau');
        let del = $(this).attr('data-metad');
        let url = $(this).attr('data-url');
        let download = (url != '' ? 1 : 0);

        $('.folderDropdown').hide();
        if(create == 1 || update == 1 || read == 1 || del == 1){
            if(create == 1 || update == 1 || read == 1){
                $('.fileDropdown').find('li.downloadLink').show('fast', function(){
                    $('.fileDropdown').find('li.downloadLink a').attr('href', url).attr('download', true);
                })
            }else{
                $('.fileDropdown').find('li.downloadLink').hide('fast', function(){
                    $('.fileDropdown').find('li.downloadLink a').attr('href', 'javascript:void(0);').removeAttr('download');
                })
            }
            if(create == 1 && update == 1){
                $('.fileDropdown').find('li.editFileLink').show('fast', function(){
                    $('.fileDropdown').find('li.editFileLink a').attr('data-id', id).attr('data-name', name);
                })
            }else{
                $('.fileDropdown').find('li.editFileLink').hide('fast', function(){
                    $('.fileDropdown').find('li.editFileLink a').attr('data-id', '0').attr('data-name', '');
                })
            }
            if(create == 1 && update == 1){
                $('.fileDropdown').find('li.uploadVersionLink').show('fast', function(){
                    $('.fileDropdown').find('li.uploadVersionLink a').attr('data-id', id).attr('data-name', name);
                })
            }else{
                $('.fileDropdown').find('li.uploadVersionLink').hide('fast', function(){
                    $('.fileDropdown').find('li.uploadVersionLink a').attr('data-id', '0').attr('data-name', '');
                })
            }
            if(create == 1 && update == 1){
                $('.fileDropdown').find('li.versionHistoryLink').show('fast', function(){
                    $('.fileDropdown').find('li.versionHistoryLink a').attr('data-id', id).attr('data-name', name);
                })
            }else{
                $('.fileDropdown').find('li.versionHistoryLink').hide('fast', function(){
                    $('.fileDropdown').find('li.versionHistoryLink a').attr('data-id', '0').attr('data-name', '');
                })
            }
            /*if(create == 1 && update == 1 && read == 1){
                $('.fileDropdown').find('li.editPermissionLink').show('fast', function(){
                    $('.fileDropdown').find('li.editPermissionLink a').attr('data-id', id).attr('data-name', name);
                })
            }else{
                $('.fileDropdown').find('li.editPermissionLink').hide('fast', function(){
                    $('.fileDropdown').find('li.editPermissionLink a').attr('data-id', '0').attr('data-name', '');
                })
            }*/
            if(create == 1 && update == 1){
                $('.fileDropdown').find('li.reminderLink').show('fast', function(){
                    $('.fileDropdown').find('li.reminderLink a').attr('data-id', id).attr('data-name', name);
                })
            }else{
                $('.fileDropdown').find('li.reminderLink').hide('fast', function(){
                    $('.fileDropdown').find('li.reminderLink a').attr('data-id', '0').attr('data-name', '');
                })
            }
            if(del == 1){
                $('.fileDropdown').find('li.deleteFileLink').show('fast', function(){
                    $('.fileDropdown').find('li.deleteFileLink a').attr('data-id', id).attr('data-name', name);
                })
            }else{
                $('.fileDropdown').find('li.deleteFileLink').hide('fast', function(){
                    $('.fileDropdown').find('li.deleteFileLink a').attr('data-id', '0').attr('data-name', '');
                })
            }

            
            $('.fileDropdown').css({
                display: "block",
                left: e.clientX+'px',
                top: e.clientY+'px'
            });
        }
        return false;
   });
   $('html').on('click', function() {
        $('.fileDropdown').hide();
        $('.folderDropdown').hide();
    });
    $('.fileDropdown li a').on('click', function(e){
        var  f = $(this);
    });

    $(window).on('scroll', function(){
        $('.fileDropdown').hide();
        $('.folderDropdown').hide();
    })


    $('.fileManagerViewToggle > button').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);

        if(!$theBtn.hasClass('active')){
            if($theBtn.hasClass('btn-list')){
                $('.fileManagerViewToggle .btn-grid').removeClass('active');
                $theBtn.addClass('active');
                $('.folderGridWrap').removeClass('activeGrid').addClass('activeList')
            }else{
                $('.fileManagerViewToggle .btn-list').removeClass('active');
                $theBtn.addClass('active');
                $('.folderGridWrap').removeClass('activeList').addClass('activeGrid');
            }
        }
    })
    /* Common Scripts END */

})();