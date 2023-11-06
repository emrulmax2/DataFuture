@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Welcome <u><strong>{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.tutor.dashboard.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Today's Class List [ {{ $date }} ]</div>
                </div>
                <div class="col-span-6 text-right">
                    
                    <input type="text" value="" placeholder="DD-MM-YYYY" id="plan_date" date-value="{{ $date }}" class="form-control w-auto datepicker" name="plan_date" data-format="DD-MM-YYYY" data-single-mode="true">
                    <button id="planDateSearchBtn" type="submit" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <input type="hidden" name="tutor_id" value="{{ $user->id }}" />
                        <div id="tutorClassList" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @include('pages.tutor.dashboard.modals')
@endsection

@section('script')
    @vite('resources/js/tutor-dashboard.js')
@endsection