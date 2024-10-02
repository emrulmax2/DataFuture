
import helper from "./helper";
import Chart from "chart.js/auto";
import { bottom } from "@popperjs/core";
import colors from "./colors";

(function(){

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
            var total = 0;
            var count = 0;
            $theTable.find('tbody .serial_'+sl).each(function(){
                var $theDataCol = $(this);
                singleData.push($theDataCol.attr('data-rate'));

                total += ($theDataCol.attr('data-rate') * 1);
                count += 1;
            })
            theSet.data = singleData;
            datasets.push(theSet);

            var avgSet = {};
            var average = total / count;
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
            }
        });
    });
})()