import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    const addPearsonRegTaskModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPearsonRegTaskModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    document.getElementById('addPearsonRegTaskModal').addEventListener('hidden.tw.modal', function(event){
        $('#addPearsonRegTaskModal .studentCount').html('No of Student: 0');
        $('#addPearsonRegTaskModal [name="student_ids"]').val('');
    });

    document.getElementById('successModal').addEventListener('hidden.tw.modal', function(event){
        $('#successModal .successCloser').attr('data-action', 'None');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#student_ids').on('paste', function(e){
        var $target = $(e.target);
        var $textArea = $('<textarea></textarea>');
        $textArea.on("blur", function(e) {
            var ids = $textArea.val().replace(/\r?\n/g, ', ');
            var idsArr = ids.split(', ');
            var idsLength = idsArr.length - 1;
            $('#addPearsonRegTaskModal .studentCount').html('No of Student: '+idsLength);
            $target.val(ids);
            $textArea.remove();
        });
        $('body').append($textArea);
        $textArea.trigger('focus');
        setTimeout(function(){
            $target.trigger('focus');
        }, 10);
    });

    $('#addPearsonRegTaskForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addPearsonRegTaskForm');
    
        document.querySelector('#PearsonRegBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#PearsonRegBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "POST",
            url: route('task.manager.create.pearson.registration'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#PearsonRegBtn').removeAttribute('disabled');
            document.querySelector("#PearsonRegBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addPearsonRegTaskModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html(response.data.msg);
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });     

                setTimeout(() => {
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }else if(response.status == 206){
                addPearsonRegTaskModal.hide();
                
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html( "Oops!" );
                    $("#warningModal .warningModalDesc").html(response.data.msg);
                });

                setTimeout(() => {
                    warningModal.hide();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#PearsonRegBtn').removeAttribute('disabled');
            document.querySelector("#PearsonRegBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addPearsonRegTaskForm .${key}`).addClass('border-danger');
                        $(`#addPearsonRegTaskForm  .error-${key}`).html(val);
                    }
                } else if(error.response.status == 322){
                    addPearsonRegTaskModal.hide();
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html( "Oops!" );
                        $("#warningModal .warningModalDesc").html(error.response.data.msg);
                    });    
                } else {
                    console.log('error');
                }
            }
        });
    });

})();