import { createIcons, icons } from "lucide";


(function(){
    $('#liveAttendanceForm input[name="clock_in_no"]').on('keypress', function(event){
        if(event.which == 13){
            
            var $form = $('#liveAttendanceForm');
            let clock_in_no = $form.find('[name="clock_in_no"]').val();
            var $buttonGroup = $form.find('.liveAttendanceFormBtnGroup');
            var $actionButtons = $buttonGroup.find('.btn-action');
            var $backButton = $buttonGroup.find('.btn-back');
            var clockinno = $form.find('#clock_in_no').val();
            if(clock_in_no != ''){
                axios({
                    method: "post",
                    url: route('machine.get.attendance.history'),
                    data: {clockinno : clockinno},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        let res = response.data.res;
                        if(res.loc == '0'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="alert alert-success-soft theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> Hi &nbsp;<strong>'+res.name+'</strong>, what would you like to do?</div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                $buttonGroup.find('.btn-action.btn-type-1').fadeIn().removeAttr('disabled');
                                $backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else if(res.loc == '1'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="alert alert-success-soft theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> Hi &nbsp;<strong>'+res.name+'</strong>, what would you like to do?</div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                $buttonGroup.find('.btn-action.btn-type-2').fadeIn().removeAttr('disabled');
                                $buttonGroup.find('.btn-action.btn-type-4').fadeIn().removeAttr('disabled');
                                $backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else if(res.loc == '2'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="alert alert-success-soft theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> Hi &nbsp;<strong>'+res.name+'</strong>, what would you like to do?</div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                $buttonGroup.find('.btn-action.btn-type-3').fadeIn().removeAttr('disabled');
                                $backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else if(res.loc == '3'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="alert alert-success-soft theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> Hi &nbsp;<strong>'+res.name+'</strong>, what would you like to do?</div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                $buttonGroup.find('.btn-action.btn-type-2').fadeIn().removeAttr('disabled');
                                $buttonGroup.find('.btn-action.btn-type-4').fadeIn().removeAttr('disabled');
                                $backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else if(res.loc == '4'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="alert alert-success-soft theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> Hi &nbsp;<strong>'+res.name+'</strong>, what would you like to do?</div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                $buttonGroup.find('.btn-action.btn-type-1').fadeIn().removeAttr('disabled');
                                $backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else{
                            $form.find('.theMessage').remove();
                            $form.prepend('<div class="alert alert-danger-soft theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> User does not foud!</div>')
                            createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                            $buttonGroup.fadeIn();
                            $actionButtons.attr('disabled', 'disabled');
                            $backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                        }

                        setTimeout(function(){
                            var $form = $('#liveAttendanceForm');
                            if($form.hasClass('activeForm')){
                                $form.find('.theMessage').remove();
                                $form.find('.liveAttendanceFormBtnGroup').fadeOut('fast', function(){
                                    $form.find('.btn-action').fadeOut().attr('disabled', 'disabled');
                                    $form.find('.btn-back').fadeOut().attr('disabled', 'disabled');
                                    $form.find('#clock_in_no').val('');
                                    $form.find('[name="attendance_type"]').val('0');
                                });
                                $form.removeClass('activeForm');
                            }
                        }, 40000);
                    }
                    
                }).catch(error => {
                    if(error.response){
                        if(error.response.status == 422){
                            console.log('error');
                        }
                    }
                });
            }
        }
    });

    $('#liveAttendanceForm').on('click', '.btn-back', function(e){
        e.preventDefault();
        var $form = $('#liveAttendanceForm');
        $form.find('.theMessage').remove();
        $form.find('.liveAttendanceFormBtnGroup').fadeOut('fast', function(){
            $form.find('.btn-action').fadeOut().attr('disabled', 'disabled');
            $form.find('.btn-back').fadeOut().attr('disabled', 'disabled');
            $form.find('#clock_in_no').val('');
            $form.find('[name="attendance_type"]').val('0');
        });
    });

    $('#liveAttendanceForm input[name="clock_in_no"]').trigger('focus');
    // Force focus
    $('#liveAttendanceForm input[name="clock_in_no"]').on('focusout', function(){
        $('#liveAttendanceForm input[name="clock_in_no"]').trigger('focus');
    });


    $('#liveAttendanceForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('liveAttendanceForm');

        let $form = $(this);
        let attendance_type = $form.find('[name="attendance_type"]').val();
        let $submitBtn = $form.find('.btn-type-'+attendance_type);
        let $backBtn = $form.find('.btn-back');
        let $actopmBtn = $form.find('.btn-action');

        $backBtn.attr('disabled', 'disabled');
        $submitBtn.attr('disabled', 'disabled');
        $submitBtn.find('svg').css({display : 'inline-block'})

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('machine.store.attendance'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $backBtn.removeAttr('disabled', 'disabled');
            $submitBtn.find('svg').css({display : 'none'});
            $actopmBtn.fadeOut().attr('disabled', 'disabled');
            $form.find('[name="attendance_type"]').val('0');
            $form.find('#clock_in_no').val('');
            
            if (response.status == 200) {
                let res = response.data.res;
                    
                if(res.suc == 2){
                    $form.find('.theMessage').remove();
                    $form.prepend('<div class="alert alert-warning-soft theMessage show flex items-start mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i><span>'+res.msg+'</span></div>')
                    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });
                }else{
                    $form.find('.theMessage').remove();
                    $form.prepend('<div class="alert alert-success-soft theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i><span>'+res.msg+'</span></div>')
                    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });
                }
            }
            
        }).catch(error => {
            $backBtn.removeAttr('disabled', 'disabled');
            $submitBtn.find('svg').css({display : 'none'});
            $actopmBtn.fadeOut().attr('disabled', 'disabled');
            $form.find('[name="attendance_type"]').val('0');
            $form.find('#clock_in_no').val('');
            if(error.response){
                if(error.response.status == 422){
                    $form.find('.theMessage').remove();
                    $form.prepend('<div class="alert alert-danger-soft theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Something went wrong. Please try later.</div>')
                    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                    console.log('error');
                }
            }
        });
    })


    if($('#theLiveTime').length > 0){
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
        $("#theLiveTime").html(currentTimeString);
    }

})();