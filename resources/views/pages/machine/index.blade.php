@extends('../layout/live')

@section('head')
    <title>{{ $title }}</title>
@endsection

@section('content')
    <div class="content content--top-nav machineLiveBody">
        <div class="box p-10 theLiveCard">
            <div class="grid grid-cols-12 gap-4 mb-5">
                <div class="col-span-6">
                    <div class="theLiveDate text-xl font-medium">{{ date('l jS F, Y')}}</div>
                </div>
                <div class="col-span-6">
                    <div class="theLiveTime text-2xl font-bold text-right" id="theLiveTime"></div>
                </div>
            </div>
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
                            <button type="submit" value="4" disabled class="btn-type-4 btn btn-danger btn-action" onclick="this.form.attendance_type.value = this.value">
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
                            <button disabled disabled class="btn btn-warning text-white btn-back">
                                Back
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="attendance_type" value="0">
            </form>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/machine-live.js')
@endsection