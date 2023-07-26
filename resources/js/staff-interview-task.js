("use strict");

const editModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));

$(document).on("click", ".interview-result", function (e) { 
        e.preventDefault();
        //interviewId = $(this).attr("data-id");
        document.getElementById('id').value = $(this).attr("data-id");
    
});
$(document).on("click", ".interview-taskend", function (e) { 
            
            e.preventDefault();

            const theId = $(this).attr("data-id");
            console.log(theId);
            axios({
                method: "post",
                url: route('applicant.interview.task.update'),
                data: {
                  id: theId
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {

                if (response.status == 200) {
                    editModal.hide();
                    succModal.show();
                    
                    let status = response.data.status;
                    document.getElementById("ProgressStatus").innerHTML = status;
                    
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(response.data.msg);
                            $("#successModal .successModalDesc").html('success');
                        });    
                }

                //interviewListTable.init();

            }).catch(error => {
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

});

$(document).on("click", ".interview-start", function (e) { 
            
    e.preventDefault();

    const theId = $(this).attr("data-id");
    console.log(theId);
    axios({
        method: "post",
        url: route('applicant.interview.start'),
        data: {
          id: theId
        },
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {

        if (response.status == 200) {
            
            let startTime = response.data.data.start;
            let ProgressStatus = response.data.data.status;
            document.getElementById("progressStart").innerHTML = startTime;
            document.getElementById("ProgressStatus").innerHTML = ProgressStatus;
            document.querySelector(".interview-start").setAttribute('disabled', 'disabled');
            editModal.hide();
            succModal.show();
            document.getElementById("successModal")
                .addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html(response.data.msg);
                    $("#successModal .successModalDesc").html('success');
                });   
                
            
        }

        //interviewListTable.init();

    }).catch(error => {
        
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#${key}`).addClass('border-danger')
                    $(`#error-${key}`).html(val)
                }
            } else {
                console.log('error');
            }
        }
    });

});


$(document).on("click", ".interview-end", function (e) { 
            
    e.preventDefault();

    const theId = $(this).attr("data-id");
    
    axios({
        method: "post",
        url: route('applicant.interview.end'),
        data: {
          id: theId
        },
        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
    }).then(response => {

        if (response.status == 200) {
            editModal.hide();
            succModal.show();
            
            let endTime = response.data.data.end;
            document.getElementById("progressEnd").innerHTML = endTime;

            document.getElementById("successModal")
                .addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html(response.data.msg);
                    $("#successModal .successModalDesc").html('success');
                });    
        }

        //interviewListTable.init();

    }).catch(error => {
        
        if (error.response) {
            if (error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#${key}`).addClass('border-danger')
                    $(`#error-${key}`).html(val)
                }
            } else {
                console.log('error');
            }
        }
    });

});


(function () {

    $('#editForm').on("submit", function (e) {

        $('#editForm').find('.interview_status__input').removeClass('border-danger')
        $('#editForm').find('.interview_status__input-error').html('')

        e.preventDefault()
        document.querySelector('#update').setAttribute('disabled', 'disabled')
        document.querySelector("#update svg").style.cssText ="display: inline-block;"

        const form = document.getElementById('editForm')
        let form_data = new FormData(form);
        //const user = document.getElementById('interview_status').value;
        
        axios({
            method: "post",
            url: route('applicant.interview.result.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            document.querySelector('#update').removeAttribute('disabled');
            document.querySelector("#update svg").style.cssText = "display: none;";
            console.log(response);
            if (response.status == 200) {
                document.querySelector('#update').removeAttribute('disabled');
                document.querySelector("#update svg").style.cssText = "display: none;";
                $('.user__input').val('');
                editModal.hide();
                succModal.show();
                let result = response.data.result;
                document.getElementById("progressInterviewStatus").innerHTML = result;
                document.getElementById("successModal")
                    .addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html(response.data.msg);
                        $("#successModal .successModalDesc").html('success');
                    });                
                    
            }
            interviewListTable.init();
        }).catch(error => {
            document.querySelector('#assign').removeAttribute('disabled');
            document.querySelector("#assign svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#${key}`).addClass('border-danger')
                        $(`#error-${key}`).html(val)
                    }
                    $('#interviewerSelectForm #user').val('');
                } else {
                    console.log('error');
                }
            }
        });


    });

})()