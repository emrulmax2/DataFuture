import { createIcons, icons } from "lucide";

(function(){
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
                    window.location.href = route('accounts.management.report', theDatesArr);
                }
            });
        }
    });

    $('#managementReportTable .toggleChildRows').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var parent_id = $theLink.attr('data-parent');

        $('#managementReportTable .child_of_'+parent_id).fadeToggle();
    });

    $('#managementReportTable .toggleSalesRows').on('click', function(e){
        e.preventDefault();
        var $theLink = $(this);

        $('#managementReportTable .sales_child_row').fadeToggle();
    });
})()