<!-- BEGIN: Address Modal Content -->    
    <div id="addressModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addressForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Address</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div id="addressStart" class="grid grid-cols-12 gap-4 theAddressWrap">
                            <div class="col-span-12">
                                <label for="address_lookup" class="form-label">Address Lookup</label>
                                <input type="text" placeholder="Search address here..." id="address_lookup" class="form-control w-full theAddressLookup" name="address_lookup">
                            </div>
                            <div class="col-span-12">
                                <label for="student_address_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Address Line 1" autocomplete="off" id="student_address_address_line_1" class="form-control w-full required address_line_1" name="student_address_address_line_1">
                                <div class="acc__input-error error-student_address_city text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                                <input type="text" placeholder="Address Line 2 (Optional)" autocomplete="off" id="student_address_address_line_2" class="form-control w-full address_line_2" name="student_address_address_line_2">
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_city" class="form-label">City / Town <span class="text-danger">*</span></label>
                                <input type="text" placeholder="City / Town" id="student_address_city" autocomplete="off" class="form-control w-full required city" name="student_address_city">
                                <div class="acc__input-error error-student_address_city text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_state_province_region" class="form-label">State</label>
                                <input type="text" placeholder="State" id="student_address_state_province_region" autocomplete="off" class="form-control w-full state" name="student_address_state_province_region">
                                <div class="acc__input-error error-student_address_state_province_region text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_postal_zip_code" class="form-label">Post Code <span class="text-danger">*</span></label>
                                <input type="text" placeholder="City / Town" id="student_address_postal_zip_code" autocomplete="off" class="form-control w-full required postal_code" name="student_address_postal_zip_code">
                                <div class="acc__input-error error-student_address_postal_zip_code text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Country" id="student_address_country" autocomplete="off" class="form-control w-full required country" name="student_address_country">
                                <div class="acc__input-error error-student_address_country text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="insertAddress" class="btn btn-primary w-auto">     
                            Add Address                      
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
                        <input type="hidden" name="place" value=""/>
                        <input type="hidden" name="prefix" value=""/>
                    </div>
                </div>
            </form>
        </div>
    </div>
<!-- END: Address Modal Content -->