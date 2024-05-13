@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">My Team Holidays</strong></u></h2>
        <a href="{{ route('user.account.staff') }}" class="btn btn-primary w-auto shadow-md ml-auto">Back to Staff</a>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.users.my-account.show-info')
    <!-- END: Profile Info -->
 
    <div class="intro-y box mt-5">
        <div class="grid grid-cols-12 p-5 pb-0 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">My Team Holiday</div>
            </div>
            <div class="col-span-6 text-right">
                <select name="hrHolidayYear" id="hrHolidayYear" class="form-control w-44">
                    <option value="">Holiday Year</option>
                    @if($holiday_years->count() > 0)
                        @foreach($holiday_years as $hy)
                            <option {{ ($runningHolidayYear == $hy->id ? 'Selected' : '') }} value="{{ $hy->id }}">{{ date('Y', strtotime($hy->start_date)) }} - {{ date('Y', strtotime($hy->end_date)) }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
        <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="p-5 pt-0" id="myTeamHolidayWrap"> 
            {!! $teamHolidays !!}
        </div>
    </div>

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/user-team-holiday.js')
@endsection