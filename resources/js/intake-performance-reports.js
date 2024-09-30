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

})()