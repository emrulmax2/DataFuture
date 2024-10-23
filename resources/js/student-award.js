import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";

(function(){
    let awrdTomOptions = {
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

    let qual_award_result_id = new TomSelect('#qual_award_result_id', awrdTomOptions);
    let certificate_requested_by = new TomSelect('#certificate_requested_by', awrdTomOptions);
    let certificate_released_by = new TomSelect('#certificate_released_by', awrdTomOptions);

    const successAWModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successAWModal"));
    const addStudentAwardInfoModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addStudentAwardInfoModal"));

    document.getElementById('addStudentAwardInfoModal').addEventListener('hide.tw.modal', function(event) {
        $('#addSettingsModal .acc__input-error').html('');
        $('#addSettingsModal .modal-body input:not([type="checkbox"])').val('');
        $('#addSettingsModal .modal-body input[type="checkbox"]').prop('checked', false);
        $('#addSettingsModal .modal-body .checkLabel').html('No');
        $('.cerReqWrap, .cerRcvdWrap, .cerRelsdWrap').fadeOut();
        qual_award_result_id.clear(true);
        certificate_requested_by.clear(true);
        certificate_released_by.clear(true);
    });

    $("#successAWModal .successCloser").on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successAWModal.hide();
        }
    })

    $('#certificate_requested').on('change', function(e){
        if($(this).prop('checked')){
            $('#addStudentAwardInfoModal .cerReqWrap').fadeIn();
            $(this).siblings('.checkLabel').html('Yes');
        }else{
            $('#addStudentAwardInfoModal .cerReqWrap').fadeOut();
            $(this).siblings('.checkLabel').html('No');
        }
        $('#addStudentAwardInfoModal .cerReqWrap input').val('');
        certificate_requested_by.clear(true)
    })

    $('#certificate_received').on('change', function(e){
        if($(this).prop('checked')){
            $('#addStudentAwardInfoModal .cerRcvdWrap').fadeIn();
            $(this).siblings('.checkLabel').html('Yes');
        }else{
            $('#addStudentAwardInfoModal .cerRcvdWrap').fadeOut();
            $(this).siblings('.checkLabel').html('No');
        }
        $('#addStudentAwardInfoModal .cerRcvdWrap input').val('');
    })

    $('#certificate_released').on('change', function(e){
        if($(this).prop('checked')){
            $('#addStudentAwardInfoModal .cerRelsdWrap').fadeIn();
            $(this).siblings('.checkLabel').html('Yes');
        }else{
            $('#addStudentAwardInfoModal .cerRelsdWrap').fadeOut();
            $(this).siblings('.checkLabel').html('No');
        }
        $('#addStudentAwardInfoModal .cerRelsdWrap input').val('');
        certificate_released_by.clear(true)
    });


    $('#addStudentAwardInfoForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addStudentAwardInfoForm');
    
        document.querySelector('#addAwardBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#addAwardBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.store.award'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#addAwardBtn').removeAttribute('disabled');
            document.querySelector("#addAwardBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addStudentAwardInfoModal.hide();

                successAWModal.show();
                document.getElementById("successAWModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successAWModal .successAWModalTitle").html( "Congratulations!" );
                    $("#successAWModal .successAWModalDesc").html(response.data.msg);
                    $("#successAWModal .successCloser").attr('data-action', 'RELOAD');
                });     

                setTimeout(() => {
                    successAWModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#addAwardBtn').removeAttribute('disabled');
            document.querySelector("#addAwardBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addStudentAwardInfoForm .${key}`).addClass('border-danger');
                        $(`#addStudentAwardInfoForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('.editStudentAwardBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var award_id = $theBtn.attr('data-id');
        var student_id = $theBtn.attr('data-student');

        axios({
            method: "POST",
            url: route("student.edit.award"),
            data: {student_id : student_id, award_id : award_id},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.row;
                $('#addStudentAwardInfoModal input[name="id"]').val(award_id);
                $('#addStudentAwardInfoModal input[name="date_of_award"]').val(dataset.date_of_award ? dataset.date_of_award : '');
                if(dataset.qual_award_result_id > 0){
                    qual_award_result_id.addItem(dataset.qual_award_result_id)
                }else{
                    qual_award_result_id.clear(true);
                }

                if(dataset.certificate_requested  == 'Yes'){
                    $('#certificate_requested').prop('checked', true);
                    $('#certificate_requested').siblings('.checkLabel').html('Yes');
                    $('.cerReqWrap').fadeIn('fast', function(){
                        $('#date_of_certificate_requested').val(dataset.date_of_certificate_requested ? dataset.date_of_certificate_requested : '');
                        if(dataset.certificate_requested_by > 0){
                            certificate_requested_by.addItem(dataset.certificate_requested_by);
                        }else{
                            certificate_requested_by.clear(true);
                        }
                    })
                }else{
                    $('#certificate_requested').prop('checked', false);
                    $('#certificate_requested').siblings('.checkLabel').html('No');
                    $('.cerReqWrap').fadeOut('fast', function(){
                        $('#date_of_certificate_requested').val('');
                        certificate_requested_by.clear(true);
                    })
                }

                if(dataset.certificate_received  == 'Yes'){
                    $('#certificate_received').prop('checked', true);
                    $('#certificate_received').siblings('.checkLabel').html('Yes');
                    $('.cerRcvdWrap').fadeIn('fast', function(){
                        $('#date_of_certificate_received').val(dataset.date_of_certificate_received ? dataset.date_of_certificate_received : '');
                    })
                }else{
                    $('#certificate_received').prop('checked', false);
                    $('#certificate_received').siblings('.checkLabel').html('No');
                    $('.cerRcvdWrap').fadeOut('fast', function(){
                        $('#date_of_certificate_received').val('');
                    })
                }

                if(dataset.certificate_released  == 'Yes'){
                    $('#certificate_released').prop('checked', true);
                    $('#certificate_released').siblings('.checkLabel').html('Yes');
                    $('.cerRelsdWrap').fadeIn('fast', function(){
                        $('#date_of_certificate_released').val(dataset.date_of_certificate_released ? dataset.date_of_certificate_released : '');
                        if(dataset.certificate_released_by > 0){
                            certificate_released_by.addItem(dataset.certificate_released_by);
                        }else{
                            certificate_released_by.clear(true);
                        }
                    })
                }else{
                    $('#certificate_released').prop('checked', false);
                    $('#certificate_released').siblings('.checkLabel').html('No');
                    $('.cerRelsdWrap').fadeOut('fast', function(){
                        $('#date_of_certificate_released').val('');
                        certificate_released_by.clear(true);
                    })
                }

                
            }
        }).catch((error) => {
            console.log(error);
        });
    })

})();