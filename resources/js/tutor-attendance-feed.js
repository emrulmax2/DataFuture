import { createIcons, icons } from "lucide";

("use strict");

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $(window).on('load', function(e){
        $('#feedAttendanceTable tbody tr.theAttendanceRow').each(function(){
            updateStatusPill($(this));
        });
        reloadAttendanceCount();
    })

    $('#feedAttendanceTable').on('change', '.attendanceRadio', function(e){
        updateStatusPill($(this).closest('tr.theAttendanceRow'));
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

    function updateStatusPill($theRow){
        var $checked = $theRow.find('.attendanceRadio:checked');
        var $statusPill = $theRow.find('.feedTypeCol');

        if($checked.length > 0){
            var label = $checked.attr('data-type');
            var color = $checked.attr('data-color') || '#0d7c73';

            $statusPill
                .html(label)
                .addClass('is-marked')
                .css({
                    color: color,
                    backgroundColor: hexToRgba(color, .10),
                    borderColor: hexToRgba(color, .28)
                });
        }else{
            $statusPill
                .html('Not marked')
                .removeClass('is-marked')
                .css({
                    color: '#adbbb9',
                    backgroundColor: '#f4f5f2',
                    borderColor: '#e6e8e3'
                });
        }
    }

    function hexToRgba(hex, alpha){
        var cleanHex = (hex || '').replace('#', '');

        if(cleanHex.length === 3){
            cleanHex = cleanHex.split('').map(function(char){
                return char + char;
            }).join('');
        }

        if(cleanHex.length !== 6){
            return 'rgba(13, 124, 115, '+alpha+')';
        }

        var r = parseInt(cleanHex.substring(0, 2), 16);
        var g = parseInt(cleanHex.substring(2, 4), 16);
        var b = parseInt(cleanHex.substring(4, 6), 16);

        return 'rgba('+r+', '+g+', '+b+', '+alpha+')';
    }

    function reloadAttendanceCount(){
        var prasentCount = 0;
        var absentCount = 0;
        var totalStudents = 0;
        $('#feedAttendanceTable tbody tr.theAttendanceRow').each(function(){
            var $theRow = $(this);
            var attendance = $theRow.find('.attendanceRadio:checked').val();
            if(attendance == 1 || attendance == 2 || attendance == 3 || attendance == 5){
                prasentCount += 1;
            }
            if(attendance == 4){
                absentCount += 1;
            }

            totalStudents += 1;
        });

        var percent = totalStudents > 0 ? Math.round((prasentCount / totalStudents) * 100) : 0;

        $('.attendanceCountWrap').attr('data-numofstd', totalStudents);
        $('.attendancePresentValue').html(prasentCount);
        $('.attendanceTotalValue').html('/'+totalStudents);
        $('.attendanceProgressBar').css('width', percent+'%');
        $('.attendancePctText').html(percent+'%');
        $('.attendanceAbsentValue').html(absentCount);
        $('.attendanceFooterPresent').html(prasentCount);
        $('.attendanceFooterAbsent').html(absentCount);
    }
})();
