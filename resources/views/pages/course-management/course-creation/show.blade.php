@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('course.creation') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To List</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.course-management.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <div class="intro-y box px-5 pt-5 mt-5">
                <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
                    <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                        <div class="ml-auto mr-auto">
                            <div class="w-auto sm:w-full truncate text-primary sm:whitespace-normal font-bold text-3xl">{{ $creation->course->name }}</div>
                            <div class="text-slate-500 font-medium">{{ $creation->semester->name }}</div>
                        </div>
                    </div>
                    <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
                        <div class="font-medium text-center lg:text-left lg:mt-3">Course Creation Details</div>
                        <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                            <div class="truncate sm:whitespace-normal flex items-center">
                                <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Duration:</span> <span class="font-medium ml-2">{{ $creation->duration }}</span>
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="sliders" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Unit Length:</span> <span class="font-medium ml-2">{{ $creation->unit_length }}</span>
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="key" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">SLC Code:</span> <span class="font-medium ml-2">{{ $creation->slc_code }}</span>
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Venue:</span> <span class="font-medium ml-2">{{ (isset($creation->venue->name) ? $creation->venue->name : '') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 lg:mt-0 flex-1 px-5 pt-5 lg:pt-0">
                        <div class="flex flex-col justify-center items-center lg:items-start">
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="pound-sterling" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Fees:</span> <span class="font-medium ml-2">{{ isset($creation->fees) && !empty($creation->fees) ? '£'.number_format($creation->fees, 2) : '' }}</span>
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="pound-sterling" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Reg. Fees:</span> <span class="font-medium ml-2">{{ isset($creation->reg_fees) && !empty($creation->reg_fees) ? '£'.number_format($creation->reg_fees, 2) : '' }}</span>
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="clock-7" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Evening / Weekend:</span> <span class="font-medium ml-2">{{ isset($creation->has_evening_and_weekend) && $creation->has_evening_and_weekend == 1 ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="fingerprint" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Workplacement:</span> <span class="font-medium ml-2">{{ isset($creation->is_workplacement) && $creation->is_workplacement == 1 ? 'Yes' : 'No' }}</span>
                            </div>
                            @if(isset($creation->is_workplacement) && $creation->is_workplacement == 1)
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="clock" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Required Hours:</span> <span class="font-medium ml-2">{{ isset($creation->required_hours) && $creation->required_hours == 1 ? $creation->required_hours.' Hour' : $creation->required_hours.' Hours' }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
                    <li id="availabilty-tab" class="nav-item mr-5" role="presentation">
                        <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#availabilty" aria-controls="availabilty" aria-selected="true" role="tab" >
                            <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Availabilty
                        </a>
                    </li>
                    <li id="instance-tab" class="nav-item mr-5" role="presentation">
                        <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0" data-tw-target="#instance" aria-controls="instance" aria-selected="true" role="tab" >
                            <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Instance
                        </a>
                    </li>
                    <li id="baseDataFuture-tab" class="nav-item mr-5" role="presentation">
                        <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0" data-tw-target="#baseDataFuture" aria-controls="baseDataFuture" aria-selected="true" role="tab" >
                            <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Datafuture
                        </a>
                    </li>
                </ul>
            </div>
            <div class="intro-y tab-content mt-5">
                <div id="availabilty" class="tab-pane active" role="tabpanel" aria-labelledby="availabilty-tab">
                    @include('pages.course-management.course-creation.details.availabilty')
                </div>
                <div id="instance" class="tab-pane" role="tabpanel" aria-labelledby="instance-tab">
                    @include('pages.course-management.course-creation.details.instance')
                </div>
                <div id="baseDataFuture" class="tab-pane" role="tabpanel" aria-labelledby="baseDataFuture-tab">
                    @include('pages.course-management.course-creation.details.datafuture')
                </div>
            </div>

            @include('pages.course-management.course-creation.details.availabilty-modal')
            @include('pages.course-management.course-creation.details.instance-modal')
            @include('pages.course-management.course-creation.details.instance-terms-modal')
            @include('pages.course-management.course-creation.details.datafuture-modal')
        </div>
    </div>
    

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
@endsection

@section('script')
    @vite('resources/js/course-management.js')
    @vite('resources/js/course-creation.js')
    @vite('resources/js/course-creation-availability.js')
    @vite('resources/js/course-creation-datafuture.js')
    @vite('resources/js/course-creation-instance.js')
@endsection