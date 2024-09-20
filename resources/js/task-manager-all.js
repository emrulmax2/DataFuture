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



    $('#student_ids').on('paste', function(e){
        var $target = $(e.target);
        var $textArea = $("<textarea></textarea>");
        $textArea.on("blur", function(e) {
            $target.val($textArea.val().replace(/\r?\n/g, ', ') );
            $textArea.remove();
        });
        $('body').append($textArea);
        $textArea.trigger('focus');
        setTimeout(function(){
            $target.trigger('focus');
        }, 10);
    });
})();