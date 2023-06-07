@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
<div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Venue Details</h2>
    </div>
    <!-- BEGIN: Profile Info -->
    <div class="intro-y box px-5 pt-5 mt-5">
        <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
            <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                <div class="ml-auto mr-auto">
                    <div class="w-auto sm:w-full truncate text-primary sm:whitespace-normal font-bold text-3xl">{{ $venue->name }}</div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
                <div class="font-medium text-center lg:text-left lg:mt-3">Venue Details</div>
                <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                    <div class="truncate sm:whitespace-normal flex items-center">
                        <i data-lucide="user" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">ID Number:</span> <span class="font-medium ml-2">{{ $venue->idnumber }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="flag" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">UKPRN:</span> <span class="font-medium ml-2">{{ $venue->ukprn }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Postal Code:</span> <span class="font-medium ml-2">{{ $venue->postcode }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex-1 px-5 pt-5 lg:pt-0">
                
            </div>
        </div>
        {{-- <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
            <li id="courseModule-tab" class="nav-item mr-5" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#courseModule" aria-controls="courseModule" aria-selected="true" role="tab" >
                    <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Course Modules
                </a>
            </li>
        </ul> --}}
        <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
            <li id="room-tab" class="nav-item mr-5" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#room" aria-controls="room" aria-selected="true" role="tab" >
                    <i data-lucide="tablet" class="w-4 h-4 mr-2"></i> Rooms
                </a>
            </li>
        </ul>
    </div>
    {{-- <div class="intro-y tab-content mt-5">
        <div id="courseModule" class="tab-pane active" role="tabpanel" aria-labelledby="courseModule-tab">
            <div class="intro-y box p-5 mt-5">
                <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                    <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                            <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                        </div>
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                            <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                <option value="1">Active</option>
                                <option value="2">Archived</option>
                            </select>
                        </div>
                        <div class="mt-2 xl:mt-0">
                            <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                            <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
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
                                    <li>
                                        <a id="tabulator-export-json" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-html" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <button data-tw-toggle="modal" data-tw-target="#" type="button" class="add_btn btn btn-primary shadow-md ml-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add</button>
                    </div>
                </div>
                <div class="overflow-x-auto scrollbar-hidden">                  
                </div>
            </div>
        </div>
    </div> --}}
    <div class="intro-y tab-content mt-5">
        <div id="room" class="tab-pane active" role="tabpanel" aria-labelledby="room-tab">
            @include('pages.venues.details.room')
        </div>
    </div>
    @include('pages.venues.details.room-modal')

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
    {{-- <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
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
    </div> --}}
    <!-- END: Delete Confirm Modal Content -->
@endsection