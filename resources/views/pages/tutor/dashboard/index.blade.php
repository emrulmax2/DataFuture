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
                    
                    <input type="text" value="{{ $date }}" placeholder="DD-MM-YYYY" id="plan_date" date-value="{{ $date }}" class="form-control w-auto datepicker" name="plan_date" data-format="DD-MM-YYYY" data-single-mode="true">
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
    
    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">My Modules</div>
                </div>

            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div id="studentProcessAccordion" class="accordion">
                    <div class="accordion-item">
                    @foreach($termList as $term)
                        <div id="studentProcessAccordion-{{ $term->id }}" class="accordion-header">
                            <button class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#studentProcessAccordion-collapse-{{ $term->id }}" aria-expanded="false" aria-controls="studentProcessAccordion-collapse-1">
                                {{ $term->name }}
                                    <span class="py-1 px-4 inline-flex rounded-full bg-warning text-sm font-semibold text-white ml-2 relative">{{ $term->total_modules }} Modules</span>
                                
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="studentProcessAccordion-collapse-{{ $term->id }}" class="accordion-collapse collapse" aria-labelledby="studentProcessAccordion-{{ $term->id }}" data-tw-parent="#studentProcessAccordion">
                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                <ul class="nav nav-link-tabs border-b border-slate-200/60" role="tablist">
                                    <li id="process-{{ $term->id }}-1-tab" class="nav-item mr-10 flex" role="presentation">
                                        <button class="nav-link font-medium text-slate-500 py-2 px-0 active" data-tw-toggle="pill" 
                                            data-tw-target="#process-tab-{{ $term->id }}-1" type="button" role="tab" aria-controls="process-tab-{{ $term->id }}-1" 
                                            aria-selected="true">
                                            Details
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content mt-5">
                                    <div id="process-tab-{{ $term->id }}-1" class="tab-pane leading-relaxed active" role="tabpanel" aria-labelledby="process-{{ $term->id }}-1-tab">
                                        @foreach($data[$term->id] as $moduleInfo)
                                            <a href="{{ route('tutor-dashboard.plan.module.show',$moduleInfo->id) }}" class="alert relative border cursor-pointer rounded-md px-5 py-4 bg-success border-success bg-opacity-20 border-opacity-5 text-success dark:border-success dark:border-opacity-20 mb-2 flex items-center w-auto" role="alert">
                                                <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> {{ $moduleInfo->module }} - {{ $moduleInfo->group }} - {{ $moduleInfo->course }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
            </div>
        </div>
        
    </div>

    @include('pages.tutor.dashboard.modals')
@endsection

@section('script')
    @vite('resources/js/tutor-dashboard.js')
@endsection