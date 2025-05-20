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
                    <div class="font-medium text-base">Workplacement Settings</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <div class="dropdown">
                        <button data-tw-toggle="modal" data-tw-target="#addWpSettingModal" type="button" class="btn btn-primary shadow-md md:mr-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Workplacement Setting</button>
                    </div>
                </div>
            </div>
        </div>
       
        <div class="intro-y box p-5 mt-5">
            @if($workplacement_settings->count() > 0)
            @foreach($workplacement_settings as $wp_setting)
            <div id="settingsAccordion-{{ $wp_setting->id }}" class="accordion">
                <div class="accordion-item {{ $loop->last ? '' : 'border-b' }}">
                    <div id="settingsAccordion-{{ $wp_setting->id }}" class="accordion-header flex justify-between {{ $loop->first ? '' : 'pt-4' }}">
                        <button class="accordion-button collapsed relative w-full text-lg font-semibold"
                            type="button"
                            data-target="#settingsAccordion-collapse-{{ $wp_setting->id }}"
                            aria-expanded="false"
                            aria-controls="settingsAccordion-collapse-{{ $wp_setting->id }}">
                            <div class="flex items-center font-medium text-base">
                                <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                {{ $wp_setting->name }}
                            </div>
                        </button>
                        <div class="flex">
                            <button data-id="{{ $wp_setting->id }}" data-tw-toggle="modal" data-tw-target="#addWpSettingTypeModal" type="button" class="addWpSettingType_btn btn-rounded btn btn-primary text-white px-2 whitespace-nowrap ml-1 text-xs font-bold h-[30px]"><i data-lucide="plus" class="w-3 h-3"></i>Add Setting Type</button>
                            <button data-id="{{ $wp_setting->id }}" data-tw-toggle="modal" data-tw-target="#editWpSettingModal" type="button" class="editWpSetting_btn btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-3 h-3"></i></button>
                            <button data-route="{{ route('workplacement-settings.destory', $wp_setting->id) }}" data-id="{{ $wp_setting->id }}" class="wpSettingDelete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-3 h-3"></i></button>
                        </div>
                    </div>
                    <div id="settingsAccordion-collapse-{{ $wp_setting->id }}" class="accordion-collapse collapse ml-4"
                        aria-labelledby="settingsAccordion-{{ $wp_setting->id }}">
                        <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed my-8">
                            <div id="nestedAccordion" class="accordion mt-3">
                                @if($wp_setting->workplacement_settng_types->count() > 0)
                                @foreach($wp_setting->workplacement_settng_types as $wp_setting_type)
                                <div class="accordion-item margin-4 padding-4 bg-gray-100 rounded-lg my-2 {{ !$loop->last ? 'border-b' : '' }}">
                                    <div id="nestedAccordion-{{ $wp_setting->id }}-{{ $wp_setting_type->id }}" class="accordion-header flex justify-between px-4">
                                        <button class="relative w-full font-semibold"
                                            type="button"
                                            aria-expanded="false"
                                            aria-controls="nestedAccordion-collapse-{{ $wp_setting->id }}-{{ $wp_setting_type->id }}">
                                            <div class="flex items-center">
                                                {{ $wp_setting_type->type }}
                                            </div>
                                        </button>
                                        <div class="flex">
                                            <button data-id="{{ $wp_setting_type->id }}" data-tw-toggle="modal" data-tw-target="#editWpSettingTypeModal" type="button" class="editWpSettingType_btn btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-3 h-3"></i></button>
                                            <button data-route="{{ route('workplacement-setting.types.destory', $wp_setting_type->id) }}" data-id="{{ $wp_setting_type->id }}" class="wpSettingTypeDelete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-3 h-3"></i></button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <div class="col-span-12">
                                    <div class="intro-y box p-5 bg-gray-100">
                                        <div class="text-center">No Setting Types Found</div>
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
                    <div class="text-center">No Workplacement Settings Found</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- END: Settings Page Content -->

@include('pages.settings.workplacement.wp-settings-modal')
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/wp-settings.js')
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