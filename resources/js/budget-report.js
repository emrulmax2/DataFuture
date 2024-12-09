import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select"; 
import tippy, { roundArrow } from "tippy.js";
import Litepicker from "litepicker";

(function(){

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#successModal .successCloser').attr('data-action', 'NONE');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            succModal.hide();
        }
    });

    $('#budgetYearReportForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('budgetYearReportForm');
    
        document.querySelector('#generateReportBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#generateReportBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('budget.management.reports.generate'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#generateReportBtn').removeAttribute('disabled');
            document.querySelector("#generateReportBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                $('.budgetReportWrap').html(response.data.htm).fadeIn();
            }
        }).catch(error => {
            document.querySelector('#generateReportBtn').removeAttribute('disabled');
            document.querySelector("#generateReportBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#budgetYearReportForm .${key}`).addClass('border-danger');
                        $(`#budgetYearReportForm  .error-${key}`).html(val);
                    }
                }else {
                    console.log('error');
                }
            }
        });
    });


    $('.budgetReportWrap').on('click', '.budgetReportRow', function(e){
        var url = $(this).attr('data-url');
        window.open(url, '_blank').focus();
    })
})();