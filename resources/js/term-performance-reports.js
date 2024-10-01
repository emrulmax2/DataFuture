import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

import helper from "./helper";
import Chart from "chart.js/auto";
import { bottom } from "@popperjs/core";

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
                        label: false,
                        data: rates,
                        barThickness: 25,
                        fill: false,
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                        ],
                        borderColor: [
                            'rgb(75, 192, 192)',
                            'rgb(54, 162, 235)',
                            'rgb(153, 102, 255)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    //maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: theTitle,
                            color: '#164e63e6',
                            padding: {
                                bottom: 20
                            },
                            font: {
                                size: 18,
                                weight: 'bold',
                                lineHeight: 1
                            }
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                color: '#164e63e6',
                                display: true,
                                font: {
                                    size: 13,
                                    weight: 'bold',
                                    lineHeight: 1
                                }
                            },
                            stacked: false,
                            /*afterFit: function(scaleInstance) {
                                scaleInstance.width = 150; // sets the width to 100px
                            }*/
                        }
                    }
                }
            });
        }
    })
})();