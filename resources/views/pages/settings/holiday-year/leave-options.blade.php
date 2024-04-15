@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('holiday.year') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Holiday Years</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.settings.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Display Information -->
            <form method="POST" action="#" id="holidayYearLeaveOptionForm">
                <div class="intro-y box lg:mt-5">
                    <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                        <h2 class="font-medium text-base mr-auto">Leave Options</h2>
                        <button type="submit" id="updateLO" class="btn btn-primary ml-auto shadow-md w-auto">
                            Update Options
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
                        <input type="hidden" name="hr_holiday_year_id" value="{{ $holidayYear->id }}"/>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-4">
                                <div class="form-check form-switch m-0">
                                    <label class="form-check-label m-0 mr-5" for="leave_option_1">Holiday / Vacation</label>
                                    <input {{ ( in_array(1, $leaveOptions) ? 'checked' : '') }} id="leave_option_1" name="leave_options[]" class="form-check-input" value="1" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch m-0">
                                    <label class="form-check-label m-0 mr-5" for="leave_option_2">Unauthorised Absent</label>
                                    <input {{ ( in_array(2, $leaveOptions) ? 'checked' : '') }} id="leave_option_2" name="leave_options[]" class="form-check-input" value="2" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch m-0">
                                    <label class="form-check-label m-0 mr-5" for="leave_option_3">Sick Leave</label>
                                    <input {{ ( in_array(3, $leaveOptions) ? 'checked' : '') }} id="leave_option_3" name="leave_options[]" class="form-check-input" value="3" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch m-0">
                                    <label class="form-check-label m-0 mr-5" for="leave_option_4">Authorised Unpaid</label>
                                    <input {{ ( in_array(4, $leaveOptions) ? 'checked' : '') }} id="leave_option_4" name="leave_options[]" class="form-check-input" value="4" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch m-0">
                                    <label class="form-check-label m-0 mr-5" for="leave_option_5">Authorised Paid</label>
                                    <input {{ ( in_array(5, $leaveOptions) ? 'checked' : '') }} id="leave_option_5" name="leave_options[]" class="form-check-input" value="5" type="checkbox">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Settings Page Content -->

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
    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/leave-options.js')
@endsection