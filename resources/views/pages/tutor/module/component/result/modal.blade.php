
<!-- BEGIN: Import Modal -->
<div id="addActivityModal" class="modal" size="xl" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class=" modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">SELECT AN ACTIVITY</h2>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div class="modal-body">
                <div id="activit-contentlist" class="grid grid-cols-12 gap-5 mt-5 pt-5"></div>
            </div>
        </div>
    </div>
</div>
<!-- END: Import Modal -->
<!-- BEGIN: Import Modal -->
<div id="addStudentPhotoModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Upload Documents</h2>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('plan-taskupload.store') }}" class="dropzone" id="addStudentPhotoForm" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input type="hidden" name="documents" type="file" />
                    </div>
                    <div class="dz-message" data-dz-message>
                        <div class="text-lg font-medium">Drop file here or click to upload.</div>
                        <div class="text-slate-500">
                            Select .jpg, .png, or .gif formate image. Max file size should be 5MB.
                        </div>
                    </div>
                    <input type="hidden" name="plan_task_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button id="uploadStudentPhotoBtn" type="button" class="btn btn-outline-success w-20 mr-1">Upload
                    <span class="ml-2 h-4 w-4" style="display: none">
                        <svg class="w-full h-full" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform type="rotate" attributeName="transform" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite" />
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </span>
                </button>
                <button type="button" data-tw-dismiss="modal" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
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
                        <button type="button" data-url="" data-id="0" data-action="none" data-assessmentPlan="" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->


    <div id="success-notification-content" class="py-5 pl-5 pr-14 bg-white border border-slate-200/60 rounded-lg shadow-xl dark:bg-darkmode-600 dark:text-slate-300 dark:border-darkmode-600 hidden flex flex flex">
        <i data-tw-merge data-lucide="check-circle" class="stroke-1.5 w-5 h-5 text-success"></i>
        <div class="ml-4 mr-4">
            <div class="font-medium">Result Updated!</div>
            <div class="mt-1 text-slate-500">
                The current result updated.
            </div>
        </div>
    </div>

    

    <div id="error-notification-content" class="py-5 pl-5 pr-14 bg-white border border-slate-200/60 rounded-lg shadow-xl dark:bg-darkmode-600 dark:text-slate-300 dark:border-darkmode-600 hidden flex flex flex">
        <i data-tw-merge data-lucide="x-circle" class="stroke-1.5 w-5 h-5 text-danger"></i>
        <div class="ml-4 mr-4">
            <div class="font-medium">Result Couldn't Updated!</div>
            <div class="mt-1 text-slate-500">
                The current result could not updated.
            </div>
        </div>
    </div>

    <!-- BEGIN: Success Modal Content -->
<div id="attemedModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content ">
            <div class="modal-header">
                <h2 id="totalAttemptHeader" class="font-medium text-base mr-auto">Total Attempts <i data-loading-icon="oval"  class="w-4 h-4 ml-2 dark:text-white" ></i></h2>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div id="resultListByStudentDiv" class="modal-body">
                <table id="resultListByStudent" data-tw-merge class="w-full text-left">
                    <thead data-tw-merge class="">
                        <tr data-tw-merge class="">
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                #
                            </th>
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                Grade
                            </th>
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                Published At
                            </th>
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody><tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                        <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                           1
                        </td>
                        <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                        None
                        </td>
                        <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                        None
                        </td>
                        <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                            <button type="button" data-url="" data-id="" data-action="DELETE" class="delete-result btn btn-danger text-white">Delete</button>
                        </td>
                    </tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->