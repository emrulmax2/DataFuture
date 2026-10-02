import { createIcons, icons } from "lucide";


(function(){
    const clockoutConfirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#clockoutConfirmModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    
    const clockoutConfirmModalEl = document.getElementById('clockoutConfirmModal')
    clockoutConfirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#clockoutConfirmModal .employeeName').html('');
        $('#clockoutConfirmModal button').attr('data-clockinno', clock_in_no);

        $('#clockoutConfirmModal button').removeAttr('disabled');
        $('#clockoutConfirmModal button').find('svg').fadeOut();

        let $form = $('#liveAttendanceForm');
        setTimeout(function(){
            $form.find('.theMessage').remove();
            $form.find('.liveAttendanceFormBtnGroup').fadeOut('fast', function(){
                $form.find('.btn-action').fadeOut().attr('disabled', 'disabled');
                $form.find('.btn-back').fadeOut().attr('disabled', 'disabled');
                $form.find('#clock_in_no').val('');
                $form.find('[name="attendance_type"]').val('0');
            });
        }, 5000);
    });

    $('#clockoutConfirmModal button').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let clockinno = $theBtn.attr('data-clockinno');
        let type = $theBtn.attr('data-type');

        $('#clockoutConfirmModal button').attr('disabled', 'disabled');
        $theBtn.find('svg').fadeIn();

        axios({
            method: "post",
            url: route('attendance.punch.store.dif'),
            data: {clockinno : clockinno, type : type},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            clockoutConfirmModal.hide();
            $('#liveAttendanceForm').find('.theMessage').remove();
            $('#liveAttendanceForm').find('svg').css({display : 'none'});
            $('#liveAttendanceForm .btn-action').fadeOut().attr('disabled', 'disabled');
            $('#liveAttendanceForm').find('[name="attendance_type"]').val('0');
            $('#liveAttendanceForm').find('#clock_in_no').val('');

            if (response.status == 200) {
                let res = response.data.res;
                
                if(res.suc == 2){
                    $('#liveAttendanceForm').find('.theMessage').remove();
                    $('#liveAttendanceForm').prepend('<div class="text-white alert alert-warning theMessage show flex items-start mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i><span>'+res.msg+'</span></div>')
                    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });
                }else{
                    $('#liveAttendanceForm').find('.theMessage').remove();
                    $('#liveAttendanceForm').prepend('<div class="text-white alert alert-success theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i><span>'+res.msg+'</span></div>')
                    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });
                }

                setTimeout(function(){
                    $('#liveAttendanceForm').find('.theMessage').fadeOut().remove();
                }, 2000);
            }
            
        }).catch(error => {
            if(error.response){
                if(error.response.status == 422){
                    console.log('error');
                }
            }
        });
    });

    $('#liveAttendanceForm .btn-action.btn-type-4').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let employee = $theBtn.attr('data-employee');
        let clock_in_no = $('#clock_in_no').val();
        $('#liveAttendanceForm').removeClass('activeForm');
        clockoutConfirmModal.show();

        $('#clockoutConfirmModal .employeeName').html(employee);
        $('#clockoutConfirmModal button').attr('data-clockinno', clock_in_no);
    });

    $('#liveAttendanceForm input[name="clock_in_no"]').on('keypress', function(event){
        if(event.which == 13){
            
            var $form = $('#liveAttendanceForm');
            let clock_in_no = $form.find('[name="clock_in_no"]').val();
            var $buttonGroup = $form.find('.liveAttendanceFormBtnGroup');
            var $actionButtons = $buttonGroup.find('.btn-action');
            //var $backButton = $buttonGroup.find('.btn-back');
            var clockinno = $form.find('#clock_in_no').val();
            if(clock_in_no != ''){
                axios({
                    method: "post",
                    url: route('attendance.punch.get.history'),
                    data: {clockinno : clockinno},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        let res = response.data.res;

                        /* "Allow All Services". Without it this terminal is a
                           clock-in point only: breaks and clocking out are done
                           from the person's own dashboard. The server refuses
                           those actions regardless — this just avoids offering
                           a button that would come back with a refusal. */
                        let allServices = res.all_services === true || res.all_services === 1 || res.all_services === '1';

                        /* The API pads the name with a trailing space, which
                           reads as "NAME , you are..." once it sits next to
                           punctuation. */
                        let name = function () { return $.trim(res.name || ''); };

                        /* Reveals a button only when this person may use it,
                           so every branch below can stay as it reads. */
                        let allow = function (type) {
                            if (type !== 1 && !allServices) {
                                return $();
                            }
                            return $buttonGroup.find('.btn-action.btn-type-' + type).fadeIn().removeAttr('disabled');
                        };

                        /* Called at the end of each branch. Someone restricted
                           to clock-in who is already clocked in has nothing to
                           press here, and a greeting over an empty row reads as
                           a broken terminal — so it says where to go instead. */
                        let noteIfNothingToDo = function () {
                            if ($buttonGroup.find('.btn-action:not([disabled])').length) {
                                return;
                            }
                            $form.find('.theMessage').remove();
                            /* Icon and text are the only two flex items. Left
                               loose, each run of text becomes its own item and
                               a two-word name is laid out as a block of its
                               own, pushing the comma onto a line by itself. */
                            $form.prepend('<div class="text-white alert alert-pending theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="info" class="w-6 h-6 mr-2 flex-none"></i> <span>Hi <strong>' + name() + '</strong>, you are clocked in. Please use your dashboard to take a break or clock out.</span></div>');
                            createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });
                        };

                        if(res.loc == '0'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="text-white alert alert-success theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2 flex-none"></i> <span>Hi <strong>'+name()+'</strong>, what would you like to do?</span></div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                allow(1);
                                //$backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else if(res.loc == '1'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="text-white alert alert-success theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2 flex-none"></i> <span>Hi <strong>'+name()+'</strong>, what would you like to do?</span></div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                allow(2);
                                allow(4).attr('data-employee', res.name);
                                noteIfNothingToDo();
                                //$backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else if(res.loc == '2'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="text-white alert alert-success theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2 flex-none"></i> <span>Hi <strong>'+name()+'</strong>, what would you like to do?</span></div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                allow(3);
                                noteIfNothingToDo();
                                //$backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else if(res.loc == '3'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="text-white alert alert-success theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2 flex-none"></i> <span>Hi <strong>'+name()+'</strong>, what would you like to do?</span></div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                allow(2);
                                allow(4).attr('data-employee', res.name);
                                noteIfNothingToDo();
                                //$backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else if(res.loc == '4'){
                            $buttonGroup.fadeIn('fast', function(){
                                $form.find('.theMessage').remove();
                                $form.prepend('<div class="text-white alert alert-danger theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2 flex-none"></i> <span>Hi <strong>'+name()+'</strong>, It seems that you are already clocked out for the day.</span></div>')
                                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                                $actionButtons.fadeOut().attr('disabled', 'disabled');
                                //$buttonGroup.find('.btn-action.btn-type-1').fadeIn().removeAttr('disabled');
                                //$backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
                            });

                            $form.addClass('activeForm');
                        }else{
                            $form.find('.theMessage').remove();
                            $form.prepend('<div class="text-white alert alert-danger theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2 flex-none"></i> <span>User not found.</span></div>')
                            createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });

                            $buttonGroup.fadeIn();
                            $actionButtons.attr('disabled', 'disabled');
                            //$backButton.css({ display: 'inline-flex'}).removeAttr('disabled');
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
                        }, 10000);
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
        //let $backBtn = $form.find('.btn-back');
        let $actopmBtn = $form.find('.btn-action');

        //$backBtn.attr('disabled', 'disabled');
        $submitBtn.attr('disabled', 'disabled');
        $submitBtn.find('svg').css({display : 'inline-block'})

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('attendance.punch.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            //$backBtn.removeAttr('disabled', 'disabled');
            $submitBtn.find('svg').css({display : 'none'});
            $actopmBtn.fadeOut().attr('disabled', 'disabled');
            $form.find('[name="attendance_type"]').val('0');
            $form.find('#clock_in_no').val('');
            
            if (response.status == 200) {
                let res = response.data.res;
                    
                if(res.suc == 2){
                    $form.find('.theMessage').remove();
                    $form.prepend('<div class="text-white alert alert-warning theMessage show flex items-start mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i><span>'+res.msg+'</span></div>')
                    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });
                }else{
                    $form.find('.theMessage').remove();
                    $form.prepend('<div class="text-white alert alert-success theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i><span>'+res.msg+'</span></div>')
                    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide", });
                }

                setTimeout(function(){
                    $form.find('.theMessage').fadeOut().remove();
                }, 2000);
            }
            
        }).catch(error => {
            //$backBtn.removeAttr('disabled', 'disabled');
            $submitBtn.find('svg').css({display : 'none'});
            $actopmBtn.fadeOut().attr('disabled', 'disabled');
            $form.find('[name="attendance_type"]').val('0');
            $form.find('#clock_in_no').val('');
            if(error.response){
                if(error.response.status == 422){
                    $form.find('.theMessage').remove();
                    $form.prepend('<div class="alert alert-danger text-white theMessage show flex items-center mb-3 text-lg font-medium" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Something went wrong. Please try later.</div>')
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