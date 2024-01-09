@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('term.module.creation') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To List</a>
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
            <div class="intro-y box  lg:mt-5 p-5">
                <div class="form-wizard" id="termModulCreationsStepWizard">
                    @if(!empty($moduleCreations))
                        @foreach($moduleCreations as $mc)
                            <fieldset class="wizard-fieldset {{ $loop->first ? 'show' : '' }} {{ $loop->last ? 'wizard-last-step' : '' }}">
                                <form action="" method="post" role="form" id="moduleCreationStepForms_{{ $mc->id }}" enctype="multipart/form-data">
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12">
                                            <h2 class="text-xl font-medium mb-5 text-left">Module {{ ($loop->index < 9 ) ? '0'.$loop->index + 1 : $loop->index + 1}}</h2>
                                        </div>
                                        <div class="col-span-6">
                                            <div class="grid grid-cols-12 gap-0">
                                                <div class="col-span-3"><div class="text-left text-slate-500 font-medium">Module Name:</div></div>
                                                <div class="col-span-9"><div class="text-left font-medium font-bold"><u>{{ $mc->module_name }}</u></div></div>
                                            </div>
                                        </div>
                                        @if($mc->module_level_id > 0)
                                            <div class="col-span-6">
                                                <div class="grid grid-cols-12 gap-0">
                                                    <div class="col-span-3"><div class="text-left text-slate-500 font-medium">Module Level:</div></div>
                                                    <div class="col-span-9"><div class="text-left font-medium font-bold">{{ $mc->level->name }}</div></div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-span-6">
                                            <div class="grid grid-cols-12 gap-0">
                                                <div class="col-span-3"><div class="text-left text-slate-500 font-medium">Credit Value:</div></div>
                                                <div class="col-span-9"><div class="text-left font-medium font-bold">{{ $mc->credit_value }}</div></div>
                                            </div>
                                        </div>
                                        <div class="col-span-6">
                                            <div class="grid grid-cols-12 gap-0">
                                                <div class="col-span-3"><div class="text-left text-slate-500 font-medium">Unit Value:</div></div>
                                                <div class="col-span-9"><div class="text-left font-medium font-bold">{{ $mc->unit_value }}</div></div>
                                            </div>
                                        </div>
                                        <div class="col-span-6">
                                            <div class="grid grid-cols-12 gap-0">
                                                <div class="col-span-3"><div class="text-left text-slate-500 font-medium">Code:</div></div>
                                                <div class="col-span-9"><div class="text-left font-medium font-bold">{{ $mc->code }}</div></div>
                                            </div>
                                        </div>
                                        <div class="col-span-6">
                                            <div class="grid grid-cols-12 gap-0">
                                                <div class="col-span-3"><div class="text-left text-slate-500 font-medium">Course Status:</div></div>
                                                <div class="col-span-9"><div class="text-left font-medium font-bold">{{ ucfirst($mc->status) }}</div></div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 pt-5">
                                            <h3 class="font-medium text-base mt-0 mb-0">Course Module Base Assesments:</h3>
                                            <div class="assessmentError text-danger mt-0" style="display: none;"></div>
                                        </div>
                                        <div class="col-span-12">
                                            <div class="overflow-x-auto">
                                                <table class="table  table-striped border-t">
                                                    <thead>
                                                        <tr>
                                                            <th class="whitespace-nowrap">
                                                                #
                                                            </th>
                                                            <th class="whitespace-nowrap">Name</th>
                                                            <th class="whitespace-nowrap">Code</th>
                                                            <th class="whitespace-nowrap">&nbsp;</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if(!empty($mc->module->assesments))
                                                            @foreach($mc->module->assesments as $ass)
                                                                <tr>
                                                                    <td class="whitespace-nowrap">{{ $loop->index + 1 }}</td>
                                                                    <td class="whitespace-nowrap">{{ $ass->assesment_name}}</td>
                                                                    <td class="whitespace-nowrap">{{ $ass->assesment_code}}</td>
                                                                    <td class="whitespace-nowrap">
                                                                        <div class="form-check form-switch">
                                                                            <input class="cmb_assessment form-check-input" id="cmb_assessment_{{ $mc->id }}_{{ $ass->id }}" name="cmb_assessment[]" value="{{ $ass->id }}" type="checkbox">
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td class="whitespace-nowrap" colspan="4">No Assessment Found!</td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t mt-5 pt-5 flex flex-col sm:flex-row items-center">
                                        @if(!$loop->first)
                                            <button type="button" class=" btn btn-outline-secondary w-auto form-wizard-previous-btn mr-auto">Previous</button>
                                        @endif
                                        <button type="button" name="next" value="Next" class="form-wizard-next-btn btn btn-primary w-auto ml-auto">
                                            Save & {{ $loop->last ? 'Exit' : 'Continue'}}
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
                                        <input type="hidden" name="module_creation_id" value="{{ $mc->id }}"/>
                                    </div>
                                </form>
                            </fieldset>
                        @endforeach 
                    @endif
                </div>
            </div>
        </div>
    </div>

    
    <!-- BEGIN: Success Modal Content -->
    <div id="successModalMCRD" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitleMCRD"></div>
                        <div class="text-slate-500 mt-2 successModalDescMCRD"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <a href="#" class="btn btn-primary w-auto">Ok, Get me there</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->  

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModalMCRD" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitleMCRD"></div>
                        <div class="text-slate-500 mt-2 warningModalDescMCRD"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" type="button" class="btn btn-primary w-24">Ok, Got it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalMCRD" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleMCRD">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescMCRD"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithMCRD btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/course-management.js')
    @vite('resources/js/term-module-creation.js')
@endsection