@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-3 2xl:col-span-3 relative z-10">
            <!-- BEGIN: Profile Info -->
            @include('pages.accounts.sidebar')
            <!-- END: Profile Info -->
        </div>
        <div class="col-span-12 xl:col-span-9 2xl:col-span-9 z-10 pt-6">
            <div class="-mb-6 intro-y">
                <div class="alert alert-dismissible show box bg-primary text-white flex items-center mb-6" role="alert">
                    <span>
                        Introducing new dashboard! Download now at <a href="https://themeforest.net/item/midone-jquery-tailwindcss-html-admin-template/26366820" class="underline ml-1" target="blank">themeforest.net</a>.
                        <button class="rounded-md bg-white bg-opacity-20 dark:bg-darkmode-300 hover:bg-opacity-30 py-0.5 px-2 -my-3 ml-2">Live Preview</button>
                    </span>
                    <button type="button" class="btn-close text-white" data-tw-dismiss="alert" aria-label="Close">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div class="mt-14 mb-3 grid grid-cols-12 sm:gap-10 intro-y">
                <div class="col-span-12 sm:col-span-6 md:col-span-4 py-6 sm:pl-5 md:pl-0 lg:pl-5 relative text-center sm:text-left">
                    <div class="absolute pt-0.5 2xl:pt-0 mt-5 2xl:mt-6 top-0 right-0 dropdown">
                        <a class="dropdown-toggle block" href="javascript:;" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="more-vertical" class="w-5 h-5 text-slate-500"></i>
                        </a>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a href="" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Monthly Report
                                    </a>
                                </li>
                                <li>
                                    <a href="" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Annual Report
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="text-sm 2xl:text-base font-medium -mb-1">
                        Hi Angelina, <span class="text-slate-600 dark:text-slate-300 font-normal">welcome back!</span>
                    </div>
                    <div class="text-base 2xl:text-lg justify-center sm:justify-start flex items-center text-slate-600 dark:text-slate-300 leading-3 mt-14 2xl:mt-24">
                        My Total Assets
                        <i data-lucide="alert-circle" class="tooltip w-5 h-5 ml-1.5 mt-0.5" title="Total value of your sales: $158.409.416"></i>
                    </div>
                    <div class="2xl:flex mt-5 mb-3">
                        <div class="flex items-center justify-center sm:justify-start">
                            <div class="relative text-2xl 2xl:text-3xl font-medium leading-6 pl-3 2xl:pl-4">
                                <span class="absolute text-xl 2xl:text-2xl top-0 left-0 -mt-1 2xl:mt-0">$</span> 142,402,210
                            </div>
                            <a class="text-slate-500 ml-4 2xl:ml-16" href="">
                                <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                            </a>
                        </div>
                        <div class="mt-5 2xl:flex 2xl:justify-center 2xl:mt-0 2xl:-ml-20 2xl:w-14 2xl:flex-none 2xl:pl-2.5">
                            <div class="font-medium inline-flex bg-success text-white rounded-full px-2 py-1 text-xs 2xl:text-sm 2xl:p-0 2xl:text-success 2xl:bg-transparent 2xl:flex items-center tooltip cursor-pointer 2xl:justify-center" title="49% Higher than last month">
                                49% <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                            </div>
                        </div>
                    </div>
                    <div class="text-slate-500">Last updated 1 hour ago</div>
                    <div class="2xl:text-base text-slate-600 dark:text-slate-300 mt-6 -mb-1">
                        Total net margin <a href="" class="underline decoration-dotted underline-offset-4 text-primary dark:text-slate-400">$12,921,050</a>
                    </div>
                    <div class="mt-14 2xl:mt-24 dropdown">
                        <button class="dropdown-toggle btn btn-rounded-primary w-44 2xl:w-52 px-4 relative justify-start" aria-expanded="false" data-tw-toggle="dropdown">
                            Download Reports
                            <span class="w-8 h-8 absolute flex justify-center items-center right-0 top-0 bottom-0 my-auto ml-auto mr-1">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </span>
                        </button>
                        <div class="dropdown-menu w-44 2xl:w-52">
                            <ul class="dropdown-content">
                                <li>
                                    <a href="" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Monthly Report
                                    </a>
                                </li>
                                <li>
                                    <a href="" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Annual Report
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row-start-2 md:row-start-auto col-span-12 md:col-span-4 py-6 border-black border-opacity-10 border-t md:border-t-0 md:border-l md:border-r border-dashed px-10 sm:px-28 md:px-5 -mx-5">
                    <div class="flex flex-wrap items-center">
                        <div class="flex items-center w-full sm:w-auto justify-center sm:justify-start mr-auto mb-5 2xl:mb-0">
                            <div class="w-2 h-2 bg-primary rounded-full -mt-4"></div>
                            <div class="ml-3.5">
                                <div class="relative text-xl 2xl:text-2xl font-medium leading-6 2xl:leading-5 pl-3.5 2xl:pl-4">
                                    <span class="absolute text-base 2xl:text-xl top-0 left-0 2xl:-mt-1.5">$</span> 47,578.77
                                </div>
                                <div class="text-slate-500 mt-2">Yearly budget</div>
                            </div>
                        </div>
                        <select class="form-select bg-transparent border-black border-opacity-10 dark:border-darkmode-400 dark:bg-transparent mx-auto sm:mx-0 py-1.5 px-3 w-auto -mt-2">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom-date">Custom Date</option>
                        </select>
                    </div>
                    <div class="mt-10 text-slate-600 dark:text-slate-300">You have spent about 35% of your annual budget.</div>
                    <div class="mt-6">
                        <div class="h-[290px]">
                            <canvas id="report-bar-chart-1"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-4 py-6 border-black border-opacity-10 border-t sm:border-t-0 border-l md:border-l-0 border-dashed -ml-4 pl-4 md:ml-0 md:pl-0">
                    <ul
                        class="
                            nav
                            nav-pills
                            w-3/4
                            2xl:w-4/6
                            bg-slate-200
                            dark:bg-black/10
                            rounded-md
                            mx-auto
                            p-1
                        "
                        role="tablist"
                    >
                        <li id="active-users-tab" class="nav-item flex-1" role="presentation">
                            <button
                                class="nav-link w-full py-1.5 px-2 active"
                                data-tw-toggle="pill"
                                data-tw-target="#active-users"
                                type="button"
                                role="tab"
                                aria-controls="active-users"
                                aria-selected="true"
                            >
                                Active
                            </button>
                        </li>
                        <li id="inactive-users-tab" class="nav-item flex-1" role="presentation">
                            <button
                                class="nav-link w-full py-1.5 px-2"
                                data-tw-toggle="pill"
                                data-tw-target="#inactive-users"
                                type="button"
                                role="tab"
                                aria-selected="false"
                            >
                                Inactive
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content mt-6">
                        <div class="tab-pane active" id="active-users" role="tabpanel" aria-labelledby="active-users-tab">
                            <div class="relative mt-8">
                                <div class="h-[215px]">
                                    <canvas id="report-donut-chart-3"></canvas>
                                </div>
                                <div class="flex flex-col justify-center items-center absolute w-full h-full top-0 left-0">
                                    <div class="text-xl 2xl:text-2xl font-medium">2.501</div>
                                    <div class="text-slate-500 mt-0.5">Active Users</div>
                                </div>
                            </div>
                            <div class="mx-auto w-10/12 2xl:w-2/3 mt-8">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-primary rounded-full mr-3"></div>
                                    <span class="truncate">17 - 30 Years old</span>
                                    <span class="font-medium ml-auto">62%</span>
                                </div>
                                <div class="flex items-center mt-4">
                                    <div class="w-2 h-2 bg-pending rounded-full mr-3"></div>
                                    <span class="truncate">31 - 50 Years old</span>
                                    <span class="font-medium ml-auto">33%</span>
                                </div>
                                <div class="flex items-center mt-4">
                                    <div class="w-2 h-2 bg-warning rounded-full mr-3"></div>
                                    <span class="truncate">>= 50 Years old</span>
                                    <span class="font-medium ml-auto">10%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

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
                        <button type="button" data-action="NONE" class="btn btn-primary successCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/accounts.js')
    @vite('resources/js/accounts-summary.js')
@endsection
