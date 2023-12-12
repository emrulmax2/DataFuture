import { createIcons, icons } from "lucide";

(function(){
    const editStudentAWBModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentAWBModal"));
    const confirmRDVModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmRDVModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    const confirmRDVModalEl = document.getElementById('confirmRDVModal')
    confirmRDVModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#confirmRDVModal .agreeWith').attr('data-status', '');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });

    $('#confirmRDVModal .disAgreeWith').on('click', function(e){
        e.preventDefault();
        confirmRDVModal.hide();
        window.location.reload();
    });

    $('#editStudentAWBForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editStudentAWBForm');
    
        document.querySelector('#saveSABD').setAttribute('disabled', 'disabled');
        document.querySelector("#saveSABD svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.awarding.body.details'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#saveSABD').removeAttribute('disabled');
                document.querySelector("#saveSABD svg").style.cssText = "display: none;";

                editStudentAWBModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Awarding Body details successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.show();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveSABD').removeAttribute('disabled');
            document.querySelector("#saveSABD svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editStudentAWBForm .${key}`).addClass('border-danger');
                        $(`#editStudentAWBForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.registration_document_verified:not(:checked)').on('change', function(){
        var $input = $(this);
        var inputValue = $input.val();
        

        confirmRDVModal.show();

        const confirmRDVModalEl = document.getElementById('confirmRDVModal')
        confirmRDVModalEl.addEventListener('shown.tw.modal', function(event) {
            $('#confirmRDVModal .agreeWith').attr('data-status', inputValue);
            $('#confirmRDVModal .confModTitle').html('Are you sure?');
            $('#confirmRDVModal .confModDesc').html('Want to change the student awarding body registration document status? Click on agree to procceed.');
        });
    });

    $('#reset_regDocVerify').on('click', function(){
        var $btn = $(this);
        $btn.find('svg').fadeIn();
        $btn.attr('disabled', 'disabled');

        confirmRDVModal.show();

        const confirmRDVModalEl = document.getElementById('confirmRDVModal')
        confirmRDVModalEl.addEventListener('shown.tw.modal', function(event) {
            $('#confirmRDVModal .agreeWith').attr('data-status', 'Reset');
            $('#confirmRDVModal .confModTitle').html('Are you sure?');
            $('#confirmRDVModal .confModDesc').html('Want to reset the student awarding body registration document status? Click on agree to procceed.');
        });
    });

    $('#confirmRDVModal .agreeWith').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var status = $('#confirmRDVModal .agreeWith').attr('data-status');
        var row_id = $('#confirmRDVModal .agreeWith').attr('data-recordid');
        var student_id = $('#confirmRDVModal .agreeWith').attr('data-student');
        var student_crel_id = $('#confirmRDVModal .agreeWith').attr('data-scrid');

        $('#confirmRDVModal button').attr('disabled', 'disabled');
        $btn.html('Processing...');

        axios({
            method: "post",
            url: route('student.update.awarding.body.status'),
            data: {student_id : student_id, student_crel_id: student_crel_id, row_id : row_id, status : status},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#confirmRDVModal button').removeAttr('disabled');
                $btn.html('Done!');

                confirmRDVModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student awarding body registration document status successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.show();
                    //window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            $('#confirmRDVModal button').removeAttr('disabled');
            $btn.html('Yes, I agree');
            if (error.response) {
                console.log('error');
            }
        });
    })

})()