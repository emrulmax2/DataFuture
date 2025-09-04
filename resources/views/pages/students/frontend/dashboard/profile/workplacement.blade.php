@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 2xl:col-span-9">
        <div class="grid grid-cols-12 gap-6">
            <!-- BEGIN: Profile Info -->
            @include('pages.students.frontend.dashboard.show-info')
            <!-- END: Profile Info -->
            
            <div class="intro-y mt-5 col-span-12">
                <form method="post" action="#" id="studentAttendanceExcuseForm" enctype="multipart/form-data">
                    <div class="intro-y box">
                        <div class="grid grid-cols-12 gap-0 items-center p-5">
                            <div class="col-span-6">
                                <div class="font-medium text-base">Workplacement Details</div>
                            </div>
                        </div>
                        <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        <div class="p-5">
                            @if(!empty($workplacement_details))
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12">
                                    <div class="grid grid-cols-12 gap-3 items-center">
                                        <div class="col-span-4 text-slate-500 font-medium">Workplacement Details</div>
                                        <div class="col-span-8">
                                            <span class=" inline-flex px-2 py-0 ml-2 rounded-0">
                                                {{ $workplacement_details->name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-6">
                                    <div class="grid grid-cols-12 gap-3 items-center">
                                        <div class="col-span-4 text-slate-500 font-medium mb-4">Hours Required</div>
                                        <div class="col-span-8">
                                            <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0 mb-4 theTogglers">
                                                {{ $student->crel->creation->required_hours.' Hours' }}
                                                <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto collapsibles">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                @foreach($total_hours_calculations as $level_hours)
                                                        @foreach($level_hours->learning_hours as $learningHour)
                                                            <tr>
                                                                <td>{{ (isset($level_hours->name) && $level_hours->name) ? $level_hours->name : '' }}</td>
                                                                <td>{{ (isset($learningHour->name) && $learningHour->name) ? $learningHour->name : '' }}</td>
                                                                <td>{{ (isset($learningHour->hours) && $learningHour->hours > 0 ? $learningHour->hours.' Hours' : '0 Hours') }}</td>
                                                            </tr>
                                                        @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-6">
                                    <div class="grid grid-cols-12 gap-3 items-center">
                                        <div class="col-span-4 text-slate-500 font-medium mb-4">Hours Completed</div>
                                        <div class="col-span-8">
                                            <span class="btn inline-flex btn-success px-2 py-0 ml-2 text-white rounded-0 hoursCompleted mb-4 theTogglers">
                                                {{ (isset($work_hours) && $work_hours > 0 ? $work_hours.' Hours' : '0 Hours') }}
                                                <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                                            </span>
                                        </div>
                                    </div>
                                    @if(!empty($confirmed_hours))
                                        <div class="overflow-x-auto collapsibles">
                                            <table class="table table-bordered table-sm">
                                                <tbody>
                                                    @foreach($confirmed_hours as $hours)
                                                        <tr>
                                                            <td>{{ (isset($hours['lavel_hours']) && !empty($hours['lavel_hours'])) ? $hours['lavel_hours'] : '' }}</td>
                                                            <td>{{ (isset($hours['learning_hours']) && !empty($hours['learning_hours'])) ? $hours['learning_hours'] : '' }}</td>
                                                            <td>{{ (isset($hours['confirmed_hours']) && !empty($hours['confirmed_hours'])) ? $hours['confirmed_hours'] : '' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else 
                                        <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                            <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Data not available right now.
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @else 
                                <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Data not available right now.
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div><!--End 2xl:col-span-9-->  
    @include('pages.students.frontend.dashboard.profile.sidebar')
 
</div><!--End GRID-->   

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
                    <button type="button" data-action="none" class="successCloser btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-frontend-global.js')
@endsection