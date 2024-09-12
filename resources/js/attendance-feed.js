import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";
import IMask from 'imask';

("use strict");

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    let tomOptionsSingle = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    let tutor_id = new TomSelect(document.getElementById('tutor_id'), tomOptionsSingle);

    if($('.timePicker').length > 0){
        $('.timePicker').each(function(){
            var timeMask = IMask(
                this,
                {
                    overwrite: true,
                    autofix: true,
                    mask: 'HH:MM - HH2:MM2',
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
                        },
                        HH2: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'HH',
                            from: 0,
                            to: 23,
                            maxLength: 2
                        },
                        MM2: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'MM',
                            from: 0,
                            to: 59,
                            maxLength: 2
                        },
                    }
                }
            );
        });
    }

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

    $('#feedAttendanceTable').on('change', '.checkAllSmsNotify', function(){
        let $theEmailCheck = $(this);

        if($theEmailCheck.prop('checked')){
            $('#feedAttendanceTable').find('.checkSmsNotify').prop('checked', true);
        }else{
            $('#feedAttendanceTable').find('.checkSmsNotify').prop('checked', false);
        }
    });

    $('#feedAttendanceTable').on('change', '.checkSmsNotify', function(){
        var allLength = $('#feedAttendanceTable').find('.checkSmsNotify').length;
        var checkedLength = $('#feedAttendanceTable').find('.checkSmsNotify:checked').length;

        if(allLength == checkedLength){
            $('#feedAttendanceTable').find('.checkAllSmsNotify').prop('checked', true);
        }else{
            $('#feedAttendanceTable').find('.checkAllSmsNotify').prop('checked', false);
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

    $('#attendanceFeedForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('attendanceFeedForm');
    
        document.querySelector('#saveAtnBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveAtnBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('attendance.create.and.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveAtnBtn').removeAttribute('disabled');
            document.querySelector("#saveAtnBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                document.querySelector('#saveAtnBtn').removeAttribute('disabled');
                document.querySelector("#saveAtnBtn svg").style.cssText = "display: none;";
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Success!" );
                    $("#successModal .successModalDesc").html('Attendance successfully feeded.');
                });                
                    
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 1000);
            }
        }).catch(error => {
            document.querySelector('#saveAtnBtn').removeAttribute('disabled');
            document.querySelector("#saveAtnBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#attendanceFeedForm .${key}`).addClass('border-danger')
                        $(`#attendanceFeedForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
        
    })
})();