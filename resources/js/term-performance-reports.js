import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

import helper from "./helper";
import Chart from "chart.js/auto";
import { bottom } from "@popperjs/core";

(function(){
    $('#termPerformanceReportAccordion .accordion-button').on('click', function(e){
        var $thebtn = $(this);
        var hash = $thebtn.attr('data-tw-target');
        window.location.hash = hash;
    });

    $(window).on('load', function(){
        if(window.location.hash){     
            $('#termPerformanceReportAccordion .accordion-button[data-tw-target="'+window.location.hash+'"]').removeClass('collapsed').attr('aria-expanded', 'true');
            $('#termPerformanceReportAccordion '+window.location.hash).addClass('show').show();
        }
    });

})()