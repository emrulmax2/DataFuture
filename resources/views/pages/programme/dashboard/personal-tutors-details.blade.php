@extends('../layout/' . $layout)

@section('subhead')
    <title>Programme Dashboard - Welcome to London churchill college</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9 pt-5 relative">
            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-12">  
                <div id="term-dropdown" class="dropdown w-1/2 sm:w-auto mr-auto">
                                        <button id="selected-term" class="dropdown-toggle btn btn-primary text-white w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> <i data-loading-icon="oval" class="w-4 h-4 mr-2 hidden"  data-color="white"></i> <span>Test</span> <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                        </button>
                                        <div class="dropdown-menu w-40">
                                            <ul class="dropdown-content">
                                                
                                                
                                                
                                            </ul>
                                        </div>
                                    </div>       
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-12 mt-3 2xl:mt-5">
                        <div class="intory-x box zoom-in p-5">
                            <div class="text-center pt-5 pb-3">
                                <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative ml-auto mr-auto">
                                    <img alt="{{ (isset($tutor->employee->full_name) ? $tutor->employee->full_name : '') }}" class="rounded-full" src="{{ (isset($tutor->employee->photo_url) ? $tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                </div>
                                <div class="mt-3 text-center">
                                    <div class="truncate sm:whitespace-normal font-medium text-lg">{{ (isset($tutor->employee->full_name) ? $tutor->employee->full_name : 'Unknown Employee') }}</div>
                                    <div class="text-slate-500">
                                        @if(isset($tutor->employee->address->address_line_1) && $tutor->employee->address->address_line_1 > 0)
                                            @if(isset($tutor->employee->address->address_line_1) && !empty($tutor->employee->address->address_line_1))
                                                {{ $tutor->employee->address->address_line_1 }}, 
                                            @endif
                                            @if(isset($tutor->employee->address->address_line_2) && !empty($tutor->employee->address->address_line_2))
                                                {{ $tutor->employee->address->address_line_2 }},
                                            @endif
                                            @if(isset($tutor->employee->address->city) && !empty($tutor->employee->address->city))
                                                {{ $tutor->employee->address->city }}, 
                                            @endif
                                            @if(isset($tutor->employee->address->state) && !empty($tutor->employee->address->state))
                                                {{ $tutor->employee->address->state }}, 
                                            @endif
                                            @if(isset($tutor->employee->address->post_code) && !empty($tutor->employee->address->post_code))
                                                {{ $tutor->employee->address->post_code }}, 
                                            @endif
                                            @if(isset($tutor->employee->address->country) && !empty($tutor->employee->address->country))
                                                {{ $tutor->employee->address->country }}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-3 mt-5">
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="truncate sm:whitespace-normal flex items-center">
                                            <i data-lucide="mail" class="w-4 h-4 mr-2"></i> {{ (isset($tutor->employee->email) ? $tutor->employee->email : '---') }}
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="truncate sm:whitespace-normal flex items-center">
                                            <i data-lucide="mail" class="w-4 h-4 mr-2"></i> {{ (isset($tutor->email) ? $tutor->email : '---') }}
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="truncate sm:whitespace-normal flex items-center">
                                            <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> {{ (isset($tutor->employee->mobile) ? $tutor->employee->mobile : '---') }}
                                        </div>
                                    </div>
                                    @if(isset($tutor->employee->employment->mobile) && !empty($tutor->employee->employment->mobile))
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="truncate sm:whitespace-normal flex items-center">
                                            <i data-lucide="tablet-smartphone" class="w-4 h-4 mr-2"></i> {{ (isset($tutor->employee->employment->mobile) ? $tutor->employee->employment->mobile : '---') }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection