@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div id="agentDashboard" class="grid grid-cols-12 gap-6">
        {{-- <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6 mt-3 2xl:mt-8">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 lg:col-span-12 xl:col-span-8 mt-2">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Welcome <u> Agent Jhon Smith</u></h2>
                    
                    </div>
                    <div class="report-box-2 intro-y mt-12 sm:mt-5">
                        <div class="box sm:flex">
                            <div class="px-8 py-12 flex flex-col justify-center flex-1">
                                <div class="w-30 h-30 flex-none image-fit rounded-full overflow-hidden">
                                    <img alt="Jhon Smith" class="rounded-full" src=" {{ asset('build/assets/images/avater.png') }}">
                                </div>
                                <div class="relative text-3xl font-medium mt-5">
                                    Jhon Smith
                                </div>
                                <div class="mt-4 text-slate-500">
                                    
                                    House-223</span><br/>
                                    Barclay Hall</span><br/>
                                    London City</span>, 
                                    London State</span>, 
                                    Ey68G</span>,<br/>
                                    United Kingdom</span><br/>
                                        
                                </div>
                            </div>
                            <div class="px-8 py-12 flex flex-col justify-center flex-1 border-t sm:border-t-0 sm:border-l border-slate-200 dark:border-darkmode-300 border-dashed">
                                <div class="text-slate-500 text-xs">Email</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">
                                        agent1email@xmail.com<br/>
                                    </div>
                                </div>
                                <div class="text-slate-500 text-xs mt-5">Mobile</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">+898092222</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: General Report -->
                <!-- BEGIN: Important Notes -->
                <div class="col-span-12 sm:col-span-6 lg:col-span-4 xl:col-span-4 mt-2">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-auto">Today's Application</h2>
                        <div class="sm:ml-auto mt-3 sm:mt-0 relative text-slate-500">
                            <i class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0" data-lucide="calendar-days"></i>
                            <input id="tutor-calendar-date" value="{{ date('d-m-Y') }}" type="text" class="form-control sm:w-56 box pl-10 " placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            <input name="tutor_id" value="{{ $user->id }}" type="hidden" />
                        </div>
                        
                    </div>  
                    <div id="todays-application">
                            <div class="mt-5 intro-x">
                                <div class="box zoom-in">
                                    <div class="pt-5 px-5 flex items-center">
                                        <div class="ml-0 mr-auto">
                                            <div class="text-base font-medium truncate w-full relative">Mr. Siman Tech</div>
                                            <div class="text-slate-400 mt-1">HND IN BUSINESS</div>
                                            <div class="text-slate-400 mt-1">2022-MAY</div>
                                        </div>
                                        <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-12 h-10 inline-flex justify-center items-center">1</div>
                                        
                                    </div>
                                    <div class="mt-5 px-5 pb-5 flex font-medium justify-center">
                                        <a data-tw-toggle="modal" data-id="" data-tw-target="#editApplicationDeteilsModal" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Start Apply</a>
                                
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 intro-x">
                                <div class="box zoom-in">
                                    <div class="pt-5 px-5 flex items-center">
                                        <div class="ml-0 mr-auto">
                                            <div class="text-base font-medium truncate w-full relative">Ms. Jamon Park</div>
                                            <div class="text-slate-400 mt-1">HND IN BUSINESS</div>
                                            <div class="text-slate-400 mt-1">2022-MAY</div>
                                        </div>
                                        <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-12 h-10 inline-flex justify-center items-center">2</div>
                                        
                                    </div>
                                    <div class="mt-5 px-5 pb-5 flex font-medium justify-center">
                                        <a data-tw-toggle="modal" data-id="" data-tw-target="#editApplicationDeteilsModal" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Start Apply</a>
                                   
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
                <!-- END: Important Notes -->
            </div>
        </div> --}}
        <div class="col-span-12 xl:col-span-9 2xl:col-span-9 box p-10 my-10">
            <div class="col-span-12 w-full flex">
                <h2 class="text-lg font-medium mr-auto">My Applicants</h2>
                @if ($user->email_verified_at != NULL)
                <div id="term-dropdown" class="dropdown w-1/2 sm:w-auto ml-auto">
                    {{-- <button id="selected-term" class="dropdown-toggle btn btn-primary text-white w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                        <i  data-lucide="file-text" class="w-4 h-4 mr-2 "></i> <i data-loading-icon="oval" class="w-4 h-4 mr-2 hidden"  data-color="white"></i> <span>2009 Sep HND</span> <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                    </button>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a  id="term-1" data-tutor_id=""  data-instance_term_id="" data-instance_term="" href="javascript:;" class="dropdown-item term-select  dropdown-active">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> 2009 Sep HND
                                </a>
                            </li>
                            
                        </ul>
                    </div> --}}
                </div>
                @endif
            </div>
            @if (session('applicantSubmission'))
                <div class="alert alert-success-soft alert-dismissible show flex items-center mb-2" role="alert">
                    <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ Session::get('applicantSubmission') }}
                    <button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Close">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                @php session()->forget('applicantSubmission'); @endphp
            @endif

            <div id="studentSearchAccordionWrap" class="pt-4 mb-2">
    
                <div id="studentSearchAccordion" class="accordion accordion-boxed pt-2">
                    <div class="accordion-item">
                        <div id="studentSearchAccordion-1" class="accordion-header">
                            <button  id="studentGroupSearchBtn" class="accordion-button collapsed relative w-full text-sm font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#studentSearchAccordion-collapse-1" aria-expanded="false" aria-controls="studentSearchAccordion-collapse-1">
                                Search
                                <span class="accordionCollaps" style="width: 18px; height: 16px;"></span>
                            </button>
                        </div>
                        <div id="studentSearchAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="studentSearchAccordion-1" data-tw-parent="#studentSearchAccordion">
                            <div class="accordion-body">
                                <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="application_no" class="form-label">Referrence No</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="application_no" name="application_no" class="form-control registration_no" value="" placeholder=""/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Search By Name</label>
                                        <input id="query-CNTR" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search by Name">
                                    </div>
                                    
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="applicantEmail" class="form-label">Applicant Email</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="applicantEmail" name="applicantEmail" class="form-control email" value="" placeholder=""/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="phone" class="form-label">Applicant Phone</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="applicantPhone" name="applicantPhone" class="form-control phone" value="" placeholder=""/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="semesters" class="form-label">Intake Semester</label>
                                        <select id="semesters" class="w-full tom-selects" name="semesters[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($semesters))
                                                @foreach($semesters as $sem)
                                                    <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-semesters text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="courses" class="form-label">Course </label>
                                        <select id="courses" class="w-full tom-selects" name="courses[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($courses))
                                                @foreach($courses as $crs)
                                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-course text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="statuses" class="form-label">Status</label>
                                        <select id="statuses" class="w-full tom-selects" name="statuses[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($statuses))
                                            @foreach($statuses as $crs)
                                                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                            @endforeach
                                        @endif
                                        </select>
                                    </div>
                                    
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="agents" class="form-label">Agent/SubAgents</label>
                                        <select id="agents" class="w-full tom-selects" name="agents[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($agents))
                                            @foreach($agents as $crs)
                                                <option value="{{ $crs->agent_user_id }}">{{ $crs->full_name }} [{{ $crs->code }}]</option>
                                            @endforeach
                                        @endif
                                        </select>
                                    </div>

                                    
                                    <div class="col-span-12 sm:col-span-6 pt-7">
                                        <button id="studentGroupSearchSubmitBtn" type="button" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search <i  data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2 searchLoading hidden" ></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="applicantApplicantionList" class="mt-5 table-report table-report--tabulator"></div>
            </div>

        </div>

        
        <div class="col-span-12 xl:col-span-3 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <!-- BEGIN: Visitors -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-12 mt-3 2xl:mt-8">
                        <div class="intro-y flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">New Student List  </h2>
                            <div class=" sm:w-auto mt-4 sm:mt-0 ml-auto">
                                <a id="currentTermId" data-tw-toggle="modal" data-id="" data-tw-target="#addDeteilsModal" href="#" class="star-verification btn btn-primary shadow-md mr-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Account</a>
                            </div>
                        </div>
                        <div id="TermBox">
                            <div id="total-application">
                                <div class="report-box-2 intro-y mt-5 mb-7">
                                    <div class="box p-5">
                                        <div class="flex items-center">
                                            Total Active Application
                                        </div>
                                        <div class="text-2xl font-medium mt-2">{{ $totalRecentApplication }}</div>
                                    </div>
                                </div>
                            </div>
                            <div id="applicant-list">
                                @foreach ($recentData as $recentapplicant)
                                    @if($recentapplicant->mobile_verified_at && $recentapplicant->email_verified_at )
                                        <div data-applicationid="{{ $recentapplicant->id }}" data-email-verified="{{($recentapplicant->email_verified_at ? 1:0)}}" data-email="{{ $recentapplicant->email }}" data-mobile="{{ $recentapplicant->mobile }}" data-mobile-verified="{{ ($recentapplicant->mobile_verified_at ? 1:0) }}"  class="newapplicant-modal" style="inline-block">
                                    @else
                                        <div data-tw-target="#confirmModal" data-tw-toggle="modal" data-applicationid="{{ $recentapplicant->id }}" data-email-verified="{{($recentapplicant->email_verified_at ? 1:0)}}" data-email="{{ $recentapplicant->email }}" data-mobile="{{ $recentapplicant->mobile }}" data-mobile-verified="{{ ($recentapplicant->mobile_verified_at ? 1:0) }}"  class="newapplicant-modal" style="inline-block">
                                    @endif
                                            <div  class="intro-y module-details_1 ">
                                                
                                                <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                                                    <div data-tw-target="#confirmDeleteModal" data-tw-toggle="modal" data-id="{{ $recentapplicant->id }}" title="Do you want to remove this item?" class="tooltip w-5 h-5 flex items-center justify-center absolute rounded-full text-white bg-danger right-0 top-0 -mr-2 -mt-2 delete_btn">
                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                    </div>
                                                    <div class="ml-4 mr-auto">
                                                        <div class="font-medium">
                                                            @if($recentapplicant->mobile_verified_at && $recentapplicant->email_verified_at )
                                                            <a href="{{ route("agent.application",$recentapplicant->id) }}" style="inline-block">{{ $recentapplicant->full_name }}</a>
                                                            @else
                                                            {{ $recentapplicant->full_name }}
                                                            @endif
                                                        </div>
                                                        <div class="text-slate-500 text-xs mt-0.5 ">
                                                            @if($recentapplicant->email_verified_at)
                                                                <i data-lucide="check-circle" class="w-4 h-4 mr-1 text-success inline-flex"></i> Email verified
                                                            @else
                                                                <i data-lucide="x-circle" class="w-4 h-4 mr-1 text-danger inline-flex"></i> Email not verified
                                                            @endif
                                                        </div>
                                                        <div class="text-slate-500 text-xs mt-0.5 ">

                                                            @if($recentapplicant->mobile_verified_at)
                                                                <i data-lucide="check-circle" class="w-4 h-4 mr-1 text-success inline-flex"></i> Mobile verified
                                                            @else
                                                                <i data-lucide="x-circle" class="w-4 h-4 mr-1 text-danger inline-flex"></i> Mobile not verified
                                                            @endif

                                                        </div>
                                                    </div>
                                                    @if($recentapplicant->mobile_verified_at && $recentapplicant->email_verified_at )
                                                        <a href="{{ route("agent.application",$recentapplicant->id) }}" class="btn btn-sm btn-success w-28 mr-2 mb-2 text-white">
                                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Apply Now
                                                        </a>
                                                    @else
                                                        <div class="rounded-full text-lg bg-warning text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">
                                                            <i data-lucide="alert-circle" class="w-4 h-4 m-auto text-white"></i>
                                                        </div>
                                                    @endif
                                                    
                                                </div>
                                            </div>
                                        </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <!-- END: Visitors -->
                </div>
            </div>
        </div>
    </div>
    
  
 


    @if (session('verifymessage'))
        <!-- BEGIN: Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div id="title-notification" class="font-medium"> @if(session('sessiontitle')) {{ session('sessiontitle') }} @else Email Sent! @endif </div>
                <div id="title-context" class="text-slate-500 mt-1">{{ session('verifymessage') }}</div>
            </div>
        </div>
        <!-- END: Notification Content -->
        <!-- BEGIN: Notification Toggle -->
        <button id="success-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
        <!-- END: Notification Toggle -->
    @endif
    @if ($user->email_verified_at == NULL)
    
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                   
            <form id="resendverification" method="post" action="{{ route('agent.verification.send') }}" class="xl:flex sm:mr-auto" >
                @csrf
                <div class="sm:flex items-center sm:mr-4">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">A verification email has been sent to your inbox. Kindly go to your email address and click the verify button to confirm your email.

                        <br/>In case you don't see the email in your inbox, please check your Junk/Spam folder. Thank you.</label>
                </div>
                <div class="flex justify-end mx-auto sm:mt-0">
                    <button id="emailverification" type="submit" class="btn btn-dark w-1/2 sm:w-auto mr-2">
                        <i data-lucide="send" class="w-4 h-4 mr-2"></i> Click Here to Resend Verification Email
                    </button>
                </div>
            </form>
        </div>

    </div>

    @endif

    <!-- BEGIN: HTML Table Data -->

        <!-- ALL APPLICANT BASE DATA WILL BE HERE -->
  
    <!-- End: HTML Table Data --> 
    @include('pages.agent.dashboard.modals')

