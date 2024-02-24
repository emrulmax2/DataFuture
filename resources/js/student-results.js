import IMask from 'imask';
 
("use strict");    

(function () {

    
    $(".timeMask").each(function () {
        var maskOptions = {
            mask: 'HH:MM',
            blocks: {
            HH: {
                    mask: IMask.MaskedRange,
                    placeholderChar: 'HH',
                    from: 0,
                    to: 23,
                    maxLength: 2
                },
            MM: {
                    mask: IMask.MaskedRange,
                    placeholderChar: 'MM',
                    from: 0,
                    to: 59,
                    maxLength: 2
                }
            }
        };
        var mask = IMask(this, maskOptions);
    });

    
    $(".tablepoint-toggle").on('click', function(e) {
        e.preventDefault();
        let tthis = $(this)
        let currentThis=tthis.children(".plusminus").eq(0);
        console.log(currentThis);
        let nextThis=tthis.children(".plusminus").eq(1);
        if(currentThis.hasClass('hidden') ) {
            currentThis.removeClass('hidden')
            nextThis.addClass('hidden')
        }else {
            nextThis.removeClass('hidden')
            currentThis.addClass('hidden')
        }

        tthis.parent().siblings('div.tabledataset').slideToggle();

    });
    $(".toggle-heading").on('click', function(e) {
        e.preventDefault();
        let tthis = $(this)
        tthis.siblings("div.tablepoint-toggle").trigger('click')
    })

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#delete-confirmation-modal"));
    const editAttemptModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAttemptModal"));
    // Confirm Modal Action

    $('.delete_btn').on('click', function(){
        let $statusBTN = $(this);

        let rowID = $statusBTN.attr('data-id');
        let confModalDelTitle = "Do you want to delete";
        confirmModal.show();
        document.getElementById('delete-confirmation-modal').addEventListener('shown.tw.modal', function(event){
            $('#delete-confirmation-modal .confModTitle').html(confModalDelTitle);
            $('#delete-confirmation-modal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
            $('#delete-confirmation-modal .agreeWith').attr('data-id', rowID);
            $('#delete-confirmation-modal .agreeWith').attr('data-action', 'DELETE');
        });
    });
    $('.edit_btn').on('click', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');
        let grade = $statusBTN.attr('data-grade');
        let publishTime = $statusBTN.attr('data-publishTime');
        let publishDate = $statusBTN.attr('data-publishDate');
        editAttemptModal.show();
        document.getElementById('editAttemptModal').addEventListener('shown.tw.modal', function(event){
            $('#editAttemptModal input[name="id"]').val(rowID);
            $('#editAttemptModal select[name="grade_id"]').val(grade);
            $('#editAttemptModal input[name="published_at"]').val(publishDate);
            $('#editAttemptModal input[name="published_time"]').val(publishTime);
        });
    });

    $("#editAttemptForm").on("submit", function (e) {
        let editId = $('#editAttemptForm input[name="id"]').val();

        e.preventDefault();
        const form = document.getElementById("editAttemptForm");

        document.querySelector('#update').setAttribute('disabled', 'disabled');
        document.querySelector('#update svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("result.update", editId),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#update").removeAttribute("disabled");
                document.querySelector("#update svg").style.cssText = "display: none;";
                editAttemptModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Success!");
                    $("#successModal .successModalDesc").html('Result updated');
                });
            }
            location.reload();
        }).catch((error) => {
            document.querySelector("#update").removeAttribute("disabled");
            document.querySelector("#update svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editForm .${key}`).addClass('border-danger')
                        $(`#editForm  .error-${key}`).html(val)
                    }
                }else {
                    console.log("error");
                }
            }
        });
    });

    $('#delete-confirmation-modal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let resultId = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#delete-confirmation-modal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('result.destroy', resultId),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {

                    $('#delete-confirmation-modal button').removeAttr('disabled');
                    confirmModal.hide();
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Data successfully deleted.');
                    });

                    location.reload();

                }
            }).catch(error =>{
                console.log(error)
            });
        } 
    })
})()