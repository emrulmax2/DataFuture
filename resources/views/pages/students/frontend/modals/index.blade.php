<!-- BEGIN: Address Modal -->
<div id="addressCorrespondenceModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addressCorrespondenceForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Update Correspondence Address</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="student_address_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Address Line 1" id="student_address_address_line_1" class="form-control w-full" name="student_address_address_line_1">
                            <div class="acc__input-error error-student_address_address_line_1 text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                            <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_2" class="form-control w-full" name="student_address_address_line_2">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_city" class="form-label">City / Town <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_city" class="form-control w-full" name="student_address_city">
                            <div class="acc__input-error error-student_address_city text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_state_province_region" class="form-label">State</label>
                            <input type="text" placeholder="State" id="student_address_state_province_region" class="form-control w-full" name="student_address_state_province_region">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_postal_zip_code" class="form-label">Post Code <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_postal_zip_code" class="form-control w-full" name="student_address_postal_zip_code">
                            <div class="acc__input-error error-student_address_postal_zip_code text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Country" id="student_address_country" class="form-control w-full" name="student_address_country">
                            <div class="acc__input-error error-student_address_country text-danger mt-2"></div>
                        </div>
                        <link rel="stylesheet" type="text/css" href="https://services.postcodeanywhere.co.uk/css/captureplus-2.30.min.css?key=gy26-rh34-cf82-wd85" />
                        <script type="text/javascript" src="https://services.postcodeanywhere.co.uk/js/captureplus-2.30.min.js?key=gy26-rh34-cf82-wd85"></script>
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
                    <input type="hidden" name="address_id" value="@if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0) {{ $student->contact->term_time_address_id }} @endif"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Address Modal -->

