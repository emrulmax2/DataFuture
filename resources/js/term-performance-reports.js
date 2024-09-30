import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

import helper from "./helper";
import Chart from "chart.js/auto";

(function(){
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

    var term_declaration_id = new TomSelect('#term_declaration_id', dueTomOptions);

    $(window).on('load', function(){
        let $theTable = $('#attendanceRateOvTable');
        let theTitle = $theTable.attr('data-title');
        let labels = [];
        let rates = [];

        $theTable.find('.rateRow').each(function(){
            let $theRow = $(this);
            labels.push($theRow.attr('data-label'));
            rates.push($theRow.attr('data-rate'));
        });

        if(labels.length > 0 && rates.length > 0){
            let ctx = document.getElementById('attendanceRateBarChart');
            let attendanceRateBarChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        axis: 'y',
                        label: theTitle,
                        data: rates,
                        barThickness: 30,
                        backgroundColor: 'rgba(74, 179, 244, .8)'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    //maintainAspectRatio: false,
                }
            });
        }
    })
})();