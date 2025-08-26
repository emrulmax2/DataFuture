{{-- addNewAccountConfirm --}}

<div id="addNewAccountConfirm" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title  font-medium text-lg">Create New Application Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class=" font-medium text-md mb-5">Are you sure you want to create an application account for this applicant?</h5>
                <p class="mt-3 mx-auto"><b>Note:</b> an applicant password change email will sent to the applicant personal email. Please check the email to create password.</p>
                {{-- <ul>
                    <li>Full Name: <span id="studentFullName"></span></li>
                    <li>Date of Birth: <span id="studentDOB"></span></li>
                </ul> --}}
            </div>
            <div class="modal-footer">
                <input type="hidden" id="student_id" name="student_id" value="">
                <button type="button" data-tw-dismiss="modal" class="disAgreeWith btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                
                    <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-primary w-auto">
                        Yes, I confirm
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
            
            </form>
        </div>
    </div>
</div>