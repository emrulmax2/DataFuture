import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";


(function(){

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    let editEditor;
    if($("#editEditor").length > 0){
        const el = document.getElementById('editEditor');
        ClassicEditor.create(el).then((editor) => {
            editEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }


    $('#editLetterForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editLetterForm');
    
        document.querySelector('#editLetterSet').setAttribute('disabled', 'disabled');
        document.querySelector("#editLetterSet svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append("description", editEditor.getData());
        axios({
            method: "post",
            url: route('letter.set.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#editLetterSet').removeAttribute('disabled');
            document.querySelector("#editLetterSet svg").style.cssText = "display: none;";
            
            if (response.status == 200) {

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Letter set successfully updated.');
                });                
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#editLetterSet').removeAttribute('disabled');
            document.querySelector("#editLetterSet svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editLetterForm .${key}`).addClass('border-danger')
                        $(`#editLetterForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
})()