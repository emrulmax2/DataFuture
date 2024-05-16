import TomSelect from "tom-select";

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

    var employeeIDS = new TomSelect('#employee_ids', tomOptions);
    var editEmployeeIds = new TomSelect('#edit_employee_ids', tomOptions);


    const addFolderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addFolderModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const editFolderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFolderModal"));
    const editFolderPermissionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFolderPermissionModal"));
    let confModalDelTitle = 'Are you sure?';

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
            employeeIDS.clear(true);
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
        editEmployeeIds.clear(true);

        $('#editFolderPermissionModal .modal-footer input[name="id"]').val('0');
    });


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

    editEmployeeIds.on('item_add', function(employee_id, item){
        let folder_id = $('#editFolderPermissionModal [name="folder_id"]').val();
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

    $('#addFolderModal #permission_inheritence').on('change', function(){
        if($(this).prop('checked')){
            $('#addFolderModal .permission_inheritence_label').html('Yes');
            $('#addFolderModal .permissionWrap').fadeOut('fast', function(){
                $('#addFolderModal .folderPermissionTable').find('.permissionEmployeeRow').remove();
                $('#addFolderModal .folderPermissionTable').find('.noticeTr').fadeIn();
                employeeIDS.clear(true);
            });
        }else{
            $('#addFolderModal .permission_inheritence_label').html('No');
            $('#addFolderModal .permissionWrap').fadeIn('fast', function(){
                $('#addFolderModal .folderPermissionTable').find('.permissionEmployeeRow').remove();
                $('#addFolderModal .folderPermissionTable').find('.noticeTr').fadeIn();
                employeeIDS.clear(true);
            });
        }
    })

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
    
        document.querySelector('#createFolder').setAttribute('disabled', 'disabled');
        document.querySelector("#createFolder svg").style.cssText ="display: inline-block;";

        var userLengt = $('#addFolderModal .folderPermissionTable').find('.permissionEmployeeRow').length;

        if(userLengt == 0 && !$('#addFolderForm #permission_inheritence').prop('checked')){
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

                if(employee_ids.length > 0){
                    for (var employee_id of employee_ids) {
                        editEmployeeIds.addItem(employee_id, true);
                    }
                }else{
                    editEmployeeIds.clear(true); 
                }

                if(row.htm != ''){
                    $('#editFolderPermissionModal .folderPermissionTable').find('.noticeTr').fadeOut('fast', function(){
                        //$('#editFolderPermissionModal .folderPermissionTable tbody').append(row.htm);
                    });
    
                    createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
                }else{
                    $('#editFolderPermissionModal .folderPermissionTable').find('.noticeTr').fadeIn();
                }
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
    })

})();