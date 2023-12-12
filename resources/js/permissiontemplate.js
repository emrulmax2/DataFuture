import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";


(function () {
    const permissionTemplateAccr = tailwind.Accordion.getInstance(document.querySelector("#permissionTemplateAccr"));
    const permissionCategoryDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#permissionCategoryDropdown"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addPermissionGroupModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPermissionGroupModal"));
    let confModalDelTitle = 'Are you sure?';

    document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
        $('#confirmModal .agreeWith').attr('data-id', '0');
        $('#confirmModal .agreeWith').attr('data-action', 'none');
    });

    const addPermissionGroupModalEl = document.getElementById('addPermissionGroupModal')
    addPermissionGroupModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addPermissionGroupModal .acc__input-error').html('');
        $('#addPermissionGroupModal input[name="name"]').val('');
        $('#addPermissionGroupModal input[type="checkbox"]').prop('checked', false);
        $('#addPermissionGroupModal input[name="permission_template_id"]').val('0');
    });

    $('#closePCDropdown').on('click', function(e){
        e.preventDefault();
        permissionCategoryDropdown.hide();
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    /* Assign Category Form Submission */
    $('#assignPermissionCategoryForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('assignPermissionCategoryForm');
    
        document.querySelector('#addPermissionCat').setAttribute('disabled', 'disabled');
        document.querySelector("#addPermissionCat svg.theLoader").style.cssText ="display: inline-block;";

        var permission_category_ids = [];
        var role_id = $('input[name="role_id"]', $form).val();
        $form.find('.permission_category_id').each(function(){
            if($(this).prop('checked')){
                permission_category_ids.push($(this).val());
            }
        });

        if(permission_category_ids.length > 0){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('permissiontemplate.store'),
                data: {permission_category_ids : permission_category_ids, role_id : role_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#addPermissionCat').removeAttribute('disabled');
                    document.querySelector("#addPermissionCat svg.theLoader").style.cssText = "display: none;";

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html(response.data.message);
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#addPermissionCat').removeAttribute('disabled');
                document.querySelector("#addPermissionCat svg.theLoader").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html("Error Found!" );
                            $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact administrator.');
                        });
                        setTimeout(function(){
                            warningModal.hide();
                        }, 2000);
                    } else {
                        console.log('error');
                    }
                }
            });
        }else{
            document.querySelector('#addPermissionCat').removeAttribute('disabled');
            document.querySelector("#addPermissionCat svg.theLoader").style.cssText = "display: none;";

            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html("Error Found!" );
                $("#warningModal .warningModalDesc").html('You have to select at least one category to continue.');
            });

            setTimeout(function(){
                warningModal.hide();
            }, 2000);
        }
        
    });
    /* Assign Category Form Submission */
    
    /*Insert or Delete Permission Category in Template */
    $('.is_inserted').on('click', function(e){
        e.preventDefault();
        var $input = $(this);
        var permissionTemplateId = $input.val();

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('You really want to update this category status? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', permissionTemplateId);
            $('#confirmModal .agreeWith').attr('data-action', 'UPDATEPCAT');
        });

    });
    /*Insert or Delete Permission Category in Template */

    /* Confirm Modal Action */
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');
        let roleID = $agreeBTN.attr('data-role');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'UPDATEPCAT'){
            axios({
                method: 'post',
                url: route('permissiontemplate.update'),
                data: {recordID : recordID, roleID : roleID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    if(response.data.res == 2){
                        $('#permission_category_id_'+recordID).prop('checked', false);
                        $('#permission_category_btn_'+recordID).attr('disabled', 'disabled');
                    }else{
                        $('#permission_category_id_'+recordID).prop('checked', true);
                        $('#permission_category_btn_'+recordID).removeAttr('disabled');
                    }
                    //permissionTemplateAccr.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('DONE!');
                        $('#successModal .successModalDesc').html('Permission template category status successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
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
    /* Confirm Modal Action */




    $('.addPermissionGroupBtn').on('click', function(e){
        var $input = $(this);
        var permission_template_id = $input.attr('data-template');
        $('#addPermissionGroupModal input[name="permission_template_id"]').val(permission_template_id);
    })


    $('#addPermissionGroupForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addPermissionGroupForm');
    
        document.querySelector('#savePermissionGroup').setAttribute('disabled', 'disabled');
        document.querySelector("#savePermissionGroup svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('permissiontemplate.group.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#savePermissionGroup').removeAttribute('disabled');
            document.querySelector("#savePermissionGroup svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addPermissionGroupModal.hide();
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Permission template group access details successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#savePermissionGroup').removeAttribute('disabled');
            document.querySelector("#savePermissionGroup svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addPermissionGroupForm .${key}`).addClass('border-danger')
                        $(`#addPermissionGroupForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})();