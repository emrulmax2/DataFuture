

(function(){
    const loadMonth = ($theBtn) => {
        if($theBtn.hasClass('dataLoaded') || $theBtn.hasClass('dataLoading')){
            return;
        }

        let target = $theBtn.attr('data-target');
        let theDate = $theBtn.attr('data-date');
        let theEmployee = $theBtn.attr('data-employee');
        let theYear = $theBtn.attr('data-year');
        let $monthItem = $theBtn.closest('.lcc_month_accordion_item');
        let $monthBadge = $monthItem.find('.js-month-worked-total').first();

        $theBtn.addClass('dataLoading');
        $monthBadge.text('Loading...');

        axios({
            method: "post",
            url: route("employee.time.keeper.generate.recored"),
            data: {employee_id : theEmployee, the_date : theDate, holiday_year : theYear},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                $theBtn.addClass('dataLoaded');
                let res = response.data.res;

                $(target+' .ep-tk-table tbody').html(res.html);
                $(target+' .ep-tk-table tfoot .tfootTotalWorkingHour').html(res.workingHourTotal);
                $(target+' .ep-tk-table tfoot .tfootTotalHolidayHour').html(res.holidayHourTotal);
                $(target+' .ep-tk-table tfoot .tfootTotalPay').html(res.monthTotalPay);
                $monthBadge.text(res.workingHourTotal + ' worked').addClass('is-loaded');
            }
        })
        .catch((error) => {
            $monthBadge.text('Try again');
            if (error.response) {
                console.log("error");
            }
        })
        .finally(() => {
            $theBtn.removeClass('dataLoading');
        });
    };

    $('.lcc_accordion_button').on('click', function(){
        let $theBtn = $(this);
        let $lcc_custom_accordion = $theBtn.parent('.lcc_accordion_item').parent('.lcc_custom_accordion');
        let $lcc_accordion_item = $theBtn.parent('.lcc_accordion_item');
        let $lcc_accordion_body = $lcc_accordion_item.find('.lcc_accordion_body');

        if($theBtn.hasClass('active')){
            $lcc_accordion_body.slideUp();
            $theBtn.toggleClass('active');
        }else{
            $lcc_custom_accordion.find('.lcc_accordion_button').removeClass('active');
            $lcc_custom_accordion.find('.lcc_accordion_body').slideUp();

            $theBtn.toggleClass('active');
            $lcc_accordion_body.slideDown();
        }
    });

    $('.lcc_month_accordion_button').on('click', function(){
        let $theMonthBtn = $(this);
        let $employee_month_attendance_accordion = $theMonthBtn.parent('.lcc_month_accordion_item').parent('.employee_month_attendance_accordion');
        let $lcc_month_accordion_item = $theMonthBtn.parent('.lcc_month_accordion_item');
        let $lcc_month_accordion_body = $lcc_month_accordion_item.find('.lcc_month_accordion_body');
        $employee_month_attendance_accordion.addClass('used')
        if($theMonthBtn.hasClass('active')){
            $lcc_month_accordion_body.slideUp();
            $theMonthBtn.toggleClass('active');
        }else{
            $employee_month_attendance_accordion.find('.lcc_month_accordion_button').removeClass('active');
            $employee_month_attendance_accordion.find('.lcc_month_accordion_body').slideUp();

            $theMonthBtn.toggleClass('active');
            $lcc_month_accordion_body.slideDown();
            loadMonth($theMonthBtn);
        }
    });

    $('.lccEmpTimeKeepingBtn.active').each(function(){
        loadMonth($(this));
    });
})();
