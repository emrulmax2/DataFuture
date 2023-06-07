@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Add Term Modules Creations</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('term.module.creation') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To List</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box px-5 py-10 mt-5">
        <form method="POST" action="#" id="termModuleCreationFormStp1">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-6 col-start-4">
                    <div class="grid grid-cols-12">
                        <div class="col-span-5">
                            <h2 class="text-sm font-medium mb-2 text-uppercase">Selected Modules</h2>
                            <div class="border p-5 pb-2 selectedElements">
                                <div class="alert alert-pending-soft seError show flex items-center mb-3" role="alert">
                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp; Modules not selected yet.
                                </div>
                            </div>
                        </div>
                        <div class="col-span-2">
                            
                        </div>
                        <div class="col-span-5">
                            <h2 class="text-sm font-medium mb-2 text-uppercase">Available Course Modules</h2>
                            <div class="border p-5 pb-2 availableModules">
                                @if(!empty($modules))
                                    @foreach($modules as $mod)
                                        <div data-terminstantid="{{ $instanceTermId }}" data-modid="{{ $mod->id }}" class="singleModule singleModule_{{ $instanceTermId }}_{{ $mod->id }} box px-5 py-4 cursor-pointer hover:text-white zoom-in bg-slate-100 border border-slate-200/60 hover:bg-primary mb-3">
                                            <div class="font-medium text-base">{{ $mod->name }}</div>
                                            <span></span>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="alert alert-danger-soft show flex baseError items-center mb-3" role="alert" style="display: none;">
                                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp; No more modules available.
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6">
                            <div class="text-left pt-5">
                                <a href="{{ route('term.module.creation') }}"  class="btn btn-outline-secondary w-auto mr-1">Cancel & Back</a>
                            </div>
                        </div>
                        <div class="col-span-6">
                            <div class="text-right pt-5">
                                <button disabled type="submit" id="saveandcontinue" class="btn btn-primary w-auto">
                                    Save & Continue
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
                                <input type="hidden" name="instanceTermId" value="{{ $instanceTermId }}"/>
                                <input type="hidden" name="courseId" value="{{ $courseId }}"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- END: HTML Table Data -->
    
    <!-- BEGIN: Success Modal Content -->
    <div id="successModalMCR" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitleMCR"></div>
                        <div class="text-slate-500 mt-2 successModalDescMCR"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <a href="#" type="button" class="btn mcrRedirect btn-primary w-auto">Ok, Get me there</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->  

    <!-- BEGIN: Success Modal Content -->
    <div id="warningModalMCR" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitleMCR"></div>
                        <div class="text-slate-500 mt-2 warningModalDescMCR"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" type="button" class="btn btn-primary w-auto">Ok, Got it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalMCR" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleMCR">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescMCR"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithMCR btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
    @endsection