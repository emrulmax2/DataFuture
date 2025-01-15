import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Litepicker from "litepicker";


("use strict");
var outStandingHolidayReportTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let holiday_year_id = $("#holiday_year_id").val() != "" ? $("#holiday_year_id").val() : "";
        let from_date = $("#from_date").val() != "" ? $("#from_date").val() : "";
        let to_date = $("#to_date").val() != "" ? $("#to_date").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "2";
        
        let tableContent = new Tabulator("#outStandingHolidayReportTable", {
            ajaxURL: route("hr.portal.reports.outstanding.holiday.list"),
            ajaxParams: { holiday_year_id: holiday_year_id, from_date: from_date, to_date: to_date, status : status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: true,
            paginationSizeSelector: [true],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#SL",
                    field: "sl",
                    width: "80",
                    headerSort: false,
                },
                {
                    title: "Employee",
                    field: "full_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Department",
                    field: "department",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Allocation",
                    field: "allocation",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Taken",
                    field: "taken",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Balance Hour",
                    field: "balance_hour",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Balance",
                    field: "balance_amount",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) { 
                        if(cell.getData().status == 'Active'){
                            return '<span class="btn btn-success-soft px-1 py-0 rounded-0">Active</span>';
                        }else{
                            return '<span class="btn btn-pending-soft px-1 py-0 rounded-0">Inactive</span>';
                        }
                    }
                },
                
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                $('#oshSearchBtn svg.theLoader').fadeOut();
                $('#oshExportBtn').fadeIn();
            },
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Status Details",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();


(function(){
    let OSHLitepicker = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 2015,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    let tomOptionsOSH = {
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

    let holiday_year_id = new TomSelect('#holiday_year_id', tomOptionsOSH);
    let from_date = new Litepicker({ element: document.getElementById('from_date'), ...OSHLitepicker });
    let to_date = new Litepicker({ element: document.getElementById('to_date'), ...OSHLitepicker });

    $('#holiday_year_id').on('change', function(){
        var $theYear = $(this);
        var theYear = $theYear.val();
        var todayDate = new Date().toISOString().slice(0, 10);

        $('#from_date').val('');
        $('#to_date').val('');
        if(theYear > 0){
            let minDate = $('option:selected', $theYear).attr('data-start');
            let maxDate = $('option:selected', $theYear).attr('data-end');
            from_date.setOptions({
                minDate: minDate,
                //startDate: minDate,
                maxDate: maxDate
            });
            to_date.setOptions({
                minDate: minDate,
                //startDate: minDate,
                maxDate: maxDate
            });
        }else{
            from_date.setOptions({
                minDate: '1900-01-01',
                //startDate: todayDate,
                maxDate: '2050-12-31',
            });
            to_date.setOptions({
                minDate: '1900-01-01',
                //startDate: todayDate,
                maxDate: '2050-12-31',
            });
        }
    });

    $('#outstandingHolidayReportForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('outstandingHolidayReportForm');
        var $theForm = $(this);
        var action = $('[name="action"]', $theForm).val();
        $theForm.find('button[type="submit"] svg.theLoader').fadeOut();

        var errors = 0;
        $theForm.find('.require').each(function(){
            if($(this).val() == ''){
                $(this).siblings('.acc__input-error').html('This field is required.');
                errors += 1;
            }else{
                $(this).siblings('.acc__input-error').html('');
            }
        })
        $theForm.find('select.tomRequire').each(function(){
            if($(this).val() == ''){
                $(this).siblings('.acc__input-error').html('This field is required.');
                errors += 1;
            }else{
                $(this).siblings('.acc__input-error').html('');
            }
        })

        if(errors > 0){
            return false;
        }else{
            if(action == 'export'){
                $('#oshExportBtn svg.theLoader').fadeIn();
                let form_data = new FormData(form);

                axios({
                    method: "post",
                    url: route('hr.portal.reports.outstanding.holiday.export'),
                    data: form_data,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                    responseType: 'blob',
                }).then(response => {
                    $('#oshExportBtn svg.theLoader').fadeOut();
                    if (response.status == 200) {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', 'Outstanding_holiday_report.xlsx'); 
                        document.body.appendChild(link);
                        link.click();
                    }
                }).catch(error => {
                    $('#oshExportBtn svg.theLoader').fadeOut();
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('#oshExportBtn svg.theLoader').fadeOut();
                $('#oshExportBtn').fadeOut();

                $('#oshSearchBtn svg.theLoader').fadeIn();
                $('.outStandingHolidayReportWrap').fadeIn('fast', function(){
                    outStandingHolidayReportTable.init();
                })
            }
        }
    });

    $('#oshResetBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var $theForm = $('#outstandingHolidayReportForm');

        $theForm.find('[name="action"]').val('search');
        holiday_year_id.clear(true);
        $theForm.find('[name="from_date"]').val('');
        $theForm.find('[name="to_date"]').val('');
        $theForm.find('[name="status"]').val('2');

        $theForm.find('button[type="submit"] svg.theLoader').fadeOut();
        $('#oshExportBtn').fadeOut();
        $('.outStandingHolidayReportWrap').fadeOut('fast', function(){
            $('#outStandingHolidayReportTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
        })
    })
})()