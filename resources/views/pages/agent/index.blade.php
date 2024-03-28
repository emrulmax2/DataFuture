@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Agent List</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button data-tw-toggle="modal" data-tw-target="#addAgentModal" type="button" class="add_btn btn btn-primary shadow-md mr-2">Add Agent</button>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm-Agent" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                    <input id="query-Agent" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                </div>

                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                    <select id="status-Agent" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                        <option value="1">Active</option>
                        <option value="2">Archived</option>
                    </select>
                </div>
                <div class="mt-2 xl:mt-0">
                    <button id="tabulator-html-filter-go-Agent" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset-Agent" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </form>
            <div class="flex mt-5 sm:mt-0">
                <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                </button>
                <div class="dropdown w-1/2 sm:w-auto">
                    <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                    </button>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                </a>
                            </li>
                            {{-- <li>
                                <a id="tabulator-export-json" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                </a>
                            </li> --}}
                            <li>
                                <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                </a>
                            </li>
                            {{-- <li>
                                <a id="tabulator-export-html" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                </a>
                            </li> --}}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="agentTableId" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
    <!-- BEGIN: Add Modal -->
    <div id="addAgentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addAgentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Agent</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="intro-x col-span-6">
                                <label for="first_name" class="form-label inline-flex">First name <span class="text-danger">*</span></label>
                                <input id="first_name" type="text" class="form-control rounded-none form-control " name="first_name" aria-label="default input example">
                                <div class="acc__input-error error-first_name text-danger mt-2"></div>
                            </div> 
                            <div class=" intro-x col-span-6">
                                <label for="last_name" class="form-label inline-flex">Last name <span class="text-danger">*</span></label>
                                <input id="last_name" type="text" class="form-control rounded-none form-control " name="last_name" aria-label="default input example">
                                <div class="acc__input-error error-last_name text-danger mt-2"></div>
                            </div>
                            
                            <div class="intro-x col-span-6">
                                <label for="code" class="form-label inline-flex">Refferel Code <span class="text-danger">*</span></label>
                                <input id="code" type="text" class="form-control rounded-none form-control" value="{{ $unique }}" name="code" aria-label="default input example">
                                <div class="acc__input-error error-code text-danger mt-2"></div>
                            </div>
                            <div class="intro-x col-span-6">
                                <label for="organization" class="form-label inline-flex">Organization <span class="text-danger">*</span></label>
                                <input id="organization" type="text" class="form-control rounded-none form-control" name="organization" aria-label="default input example">
                                <div class="acc__input-error error-organization text-danger mt-2"></div>
                            </div>
                            <div class="intro-x  col-span-12">
                                    <input type="email" id="email" name="email" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Email">
                                    <div id="error-email" class="acc__input-error error-email text-danger mt-2"></div>
        
                                    <input type="password" autocomplete="off" id="password" name="password" autocomplete="off" class="password intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password">
                                    <div id="error-password" class="acc__input-error error-password text-danger mt-2"></div>
        
                                    <div class="intro-x w-full grid grid-cols-12 gap-4 h-1 mt-3">
                                        <div id="strength-1" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                        <div id="strength-2" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                        <div id="strength-3" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                        <div id="strength-4" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                    </div>
                                    <!-- BEGIN: Custom Tooltip Toggle -->
                                    <a href="javascript:;" data-theme="light" data-tooltip="custom-content-tooltip" data-trigger="click" class="tooltip intro-x text-slate-500 block mt-2 text-xs sm:text-sm" title="What is a secure password?">What is a secure password?</a>
                                    <!-- END: Custom Tooltip Toggle -->
                                    <!-- BEGIN: Custom Tooltip Content -->
                                    <div class="tooltip-content">
                                        <div id="custom-content-tooltip" class="relative flex items-center py-1">
                                            <ul class="list-disc mt-5 ml-4 text-md dark:text-slate-400">
                                                <li class="">
                                                    <span class="low-upper-case">
                                                        <i class="fas fa-circle" aria-hidden="true"></i>
                                                        &nbsp;Lowercase &amp; Uppercase
                                                    </span>
                                                </li>
                                                <li class="">
                                                    <span class="one-number">
                                                        <i class="fas fa-circle" aria-hidden="true"></i>
                                                        &nbsp;Number (0-9)
                                                    </span> 
                                                </li>
                                                <li class="">
                                                    <span class="one-special-char">
                                                        <i class="fas fa-circle" aria-hidden="true"></i>
                                                        &nbsp;Special Character (!@#$%^&*)
                                                    </span>
                                                </li>
                                                <li class="">
                                                    <span class="eight-character">
                                                        <i class="fas fa-circle" aria-hidden="true"></i>
                                                        &nbsp;Atleast 8 Character
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- END: Custom Tooltip Content -->
                                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="off" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password Confirmation">
                                    <div id="error-confirmation" class="acc__input-error error-password_confirmation  text-danger mt-2"></div>
                                
                            </div>
                        </div> 
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveAgent" class="btn btn-primary w-auto">
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
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->
    <!-- BEGIN: Edit Modal -->
    <div id="editAgentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editAgentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Agent</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="intro-x col-span-6">
                                <label for="first_name1" class="form-label inline-flex">First name <span class="text-danger">*</span></label>
                                <input id="first_name1" type="text" class="form-control rounded-none form-control " name="first_name" aria-label="default input example">
                                <div class="acc__input-error error-first_name text-danger mt-2"></div>
                            </div> 
                            <div class=" intro-x col-span-6">
                                <label for="last_name1" class="form-label inline-flex">Last name <span class="text-danger">*</span></label>
                                <input id="last_name1" type="text" class="form-control rounded-none form-control " name="last_name" aria-label="default input example">
                                <div class="acc__input-error error-last_name text-danger mt-2"></div>
                            </div>
                            
                            <div class="intro-x col-span-6">
                                <label for="code1" class="form-label inline-flex">Refferel Code <span class="text-danger">*</span></label>
                                <input id="code1" type="text" class="form-control rounded-none form-control" value="{{ $unique }}" name="code" aria-label="default input example">
                                <div class="acc__input-error error-code text-danger mt-2"></div>
                            </div>
                            <div class="intro-x col-span-6">
                                <label for="organization1" class="form-label inline-flex">Organization <span class="text-danger">*</span></label>
                                <input id="organization1" type="text" class="form-control rounded-none form-control" name="organization" aria-label="default input example">
                                <div class="acc__input-error error-organization text-danger mt-2"></div>
                            </div>
                            <div class="intro-x  col-span-12">
                                    <div class="inline-flex">
                                    <input type="email" id="email1" name="email" class="intro-x login__input form-control py-3 px-4 w-auto mt-4 " placeholder="Email"> <span id="verificationEmail" class="mt-4 font-medium items-center w-24 inline-flex text-danger intro-x"></span>
                                    </div>
                                    <div id="error-email1" class="acc__input-error error-email text-danger mt-2"></div>
        
                                    <input type="password" autocomplete="off" id="password1" name="password" autocomplete="off" class="password intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password">
                                    <div id="error-password1" class="acc__input-error error-password text-danger mt-2"></div>
        
                                    <div class="intro-x w-full grid grid-cols-12 gap-4 h-1 mt-3">
                                        <div id="strength-5" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                        <div id="strength-6" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                        <div id="strength-7" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                        <div id="strength-8" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                    </div>
                                    <!-- BEGIN: Custom Tooltip Toggle -->
                                    <a href="javascript:;" data-theme="light" data-tooltip="custom-content-tooltip" data-trigger="click" class="tooltip intro-x text-slate-500 block mt-2 text-xs sm:text-sm" title="What is a secure password?">What is a secure password?</a>
                                    <!-- END: Custom Tooltip Toggle -->
                                    <!-- BEGIN: Custom Tooltip Content -->
                                    <div class="tooltip-content">
                                        <div id="custom-content-tooltip" class="relative flex items-center py-1">
                                            <ul class="list-disc mt-5 ml-4 text-md dark:text-slate-400">
                                                <li class="">
                                                    <span class="low-upper-case">
                                                        <i class="fas fa-circle" aria-hidden="true"></i>
                                                        &nbsp;Lowercase &amp; Uppercase
                                                    </span>
                                                </li>
                                                <li class="">
                                                    <span class="one-number">
                                                        <i class="fas fa-circle" aria-hidden="true"></i>
                                                        &nbsp;Number (0-9)
                                                    </span> 
                                                </li>
                                                <li class="">
                                                    <span class="one-special-char">
                                                        <i class="fas fa-circle" aria-hidden="true"></i>
                                                        &nbsp;Special Character (!@#$%^&*)
                                                    </span>
                                                </li>
                                                <li class="">
                                                    <span class="eight-character">
                                                        <i class="fas fa-circle" aria-hidden="true"></i>
                                                        &nbsp;Atleast 8 Character
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- END: Custom Tooltip Content -->
                                    <input type="password" id="password_confirmation1" name="password_confirmation" autocomplete="off" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password Confirmation">
                                    <div id="error-confirmation1" class="acc__input-error error-password_confirmation  text-danger mt-2"></div>
                                
                            </div>
                        </div> 
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateAgent" class="btn btn-primary w-auto">
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
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
    @endsection

@section('script')
    <script type="module">
        (function () {

            function checkPasswordStrength(password) {
            // Initialize variables
            let strength = 0;
            let tips = "";
            
            //let lowUpperCase = document.querySelector(".low-upper-case i");

            //let number = document.querySelector(".one-number i");
            //let specialChar = document.querySelector(".one-special-char i");
            //let eightChar = document.querySelector(".eight-character i");

            //If password contains both lower and uppercase characters
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
                strength += 1;
                //lowUpperCase.classList.remove('fa-circle');
                //lowUpperCase.classList.add('fa-check');
            } else {
                //lowUpperCase.classList.add('fa-circle');
                //lowUpperCase.classList.remove('fa-check');
            }
            //If it has numbers and characters
            if (password.match(/([0-9])/)) {
                strength += 1;
                //number.classList.remove('fa-circle');
                //number.classList.add('fa-check');
            } else {
                //number.classList.add('fa-circle');
                //number.classList.remove('fa-check');
            }
            //If it has one special character
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
                strength += 1;
                //specialChar.classList.remove('fa-circle');
                //specialChar.classList.add('fa-check');
            } else {
                //specialChar.classList.add('fa-circle');
                //specialChar.classList.remove('fa-check');
            }
            //If password is greater than 7
            if (password.length > 7) {
                strength += 1;
                //eightChar.classList.remove('fa-circle');
                //eightChar.classList.add('fa-check');
            } else {
                //eightChar.classList.add('fa-circle');
                //eightChar.classList.remove('fa-check');   
            }
        
            // Return results
            if (strength < 2) {
                return strength;
            } else if (strength === 2) {
                return strength;
            } else if (strength === 3) {
                return strength;
            } else {
                return strength;
            }
        }
            $('.password').on('keyup', function(e) {
                
                let totalText = this.value
                let strenghtTips = checkPasswordStrength(totalText)
                console.log(strenghtTips)
                const box1 = document.getElementById('strength-1');
                const box2 = document.getElementById('strength-2');
                const box3 = document.getElementById('strength-3');
                const box4 = document.getElementById('strength-4');
                const box5 = document.getElementById('strength-5');
                const box6 = document.getElementById('strength-6');
                const box7 = document.getElementById('strength-7');
                const box8 = document.getElementById('strength-8');

                switch (strenghtTips) {
                    case 1:
                            box1.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box1.classList.add('bg-danger');

                            box5.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box5.classList.add('bg-danger');

                            box2.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box3.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box4.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box6.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box7.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box8.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');

                            box2.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box3.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box4.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box6.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box7.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box8.classList.add('bg-slate-100','dark:bg-darkmode-800');

                            break;
                    case 2: 
                            box1.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box1.classList.add('bg-danger');

                            box5.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box5.classList.add('bg-danger');

                            box2.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box2.classList.add('bg-warning'); 

                            box6.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box6.classList.add('bg-warning');

                            box3.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box4.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box7.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box8.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            
                            box3.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box4.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box7.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box8.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            break;
                    case 3: 
                            box1.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box1.classList.add('bg-danger');

                            box5.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box5.classList.add('bg-danger');

                            box2.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box2.classList.add('bg-warning'); 

                            box6.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box6.classList.add('bg-warning');

                            box3.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box3.classList.add('bg-success'); 

                            box7.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box7.classList.add('bg-success');


                            box4.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box8.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            
                            box4.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box8.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            break;
                    case 4: 
                    case 5: 
                    case 6: 
                    case 7: 
                    case 8: 
                    case 9: 
                            box1.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box1.classList.add('bg-danger');

                            box5.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box5.classList.add('bg-danger');

                            box2.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box2.classList.add('bg-warning'); 

                            box6.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box6.classList.add('bg-warning');

                            box3.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box3.classList.add('bg-success'); 

                            box7.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box7.classList.add('bg-success');

                            box4.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box4.classList.add('bg-success');

                            box8.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box8.classList.add('bg-success');

                            break;
                    default:
                            box1.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box2.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box3.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box4.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box5.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box6.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box7.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box8.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            
                            box1.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box2.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box3.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box4.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box5.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box6.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box7.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box8.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            break;
                }
            })
        })()
        
    </script>
    @vite('resources/js/agent-crud.js')
@endsection