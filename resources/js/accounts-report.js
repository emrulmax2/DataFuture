import { createIcons, icons } from "lucide";

(function(){
    let pickerOptions = {
        autoApply: true,
        singleMode: false,
        numberOfColumns: 2,
        numberOfMonths: 2,
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
                    window.location.href = route('accounts.report', theDatesArr);
                }
            });
        }
    });
    
    $('.categoryToggler').on('click', function(e){
        e.preventDefault();
        let $theLink = $(this);
        let category_id = $theLink.attr('data-id');
        let start_date = $theLink.attr('data-start');
        let end_date = $theLink.attr('data-end');

        let $expandTr = $('.dt_'+category_id);
        if($theLink.hasClass('active')){
            $theLink.removeClass('active');
            $theLink.find('svg').fadeOut('fast');
            $expandTr.css({'display' : 'none'});
            $expandTr.find('td.data_td').html('');
        }else{
            $theLink.addClass('active');
            $theLink.find('svg').fadeIn('fast');
            axios({
                method: "post",
                url: route('accounts.report.details'),
                data: {category_id : category_id, start_date : start_date, end_date : end_date},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theLink.find('svg').fadeOut('fast');
                if (response.status == 200) {
                    $expandTr.css({'display' : 'table-row'});
                    $expandTr.find('td.data_td').html(response.data.res);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                $theLink.removeClass('active');
                $theLink.find('svg').fadeOut('fast');
                $expandTr.css({'display' : 'none'});
                $expandTr.find('td.data_td').html('');

                if (error.response) {
                    console.log('error');
                }
            });
        }
    })
})();