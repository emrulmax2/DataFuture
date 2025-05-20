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
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Workplacement Companies / Supervisor</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <div class="dropdown">
                        <button data-tw-toggle="modal" data-tw-target="#addWPCompanyModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add Company</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="intro-y box p-2 mt-2">
            <div class="grid grid-cols-12 gap-0 items-center">
                <form id="searchForm" action="{{ route('workplacement.companies.search') }}" method="GET" class="col-span-12 w-full">
                    <div class="w-full relative">
                        <input type="text" placeholder="Search Keyword...." id="search" 
                               class="form-control w-full pr-10" name="search" 
                               value="{{ request('search') }}">
                        <i data-lucide="search" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-[18px] h-[18px]"></i>
                    </div>
                </form>
            </div>
        </div>

        <div class="intro-y box p-5 mt-2 companyListContainer">
            <div class="col-span-12">
                <div class="intro-y box p-5">
                    <div class="text-center">No Workplacement Companies Found</div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Settings Page Content -->
@include('pages.settings.workplacement.wp-company-modal')

@endsection

@section('script')
@vite('resources/js/settings.js')
@vite('resources/js/wp-company-supervisor.js')
@endsection