@endsection


@section('script')
    <script type="module">
        (function () {
            if($('#success-notification-toggle').length>0) {
                $("#success-notification-toggle").trigger('click')
            }
        async function resetForm() {
                // Reset state
                $('#AgentChangePasswordModalForm').find('.login__input').removeClass('border-danger')
                $('#AgentChangePasswordModalForm').find('.login__input-error').html('')
            
                // Post form
                let myform = document.getElementById("AgentChangePasswordModalForm");
                let formData = new FormData(myform);
                // Loading state
                $('#btn-changepassword').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
                tailwind.svgLoader()
                await helper.delay(500)

                axios.post(route('agent.change.password.post'), formData).then(res => {
                    
                    // $("#title-notification").html("Password Changed!");
                    // $("#title-context").html("Password Changed Successfully");

                    // $("#success-notification-toggle").trigger('click')
                    $('#btn-changepassword').html('Update Passwored')

                }).catch(err => {
                    $('#btn-changepassword').find('.login__input').removeClass('border-danger')
                    $('#btn-changepassword').find('.login__input-error').html('')
                    $('#btn-changepassword').html('Update Passwored')
                    if (err.response.data.message != 'Password Could not Updated.') {
                        for (const [key, val] of Object.entries(err.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        
                        $(`#password`).addClass('border-danger')
                        $(`#error-password`).html(err.response.data.message)
                    }
                })
            }

            $('#AgentChangePasswordModalForm').on('keyup', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    resetForm()
                }
            })

            $('#btn-changepassword').on('click', function() {
                resetForm()
            })

            

        })()
    </script>
    @vite('resources/js/agent-dahsboard.js')
@endsection
