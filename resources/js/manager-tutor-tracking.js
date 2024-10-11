import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

(function(){
    const viewElearnincTrackingModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewElearnincTrackingModal"));
        
    document.getElementById('viewElearnincTrackingModal').addEventListener('hide.tw.modal', function(event) {
        $('#viewElearnincTrackingModal #dailyClassInfoTable tbody').html('');
    });

    $(document).on('click', '.showUndeciededModulesBtn', function(e){
        e.preventDefault();
        var $theLink = $(this);

        var tutor_id = $theLink.attr('data-tutor');
        var term_id = $theLink.attr('data-term');
        var plan_id = $theLink.attr('data-plan');

        axios({
            method: "POST",
            url: route('programme.dashboard.get.undecided.class'),
            data: {tutor_id : tutor_id, term_id :term_id, plan_id : plan_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#viewElearnincTrackingModal #dailyClassInfoTable tbody').html(response.data.htm);

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });
})()