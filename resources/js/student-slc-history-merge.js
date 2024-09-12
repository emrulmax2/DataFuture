import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            warningModal.hide();
            window.location.reload();
        }else{
            warningModal.hide();
        }
    })

    $(document).on('click', '.assignAttendanceToReg', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let reg_id = $theBtn.attr('data-reg');
        let atn_id = $theBtn.attr('data-atn');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to assign #'+atn_id+' this attendance to #'+reg_id+' registration?');
            $("#confirmModal .agreeWith").attr('data-recordid', reg_id+'_'+atn_id);
            $("#confirmModal .agreeWith").attr('data-status', 'ASSIGNATNTOREG');
        });
    });

    $(document).on('click', '.assignCocToAttendance', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let atn_id = $theBtn.attr('data-atn');
        let coc_id = $theBtn.attr('data-coc');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to assign #'+coc_id+' this COC to #'+atn_id+' registration?');
            $("#confirmModal .agreeWith").attr('data-recordid', atn_id+'_'+coc_id);
            $("#confirmModal .agreeWith").attr('data-status', 'ASSIGNCOCTOATN');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'ASSIGNATNTOREG'){
            axios({
                method: 'post',
                url: route('student.slc.attendance.sync.to.registration'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s SLC Attendance sync successfully with selected registration.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                confirmModal.hide();
                if(error.response.status == 422){
                    warningModal.show();
                    document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                        $('#warningModal .warningModalTitle').html('Oops!');
                        $('#warningModal .warningModalDesc').html('Something went wrong. Please try later or contact with the administrator.');
                        $('#warningModal .warningCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        warningModal.hide();
                    }, 2000);
                }else{
                    console.log('error');
                }
            });
        }else if(action == 'ASSIGNCOCTOATN'){
            axios({
                method: 'post',
                url: route('student.slc.coc.sync.to.attendance'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student\'s SLC COC sync successfully with selected Attendance.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                confirmModal.hide();
                if(error.response.status == 422){
                    warningModal.show();
                    document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                        $('#warningModal .warningModalTitle').html('Oops!');
                        $('#warningModal .warningModalDesc').html('Something went wrong. Please try later or contact with the administrator.');
                        $('#warningModal .warningCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        warningModal.hide();
                    }, 2000);
                }else{
                    console.log('error');
                }
            });
        }else{
            confirmModal.hide();
        }
    });
})()