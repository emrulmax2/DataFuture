import helper from "./helper";
import Chart from "chart.js/auto";
import { bottom } from "@popperjs/core";

(function(){
    if($('#attendanceRateOvTable').length > 0){
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
                            barThickness: 25,
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
                                display: false,
                                //text: theTitle,
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
                                    scale.width = 250;
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
    }

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
                            display: false,
                            //text: theTitle,
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
})();