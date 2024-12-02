import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";


(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        maxOptions: null,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    
    const addCategoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCategoryModal"));
    const editCategoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCategoryModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const addCategoryModalEl = document.getElementById('addCategoryModal')
    addCategoryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addCategoryModal .acc__input-error').html('');
        $('#addCategoryModal .modal-body input:not([type="checkbox"]):not([type="radio"])').val('');
        $('#addCategoryModal .modal-body select').val('');
        $('#addCategoryModal input[name="audit_status"]').prop('checked', false);
        $('#addCategoryModal input[name="status"]').prop('checked', true);
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    // Toggle Trans Type Add Form
    $('#addCategoryForm').on('change', 'input[name="trans_type"]', function(e){
        var trans_type = document.querySelector('#addCategoryForm input[name="trans_type"]:checked').value;
        $('#addCategoryForm select[name="parent_id"]').attr('disabled', 'disabled');
        $('#addCategoryForm #saveCategory').attr('disabled', 'disabled');

        axios({
            method: "post",
            url: route('site.settings.category.filter.dropdown'),
            data: {trans_type : trans_type},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#addCategoryForm select[name="parent_id"]').removeAttr('disabled');
                $('#addCategoryForm #saveCategory').removeAttr('disabled');
                
                $('#addCategoryForm select[name="parent_id"]').html(response.data.html).val('');
            }
        }).catch(error =>{
            console.log(error);
        });
    });

    $('#addCategoryForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addCategoryForm');
    
        document.querySelector('#saveCategory').setAttribute('disabled', 'disabled');
        document.querySelector("#saveCategory svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('site.settings.category.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveCategory').removeAttribute('disabled');
            document.querySelector("#saveCategory svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addCategoryModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Transaction category Successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveCategory').removeAttribute('disabled');
            document.querySelector("#saveCategory svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addCategoryForm .${key}`).addClass('border-danger');
                        $(`#addCategoryForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $(".categoryTreeWrap").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("site.settings.category.edit"),
            data: {row_id : editId},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.row;
                let options = response.data.options;
                $('#editCategoryModal select[name="parent_id"]').html(options).val('');

                $('#editCategoryModal input[name="category_name"]').val(dataset.category_name ? dataset.category_name : '');
                $('#editCategoryModal input[name="code"]').val(dataset.code ? dataset.code : '');
                $('#editCategoryModal select[name="parent_id"]').val(dataset.parent_id ? dataset.parent_id : '');

                if(dataset.trans_type == 1){
                    $('#editCategoryModal #edit_outflow').prop('checked', true);
                }else{
                    $('#editCategoryModal #edit_inflow').prop('checked', true);
                }
                if(dataset.audit_status == 1){
                    $('#editCategoryModal [name="audit_status"]').prop('checked', true);
                }else{
                    $('#editCategoryModal [name="audit_status"]').prop('checked', false);
                }
                if(dataset.status == 1){
                    $('#editCategoryModal [name="status"]').prop('checked', true);
                }else{
                    $('#editCategoryModal [name="status"]').prop('checked', false);
                }
                $('#editCategoryModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editCategoryForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editCategoryForm');
    
        document.querySelector('#updateCategory').setAttribute('disabled', 'disabled');
        document.querySelector("#updateCategory svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('site.settings.category.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateCategory').removeAttribute('disabled');
            document.querySelector("#updateCategory svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editCategoryModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Transaction category Successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateCategory').removeAttribute('disabled');
            document.querySelector("#updateCategory svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editCategoryForm .${key}`).addClass('border-danger');
                        $(`#editCategoryForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.categoryTreeWrap').on('click', '.delete_btn', function(){
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

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('site.settings.category.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
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
    })

    /* Get Tree HTML By Parent Start */
    $('.classPlanTree').on('click', '.parent_category', function(e){
        e.preventDefault();
        var $link = $(this);
        var $parent = $link.parent('li');

        if($parent.hasClass('hasData')){
            $('> .theChild', $parent).slideToggle();
            $parent.toggleClass('opened');
        }else{
            $('svg', $link).fadeIn();
            var type = $link.attr('data-type');
            var category_id = $link.attr('data-category');
            axios({
                method: "post",
                url: route('site.settings.category.get.tree.html'),
                data: {type : type, category_id : category_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('svg', $link).fadeOut();
                if (response.status == 200) {
                    $parent.addClass('hasData opened');
                    $parent.append(response.data.htm);

                    tailwind.svgLoader();
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                if (error.response) {
                    $('svg', $link).fadeOut();
                    console.log('error');
                }
            });
        }
    });
    /* Get Tree HTML By Parent END */
})();