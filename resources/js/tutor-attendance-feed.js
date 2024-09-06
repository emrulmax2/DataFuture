import { createIcons, icons } from "lucide";

("use strict");

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $(window).on('load', function(e){
        $('#feedAttendanceTable tbody tr.theAttendanceRow').each(function(){
            var $theRow = $(this);
            var label = $theRow.find('.attendanceRadio:checked').attr('data-type');
            var color = $theRow.find('.attendanceRadio:checked').attr('data-color');

            $theRow.find('.feedTypeCol').html(label).css({color: color});
        });
        reloadAttendanceCount();
    })

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

    $('.save').on('click', function (e) {
        e.preventDefault();

        var parentForm = $(this).parents('form');
        var formID = parentForm.attr('id');
        const form = document.getElementById(formID);
        let url = $("#"+formID+" input[name=url]").val();
        
        let form_data = new FormData(form);
        console.log(form_data)
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
                        window.location.reload();
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

    let classEnd = $("#dataclassend").data("classend") *1;
    if(classEnd == 0 ) {

        var minutesLabel = document.getElementById("minutes");
        var secondsLabel = document.getElementById("seconds");
        var hoursLebel = document.getElementById("hours");

        let hms = hoursLebel.innerHTML+':'+minutesLabel.innerHTML+':'+secondsLabel.innerHTML;
        let a = hms.split(':');
        let seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]);
        var totalSeconds = seconds;
        
        setInterval(setTime, 1000);

        function setTime() {
            ++totalSeconds;
            secondsLabel.innerHTML = pad(totalSeconds % 60);
            minutesLabel.innerHTML = pad(parseInt((totalSeconds % 3600) / 60));
            hoursLebel.innerHTML =   pad(parseInt(totalSeconds / (60*60)));
            
        }

        function pad(val) {
            var valString = val + "";
            if (valString.length < 2) {
                return "0" + valString;
            } else {
                return valString;
            }
        }

    }

    function reloadAttendanceCount(){
        var prasentCount = 0;
        var totalStudents = 0;
        $('#feedAttendanceTable tbody tr.theAttendanceRow').each(function(){
            var $theRow = $(this);
            var attendance = $theRow.find('.attendanceRadio:checked').val();
            if(attendance == 1 || attendance == 2 || attendance == 3 || attendance == 5){
                prasentCount += 1;
            }

            totalStudents += 1;
        });

        $('.attendanceCountWrap').html(prasentCount+'/'+totalStudents);
    }
})();