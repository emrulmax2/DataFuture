
import helper from "./helper";
import Chart from "chart.js/auto";
import { bottom } from "@popperjs/core";
import colors from "./colors";

import html2canvas from "html2canvas";
import { jsPDF } from "jspdf";

(function(){

    if($('#attendanceTrendLineChart').length > 0){
        let attendanceTrendLineChart = null;
        $(window).on('load', function(){
            let $theTable = $('#attendanceTrendOvTable');
            let theTitle = $theTable.attr('data-title');
            let labels = [];
            let datasets = [];

            $theTable.find('tbody tr').each(function(){
                var $theRow = $(this);
                labels.push($theRow.find('.labels').attr('data-labels'));
            });

            $theTable.find('thead tr th.countable').each(function(){
                var $theHead = $(this);
                var enabled = $theHead.find('.col_selection').prop('checked') ? true : false;
                if(enabled){
                    var sl = $theHead.attr('data-sl');
                    var label = $theHead.attr('data-label');
                    var color = $theHead.attr('data-color');

                    var theSet = {};
                    theSet.label = label;
                    theSet.borderWidth = 4;
                    theSet.borderColor = color;
                    theSet.backgroundColor = color;
                    theSet.pointBorderColor = color;
                    theSet.tension = 0.1;

                    var singleData = [];
                    var attendances = 0;
                    var attendance_count = 0;
                    $theTable.find('tbody .serial_'+sl).each(function(){
                        var $theDataCol = $(this);
                        singleData.push($theDataCol.attr('data-rate'));

                        attendances += ($theDataCol.attr('data-attendance') * 1);
                        attendance_count += ($theDataCol.attr('data-count') * 1);
                    })
                    theSet.data = singleData;
                    datasets.push(theSet);

                    var avgSet = {};
                    var average = (attendances > 0 && attendance_count > 0 ? attendances * 100 / attendance_count : 0);
                    var averageData = [];
                    for(var i = 0; i <= labels.length; i++){
                        averageData.push(average.toFixed(2));
                    }
                    avgSet.label = label+' Average';
                    avgSet.borderWidth = 4;
                    avgSet.borderColor = color;
                    avgSet.backgroundColor = color;
                    avgSet.pointBorderColor = color;
                    avgSet.tension = 0.1;
                    avgSet.data = averageData;
                    datasets.push(avgSet);
                }
            });
            
            /*$theTable.find('tbody tr').each(function(){
                var $theRow = $(this);
            })*/

            let ctx = document.getElementById('attendanceTrendLineChart').getContext("2d");
            attendanceTrendLineChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels : labels,
                    datasets : datasets
                },
                options: {
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
                            display: false,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                        }
                    }
                }
            });
        });

        $('#attendanceTrendOvTable').on('change', '.col_selection', function(e){
            let $theTable = $('#attendanceTrendOvTable');
            let labels = [];
            let datasets = [];

            $theTable.find('tbody tr').each(function(){
                var $theRow = $(this);
                labels.push($theRow.find('.labels').attr('data-labels'));
            });

            $theTable.find('thead tr th.countable').each(function(){
                var $theHead = $(this);
                var enabled = $theHead.find('.col_selection').prop('checked') ? true : false;
                if(enabled){
                    var sl = $theHead.attr('data-sl');
                    var label = $theHead.attr('data-label');
                    var color = $theHead.attr('data-color');

                    var theSet = {};
                    theSet.label = label;
                    theSet.borderWidth = 4;
                    theSet.borderColor = color;
                    theSet.backgroundColor = color;
                    theSet.pointBorderColor = color;
                    theSet.tension = 0.1;

                    var singleData = [];
                    var attendances = 0;
                    var attendance_count = 0;
                    $theTable.find('tbody .serial_'+sl).each(function(){
                        var $theDataCol = $(this);
                        singleData.push($theDataCol.attr('data-rate'));

                        attendances += ($theDataCol.attr('data-attendance') * 1);
                        attendance_count += ($theDataCol.attr('data-count') * 1);
                    })
                    theSet.data = singleData;
                    datasets.push(theSet);

                    var avgSet = {};
                    var average = (attendances > 0 && attendance_count > 0 ? attendances * 100 / attendance_count : 0);
                    var averageData = [];
                    for(var i = 0; i <= labels.length; i++){
                        averageData.push(average.toFixed(2));
                    }
                    avgSet.label = label+' Average';
                    avgSet.borderWidth = 4;
                    avgSet.borderColor = color;
                    avgSet.backgroundColor = color;
                    avgSet.pointBorderColor = color;
                    avgSet.tension = 0.1;
                    avgSet.data = averageData;
                    datasets.push(avgSet);
                }
            });
            attendanceTrendLineChart.data.datasets = datasets;
            attendanceTrendLineChart.update();
        })
    }

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
})()