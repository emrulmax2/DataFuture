@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Add Class plans</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('class.plan') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To List</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form method="post" id="classPlanAddForm" action="#">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-2">
                    <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                    <select id="course" name="course" class="form-control w-full">
                        <option value="">Please Select</option>
                        @if(!empty($courses))
                            @foreach($courses as $crs)
                                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="acc__input-error error-course text-danger mt-2" style="display: none;"></div>
                </div>
                <div class="col-span-2">
                    <label for="instanceTermId" class="form-label">Terms <span class="text-danger">*</span></label>
                    <select id="instanceTermId" name="instanceTermId" class="form-control w-full">
                        <option value="">Please Select</option>
                        @if(!empty($terms))
                            @foreach($terms as $trm)
                                <option value="{{ $trm->id }}">{{ $trm->name }} - {{ $trm->term }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="acc__input-error error-instanceTermId text-danger mt-2" style="display: none;"></div>
                </div>
                <div class="col-span-2 pt-7">
                    <button id="findModuleList" type="button" class="btn btn-primary w-auto" >
                        Search Modules
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
                </div>
                <div class="col-span-6">
                    <div class="instanceTermDetails pt-7" style="display: none;"></div>
                </div>

                <div class="col-span-12">
                    <div class="availableModules pt-5" style="display: none;"></div>
                </div>

                <div class="col-span-8">
                    <div class="formError" style="display: none;"></div>
                </div>
                <div class="col-span-4">
                    <div class="text-right theSubmitArea pt-5" style="display: none;">
                        <button id="submitModulesBtn" type="submit" class="btn btn-primary w-auto">
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
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- END: HTML Table Data -->
    
    
    <!-- BEGIN: Success Modal Content -->
    <div id="successModalCP" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitleCP"></div>
                        <div class="text-slate-500 mt-2 successModalDescCP"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalCP" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleCP">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescCP"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithCP btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
    @endsection