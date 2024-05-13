import { createIcons, icons } from "lucide";

(function(){
    $('#hrHolidayYear').on('change', function(){
        let $theHolidayYear = $(this);
        let theHolidayYear = $theHolidayYear.val();

        $theHolidayYear.attr('readonly', 'readonly');
        if(theHolidayYear > 0 && theHolidayYear != ''){
            axios({
                method: "POST",
                url: route('user.account.staff.team.holiday.ajax'),
                data: {holiday_year : theHolidayYear},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theHolidayYear.removeAttr('readonly');
                if (response.status == 200) {
                    let htmls = response.data.res;
                    
                    $('#myTeamHolidayWrap').html(htmls);
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $theHolidayYear.removeAttr('readonly');
            $('#myTeamHolidayWrap').html('<div class="alert alert-danger-soft show flex items-start mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <div><strong>Oops!</strong> Please select a holiday year to view your team members holiday calculations.</div></div>');
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        }
    })
})()