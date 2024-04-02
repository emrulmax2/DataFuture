
import dayjs from "dayjs";
import Litepicker from "litepicker";

(function(){
    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        format: "MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const theMonth = new Litepicker({
        element: document.getElementById('the_month'),
        ...dateOption
    });


    $('#attendanceReportForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('attendanceReportForm');
        
        document.querySelector('#resetForm').setAttribute('disabled', 'disabled');
        document.querySelector('#downloadExcel').setAttribute('disabled', 'disabled');
        document.querySelector('#generateReport').setAttribute('disabled', 'disabled');
        document.querySelector("#generateReport svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('hr.portal.reports.attendance.generate'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#resetForm').removeAttribute('disabled');
            document.querySelector('#downloadExcel').removeAttribute('disabled');
            document.querySelector('#generateReport').removeAttribute('disabled');
            document.querySelector("#generateReport svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                var res = response.data.res;
                $('.attendanceReportWrap').fadeIn().html(res.html);
                createIcons({icons, "stroke-width": 1.5, nameAttr: "data-lucide"});    
            }
        }).catch(error => {
            document.querySelector('#resetForm').removeAttribute('disabled');
            document.querySelector('#downloadExcel').removeAttribute('disabled');
            document.querySelector('#generateReport').removeAttribute('disabled');
            document.querySelector("#generateReport svg").style.cssText = "display: none;";
            if (error.response) {
                console.log('error');
            }
        });
    })

})();