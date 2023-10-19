@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

<!-- BEGIN: Profile Info -->
@include('pages.students.frontend.dashboard.show-info')
<!-- END: Profile Info -->

<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Course Details</div>
        </div>
    </div>
    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
    <div class="grid grid-cols-12 gap-4"> 
        <div class="col-span-12 sm:col-span-12">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-4 text-slate-500 font-medium">Course & Semester</div>
                <div class="col-span-8 font-medium">{{ $student->crel->creation->course->name.' - '.$student->crel->propose->semester->name }}</div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-12">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-4 text-slate-500 font-medium">Awarding Body</div>
                <div class="col-span-8 font-medium">{{ (isset($student->crel->creation->course->body->name) ? $student->crel->creation->course->body->name : 'Unknown')}}</div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-12">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-4 text-slate-500 font-medium">Duration</div>
                <div class="col-span-8 font-medium">
                    {{ (isset($student->crel->creation->duration) ? $student->crel->creation->duration : '0')}} 
                    {{ (isset($student->crel->creation->unit_length) ? $student->crel->creation->unit_length : '')}} 
                </div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-12">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-4 text-slate-500 font-medium">SLC Course Code</div>
                <div class="col-span-8 font-medium">{{ (isset($student->crel->creation->slc_code) ? $student->crel->creation->slc_code : 'Unknown')}} </div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-12">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-4 text-slate-500 font-medium">Evening & Weekend Indicator</div>
                <div class="col-span-8 font-medium">{!! (isset($student->crel->propose->full_time) && $student->crel->propose->full_time == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-12">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-4 text-slate-500 font-medium">Fee Eligibility</div>
                <div class="col-span-8 font-medium">{!! (isset($student->crel->feeeligibility->elegibility->name) && isset($student->crel->feeeligibility->fee_eligibility_id) && $student->crel->feeeligibility->fee_eligibility_id > 0 ? $student->crel->feeeligibility->elegibility->name : '---') !!}</div>
            </div>
        </div>
    </div>
</div>
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Awarding Body</div>
        </div>
    </div>
    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
    <div class="grid grid-cols-12 gap-4"> 
        <div class="col-span-12 sm:col-span-12">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-4 text-slate-500 font-medium">Awarding Body Ref</div>
                <div class="col-span-8 font-medium">{{ (isset($student->crel->abody->reference) ? $student->crel->abody->reference : '') }}</div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-12">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-4 text-slate-500 font-medium">Awarding Body Reg. Expire Date</div>
                <div class="col-span-8 font-medium">{{ (isset($student->crel->abody->registration_expire_date) ? $student->crel->abody->registration_expire_date : '') }}</div>
            </div>
        </div>
    </div>
</div>
   
@endsection


@section('script')
    @vite('resources/js/student-frontend-dashboard.js')
@endsection
