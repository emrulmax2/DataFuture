@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Student Option Value</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>

    <div class="intro-y grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 lg:col-span-6">
            <div class="intro-y box optionBox">
                <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Titles</h2>
                    <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                        <button data-tw-toggle="modal" data-tw-target="#addTitleModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Title</button>
                    </div>
                </div>
                <div class="optionBoxBody p-5" data-tableid="titleListTable">
                    @include('pages.studentoption.title.index')
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6">
            <div class="intro-y box optionBox">
                <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Ethnicities</h2>
                    <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                        <button data-tw-toggle="modal" data-tw-target="#addEthnicityModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Ethnicity</button>
                    </div>
                </div>
                <div class="optionBoxBody p-5" data-tableid="ethnicityListTable">
                    @include('pages.studentoption.ethnicity.index')
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6">
            <div class="intro-y box optionBox">
                <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Kins Relation</h2>
                    <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                        <button data-tw-toggle="modal" data-tw-target="#addKinsModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Relation</button>
                    </div>
                </div>
                <div class="optionBoxBody p-5" data-tableid="kinsListTable">
                    @include('pages.studentoption.kins-relation.index')
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6">
            <div class="intro-y box optionBox">
                <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Sexual Orientation</h2>
                    <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                        <button data-tw-toggle="modal" data-tw-target="#addSexoModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Orientation</button>
                    </div>
                </div>
                <div class="optionBoxBody p-5" data-tableid="sexoListTable">
                    @include('pages.studentoption.sexual-orientation.index')
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6">
            <div class="intro-y box optionBox">
                <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Religions</h2>
                    <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                        <button data-tw-toggle="modal" data-tw-target="#addRelgnModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Religion</button>
                    </div>
                </div>
                <div class="optionBoxBody p-5" data-tableid="relgnListTable">
                    @include('pages.studentoption.religion.index')
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6">
            <div class="intro-y box optionBox">
                <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Genders</h2>
                    <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                        <button data-tw-toggle="modal" data-tw-target="#addHgenModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Gender</button>
                    </div>
                </div>
                <div class="optionBoxBody p-5" data-tableid="hgenListTable">
                    @include('pages.studentoption.hesagender.index')
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6">
            <div class="intro-y box optionBox">
                <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Countries</h2>
                    <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                        <button data-tw-toggle="modal" data-tw-target="#addCountryModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Gender</button>
                    </div>
                </div>
                <div class="optionBoxBody p-5" data-tableid="countryListTable">
                    @include('pages.studentoption.country.index')
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
    @vite('resources/js/student-option.js')
    @vite('resources/js/title.js')
    @vite('resources/js/ethnicity.js')
    @vite('resources/js/kins-relation.js')
    @vite('resources/js/sexual-rientation.js')
    @vite('resources/js/religion.js')
    @vite('resources/js/hesagender.js')
    @vite('resources/js/country.js')
@endsection