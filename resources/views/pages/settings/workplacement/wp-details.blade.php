@extends('../layout/' . $layout)

@section('subhead')
<title>{{ $title }}</title>
@endsection

@section('subcontent')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
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
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Workplacement Details</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <div class="dropdown">
                        <button data-tw-toggle="modal" data-tw-target="#workplacementAddModal" type="button" class="btn btn-primary shadow-md md:mr-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Workplacement Details</button>
                    </div>
                </div>
            </div>
        </div>
       

        <div class="intro-y box p-5 mt-5">
            @if($workplacement_details->count() > 0)
            @foreach($workplacement_details as $workplacement_detail)
            <div id="workplacementAccordion-{{ $workplacement_detail->id }}" class="accordion">
                <div class="accordion-item {{ $loop->last ? '' : 'border-b' }}">
                    <div id="workplacementAccordion-{{ $workplacement_detail->id }}" class="accordion-header flex justify-between {{ $loop->first ? '' : 'pt-4' }}">
                        <button class="accordion-button collapsed relative w-full text-lg font-semibold"
                            type="button"
                            data-target="#workplacementAccordion-collapse-{{ $workplacement_detail->id }}"
                            aria-expanded="false"
                            aria-controls="workplacementAccordion-collapse-{{ $workplacement_detail->id }}">
                            <div class="flex items-center font-medium text-base">
                                <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                {{ $workplacement_detail->name }}
                            </div>
                        </button>
                        <div class="flex">
                            <button data-id="{{ $workplacement_detail->id }}" data-tw-toggle="modal" data-tw-target="#addLevelHoursModal" type="button" class="addLevelHours_btn btn-rounded btn btn-primary text-white px-2 whitespace-nowrap ml-1 text-xs font-bold h-[30px]"><i data-lucide="plus" class="w-3 h-3"></i>Add Level Hours</button>
                            <button data-id="{{ $workplacement_detail->id }}" data-tw-toggle="modal" data-tw-target="#workplacementEditModal" type="button" class="editWorkPlacement_btn btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-3 h-3"></i></button>
                            <button data-route="{{ route('workplacement.delete', $workplacement_detail->id) }}" data-id="{{ $workplacement_detail->id }}" class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-3 h-3"></i></button>
                        </div>
                    </div>
                    <div id="workplacementAccordion-collapse-{{ $workplacement_detail->id }}" class="accordion-collapse collapse ml-4"
                        aria-labelledby="workplacementAccordion-{{ $workplacement_detail->id }}">
                        <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed my-8">
                            <div id="nestedAccordion" class="accordion mt-3">
                                @if($workplacement_detail->level_hours->count() > 0)
                                @foreach($workplacement_detail->level_hours as $level_hour)
                                <div class="accordion-item margin-4 padding-4 bg-gray-100 rounded-lg my-2 {{ !$loop->last ? 'border-b' : '' }}">
                                    <div id="nestedAccordion-{{ $workplacement_detail->id }}-{{ $level_hour->id }}" class="accordion-header flex justify-between px-4">
                                        <button class="accordion-button collapsed relative w-full font-semibold"
                                            type="button"
                                            data-target="#nestedAccordion-collapse-{{ $workplacement_detail->id }}-{{ $level_hour->id }}"
                                            aria-expanded="false"
                                            aria-controls="nestedAccordion-collapse-{{ $workplacement_detail->id }}-{{ $level_hour->id }}">
                                            <div class="flex items-center">
                                                <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                {{ $level_hour->name }} <span class="mr-4">(Hours: {{ $level_hour->hours }} )</span>
                                            </div>
                                        </button>
                                        <div class="flex">
                                            <button data-id="{{ $level_hour->id }}" data-tw-toggle="modal" data-tw-target="#addLearningHoursModal" type="button" class="addLearningHours_btn btn-rounded btn btn-primary text-white px-2 whitespace-nowrap ml-1 text-xs font-bold h-[30px]"><i data-lucide="plus" class="w-3 h-3"></i>Add Learning Hours</button>
                                            <button data-id="{{ $level_hour->id }}" data-tw-toggle="modal" data-tw-target="#levelHoursEditModal" type="button" class="editLevelHours_btn btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-3 h-3"></i></button>
                                            <button data-route="{{ route('level.hours.delete', $level_hour->id) }}" data-id="{{ $level_hour->id }}" class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-3 h-3"></i></button>
                                        </div>
                                    </div>
                                    <div id="nestedAccordion-collapse-{{ $workplacement_detail->id }}-{{ $level_hour->id }}" class="accordion-collapse collapse"
                                        aria-labelledby="nestedAccordion-{{ $workplacement_detail->id }}-{{ $level_hour->id }}">
                                        <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                @if($level_hour->learning_hours->count() > 0)
                                                @foreach($level_hour->learning_hours as $learning_hour)
                                                <div class="flex items-center justify-between p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="font-medium">{{ $learning_hour->name }} <span class="mr-4">(Hours: {{ $learning_hour->hours }} )</span></div>
                                                    <div class="flex items-center">
                                                        
                                                        <button data-id="{{ $learning_hour->id }}" data-tw-toggle="modal" data-tw-target="#editLearningHoursModal" type="button" class="editLearningHours_btn btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-3 h-3"></i></button>
                                                        <button data-route="{{ route('learning.hours.delete', $learning_hour->id) }}" data-id="{{ $learning_hour->id }}" class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-3 h-3"></i></button>
                                                    </div>
                                                </div>
                                                @endforeach
                                                @else
                                                <div class="col-span-12">
                                                    <div class="flex items-center justify-between p-3 rounded-lg my-2 mx-4 bg-white">
                                                        <div class="flex items-center justify-center w-full"> No Learning Hours Found </div>
                                                    </div>
                                                </div>
                                                @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <div class="col-span-12">
                                    <div class="intro-y box p-5 bg-gray-100">
                                        <div class="text-center">No Level Hours Found</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @else
            <div class="col-span-12">
                <div class="intro-y box p-5 ">
                    <div class="text-center">No Workplacement Details Found</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- END: Settings Page Content -->

@include('pages.settings.workplacement.modal')
@endsection

@section('script')
@vite('resources/js/settings.js')
@vite('resources/js/workplacement-details.js')
<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                collapse.classList.add('collapse');
            });

            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = button.getAttribute('data-target');
                    const targetContent = document.querySelector(targetId);
                    const plusIcon = button.querySelector('.accordion-icon-plus');
                    const minusIcon = button.querySelector('.accordion-icon-minus');
                    
                    const isExpanded = button.getAttribute('aria-expanded') === 'true';

                    if (isExpanded) {
                        plusIcon.classList.remove('hidden');
                        minusIcon.classList.add('hidden');
                    } else {
                        plusIcon.classList.add('hidden');
                        minusIcon.classList.remove('hidden');
                    }
                    
                    if (!isExpanded) {
                        targetContent.classList.remove('collapse');
                        targetContent.classList.add('show');
                        button.setAttribute('aria-expanded', 'true');
                    } else {
                        targetContent.classList.remove('show');
                        targetContent.classList.add('collapse');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });
    })();
</script>
@endsection