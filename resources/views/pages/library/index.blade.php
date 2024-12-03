@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">       
        <div class="col-span-12 2xl:col-span-9"> 
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Library Dashboard</h2>
                        
                    </div>
                    
                </div>
            </div>
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12">
                    <div class="intro-y box p-5 mt-5">
                        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                    <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                                </div>
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Location</label>
                                    <select id="searc_location" name="location[]" multiple class="tom-selects w-full mt-2 sm:mt-0 sm:w-72" >
                                        <option value="">Please Select</option>
                                        @if($locations->count() > 0)
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                            @endforeach
                                        @endif
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
                                <div class="dropdown w-1/2 sm:w-auto mr-2">
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
                                                <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto scrollbar-hidden">
                            <div id="amazonBookInfoListTable" class="mt-5 table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Library Info</h2>
                            <a href="{{ route('library.settings') }}" class="ml-auto text-primary truncate">Settings</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No data found!.
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Issued Books</h2>
                            <a href="javascript:void(0);" class="ml-auto text-primary truncate">View All</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No data found!.
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Overdue Books</h2>
                            <a href="javascript:void(0);" class="ml-auto text-primary truncate">View All</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No data found!.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div> 
    </div>

    <!-- BEGIN: View Qty Modal -->
    <div id="viewQtyModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">View Book Locations</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="libraryBookLocationList" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: View Qty Modal -->

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
    @vite('resources/js/library-management-dashboard.js')
@endsection
