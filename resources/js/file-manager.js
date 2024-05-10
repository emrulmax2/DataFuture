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


    const addFolderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addFolderModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
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
        $('#addFolderModal .modal-body input').val('');
        $('#addFolderModal .folderPermissionTable').find('.permissionEmployeeRow').remove();
        $('#addFolderModal .folderPermissionTable').find('.noticeTr').fadeIn();
        employeeIDS.clear(true);
    });


    employeeIDS.on('item_add', function(employee_id, item){
        axios({
            method: "post",
            url: route('file.manager.get.employee.permission.set'),
            data: {employee_id : employee_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('.leaveTableLoader').removeClass('active');
            if (response.status == 200) {
                let res = response.data.res;
                $('#addFolderForm .folderPermissionTable').find('.noticeTr').fadeOut('fast', function(){
                    $('#addFolderForm .folderPermissionTable tbody').append(res);
                });

                createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            }
        }).catch(error => {
            $('.leaveTableLoader').removeClass('active');
            if (error.response) {
                console.log('error');
            }
        });
    });
    employeeIDS.on('item_remove', function(employee_id, item){
        let $theTr = $('#employeeFolderPermission_'+employee_id);
        $theTr.remove();

        var permissionTrLength = $('#addFolderModal .folderPermissionTable').find('.permissionEmployeeRow').length;
        if(permissionTrLength == 0){
            $('#addFolderModal .folderPermissionTable').find('.noticeTr').fadeIn();
        }else{
            $('#addFolderModal .folderPermissionTable').find('.noticeTr').fadeOut();
        }
    });

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
            $('.leaveTableLoader').removeClass('active');
            if (response.status == 200) {
                let res = response.data.res;
                $thePermissionRow.find('.permissionCols').remove();;
                $thePermissionRow.append(res);

                createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide",});
            }
        }).catch(error => {
            $('.leaveTableLoader').removeClass('active');
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

        if(userLengt == 0){
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

    /*$(".folderWrap").on('click', function(e) {
        if (!$(e.target).hasClass('dropdown') && !$(e.target).hasClass('dropdown-toggle') && !$(e.target).hasClass('dropdownSVG')) {
            window.location.href = $(this).attr('data-href');
        }
    });*/

    $('.folderWrap').on('dblclick', function(){
        window.location.href = $(this).attr('data-href');
    });

})();