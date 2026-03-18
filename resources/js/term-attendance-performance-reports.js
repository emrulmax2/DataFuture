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
    let trmDecTomOptions = {
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

    let trmDecTomOptionsMul = {
        ...trmDecTomOptions,
        plugins: {
            ...trmDecTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var term_declaration_id = new TomSelect('#term_declaration_id', trmDecTomOptions);
    let attendanceRateBarChart = null;
    $('#term_declaration_id').on('change', function(){
        $('#viewTermAttendanceTrendBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#downloadJSPDFBTN').fadeOut();
        $('#termAttendanceRateWrap').fadeOut().html('');
    });

    $('#termAttendanceRateSearchForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('termAttendanceRateSearchForm');
        let term_declaration_id = $form.find('#term_declaration_id').val();
        
        if(term_declaration_id.length > 0){
            $form.find('.error-term_declaration_id').html('')
            document.querySelector('#termAttendanceRateSearchBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#termAttendanceRateSearchBtn svg").style.cssText ="display: inline-block;";
            $('#viewTermAttendanceTrendBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#downloadJSPDFBTN').fadeOut();
            $('#termAttendanceRateWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.term.performance.generate.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#termAttendanceRateSearchBtn').removeAttribute('disabled');
                document.querySelector("#termAttendanceRateSearchBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    //console.log(response.data);
                    //return false;
                    let pdf_url = route('reports.term.performance.term.trend', term_declaration_id);
                    $('#termAttendanceRateWrap').fadeIn().html(response.data.htm);
                    $('#viewTermAttendanceTrendBtn').attr('href', pdf_url).fadeIn();
                    $('#downloadJSPDFBTN').fadeIn();

                    setTimeout(() => {
                        attendanceRateBarChart = drawTheChart();
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#termAttendanceRateSearchBtn').removeAttribute('disabled');
                document.querySelector("#termAttendanceRateSearchBtn svg").style.cssText = "display: none;";
                $('#viewTermAttendanceTrendBtn').attr('href', 'javascript:void(0);').fadeOut();
                $('#downloadJSPDFBTN').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-term_declaration_id').html('Semesters can not be empty.');
            $('#termAttendanceRateWrap').fadeOut().html('');
            $('#viewTermAttendanceTrendBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#downloadJSPDFBTN').fadeOut();
        }
    });

    $('#termAttendanceRateWrap').on('change', '#attendanceRateOvTable .rateRowCheck', function(e){
        if(attendanceRateBarChart != null){
            let $theTable = $('#termAttendanceRateWrap').find('#attendanceRateOvTable');
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
                    //labels.push($theRow.attr('data-label'));
                    rates.push($theRow.attr('data-rate'));

                    var thisLabel = [];
                    var datalabel = $theRow.attr('data-label');
                    if(datalabel.length > 33){
                        thisLabel.push(datalabel.substr(0, 33));
                        thisLabel.push(datalabel.substr(34));
                        labels.push(thisLabel);
                    }else{
                        labels.push($theRow.attr('data-label'));
                    }
                }
            });

            attendanceRateBarChart.data.datasets[0].data = rates;
            attendanceRateBarChart.data.datasets[0].backgroundColor = bgs;
            attendanceRateBarChart.data.datasets[0].borderColor = bds;
            attendanceRateBarChart.data.labels = labels;

            attendanceRateBarChart.update();
        }else{
            drawTheChart();
        }
    });

    function drawTheChart(){
        let attendanceRateBarChart = null;
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
                var thisLabel = [];
                var datalabel = $theRow.attr('data-label');
                if(datalabel.length > 33){
                    thisLabel.push(datalabel.substr(0, 33));
                    thisLabel.push(datalabel.substr(34));
                    labels.push(thisLabel);
                }else{
                    labels.push($theRow.attr('data-label'));
                }
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
                                scale.width = 250;
                            },
                        }
                    }
                }
            });
        }

        return attendanceRateBarChart;
    }
    
    /*let dueTomOptions = {
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
                var thisLabel = [];
                var datalabel = $theRow.attr('data-label');
                if(datalabel.length > 33){
                    thisLabel.push(datalabel.substr(0, 33));
                    thisLabel.push(datalabel.substr(34));
                    labels.push(thisLabel);
                }else{
                    labels.push($theRow.attr('data-label'));
                }
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
                //labels.push($theRow.attr('data-label'));
                rates.push($theRow.attr('data-rate'));

                var thisLabel = [];
                var datalabel = $theRow.attr('data-label');
                if(datalabel.length > 33){
                    thisLabel.push(datalabel.substr(0, 33));
                    thisLabel.push(datalabel.substr(34));
                    labels.push(thisLabel);
                }else{
                    labels.push($theRow.attr('data-label'));
                }
            }
        });

        attendanceRateBarChart.data.datasets[0].data = rates;
        attendanceRateBarChart.data.datasets[0].backgroundColor = bgs;
        attendanceRateBarChart.data.datasets[0].borderColor = bds;
        attendanceRateBarChart.data.labels = labels;

        attendanceRateBarChart.update();
    });*/


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