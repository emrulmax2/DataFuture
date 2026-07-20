import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";
import { initPermissionPickers, bindPermissionToggles } from "./permission-form";
import { initPrivilegeUi } from "./privilege-ui";


(function(){

    initPermissionPickers();
    bindPermissionToggles();
    initPrivilegeUi();

    let tomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Search Here...',
            dropdownParent: 'body',
            dropdownClass: 'ts-dropdown lcc-tom-float',
            persist: false,
            // The options are real departments, so an ad-hoc entry could never
            // resolve to a template.
            create: false,
            allowEmptyOption: true,
        };

    var department_id_select = new TomSelect('#department_id_select', tomOptions);

    // The privileges toolbar below is sticky and sits in a higher stacking
    // context, so the open menu would render behind it. Backs up the :has() rule.
    const templateBar = document.querySelector('.ep-privilege-toolbar--template');
    department_id_select.on('dropdown_open', () => templateBar?.classList.add('is-menu-open'));
    department_id_select.on('dropdown_close', () => templateBar?.classList.remove('is-menu-open'));

    if($('#department_id_select').val() > 0){
        $('#loadPermissionTemplateBtn').removeClass('hidden');
    }else{
        $('#loadPermissionTemplateBtn').addClass('hidden');
    }

    department_id_select.on('change', function(value){
        if(value > 0){
            $('#loadPermissionTemplateBtn').removeClass('hidden');
        }else{
            // Clearing the template resets to the blank grouped list, so the field
            // names go back to permissions[0] and nothing stays tied to a template.
            $('#loadPermissionTemplateBtn').addClass('hidden');
            loadPermissionTemplate(0);
        }
    })

    function loadPermissionTemplate(department_id){
        return axios({
            method: 'post',
            url: route('employee.privilege.new.template'),
            data: { department_id: department_id },
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#permission-template-wrapper').html(response.data.html);
            initPermissionPickers(document.getElementById('permission-template-wrapper'));
            // Rebuilds the rail, counters and print report over the new cards.
            initPrivilegeUi();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });
    }


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#employeePrivilegeForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('employeePrivilegeForm');
    
        $('#savePermissionBtn').prop('disabled', true);
        $('#savePermissionBtn svg.theSaveLoader').removeClass('hidden');

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.privilege.new.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            $('#savePermissionBtn').prop('disabled', false);
            $('#savePermissionBtn svg.theSaveLoader').addClass('hidden');
            
            if (response.status == 200) {
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee privilege successfully stored into the DB.');
                });                
                  
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 1000)
            }
        }).catch(error => {
           $('#savePermissionBtn').prop('disabled', false);
            $('#savePermissionBtn svg.theSaveLoader').addClass('hidden');
            if (error.response) {
                console.log('error');
            }
        });
    });

    // Revoking every permission is destructive and cannot be undone from the UI,
    // so the button only opens a confirmation; the modal's button does the work.
    const revokeConfirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#revokeConfirmModal"));

    $('#privilegeResetBtn').on('click', function(e){
        e.preventDefault();
        revokeConfirmModal.show();
    });

    $('#privilegeResetConfirmBtn').on('click', function(e){
        e.preventDefault();

        const $confirm = $('#privilegeResetConfirmBtn');
        $confirm.prop('disabled', true);
        $('#privilegeResetBtn svg.theResetLoader').removeClass('hidden');

        axios({
            method: 'post',
            url: route('employee.privilege.new.reset'),
            data: { employee_id: $('#employeePrivilegeForm input[name="employee_id"]').val() },
            headers: {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            revokeConfirmModal.hide();
            $confirm.prop('disabled', false);
            $('#privilegeResetBtn svg.theResetLoader').addClass('hidden');

            successModal.show();
            $("#successModal .successModalTitle").html("Permissions Revoked");
            $("#successModal .successModalDesc").html(
                (response.data.revoked || 0) + ' permission(s) removed. This employee now has no access.'
            );

            setTimeout(function(){
                successModal.hide();
                window.location.reload();
            }, 1200);
        }).catch(error => {
            revokeConfirmModal.hide();
            $confirm.prop('disabled', false);
            $('#privilegeResetBtn svg.theResetLoader').addClass('hidden');

            warningModal.show();
            $("#warningModal .warningModalTitle").html("Could not revoke");
            $("#warningModal .warningModalDesc").html(
                error.response?.data?.res || 'Something went wrong. Please try again or contact the administrator.'
            );
        });
    });

    $('#loadPermissionTemplateBtn').on('click', function(e){
        e.preventDefault();

        $('#loadPermissionTemplateBtn').prop('disabled', true);
        $('#loadPermissionTemplateBtn svg.theLoader').removeClass('hidden');

        let department_id = $('#department_id_select').val();
        if(!department_id){
            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html("Error Found!" );
                $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact administrator.');
                $("#warningModal .warningCloser").attr('data-action', 'RELOAD');
            });

            $('#loadPermissionTemplateBtn').prop('disabled', false);
            $('#loadPermissionTemplateBtn svg.theLoader').addClass('hidden');

            return;
        }
        loadPermissionTemplate(department_id).then(() => {
            $('#loadPermissionTemplateBtn').prop('disabled', false);
            $('#loadPermissionTemplateBtn svg.theLoader').addClass('hidden');
        }).catch(error => {
            $('#loadPermissionTemplateBtn').prop('disabled', false);
            $('#loadPermissionTemplateBtn svg.theLoader').addClass('hidden');
            console.log(error);
        });
    });

    $(document).on('click', '#employee-privilege-list .accordion-button', function (e) {
        e.preventDefault();
        const $button = $(this);
        const $targetContent = $($button.data('target'));

        if ($button.hasClass('collapsed')) {
            $targetContent.addClass('show').stop(true, true).slideDown(200);
            $button.removeClass('collapsed').attr('aria-expanded', 'true');
            $button.find('.accordion-icon-plus').addClass('hidden');
            $button.find('.accordion-icon-minus').removeClass('hidden');
        } else {
            $targetContent.removeClass('show').stop(true, true).slideUp(200);
            $button.addClass('collapsed').attr('aria-expanded', 'false');
            $button.find('.accordion-icon-plus').removeClass('hidden');
            $button.find('.accordion-icon-minus').addClass('hidden');
        }
    });



})();