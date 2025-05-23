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
            @if(isset(auth()->user()->priv()['site_settings']) && auth()->user()->priv()['site_settings'] == 1)
                <!-- BEGIN: Display Information -->
                <div class="intro-y box lg:mt-5">
                    <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                        <h2 class="font-medium text-base mr-auto">Update Company Information</h2>
                    </div>
                    <div class="p-5">
                        <form method="post" action="#" id="companySettingsForm" enctype="multipart/form-data">
                            <div class="flex flex-col-reverse xl:flex-row flex-col">
                                <div class="flex-1 mt-6 xl:mt-0">
                                    <div class="grid grid-cols-12 gap-x-5 gap-y-4">
                                        <div class="col-span-12 sm:col-span-6">
                                            <label for="company_name" class="form-label">Company Name</label>
                                            <input id="company_name" type="text" name="company_name" class="form-control" placeholder="Company Name" value="{{ (isset($opt['company_name']) ? $opt['company_name'] : '' ) }}">
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <label for="company_address" class="form-label">Address</label>
                                            <input id="company_address" type="text" name="company_address" class="form-control" placeholder="Address" value="{{ (isset($opt['company_address']) ? $opt['company_address'] : '' ) }}">
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <label for="company_phone" class="form-label">Phone</label>
                                            <input id="company_phone" type="text" name="company_phone" class="form-control" placeholder="Phone" value="{{ (isset($opt['company_phone']) ? $opt['company_phone'] : '' ) }}">
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <label for="company_email" class="form-label">Email Address</label>
                                            <input id="company_email" type="text" name="company_email" class="form-control" placeholder="Email Address" value="{{ (isset($opt['company_email']) ? $opt['company_email'] : '' ) }}">
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <label for="company_term_condition_url" class="form-label">Terms and condition URL</label>
                                            <input id="company_term_condition_url" type="text" name="company_term_condition_url" class="form-control" placeholder="Terms and condition URL" value="{{ (isset($opt['company_term_condition_url']) ? $opt['company_term_condition_url'] : '' ) }}">
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <label for="company_e_learning_url" class="form-label">E-Learning URL</label>
                                            <input id="company_e_learning_url" type="text" name="company_e_learning_url" class="form-control" placeholder="E-Learning URL" value="{{ (isset($opt['company_e_learning_url']) ? $opt['company_e_learning_url'] : '' ) }}">
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <label for="company_doc_req_url" class="form-label">Document Request URL</label>
                                            <input id="company_doc_req_url" type="text" name="company_doc_req_url" class="form-control" placeholder="Document Request URL" value="{{ (isset($opt['company_doc_req_url']) ? $opt['company_doc_req_url'] : '' ) }}">
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <label for="company_hcuci" class="form-label">HESA College Unique Code Identifier</label>
                                            <input id="company_hcuci" type="text" name="company_hcuci" class="form-control" placeholder="Unique Code Identifier" value="{{ (isset($opt['company_hcuci']) ? $opt['company_hcuci'] : '' ) }}">
                                        </div>
                                        <div class="col-span-12 sm:col-span-12">
                                            <label for="company_registration" class="form-label">Company Registration Details</label>
                                            <textarea rows="3" id="company_registration" name="company_registration" class="form-control" placeholder="Company Reg. No. 5995926, Companies House, England and Wales">{{ (isset($opt['company_registration']) ? $opt['company_registration'] : '' ) }}</textarea>
                                        </div>
                                        <div class="col-span-12 sm:col-span-12">
                                            <label for="company_right" class="form-label">Copyright Info</label>
                                            <textarea rows="3" id="company_right" name="company_right" class="form-control" placeholder="Right reserved by LCC @ 2023">{{ (isset($opt['company_right']) ? $opt['company_right'] : '' ) }}</textarea>
                                        </div>
                                    </div>
                                    <button type="submit" id="updateCINF" class="btn btn-primary w-auto mt-4">
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
                                    <input type="hidden" name="category" value="SITE_SETTINGS"/>
                                </div>
                                <div class="w-52 mx-auto xl:mr-0 xl:ml-6">
                                    <div class="border-2 border-dashed shadow-sm border-slate-200/60 dark:border-darkmode-400 rounded-md p-5">
                                        <div class="h-20 relative imgUploadWrap flex justify-center items-center cursor-pointer zoom-in mx-auto">
                                            <img class="rounded-0 siteLogoImg" id="siteLogoImg" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" alt="Site Logo" src="{{ (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']) ? Storage::disk('local')->url('public/'.$opt['site_logo']) : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                        </div>
                                        <div class="mx-auto cursor-pointer relative mt-5">
                                            <button type="button" class="btn btn-linkedin w-full">Select Logo</button>
                                            <input type="file" accept=".jpg, .jpeg, .png, .gif, .svg" id="siteLogoUpload" name="site_logo" class="w-full h-full cursor-pointer top-0 left-0 absolute opacity-0">
                                        </div>
                                    </div>
                                    <div class="border-2 border-dashed shadow-sm border-slate-200/60 dark:border-darkmode-400 rounded-md p-5 mt-4">
                                        <div class="h-20 relative imgUploadWrap flex justify-center items-center cursor-pointer zoom-in mx-auto">
                                            <img class="rounded-0" alt="Site Favicon siteFaviconImg" id="siteFaviconImg" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ (isset($opt['site_favicon']) && !empty($opt['site_favicon']) && Storage::disk('local')->exists('public/'.$opt['site_favicon']) ? Storage::disk('local')->url('public/'.$opt['site_favicon']) : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                        </div>
                                        <div class="mx-auto cursor-pointer relative mt-5">
                                            <button type="button" class="btn btn-linkedin w-full">Select Favicon</button>
                                            <input accept=".png, .svg" type="file" name="site_favicon" id="siteFaviconUpload" class="w-full h-full cursor-pointer top-0 left-0 absolute opacity-0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else 
                <div class="alert alert-success-soft show flex items-center mb-2 lg:mt-5" role="alert">
                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> You do not have enough permission to view this page's content. Please navigate to the menus on the left. 
                </div>
            @endif
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
@endsection

@section('script')
    @vite('resources/js/settings.js')
@endsection