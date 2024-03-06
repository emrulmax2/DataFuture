
(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });

    
    /* Home Work Start */
    $('.attendance_action_btn').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var action_type = $this.attr('data-value');

        $('.attendance_action_btn').addClass('disabled');
        axios({
            method: 'post',
            url: route('dashboard.feed.attendance'),
            data: {action_type : action_type},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            $('.attendance_action_btn').removeClass('disabled');

            if (response.status == 200) {
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html(response.data.res);
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                }); 

                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000)
            }
        }).catch((error) => {
            $('.attendance_action_btn').removeClass('disabled');
            if (error.response) {
                console.log("error");
            }
        });
        
    });
    /* Home Work End */

})();