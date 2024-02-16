import { createIcons, icons } from "lucide";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const editCOCModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCOCModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addCOCModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCOCModal"));

    const addCOCModalEl = document.getElementById('addCOCModal')
    addCOCModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addCOCModal .acc__input-error').html('');
        $('#addCOCModal .modal-body select').val('');
        $('#addCOCModal .modal-body input:not([type="file"])').val('');
        $('#addCOCModal input[type="file"]').val(null);
        $('#addCOCModal .modal-body textarea').val('');

        $('#addCOCModal [name="slc_attendance_id"]').val(0);
        $('#addCOCModal [name="slc_registration_id"]').val(0);
    });
    const editCOCModalEl = document.getElementById('editCOCModal')
    editCOCModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editCOCModal .acc__input-error').html('');
        $('#editCOCModal .modal-body select').val('');
        $('#editCOCModal .modal-body input:not([type="file"])').val('');
        $('#editCOCModal input[type="file"]').val(null);
        $('#editCOCModal .modal-body textarea').val('');

        $('#editCOCModal [name="slc_coc_id"]').val(0);
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

    $('.addCOCBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var regid = $theBtn.attr('data-regid');
        var atnid = $theBtn.attr('data-atnid');

        $('#addCOCModal [name="slc_attendance_id"]').val(atnid);
        $('#addCOCModal [name="slc_registration_id"]').val(regid);
    })

    $('#addtCOCForm').on('change', '#addCOCDocument', function(){
        showFileNames('addCOCDocument', 'addCOCDocumentName');
    });

    $('#addtCOCForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addtCOCForm');
    
        document.querySelector('#addCOC').setAttribute('disabled', 'disabled');
        document.querySelector("#addCOC svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
            form_data.append('file', $('#addtCOCForm #addCOCDocument')[0].files[0]); 

        axios({
            method: "post",
            url: route('student.slc.coc.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#addCOC').removeAttribute('disabled');
            document.querySelector("#addCOC svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addCOCModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC COC history successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#addCOC').removeAttribute('disabled');
            document.querySelector("#addCOC svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addtCOCForm .${key}`).addClass('border-danger');
                        $(`#addtCOCForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });


    $('#editCOCForm').on('change', '#editCOCDocument', function(){
        showFileNames('editCOCDocument', 'editCOCDocumentName');
    });

    function showFileNames(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = '';
        if(fileInput.files.length > 0){
            fileName += '<ul class="m-0">';
            $.each(fileInput.files, function(index, file){
                fileName += '<li class="mb-1 text-primary flex items-center"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>'+file.name+'</li>';
            });
            fileName += '</ul>';
        }
        
        $('#'+targetPreviewId).html(fileName);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });

        return false;
    };

    $('.edit_coc_btn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var coc_id = $theBtn.attr('data-id');

        axios({
            method: "post",
            url: route('student.edit.slc.coc'),
            data: {coc_id : coc_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                var res = response.data.res;
                $('#editCOCModal [name="confirmation_date"]').val(res.confirmation_date);
                $('#editCOCModal [name="coc_type"]').val(res.coc_type);
                $('#editCOCModal [name="actioned"]').val(res.actioned);
                $('#editCOCModal [name="reason"]').val(res.reason);

                $('#editCOCModal [name="slc_coc_id"]').val(coc_id);
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#editCOCForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editCOCForm');
    
        document.querySelector('#updateCOC').setAttribute('disabled', 'disabled');
        document.querySelector("#updateCOC svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
            form_data.append('file', $('#editCOCForm #editCOCDocument')[0].files[0]); 

        axios({
            method: "post",
            url: route('student.slc.coc.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateCOC').removeAttribute('disabled');
            document.querySelector("#updateCOC svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editCOCModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student SLC COC history successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateCOC').removeAttribute('disabled');
            document.querySelector("#updateCOC svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editCOCForm .${key}`).addClass('border-danger');
                        $(`#editCOCForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.deleteCOCDoc').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var docid  = $theLink.attr('data-docid');
        var cocid  = $theLink.attr('data-cocid');

        var theid = cocid+'_'+docid;

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this coc document for the list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', theid);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETECOCDOC');
        });
    });

    $('.delete_coc_btn').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var recordid  = $theLink.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this coc for the list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordid);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETECOC');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETECOCDOC'){
            axios({
                method: 'post',
                url: route('student.destory.coc.document'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s COC document successfully deleted.');
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
        }else if(action == 'DELETECOC'){
            axios({
                method: 'delete',
                url: route('student.destory.coc'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s COC document successfully deleted.');
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
        }else{
            confirmModal.hide();
        }
    });


})();