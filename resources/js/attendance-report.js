import TomSelect from "tom-select";
import dayjs from "dayjs";
import Litepicker from "litepicker";

(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

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

    /*const theMonth = new Litepicker({
        element: document.getElementById('the_month'),
        ...dateOption
    });*/

    let multiTomOpt = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };
    var employeeID = new TomSelect('#employee_id', multiTomOpt);

    $('#attendanceReportForm #employee_id').on('change', function(e){
        let employee_id = $(this).val();
        let the_date = $('#attendanceReportForm #the_date').val();
        
        document.querySelector('#downloadExcel').setAttribute('disabled', 'disabled');

        axios({
            method: "post",
            url: route('hr.portal.reports.attendance.filter'),
            data: {the_date : the_date, employee_id : employee_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#downloadExcel').removeAttribute('disabled');
            
            if (response.status == 200) {
                var res = response.data.res;
                $('.attendanceReportWrap').fadeIn().html(res.html);
                createIcons({icons, "stroke-width": 1.5, nameAttr: "data-lucide"});    
            }
        }).catch(error => {
            document.querySelector('#downloadExcel').removeAttribute('disabled');
            if (error.response) {
                console.log('error');
            }
        });
    })

})();