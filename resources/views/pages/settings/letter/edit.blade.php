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
            <!-- BEGIN: Display Information -->
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Edit Letter</h2>
                    <a href="{{ route('letter.set') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to List</a>
                </div>
                <div class="p-5">
                    <form method="POST" action="#" id="editLetterForm" enctype="multipart/form-data">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12">
                                <label for="phase" class="form-label">Phase <span class="text-danger">*</span></label>
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input {{ (isset($letter->admission) && $letter->admission == 1 ? 'Checked' : '' ) }} id="edit_phase_admission" class="form-check-input phaseCheckboxs" name="phase[admission]" type="checkbox" value="1">
                                        <label class="form-check-label" for="edit_phase_admission">Admission</label>
                                    </div>
                                    <div class="form-check mr-4 mt-2 sm:mt-0">
                                        <input {{ (isset($letter->live) && $letter->live == 1 ? 'Checked' : '' ) }} id="edit_phase_live" class="form-check-input phaseCheckboxs"  name="phase[live]" type="checkbox" value="1">
                                        <label class="form-check-label" for="edit_phase_live">Live Student</label>
                                    </div>
                                    <div class="form-check mr-4 mt-2 sm:mt-0">
                                        <input {{ (isset($letter->hr) && $letter->hr == 1 ? 'Checked' : '' ) }} id="edit_phase_hr" class="form-check-input phaseCheckboxs" name="phase[hr]" type="checkbox" value="1">
                                        <label class="form-check-label" for="edit_phase_hr">Human Resource</label>
                                    </div>
                                </div>
                                <div class="acc__input-error error-phase text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="edit_letter_type" class="form-label">Letter Type <span class="text-danger">*</span></label>
                                <input value="{{ (isset($letter->letter_type) && !empty($letter->letter_type) ? $letter->letter_type : '') }}" id="edit_letter_type" type="text" name="letter_type" class="form-control w-full">
                                <div class="acc__input-error error-letter_type text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="edit_letter_title" class="form-label">Letter Title <span class="text-danger">*</span></label>
                                <input value="{{ (isset($letter->letter_title) && !empty($letter->letter_title) ? $letter->letter_title : '') }}" id="edit_letter_title" type="text" name="letter_title" class="form-control w-full">
                                <div class="acc__input-error error-letter_title text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <div class="flex justify-between">
                                    <label for="editEditor" class="form-label">Description <span class="text-danger">*</span></label>
                                    @include('pages.settings.letter.letter-tags')
                                </div>
                                <div class="editor document-editor">
                                    <div class="document-editor__toolbar"></div>
                                    <div class="document-editor__editable-container">
                                        <div class="document-editor__editable" id="editEditor">{!! (isset($letter->description) && !empty($letter->description) ? $letter->description : '') !!}</div>
                                    </div>
                                </div>
                                <div class="acc__input-error error-description text-danger mt-2"></div>
                            </div>

                            <div class="col-span-6">
                                <div class="form-check form-switch"  style="float: left; margin: 7px 0 0;">
                                    <label class="form-check-label mr-3 ml-0" for="edit_status">Active</label>
                                    <input {{ (isset($letter->status) && $letter->status == 1 ? 'Checked' : '') }} id="edit_status" class="form-check-input m-0" name="status" value="1" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-6 text-right">
                                <button type="submit" id="editLetterSet" class="btn btn-primary w-auto">     
                                    Update                      
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
                                <input type="hidden" name="id" value="{{ $letter->id }}"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->

    
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

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-phase="" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/letter-set-edit.js')
@endsection