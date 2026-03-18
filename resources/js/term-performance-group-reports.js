import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

import helper from "./helper";
import Chart from "chart.js/auto";
import { bottom } from "@popperjs/core";

import html2canvas from "html2canvas";
import { jsPDF } from "jspdf";

(function(){
    let attendanceRateBarChart = null;
    $(window).on('load', function(){
        let $theTable = $('#attendanceRateOvTable');
        let theTitle = $theTable.attr('data-title');
        let labels = [];
        let rates = [];
        let bgs = [];
        let bds = [];

        $theTable.find('.rateRow').each(function(){
            let $theRow = $(this);
            let $checkbox = $theRow.find('.rateRowCheck');
            if($checkbox.prop('checked')){
                labels.push($theRow.attr('data-label'));
                bgs.push($theRow.attr('data-bg'));
                bds.push($theRow.attr('data-bd'));
                rates.push($theRow.attr('data-rate'));
            }
        });

        if(labels.length > 0 && rates.length > 0){
            let ctx = document.getElementById('attendanceRateBarChart').getContext("2d");
            attendanceRateBarChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        axis: 'y',
                        label: false,
                        data: rates,
                        barThickness: 20,
                        fill: false,
                        backgroundColor: bgs,
                        borderColor: bds,
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
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
                            beginAtZero: true,
                            ticks: {
                                color: '#164e63e6',
                                display: true,
                                font: {
                                    size: 13,
                                    weight: 'bold',
                                    lineHeight: 1.5
                                }
                            },
                            stacked: false,
                            afterFit(scale) {
                                //scale.width = 250;
                            },
                        }
                    }
                }
            });
        }
    });

    $('#attendanceRateOvTable .rateRowCheck').on('change', function(e){
        let $theTable = $('#attendanceRateOvTable');
        let labels = [];
        let rates = [];
        let bgs = [];
        let bds = [];

        $theTable.find('.rateRow').each(function(){
            let $theRow = $(this);
            let $checkbox = $theRow.find('.rateRowCheck');
            if($checkbox.prop('checked')){
                bgs.push($theRow.attr('data-bg'));
                bds.push($theRow.attr('data-bd'));
                labels.push($theRow.attr('data-label'));
                rates.push($theRow.attr('data-rate'));
            }
        });

        attendanceRateBarChart.data.datasets[0].data = rates;
        attendanceRateBarChart.data.datasets[0].backgroundColor = bgs;
        attendanceRateBarChart.data.datasets[0].borderColor = bds;
        attendanceRateBarChart.data.labels = labels;

        attendanceRateBarChart.update();
    });


    $('#downloadJSPDFBTN').on('click', function () {
        const element = document.getElementById('prindJSPDFWrap');
        html2canvas(element, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff'
        }).then(canvas => {

            const imgData = canvas.toDataURL('image/png');

            // A4 Portrait
            const pdf = new jsPDF('p', 'mm', 'a4');

            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            // 60px margin → convert to mm (1px ≈ 0.264583 mm)
            const margin = 60 * 0.264583; // ≈ 15.87 mm

            const usableWidth = pageWidth - (margin * 2);
            const usableHeight = pageHeight - (margin * 2);

            // Maintain aspect ratio
            let imgWidth = usableWidth;
            let imgHeight = canvas.height * imgWidth / canvas.width;

            // If too tall → scale down
            if (imgHeight > usableHeight) {
                const ratio = usableHeight / imgHeight;
                imgHeight = usableHeight;
                imgWidth = imgWidth * ratio;
            }

            // Top aligned (NOT centered)
            const x = margin;
            const y = margin;

            pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
            pdf.save('Attendance_Rates_Reports.pdf');
        });
    });
})();