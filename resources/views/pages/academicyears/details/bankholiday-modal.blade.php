<!-- BEGIN: Add Modal -->
<div id="bankholidayAddModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="bankholidayAddForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Bank Holiday</h2>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input id="start_date" name="start_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                        <div class="acc__input-error error-start_date text-danger mt-2"></div>
                    </div>
                    <div>
                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input id="end_date" name="end_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                        <div class="acc__input-error error-end_date text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="duration" class="form-label">Duration <span class="text-danger">*</span></label>
                        <input id="duration" type="number" name="duration" class="form-control w-full">
                        <div class="acc__input-error error-duration text-danger mt-2"></div>
                    </div>
                    <div>
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input id="title" type="text" name="title" class="form-control w-full">
                        <div class="acc__input-error error-title text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select id="type" name="type" class="form-control w-full">
                            <option value="">Please Select</option>
                            <option value="Bank Holiday">Bank Holiday</option>
                        </select>
                        <div class="acc__input-error error-type text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal"
                        class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveBankholiday" class="btn btn-primary w-auto">
                        Save
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
                    <input type="hidden" name="academic_year_id" value="{{ $academicyear->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Modal -->
<!-- BEGIN: Edit Modal -->
<div id="bankholidayEditModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="bankholidayEditForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Bank Holiday</h2>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input id="start_date" name="start_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                        <div class="acc__input-error error-start_date text-danger mt-2"></div>
                    </div>
                    <div>
                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input id="end_date" name="end_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                        <div class="acc__input-error error-end_date text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="duration" class="form-label">Duration <span class="text-danger">*</span></label>
                        <input id="duration" type="number" name="duration" class="form-control w-full">
                        <div class="acc__input-error error-duration text-danger mt-2"></div>
                    </div>
                    <div>
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input id="title" type="text" name="title" class="form-control w-full">
                        <div class="acc__input-error error-title text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select id="type" name="type" class="form-control w-full">
                            <option value="">Please Select</option>
                            <option value="Bank Holiday">Bank Holiday</option>
                        </select>
                        <div class="acc__input-error error-type text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal"
                        class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateBankholiday" class="btn btn-primary w-auto">
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
                    <input type="hidden" name="id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Modal -->
<!-- BEGIN: Import Modal -->
<div id="bankholidayImportModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Import Holiday</h2>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('bankholidays.import') }}" class="dropzone" id="bankholidayImportForm" enctype="multipart/form-data">
                        @csrf
                        <div class="fallback">
                            <input name="import_holiday_file" type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500">
                                {{-- This is just a demo dropzone. Selected files are <span class="font-medium">not</span> actually uploaded. --}}
                            </div>
                        </div>
                        
                        <input type="hidden" name="academic_year_id" value="{{ $academicyear->id }}"/>
                    </form>
                </div>
                <div class="modal-footer">
                    <a style="float: left;" href="{{ route('bankholidays.export') }}" id="downloadSample" class="btn btn-success text-white w-auto">Download Sample Excel</a>
                    <button type="button" data-tw-dismiss="modal"
                        class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button id="saveImportholiday" class="btn btn-primary w-auto">Upload</button>
                </div>
            </div>
        
    </div>
</div>
<!-- END: Import Modal -->
<!-- BEGIN: Success Modal Content -->
<div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 successModalTitle">Congratulations!</div>
                    <div class="text-slate-500 mt-2 successModalDesc">Holidays data successfully uploaded</div>
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
<div id="bankholidayConfirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 bankholidayConfModTitle">Are you sure?</div>
                    <div class="text-slate-500 mt-2 bankholidayConfModDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                    <button type="button" data-id="0" data-action="none" class="bankholidayAgreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->
@section('script')
    @vite('resources/js/bankholiday.js')
@endsection