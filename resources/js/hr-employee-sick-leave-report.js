import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import dayjs from 'dayjs';
import Litepicker from 'litepicker';


("use strict");
var empSickLeaveListTable = (function () {
    var _tableGen = function () {
        let employee_id = $("#employee_id").val() != "" ? $("#employee_id").val() : "";
        let no_of_days = $("#no_of_days").val() != "" ? $("#no_of_days").val() : "";
        let from_date = $("#from_date").val() != "" ? $("#from_date").val() : "";
        let to_date = $("#to_date").val() != "" ? $("#to_date").val() : "";

        let tableContent = new Tabulator("#empSickLeaveListTable", {
            ajaxURL: route("hr.portal.reports.sick.leave.list"),
            ajaxParams: { employee_id:employee_id, no_of_days:no_of_days, from_date:from_date, to_date:to_date },
            ajaxFiltering: true,
            ajaxSorting: false,
            printAsHtml: true,
            printCopyStyle: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: true,
            paginationSizeSelector: false,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Employee",
                    field: "employee_name",
                    headerHozAlign: "left",
                    width: 250,
                },
                {
                    title: "Hourly Rate",
                    field: "hourly_rate",
                    headerHozAlign: "left",
                    width: 150,
                },
                {
                    title: "No Of Days",
                    field: "no_of_days",
                    headerHozAlign: "left",
                    width: 150,
                },
                {
                    title: "Leave Days",
                    field: "dates",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) { 
                        let dates = cell.getData().dates
                        var html = '<div class="whitespace-normal">';
                                html += cell.getData().dates
                            html += '</div>';
                        return html;
                    }
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
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

        $("#tabulator-export-xlsx-SR").on("click", function (event) {
            event.preventDefault();
            window.XLSX = xlsx;
            tableContent.download("xlsx", "Employee_Starter.xlsx", {
                sheetName: "Employee Starter Report",
            });
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    let datePickOpt = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        maxDate: new Date(),
        dropdowns: {
            minYear: 1900,
            maxYear: null,
            months: true,
            years: true,
        },
    };

    

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    $('.lccTom').each(function(){
        if ($(this).attr("multiple") !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: "Remove this item",
                    },
                }
            };
        }
        new TomSelect(this, tomOptions);
    });

    const from_date = new Litepicker({
        element: document.getElementById('from_date'), 
        ...datePickOpt
    });
    const to_date = new Litepicker({
        element: document.getElementById('to_date'), 
        ...datePickOpt
    });

    from_date.on('selected', (date) => {
        const selectedFromDate = date; 

        to_date.setOptions({
            minDate: selectedFromDate,
            //startDate: selectedFromDate
        });

        $('#no_of_days').val('');
        to_date.clearSelection();
    });

    $('#no_of_days').on('change', function(e){
        if($(this).val() > 0){
            //$('#from_date, #to_date').val('');
            from_date.clearSelection();
            to_date.clearSelection();
        }
    })

    $('#searchSickLeave').on('click', function(){
        $('#exportSickLeave').fadeIn()
        $('.empSickLeaveWrap').fadeIn('fast', function(){
            empSickLeaveListTable.init()
        })
    })

    $('#exportSickLeave').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);

        let employee_id = $("#employee_id").val() != "" ? $("#employee_id").val() : "";
        let no_of_days = $("#no_of_days").val() != "" ? $("#no_of_days").val() : "";
        let from_date = $("#from_date").val() != "" ? $("#from_date").val() : "";
        let to_date = $("#to_date").val() != "" ? $("#to_date").val() : "";

        $theBtn.prop('disabled', true);
        $theBtn.find('.theLoader').fadeIn();
        axios({
            method: "post",
            url: route("hr.portal.reports.sick.leave.export"),
            params:{ employee_id: employee_id, no_of_days: no_of_days, from_date: from_date, to_date: to_date },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            responseType: 'blob',
        }).then((response) => {
            $theBtn.prop('disabled', false);
            $theBtn.find('.theLoader').fadeOut();
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'Sick_leave_report.xlsx'); 
            document.body.appendChild(link);
            link.click();
        }).catch((error) => {
            $theBtn.prop('disabled', false);
            $theBtn.find('.theLoader').fadeOut();
            console.log(error);
        });
    })
})();