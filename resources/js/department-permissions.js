import { initPermissionPickers, bindPermissionToggles, initPermissionBulkToggles } from "./permission-form";

(function(){
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    initPermissionPickers();
    bindPermissionToggles();
    initPermissionBulkToggles();

    /* Tracks boxes ticked since the last save. Adding a sub department reloads
       the page to draw its permission tree, which would silently throw those
       away — the add dialog warns while this is set. */
    let hasUnsavedChanges = false;

    /* After a save the stored templates match the form, so the department
       counts can be recomputed from it without a reload. Mirrors store(): a
       sub department is saved when anything it posts is non-empty — a ticked
       box, a chosen accounts type or a filled date range. Disabled fields are
       not posted, and the __present marker is always empty. */
    const categoryHasSavedSet = (category) =>
        Array.from(category.querySelectorAll('input[name], select[name]')).some((el) => {
            if (el.disabled || el.name.endsWith('[__present]')) return false;
            if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
            return (el.value || '').trim() !== '';
        });

    const refreshDepartmentCounts = () => {
        document.querySelectorAll('.ss-perm-list > .accordion > .accordion-item').forEach((dept) => {
            const badge = dept.querySelector(':scope > .accordion-header [data-dept-count]');
            const categories = dept.querySelectorAll('.ss-perm-category');
            if (!badge || categories.length === 0) return;

            const total = categories.length;
            const saved = Array.from(categories).filter(categoryHasSavedSet).length;

            badge.textContent = `${saved} of ${total} sub ${total === 1 ? 'department' : 'departments'} saved`;
            badge.setAttribute('data-state', saved === 0 ? 'none' : (saved === total ? 'all' : 'some'));
        });
    };
    $('#permissionUpdateForm').on('change', 'input, select', function(){
        hasUnsavedChanges = true;
    });

    $('#permissionUpdateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('permissionUpdateForm');
    
        document.querySelector('#savePermissionsBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#savePermissionsBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('permissions.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#savePermissionsBtn').removeAttribute('disabled');
            document.querySelector("#savePermissionsBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                hasUnsavedChanges = false;
                refreshDepartmentCounts();
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Permissions Successfully Updated.');
                });     
            }
        }).catch(error => {
            document.querySelector('#savePermissionsBtn').removeAttribute('disabled');
            document.querySelector("#savePermissionsBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#permissionUpdateForm .${key}`).addClass('border-danger');
                        $(`#permissionUpdateForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    /* ---- Add sub department (add only) -------------------------------- */

    const subDeptModalEl = document.querySelector('#addSubDepartmentModal');
    if (subDeptModalEl) {
        const subDeptModal = tailwind.Modal.getOrCreateInstance(subDeptModalEl);
        const $form = $('#addSubDepartmentForm');
        const $saveBtn = $('#saveSubDepartmentBtn');

        const setBusy = (busy) => {
            $saveBtn.prop('disabled', busy);
            $saveBtn.find('.ss-spinner').css('display', busy ? 'inline-block' : 'none');
        };

        const resetForm = () => {
            $form[0].reset();
            $form.find('.acc__input-error').html('');
            $form.find('.border-danger').removeClass('border-danger');
            setBusy(false);
        };

        // Department is fixed by the row the dialog was opened from.
        $(document).on('click', '[data-add-subdepartment]', function(e){
            e.preventDefault();
            e.stopPropagation();

            resetForm();
            $form.find('input[name="department_id"]').val($(this).attr('data-department-id'));
            $('#subDepartmentDepartmentName').text($(this).attr('data-department-name'));
            $('#subDepartmentUnsavedWarning').prop('hidden', !hasUnsavedChanges);

            subDeptModal.show();
        });

        subDeptModalEl.addEventListener('shown.tw.modal', function(){
            $('#sub_department_name').trigger('focus');
        });

        subDeptModalEl.addEventListener('hide.tw.modal', resetForm);

        $form.on('submit', function(e){
            e.preventDefault();
            setBusy(true);

            axios({
                method: 'post',
                url: route('permissioncategory.store'),
                data: new FormData($form[0]),
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then(() => {
                // The new sub department needs its full permission tree drawn,
                // which the server renders — reload rather than rebuild it here.
                window.location.reload();
            }).catch(error => {
                setBusy(false);

                if (error.response && error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors || {})) {
                        $form.find(`.${key}`).addClass('border-danger');
                        $form.find(`.error-${key}`).html(Array.isArray(val) ? val[0] : val);
                    }
                } else {
                    $form.find('.error-name').html('That could not be saved. Please try again.');
                    console.log(error);
                }
            });
        });
    }
})()