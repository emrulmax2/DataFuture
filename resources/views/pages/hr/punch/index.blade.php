@extends('../layout/main')

@section('head')
    <title>{{ $title }}</title>
@endsection

@section('content')
<div class="content content--top-nav machineLiveBody">
        <div class="theCardWrap">
            <div class="grid grid-cols-12 gap-0 flex justify-between items-start mb-5">
                <div class="col-span-4">
                    <div class="font-medium text-white">{{ date('l jS F')}}</div>
                    <div class="text-xl font-bold text-white theLocation">London, GB</div>
                </div>
                <div class="col-span-4">
                    <div class="text-4xl font-bold text-white text-center theTemp">11&deg;C</div>
                </div>
                <div class="col-span-4">
                    <div class="font-medium text-white text-right whitespace-normal break-all theFeels">
                        Feels like 10&deg;C
                    </div>
                    <div class="font-medium text-white text-right whitespace-normal break-all theConditions capitalize">
                        Overcast clouds. Gentle Breeze.
                    </div>
                </div>
            </div>
            <div class="box p-0 theLiveCard bg-transparent">
                <div class="grid grid-cols-12 gap-4 mb-5">
                    <div class="col-span-12">
                        <div class="theLiveTime text-2xl font-bold text-center text-white" id="theLiveTime"></div>
                    </div>
                </div>
                @if($ip_check)
                <form method="post" action="#" class="pt-2" id="liveAttendanceForm">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <input type="password" name="clock_in_no" id="clock_in_no" placeholder="Touch Your Card" class="form-control text-center clock_in_no form-control-lg w-full"/>
                        </div>
                        <div class="col-span-12">
                            <div class="liveAttendanceFormBtnGroup">
                                <button type="submit" value="1" disabled class="btn-type-1 btn btn-facebook btn-action" onclick="this.form.attendance_type.value = this.value">
                                    Clock In
                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                        stroke="white" class="w-4 h-4 ml-2">
                                        <g fill="none" fill-rule="evenodd">
                                            <g transform="translate(1 1)" stroke-width="4">
                                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                        to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </button>
                                <button type="button" data-employee="" data-value="4" disabled class="btn-type-4 btn btn-danger btn-action">
                                    Clock Out
                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                        stroke="white" class="w-4 h-4 ml-2">
                                        <g fill="none" fill-rule="evenodd">
                                            <g transform="translate(1 1)" stroke-width="4">
                                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                        to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </button>
                                <button type="submit" value="2" disabled class="btn-type-2 btn btn-twitter btn-action" onclick="this.form.attendance_type.value = this.value">
                                    Break
                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                        stroke="white" class="w-4 h-4 ml-2">
                                        <g fill="none" fill-rule="evenodd">
                                            <g transform="translate(1 1)" stroke-width="4">
                                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                        to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </button>
                                <button type="submit" value="3" disabled class="btn-type-3 btn btn-success text-white btn-action" onclick="this.form.attendance_type.value = this.value">
                                    Return
                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                        stroke="white" class="w-4 h-4 ml-2">
                                        <g fill="none" fill-rule="evenodd">
                                            <g transform="translate(1 1)" stroke-width="4">
                                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                        to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </button>
                                {{--<button disabled disabled class="btn btn-warning text-white btn-back">
                                    Back
                                </button>--}}
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="attendance_type" value="0">
                </form>
                @else 
                <div class="alert alert-danger show flex items-center  mt-2" role="alert">
                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span><strong>Sorry!</strong> you are not allowed to punch from outside the college. </span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- BEGIN: Clockout Confirm Modal Content -->
    <div id="clockoutConfirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 mx-auto text-danger mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Hi <span class="employeeName"></span></div>
                        <div class="text-slate-500 mt-2 confModDesc">
                            <p class="pb-2">
                                Are you sure you want to clock out? Once you do, you won't be able to clock back in today. Clocking out means you're leaving work for today.
                            </p>
                            <p class="pb-2">
                                If you're just going on a break, please click 
                                <button type="button" data-clockinno="" data-type="2" class="btn btn-twitter w-auto mr-1">
                                    Break
                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                        stroke="white" class="w-4 h-4 ml-2">
                                        <g fill="none" fill-rule="evenodd">
                                            <g transform="translate(1 1)" stroke-width="4">
                                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                        to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </button>
                            </p>
                        </div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-clockinno="" data-type="4" data-action="none" class="btn btn-danger w-auto">
                            Confirm Clock Out
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Clockout Confirm Modal Content -->
    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/machine-live.js')
    <script type="module">
        (function () {
            function weatherBGImageLoading(){
                let api = '{{ env("OPEN_WEATHER_MAP_API") }}'; 
                let lat = 51.5422602;
                let lon = 0.0325046;

                fetch('https://api.openweathermap.org/data/2.5/weather?lat='+lat+'&lon='+lon+'&appid='+api+'&units=metric')
                .then(response => response.json())
                .then(data => {
                    console.log(data)
                    
                    let condition = data.weather[0].main;
                    let backgroundImage = '';
                    let theLocation = ''
                        theLocation += data.name;
                        theLocation += (data.sys.country ? ', '+data.sys.country : '');
                    let theTemp = (data.main.temp ? data.main.temp : 0.0);
                        theTemp = Math.round(theTemp)+'°C';
                    let theFeels = (data.main.feels_like ? 'Feels like '+Math.round(data.main.feels_like)+'°C' : '0°C');
                    let theConditions = (data.weather[0].description ? data.weather[0].description+'. ' : '');
                        theConditions += (data.weather[0].main ? ' '+data.weather[0].main+'.' : '');

                    
                    switch(condition.toLowerCase()) {
                        case 'clouds': backgroundImage = '{{ asset("build/assets/images/weather_bg/cloudy.jpg") }}'; break;
                        case 'rain': backgroundImage = '{{ asset("build/assets/images/weather_bg/rainy.jpg") }}'; break;
                        case 'snow': backgroundImage = '{{ asset("build/assets/images/weather_bg/snowy.jpg") }}'; break;
                        case 'clear': backgroundImage = '{{ asset("build/assets/images/weather_bg/sunny.jpg") }}'; break;
                        default: backgroundImage = '{{ asset("build/assets/images/weather_bg/default.jpg") }}';
                    }

                    $('.machineLiveBody').css({'background-image' : 'url('+backgroundImage+')' });
                    $('.theLocation').text(theLocation);
                    $('.theTemp').text(theTemp);
                    $('.theFeels').text(theFeels);
                    $('.theConditions').text(theConditions);
                });
            }
            weatherBGImageLoading();
            setInterval(weatherBGImageLoading, 900000);
        })()
    </script>
@endsection