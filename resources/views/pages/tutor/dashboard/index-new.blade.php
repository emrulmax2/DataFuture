@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - Midone - Tailwind HTML Admin Template</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6 mt-3 2xl:mt-8">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 lg:col-span-8 xl:col-span-8 mt-2">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Welcome <u>Mr Nazmus Sakib</u></h2>
                    </div>
                    <div class="report-box-2 intro-y mt-12 sm:mt-5">
                        <div class="box sm:flex">
                            <div class="px-8 py-12 flex flex-col justify-center flex-1">
                                <div class="w-30 h-30 flex-none image-fit rounded-full overflow-hidden">
                                    <img alt="Midone - HTML Admin Template" src="http://127.0.0.1:8000/build/assets/images/profile-14.jpg">
                                </div>
                                <div class="relative text-3xl font-medium mt-5">
                                    Mr Nazmus Sakib
                                </div>
                                <div class="mt-4 text-slate-500">
                                    London Churchill College<br/>
                                    116 Cavell Street<br/>
                                    London, E1 2JA,<br/>
                                    United Kingdom
                                </div>
                            </div>
                            <div class="px-8 py-12 flex flex-col justify-center flex-1 border-t sm:border-t-0 sm:border-l border-slate-200 dark:border-darkmode-300 border-dashed">
                                <div class="text-slate-500 text-xs">Email</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">
                                        limon@lcc.ac<br/>
                                        limon@lcc.ac.uk
                                    </div>
                                </div>
                                <div class="text-slate-500 text-xs mt-5">Mobile</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">+001 100-01910987</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: General Report -->
                <!-- BEGIN: Visitors -->
                <div class="col-span-12 sm:col-span-6 lg:col-span-4 xl:col-span-4 mt-2">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">My Modules</h2>
                        <a href="#" class="ml-auto btn btn-sm btn-primary px-3 py-2 text-white">2023 May HND</a>
                    </div>
                    <div class="report-box-2 intro-y mt-5 mb-7">
                        <div class="box p-5">
                            <div class="flex items-center">
                                Total No of Modules
                            </div>
                            <div class="text-2xl font-medium mt-2">3</div>
                        </div>
                    </div>
                    <div class="intro-y">
                        <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                            <div class="ml-4 mr-auto">
                                <div class="font-medium">Business Environment</div>
                                <div class="text-slate-500 text-xs mt-0.5">HND in Business</div>
                            </div>
                            <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                        </div>
                    </div>
                    <div class="intro-y">
                        <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                            <div class="ml-4 mr-auto">
                                <div class="font-medium">Business Environment</div>
                                <div class="text-slate-500 text-xs mt-0.5">HND in Business</div>
                            </div>
                            <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                        </div>
                    </div>
                    <div class="intro-y">
                        <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                            <div class="ml-4 mr-auto">
                                <div class="font-medium">Business Environment</div>
                                <div class="text-slate-500 text-xs mt-0.5">HND in Business</div>
                            </div>
                            <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                        </div>
                    </div>
                </div>
                <!-- END: Visitors -->
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <!-- BEGIN: Important Notes -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-12 mt-3 2xl:mt-8">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-auto">Class Date</h2>
                            <div class="sm:ml-auto mt-3 sm:mt-0 relative text-slate-500">
                                <i class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0" data-lucide="calendar-days"></i>
                                <input value="{{ date('d-m-Y') }}" type="text" class="form-control sm:w-56 box pl-10 datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                        </div>
                        <div class="mt-5 intro-x">
                            <div class="box zoom-in">
                                <div class="pt-5 px-5 flex items-center">
                                    <div class="ml-0 mr-auto">
                                        <div class="text-base font-medium truncate">Module Name</div>
                                        <div class="text-slate-400 mt-1">Course Name</div>
                                        <div class="text-slate-400 mt-1">Schedule - 12:00 at BH - Room 01</div>
                                    </div>
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                </div>
                                <div class="mt-5 px-5 pb-5 flex font-medium justify-center">
                                    <a href="#" class="btn btn-sm btn-primary text-white py-2 px-3">Start Class</a>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 intro-x">
                            <div class="box zoom-in">
                                <div class="pt-5 px-5 flex items-center">
                                    <div class="ml-0 mr-auto">
                                        <div class="text-base font-medium truncate">Module Name</div>
                                        <div class="text-slate-400 mt-1">Course Name</div>
                                        <div class="text-slate-400 mt-1">Schedule - 12:00 at BH - Room 01</div>
                                    </div>
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                </div>
                                <div class="mt-5 px-5 pb-5 flex font-medium justify-center">
                                    <a href="#" class="btn btn-sm btn-primary text-white py-2 px-3">Start Class</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END: Important Notes -->
                </div>
            </div>
        </div>
    </div>
@endsection
