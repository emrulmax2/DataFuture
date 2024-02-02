import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function(){

  if ($("#attendance-editAll").length) {
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));

    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
    });
    $('#attendance-update_all').on('submit', function(e) {
        e.preventDefault();
        const form = document.getElementById('attendance-update_all');

        $('.load-update').removeClass('hidden');
        //document.querySelector("#save svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('attendance.update.all'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('.load-update').addClass('hidden');
            
            if (response.status == 200) {
                

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Success!");
                    $("#successModal .successModalDesc").html('Academic attendances are updated.');
                }); 
                setTimeout(function(){
                  succModal.hide();
                  window.location.reload();
              }, 1500);        
            }
        }).catch(error => {
            $('.load-update').addClass('hidden');
            

            if (error.response) {
                if (error.response.status == 422) {

                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("OOPS!");
                        $("#errorModal .errorModalDesc").html('No Update Found.');
                    }); 
                    setTimeout(function(){
                        errorModal.hide();
                    }, 1500);
                } else {
                    console.log('error');
                }
            }
        });
    });

    $(".tablepoint-toggle").on('click', function(e) {
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

    $('div.tabledataset').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');
        let confModalDelTitle = "Are you sure?"
        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('attendance.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                }
                setTimeout(function(){
                    succModal.hide();
                    window.location.reload();
                }, 1500);
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('attendance.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                    });
                }
                setTimeout(function(){
                    succModal.hide();
                    window.location.reload();
                }, 1500);
            }).catch(error =>{
                console.log(error)
            });
        }
    })
}
})();
