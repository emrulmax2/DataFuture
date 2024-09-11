import { createIcons, icons } from "lucide";

("use strict");

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('.save').on('click', function (e) {
        e.preventDefault();

        var parentForm = $(this).parents('form');
        
        var formID = parentForm.attr('id');
        
        const form = document.getElementById(formID);
        let url = $("#"+formID+" input[name=url]").val();
        
        let form_data = new FormData(form);

        $.ajax({
            method: 'POST',
            url: url,
            data: form_data,
            dataType: 'json',
            async: false,
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            success: function(res, textStatus, xhr) {

                $('.acc__input-error', parentForm).html('');
                
                if(xhr.status == 200){
                    //update Alert

                    successModal.show();

                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Successfull!");
                        $("#successModal .successModalDesc").html('Attendance Captured.');
                    });                
                    
                    setTimeout(function(){
                        successModal.hide();
                        location.reload()
                    }, 1000);
                }
                
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('.acc__input-error').html('');
                
                if(jqXHR.status == 422){
                    for (const [key, val] of Object.entries(jqXHR.responseJSON.errors)) {
                        $(`#${formID} .${key}`).addClass('border-danger');
                        $(`#${formID}  .error-${key}`).html(val);
                    }
                }else{
                    console.log(textStatus+' => '+errorThrown);
                }
                
            }
        });
        
    });

    $('#feedAttendanceTable').on('change', '.checkAllEmailNotify', function(){
        let $theEmailCheck = $(this);

        if($theEmailCheck.prop('checked')){
            $('#feedAttendanceTable').find('.checkEmailNotify').prop('checked', true);
        }else{
            $('#feedAttendanceTable').find('.checkEmailNotify').prop('checked', false);
        }
    });

    $('#feedAttendanceTable').on('change', '.checkEmailNotify', function(){
        var allLength = $('#feedAttendanceTable').find('.checkEmailNotify').length;
        var checkedLength = $('#feedAttendanceTable').find('.checkEmailNotify:checked').length;

        if(allLength == checkedLength){
            $('#feedAttendanceTable').find('.checkAllEmailNotify').prop('checked', true);
        }else{
            $('#feedAttendanceTable').find('.checkAllEmailNotify').prop('checked', false);
        }
    });

    $(window).on('load', function(e){
        $('#feedAttendanceTable tbody tr.theAttendanceRow').each(function(){
            var $theRow = $(this);
            var label = $theRow.find('.attendanceRadio:checked').attr('data-type');
            var color = $theRow.find('.attendanceRadio:checked').attr('data-color');

            $theRow.find('.feedTypeCol').html(label).css({color: color});
        });
        reloadAttendanceCount();
    });

    $('#feedAttendanceTable').on('change', '.attendanceRadio', function(e){
        var $theRadio = $(this);
        var $theRow = $theRadio.closest('tr.theAttendanceRow');

        if($theRow.find('.attendanceRadio:checked').length > 0){
            var label = $theRow.find('.attendanceRadio:checked').attr('data-type');
            var color = $theRow.find('.attendanceRadio:checked').attr('data-color');

            $theRow.find('.feedTypeCol').html(label).css({color: color});
        }else{
            $theRow.find('.feedTypeCol').html('').css({color: '#1e293b'});
        }
        reloadAttendanceCount();
    })

    function reloadAttendanceCount(){
        $('#feedAttendanceTable .attendanceButon').each(function(){
            let $theBtn = $(this);
            let typeId = $theBtn.attr('data-id');
            let typeAttendanceCount = $('#feedAttendanceTable tbody .attendanceRadio_'+typeId+':checked').length;

            $theBtn.find('.attendanceHeaderCount_'+typeId).html(typeAttendanceCount);
        })
    }
})();