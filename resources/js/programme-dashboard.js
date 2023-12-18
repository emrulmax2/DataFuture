
import dayjs from "dayjs";
import Litepicker from "litepicker";
import { createIcons, icons } from "lucide";

(function(){

    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        inlineMode: false,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };
    const theClassDate = new Litepicker({
        element: document.getElementById('theClassDate'),
        ...dateOption
    });

    /* On Change The Calendar */
    theClassDate.on('selected', (date) => {
        let theYear = date.getFullYear();
        let theMonth = date.getMonth() + 1;
        let theDay = date.getDate();

        let theDate = theYear+'-'+theMonth+'-'+theDay;
        var planClassStatus = $('#planClassStatus').val();
        var planCourseId = $('#planCourseId').val();
        
        $('.dailyClassInfoTableWrap .leaveTableLoader, .tutorWrap .leaveTableLoader, .personalTutorWrap .leaveTableLoader').addClass('active');
        axios({
            method: 'post',
            url: route('programme.dashboard.class.info'),
            data: {planClassStatus : planClassStatus, planCourseId : planCourseId, theClassDate : theDate},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('.dailyClassInfoTableWrap .leaveTableLoader, .tutorWrap .leaveTableLoader, .personalTutorWrap .leaveTableLoader').removeClass('active');
                var res = response.data.res;
                $('#dailyClassInfoTable tbody').html(res.planTable);

                $('.tutorCount').html(res.tutors.count);
                $('.tutorWrap .theHolder').html(res.tutors.html);
                
                $('.personalTutorCount').html(res.ptutors.count);
                $('.personalTutorWrap .theHolder').html(res.ptutors.html);
            }
        }).catch(error =>{
            $('.dailyClassInfoTableWrap .leaveTableLoader, .tutorWrap .leaveTableLoader, .personalTutorWrap .leaveTableLoader').removeClass('active');
            console.log(error)
        });
    });

    /* On Change the Plan Status & Course */
    $('#planClassStatus, #planCourseId').on('change', function(e){
        var planClassStatus = $('#planClassStatus').val();
        var planCourseId = $('#planCourseId').val();
        var theClassDate = $('#theClassDate').val();

        $('.dailyClassInfoTableWrap .leaveTableLoader, .tutorWrap .leaveTableLoader, .personalTutorWrap .leaveTableLoader').addClass('active');
        axios({
            method: 'post',
            url: route('programme.dashboard.class.info'),
            data: {planClassStatus : planClassStatus, planCourseId : planCourseId, theClassDate : theClassDate},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('.dailyClassInfoTableWrap .leaveTableLoader, .tutorWrap .leaveTableLoader, .personalTutorWrap .leaveTableLoader').removeClass('active');
                var res = response.data.res;
                $('#dailyClassInfoTable tbody').html(res.planTable);

                $('.tutorCount').html(res.tutors.count);
                $('.tutorWrap .theHolder').html(res.tutors.html);
                
                $('.personalTutorCount').html(res.ptutors.count);
                $('.personalTutorWrap .theHolder').html(res.ptutors.html);
            }
        }).catch(error =>{
            $('.dailyClassInfoTableWrap .leaveTableLoader, .tutorWrap .leaveTableLoader, .personalTutorWrap .leaveTableLoader').removeClass('active');
            console.log(error)
        });
    })

    

    if($('#theClock').length > 0){
        setInterval(updateClock, 1000);
    }

    function updateClock() {
        var currentTime = new Date();
        // Operating System Clock Hours for 12h clock
        var currentHoursAP = currentTime.getHours();
        // Operating System Clock Hours for 24h clock
        var currentHours = currentTime.getHours();
        // Operating System Clock Minutes
        var currentMinutes = currentTime.getMinutes();
        // Operating System Clock Seconds
        var currentSeconds = currentTime.getSeconds();
        // Adding 0 if Minutes & Seconds is More or Less than 10
        currentMinutes = (currentMinutes < 10 ? "0" : "") + currentMinutes;
        currentSeconds = (currentSeconds < 10 ? "0" : "") + currentSeconds;
        // Picking "AM" or "PM" 12h clock if time is more or less than 12
        var timeOfDay = (currentHours < 12) ? "AM" : "PM";
        // transform clock to 12h version if needed
        currentHoursAP = (currentHours > 12) ? currentHours - 12 : currentHours;
        // transform clock to 12h version after mid night
        currentHoursAP = (currentHoursAP == 0) ? 12 : currentHoursAP;
        // display first 24h clock and after line break 12h version
        var currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds;
        // print clock js in div #clock.
        $("#theClock").html(currentTimeString);
    }
})();