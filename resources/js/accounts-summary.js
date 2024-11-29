import helper from "./helper";
import colors from "./colors";
import Chart from "chart.js/auto";

import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";

import dayjs from "dayjs";
import Litepicker from "litepicker";

(function () {
    "use strict";

    let currentRequest = null;
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        maxOptions: null,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let multiTomOpt = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };
    var summary_categories = new TomSelect('#summary_categories', multiTomOpt);
    var summary_storages = new TomSelect('#summary_storages', multiTomOpt);

    summary_categories.on('change', function(){
        summaryResultGenerator();
    });
    summary_storages.on('change', function(){
        summaryResultGenerator();
    });

    $('#advanceSearchToggle').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        
        if($theBtn.hasClass('active')){
            $theBtn.removeClass('active');
            $('.advanceSearchGroup').fadeOut('fast');
            summary_categories.clear(true);
            summary_storages.clear(true);
            summaryResultGenerator();
        }else{
            $theBtn.addClass('active');
            $('.advanceSearchGroup').fadeIn('fast');
            summary_categories.clear(true);
            summary_storages.clear(true);
        }
    })

    let pickerOptions = {
        autoApply: true,
        singleMode: false,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    let reportPicker = new Litepicker({
        element: document.getElementById('reportPicker'),
        ...pickerOptions,
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                let theDates = $('#reportPicker').val();
                if(theDates != '' && theDates.length == 23){
                    let theDatesArr = theDates.split(' - ');
                    //window.location.href = route('accounts.report', theDatesArr);
                    window.location.href = route('accounts.management.report', theDatesArr);
                }
            });
        }
    });

    let theRange = new Litepicker({
        element: document.getElementById('summary_date'),
        ...pickerOptions,
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                summaryResultGenerator();
            });
        }
    });

    $('#summary_date, #summary_search_query, #summary_min_amount, #summary_max_amount').on('keyup past change', function(){
        summaryResultGenerator();
    });


    function summaryResultGenerator(){
        let theRangeDate = $('#summary_date').val();
        let theQueryText = $('#summary_search_query').val();
        let theMinAmount = $('#summary_min_amount').val();
        let theMaxAmount = $('#summary_max_amount').val();
        let summary_categories = $('#summary_categories').val();
        let summary_storages = $('#summary_storages').val();
        
        if((theRangeDate != '' && theRangeDate.length == 23) || theQueryText != '' || theMinAmount != '' || theMaxAmount != '' || summary_categories != '' || summary_storages != ''){
            $('.summarySearchResultWrap').fadeOut().html('');
            currentRequest = $.ajax({
                type: 'POST',
                data: {theRangeDate : theRangeDate, theQueryText : theQueryText, theMinAmount : theMinAmount, theMaxAmount : theMaxAmount, summary_categories : summary_categories, summary_storages : summary_storages},
                url: route("accounts.search"),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                beforeSend : function()    {           
                    if(currentRequest != null) {
                        currentRequest.abort();
                    }
                },
                success: function(data) {
                    $('.summarySearchResultWrap').fadeIn().html(data.res);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                },
                error:function(e){
                    console.log('Error');
                }
            });
        }else{
            $('.summarySearchResultWrap').fadeOut().html('');
            if(currentRequest != null) {
                currentRequest.abort();
            }
        }
    }


    if ($("#report-line-chart").length) {
        let months = ($('#report-line-chart').attr('data-months') != '' ? JSON.parse($('#report-line-chart').attr('data-months')) : '');
        let incomes = ($('#report-line-chart').attr('data-incomes') != '' ? JSON.parse($('#report-line-chart').attr('data-incomes')) : '');
        let expense = ($('#report-line-chart').attr('data-expense') != '' ? JSON.parse($('#report-line-chart').attr('data-expense')) : '');

        if(months != '' && incomes != '' && expense != ''){
            let ctx = $("#report-line-chart")[0].getContext("2d");
            let myChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: months,
                    datasets: [
                        //Incomes
                        {
                            label: "# Inflows",
                            data: incomes,
                            borderWidth: 2,
                            borderColor: '#0d9488',
                            backgroundColor: "transparent",
                            pointBorderColor: "transparent",
                            tension: 0.4,
                        },
                        //Expenses
                        {
                            label: "# Outflows",
                            data: expense,
                            borderWidth: 2,
                            borderDash: [2, 2],
                            borderColor: 'rgba(185, 28, 28, .8)',
                            backgroundColor: "transparent",
                            pointBorderColor: "transparent",
                            tension: 0.4,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: 12,
                                },
                                color: colors.slate["500"](0.8),
                            },
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                        },
                        y: {
                            afterFit: instance => console.log(instance),
                            ticks: {
                                font: {
                                    size: 12,
                                },
                                color: colors.slate["500"](0.8),
                                callback: function (value, index, values) {
                                    return "£" + value;
                                },
                            },
                            grid: {
                                color: $("html").hasClass("dark")
                                    ? colors.slate["500"](0.3)
                                    : colors.slate["300"](),
                                borderDash: [2, 2],
                                drawBorder: false,
                            },
                        },
                    },
                },
            });
        }
    }


    $('.summarySearchResultWrap').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');
        
        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: 'post',
            url: route('accounts.storage.trans.download.link'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                let res = response.data.res;
                if(res != ''){
                    window.open(res, '_blank');
                }
            }
        }).catch(error =>{
            $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
            console.log(error)
        });
    });
})()