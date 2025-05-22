import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");




(function(){
    



    let letterEditor;
    if($("#letterEditor").length > 0){
        const el = document.getElementById('letterEditor');
        ClassicEditor.create(el).then((editor) => {
            letterEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: true,
        create: false,
        allowEmptyOption: false,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    const letter_set_id = new TomSelect('#letter_set_id', tomOptions);

    


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addLetterModal"));

    const addLetterModalEl = document.getElementById('addLetterModal')
    addLetterModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addLetterModal .acc__input-error').html('');
        $('#addLetterModal .modal-body input:not([type="checkbox"])').val('');
        $('#addLetterModal .modal-body select').val('');
        $('#addLetterModal .letterEditorArea').fadeOut();
        letterEditor.setData('');
        letter_set_id.setValue('');
        letter_set_id.clear(true);

        $('#addLetterModal .modal-body input[name="send_in_email"]').prop('checked', false);
        $('#addLetterModal .commonSmtpWrap').fadeOut();

        const today = new Date();
        const yyyy = today.getFullYear();
        let mm = today.getMonth() + 1;
        let dd = today.getDate();

        if (dd < 10) dd = '0' + dd;
        if (mm < 10) mm = '0' + mm;

        const todayDate = dd + '-' + mm + '-' + yyyy;
        $('#addLetterModal .modal-body input[name="issued_date"]').val(todayDate);
    });

    
    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
    });

    const viewCommunicationModalEl = document.getElementById('viewCommunicationModal')
    viewCommunicationModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#viewCommunicationModal .modal-body").html('');
        $('#viewCommunicationModal .modal-header h2').html('View Communication');
    });

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
    });


    /* Letter Area */
    $('#send_in_email').on('change', function() {
        if($(this).prop('checked')){
            $('.commonSmtpWrap').fadeIn('fast', function(){
                $('select', this).val('')
            });
        }else{
            $('.commonSmtpWrap').fadeOut('fast', function(){
                $('select', this).val('')
            });
        }
    });


    $('#addLetterModal #letter_set_id').on('change', function(){
        var letterSetId = $(this).val();
        if(letterSetId > 0){
            axios({
                method: 'post',
                url: route('student.get.letter.set'),
                data: {letterSetId : letterSetId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#addLetterModal .letterEditorArea').fadeIn('fast', function(){
                        var description = response.data.res.description ? response.data.res.description : '';
                        letterEditor.setData(description)
                    })
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            $('#addLetterModal .letterEditorArea').fadeOut('fast', function(){
                letterEditor.setData('')
            })
        }
    });

    $('#addLetterForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addLetterForm');
    
        document.querySelector('#sendLetterBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#sendLetterBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append("letter_body", letterEditor.getData());
        axios({
            method: "post",
            url: route('student.send.letter'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            
            if (response.status == 200) {
                
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Letter successfully generated and send.');
                    $("#successModal .successCloser").attr('data-action', 'DISMISS');
                });  
                
                //I need another axios call where I set the letter status to StudentDocumentRequstForms
                axios({
                    method: 'post',
                    url: route('task.manager.document_request.letter.update'),
                    data: {student_task_id : form_data.get('student_task_id')},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        
                        document.querySelector('#sendLetterBtn').removeAttribute('disabled');
                        document.querySelector("#sendLetterBtn svg").style.cssText = "display: none;";
                        addLetterModal.hide();
                        successModal.show(); 
                        setTimeout(function(){
                            successModal.hide();
                        }, 2000);
                        taskAssignedStudentTable.init();
                    }
                }).catch(error =>{
                    console.log(error)
                });

            }
        }).catch(error => {
            document.querySelector('#sendLetterBtn').removeAttribute('disabled');
            document.querySelector("#sendLetterBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addLetterForm .${key}`).addClass('border-danger');
                        $(`#addLetterForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });



    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let student = $agreeBTN.attr('data-student');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETELETTER'){
            axios({
                method: 'delete',
                url: route('student.letter.destroy'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    studentCommLetterListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student Communication Letter successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORELETTER'){
            axios({
                method: 'post',
                url: route('student.letter.restore'),
                data: {student : student, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    studentCommLetterListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Student Communication Letter successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    });

 

})();