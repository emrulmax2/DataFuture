
import dayjs from "dayjs";
import Litepicker from "litepicker";
import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";

import helper from "./helper";
import colors from "./colors";
import Chart from "chart.js/auto";
import tippy, { roundArrow } from "tippy.js";

(function(){
    let pgdTomOptions = {
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
    var proxyTutorId = new TomSelect('#proxy_tutor_id', pgdTomOptions); 

    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const cancelClassModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#cancelClassModal"));
    const endClassModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#endClassModal"));
    const proxyClassModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#proxyClassModal"));

    const cancelClassModalEl = document.getElementById('cancelClassModal')
    cancelClassModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#cancelClassModal .acc__input-error').html('');
        $('#cancelClassModal .modal-body textarea').val('');

        $('#cancelClassModal input[name="plan_id"]').val('0');
        $('#cancelClassModal input[name="plans_date_list_id"]').val('0');
    });

    const endClassModalEl = document.getElementById('endClassModal')
    endClassModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#endClassModal .plan_date_list_id').val('');
        $('#endClassModal .attendance_information_id').val('');
    });

    const proxyClassModalEl = document.getElementById('proxyClassModal')
    proxyClassModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#proxyClassModal [name="plan_id"]').val('0');
        $('#proxyClassModal [name="plans_date_list_id"]').val('0');
        $('#proxyClassModal [name="org_tutor_id"]').val('0');
        proxyTutorId.clear(true);
    });

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
        
        $('.dailyClassInfoTableWrap .leaveTableLoader').addClass('active');
        axios({
            method: 'post',
            url: route('programme.dashboard.class.info'),
            data: {planClassStatus : planClassStatus, planCourseId : planCourseId, theClassDate : theDate},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('.dailyClassInfoTableWrap .leaveTableLoader').removeClass('active');
                var res = response.data.res;
                $('#dailyClassInfoTable tbody').html(res.planTable);

                $('.tutorCount').html('('+res.tutors.count+')');
                $('.tutorWrap .theHolder').html(res.tutors.html);
                
                $('.personalTutorCount').html('('+res.ptutors.count+')');
                $('.personalTutorWrap .theHolder').html(res.ptutors.html);

                $('#dailyClassInfoTable .tooltip').each(function () {
                    let options = {
                        content: $(this).attr("title"),
                    };
                    $(this).removeAttr("title");
            
                    tippy(this, {
                        arrow: roundArrow,
                        animation: "shift-away",
                        ...options,
                    });
                });

            }
        }).catch(error =>{
            $('.dailyClassInfoTableWrap .leaveTableLoader').removeClass('active');
            console.log(error)
        });
    });

    /* On Change the Plan Status & Course */
    $('#planClassStatus, #planCourseId').on('change', function(e){
        var planClassStatus = $('#planClassStatus').val();
        var planCourseId = $('#planCourseId').val();
        var theClassDate = $('#theClassDate').val();

        $('.dailyClassInfoTableWrap .leaveTableLoader').addClass('active');
        axios({
            method: 'post',
            url: route('programme.dashboard.class.info'),
            data: {planClassStatus : planClassStatus, planCourseId : planCourseId, theClassDate : theClassDate},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('.dailyClassInfoTableWrap .leaveTableLoader').removeClass('active');
                var res = response.data.res;
                $('#dailyClassInfoTable tbody').html(res.planTable);

                $('.tutorCount').html('('+res.tutors.count+')');
                $('.tutorWrap .theHolder').html(res.tutors.html);
                
                $('.personalTutorCount').html('('+res.ptutors.count+')');
                $('.personalTutorWrap .theHolder').html(res.ptutors.html);
            }
        }).catch(error =>{
            $('.dailyClassInfoTableWrap .leaveTableLoader').removeClass('active');
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

    /* Attendance Rate Chart Start*/
    if ($('#attendanceRateChart').length) {
        let rates = $('#attendanceRateChart').attr('data-rate') * 1;
        let outOf = (100 - rates);
        let ctx = $("#attendanceRateChart")[0].getContext("2d");
        let myDoughnutChart = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: [
                    "",
                    "Term Atendance Rate",
                ],
                datasets: [
                    {
                        data: [outOf, rates],
                        backgroundColor: [
                            '#f1f5f9',
                            colors.primary(0.9),
                        ],
                        hoverBackgroundColor: [
                            '#f1f5f9',
                            colors.primary(0.9),
                        ],
                        borderWidth: 5,
                        borderColor: colors.white,
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                cutout: "80%",
            },
        });
    }
    /* Attendance Rate Chart End*/

    /* Cancel Class Start */
    $(document).on('click', '.cancelClass', function(e){
        var $theBtn = $(this);
        var planid = $theBtn.attr('data-planid');
        var plandateid = $theBtn.attr('data-plandateid');

        $('#cancelClassModal input[name="plan_id"]').val(planid);
        $('#cancelClassModal input[name="plans_date_list_id"]').val(plandateid);
    });

    $('#cancelClassForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('cancelClassForm');
        
        document.querySelector('#saveCancelBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveCancelBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('programme.dashboard.cancel.class'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveCancelBtn').removeAttribute('disabled');
            document.querySelector("#saveCancelBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                cancelClassModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Class status updated to CANCELED.');
                });     

                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 1000);
            }
        }).catch(error => {
            document.querySelector('#saveCancelBtn').removeAttribute('disabled');
            document.querySelector("#saveCancelBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#cancelClassForm .${key}`).addClass('border-danger');
                        $(`#cancelClassForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    /* Cancel Class End */

    /* End Class Start */
    $(document).on('click', '.endClassBtn', function(e){
        var $theBtn = $(this);
        var plandateid = $theBtn.attr('data-plandateid');
        var attendanceinfo = $theBtn.attr('data-attendanceinfo');

        $('#endClassModal input[name="plan_date_list_id"]').val(plandateid);
        $('#endClassModal input[name="attendance_information_id"]').val(attendanceinfo);
    });


    $('#endClassModalForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('endClassModalForm');
        
        document.querySelector('#endClassBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#endClassBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('programme.dashboard.end.class'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#endClassBtn').removeAttribute('disabled');
            document.querySelector("#endClassBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200){
                endClassModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "WOW!" );
                    $("#successModal .successModalDesc").html('Class successfully ended.');
                });     

                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 1000);
            }
        }).catch(error => {
            document.querySelector('#endClassBtn').removeAttribute('disabled');
            document.querySelector("#endClassBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if(error.response.status == 422){
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html( "Oops!" );
                        $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact with the administrator.');
                    });     

                    setTimeout(function(){
                        successModal.hide();
                    }, 1000);
                }else{
                    console.log('error');
                }
            }
        });
    });
    /* End Class End */

    /* Proxy Class Start */
    $(document).on('click', '.proxyClass', function(e){
        e.preventDefault();
        let $theLink = $(this);
        let plan_id = $theLink.attr('data-planid');
        let plan_date_id = $theLink.attr('data-plandateid');
        let org_tutor_id = $theLink.attr('data-tutorid');
        
        $('#proxyClassModal input[name="plan_id"]').val(plan_id);
        $('#proxyClassModal input[name="plans_date_list_id"]').val(plan_date_id);
        $('#proxyClassModal input[name="org_tutor_id"]').val(org_tutor_id);

    });

    $('#proxyClassForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('proxyClassForm');

        let org_tutor_id = $form.find('[name="org_tutor_id"]').val();
        let proxy_tutor_id = proxyTutorId.getValue();
        
        document.querySelector('#saveReAsignBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveReAsignBtn svg").style.cssText ="display: inline-block;";
        if(org_tutor_id == proxy_tutor_id){
            document.querySelector('#saveReAsignBtn').removeAttribute('disabled');
            document.querySelector("#saveReAsignBtn svg").style.cssText = "display: none;";

            $('#proxyClassModal .modal-content .proxyWarning').remove();
            $('#proxyClassModal .modal-content').prepend('<div class="alert proxyWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! You cant not assign same tutor as a proxy.</div>').fadeIn();

            createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });

            setTimeout(function(){
                $('#proxyClassModal .modal-content .proxyWarning').remove();
            }, 5000)
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('programme.dashboard.reassign.class'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveReAsignBtn').removeAttribute('disabled');
                document.querySelector("#saveReAsignBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200){
                    proxyClassModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "WOW!" );
                        $("#successModal .successModalDesc").html('Class successfully re-assigned to the new tutor.');
                    });     

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 1000);
                }
            }).catch(error => {
                document.querySelector('#saveReAsignBtn').removeAttribute('disabled');
                document.querySelector("#saveReAsignBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if(error.response.status == 422){
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#proxyClassForm .${key}`).addClass('border-danger')
                            $(`#proxyClassForm  .error-${key}`).html(val)
                        }
                    }else{
                        console.log('error');
                    }
                }
            });
        }
    });
    /* Proxy Class End */
})();