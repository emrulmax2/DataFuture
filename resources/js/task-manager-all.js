import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){

    let tomOptionsTasManager = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        maxOptions: null,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let change_status_id = new TomSelect('#change_status_id', tomOptionsTasManager);
    let term_declaration_id = new TomSelect('#term_declaration_id', tomOptionsTasManager);

    const addPearsonRegTaskModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPearsonRegTaskModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const updateBulkStatusModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#updateBulkStatusModal"));

    document.getElementById('addPearsonRegTaskModal').addEventListener('hidden.tw.modal', function(event){
        $('#addPearsonRegTaskModal .studentCount').html('No of Student: 0');
        $('#addPearsonRegTaskModal [name="student_ids"]').val('');
    });

    document.getElementById('updateBulkStatusModal').addEventListener('hidden.tw.modal', function(event){
        $('#updateBulkStatusModal .studentCount').html('No of Student: 0');
        $('#updateBulkStatusModal [name="student_ids"]').val('');
        change_status_id.clear(true);
        term_declaration_id.clear(true);
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
                ids = ids.replace(/,\s*$/, "")
            var idsArr = ids.split(',');
            var idsLength = idsArr.length;
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
    $('#student_ids').on('keyup', function(){
        let $theField = $(this);
        let ids = $theField.val();
        let idsArr = ids.split(',');
        let idsLength = idsArr.length;
        $('#addPearsonRegTaskModal .studentCount').html('No of Student: '+idsLength);
    })

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

    /* Bulk Status Start */
    $('#bulk_student_ids').on('paste', function(e){
        var $target = $(e.target);
        var $textArea = $('<textarea></textarea>');
        $textArea.on("blur", function(e) {
            var ids = $textArea.val().replace(/\r?\n/g, ', ');
                ids = ids.replace(/,\s*$/, "")
            var idsArr = ids.split(',');
            var idsLength = idsArr.length;
            $('#updateBulkStatusModal .studentCount').html('No of Student: '+idsLength);
            $target.val(ids);
            $textArea.remove();
        });
        $('body').append($textArea);
        $textArea.trigger('focus');
        setTimeout(function(){
            $target.trigger('focus');
        }, 10);
    });
    $('#bulk_student_ids').on('keyup', function(){
        let $theField = $(this);
        let ids = $theField.val();
        let idsArr = ids.split(',');
        let idsLength = idsArr.length;
        $('#updateBulkStatusModal .studentCount').html('No of Student: '+idsLength);
    });

    $('#updateBulkStatusForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('updateBulkStatusForm');
    
        document.querySelector('#upBulkStsBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#upBulkStsBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "POST",
            url: route('task.manager.update.bulk.status'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#upBulkStsBtn').removeAttribute('disabled');
            document.querySelector("#upBulkStsBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                updateBulkStatusModal.hide();

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
                updateBulkStatusModal.hide();
                
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
            document.querySelector('#upBulkStsBtn').removeAttribute('disabled');
            document.querySelector("#upBulkStsBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#updateBulkStatusForm .${key}`).addClass('border-danger');
                        $(`#updateBulkStatusForm  .error-${key}`).html(val);
                    }
                } else if(error.response.status == 322){
                    updateBulkStatusModal.hide();

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
    /* Bulk Status End */

})();