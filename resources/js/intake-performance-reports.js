import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    $('#intakePerformanceReportAccordion .accordion-button').on('click', function(e){
        var $thebtn = $(this);
        var hash = $thebtn.attr('data-tw-target');
        window.location.hash = hash;
    });

    $(window).on('load', function(){
        if(window.location.hash){     
            $('#intakePerformanceReportAccordion .accordion-button[data-tw-target="'+window.location.hash+'"]').removeClass('collapsed').attr('aria-expanded', 'true');
            $('#intakePerformanceReportAccordion '+window.location.hash).addClass('show').show();
        }
    });


    let dueTomOptions = {
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

    let dueTomOptionsMul = {
        ...dueTomOptions,
        plugins: {
            ...dueTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    /* Continuation Report Start */
    var cr_semester_id = new TomSelect('#cr_semester_id', dueTomOptionsMul);
    $('#continuationRateSearchForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('continuationRateSearchForm');
        let cr_semester_id = $form.find('#cr_semester_id').val();
        
        if(cr_semester_id.length > 0){
            $form.find('.error-cr_semester_id').html('')
            document.querySelector('#continuationRateBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#continuationRateBtn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.intake.performance.get.continuation.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#continuationRateBtn').removeAttribute('disabled');
                document.querySelector("#continuationRateBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    
                }
            }).catch(error => {
                document.querySelector('#continuationRateBtn').removeAttribute('disabled');
                document.querySelector("#continuationRateBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-cr_semester_id').html('Semesters can not be empty.');
        }
    })

    /* Continuation Report End */

})()