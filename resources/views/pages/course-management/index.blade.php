@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Course Management</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.course-management.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Display Information -->
            <div class="grid grid-cols-12 gap-6 lg:mt-5">
                <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
                    <a href="{{ route('semester') }}" class="report-box zoom-in">
                        <div class="box p-5">
                            <div class="flex">
                                <i data-lucide="check-circle" class="report-box__icon text-success"></i>
                            </div>
                            <div class="text-3xl font-medium leading-8 mt-6">{{ $semesters }}</div>                                    
                            <div class="text-base text-slate-500 mt-1">Semesters</div> 
                        </div>
                    </a>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
                    <a href="{{ route('courses') }}" class="report-box zoom-in">
                        <div class="box p-5">
                            <div class="flex">
                                <i data-lucide="check-circle" class="report-box__icon text-success"></i>
                            </div>
                            <div class="text-3xl font-medium leading-8 mt-6">{{ $courses }}</div>                                    
                            <div class="text-base text-slate-500 mt-1">Courses</div> 
                        </div>
                    </a>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
                    <a href="{{ route('term-declaration.index') }}" class="report-box zoom-in">
                        <div class="box p-5">
                            <div class="flex">
                                <i data-lucide="check-circle" class="report-box__icon text-success"></i>
                            </div>
                            <div class="text-3xl font-medium leading-8 mt-6">{{ $termdecs }}</div>                                    
                            <div class="text-base text-slate-500 mt-1">Term Declarations</div> 
                        </div>
                    </a>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
                    <a href="{{ route('term.module.creation') }}" class="report-box zoom-in">
                        <div class="box p-5">
                            <div class="flex">
                                <i data-lucide="check-circle" class="report-box__icon text-success"></i>
                            </div>
                            <div class="text-3xl font-medium leading-8 mt-6">{{ $modcreations }}</div>                                    
                            <div class="text-base text-slate-500 mt-1">Term Module Creations</div> 
                        </div>
                    </a>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
                    <a href="{{ route('groups') }}" class="report-box zoom-in">
                        <div class="box p-5">
                            <div class="flex">
                                <i data-lucide="check-circle" class="report-box__icon text-success"></i>
                            </div>
                            <div class="text-3xl font-medium leading-8 mt-6">{{ $groups }}</div>                                    
                            <div class="text-base text-slate-500 mt-1">Groups</div> 
                        </div>
                    </a>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
                    <a href="{{ route('class.plan') }}" class="report-box zoom-in">
                        <div class="box p-5">
                            <div class="flex">
                                <i data-lucide="check-circle" class="report-box__icon text-success"></i>
                            </div>
                            <div class="text-3xl font-medium leading-8 mt-6">{{ $plans }}</div>                                    
                            <div class="text-base text-slate-500 mt-1">Class Plans</div> 
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/course-management.js')
@endsection