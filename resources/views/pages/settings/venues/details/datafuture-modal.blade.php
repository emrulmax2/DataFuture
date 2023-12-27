    <!-- BEGIN: Add Base Data Future Modal -->
    <div id="venueDataFutureAddModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="venueDataFutureAddForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Base Datafuture</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="venue_datafuture_field_id" class="form-label">Field <span class="text-danger">*</span></label>
                            <select id="venue_datafuture_field_id" name="datafuture_field_id" class="lcc-tom-select w-full">
                                <option value="">Please Select</option>
                                @if(!empty($df_fields) && $df_fields->count() > 0)
                                    @foreach($df_fields as $fld)
                                        <option value="{{ $fld->id }}">{{ $fld->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-datafuture_field_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="venue_field_value" class="form-label">Field Value <span class="text-danger">*</span></label>
                            <input id="venue_field_value" type="text" name="field_value" class="form-control w-full">
                            <div class="acc__input-error error-field_value text-danger mt-2"></div>
                        </div>    
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveBaseDF" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="venue_id" value="{{ $venue->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add  Base Data Future Modal -->

    <!-- BEGIN: Add Base Data Future Modal -->
    <div id="venueDataFutureEditModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="venueDataFutureEditForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Base Datafuture</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_venue_datafuture_field_id" class="form-label">Field <span class="text-danger">*</span></label>
                            <select id="edit_venue_datafuture_field_id" name="datafuture_field_id" class="lcc-tom-select w-full">
                                <option value="">Please Select</option>
                                @if(!empty($df_fields) && $df_fields->count() > 0)
                                    @foreach($df_fields as $fld)
                                        <option value="{{ $fld->id }}">{{ $fld->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-datafuture_field_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_venue_field_value" class="form-label">Field Value <span class="text-danger">*</span></label>
                            <input id="edit_venue_field_value" type="text" name="field_value" class="form-control w-full">
                            <div class="acc__input-error error-field_value text-danger mt-2"></div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateBaseDF" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="venue_id" value="{{ $venue->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add  Base Data Future Modal -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalVCBDF" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleDF">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescDF"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithVCBDF btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->