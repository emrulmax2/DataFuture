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
            <form method="post" action="#" id="summarySearchForm">
                <div class="grid grid-cols-12 gap-4 mb-5">
                    <div class="col-span-2">
                        <div class=" relative text-slate-500">
                            <i data-lucide="calendar" class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0"></i>
                            <input type="text" placeholder="DD-MM-YYYY - DD-MM-YYYY" class="w-full form-control pl-10"  id="summary_date" name="summary_date">
                        </div>
                    </div>
                    <div class="col-span-6">
                        <input type="text" placeholder="Search Here..." class="w-full form-control" id="summary_search_query" name="summary_search_query"/>
                    </div>
                    <div class="col-span-2">
                        <input type="number" step="any" placeholder="Min Amount" class="w-full form-control" id="summary_min_amount" name="summary_min_amount"/>
                    </div>
                    <div class="col-span-2">
                        <input type="number" step="any" placeholder="Max Amount" class="w-full form-control" id="summary_max_amount" name="summary_max_amount"/>
                    </div>
                    <div class="col-span-8">
                        <div class="advanceSearchGroup" style="display: none;">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-6">
                                    <select id="summary_categories" name="summary_categories[]" multiple class="w-full tom-selects">
                                        <option value="">Select Category</option>
                                        @if($categories->count() > 0)
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-span-6">
                                    <select id="summary_storages" name="summary_storages[]" multiple class="w-full tom-selects">
                                        <option value="">Select Storage</option>
                                        @if($banks->count() > 0)
                                            @foreach($banks as $bnk)
                                                <option value="{{ $bnk->id }}">{{ $bnk->bank_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-4 text-right">
                        <button type="button" id="advanceSearchToggle" class="btn btn-primary w-auto">Advance Search</button>
                    </div>
                </div>
            </form>
            <div class="summarySearchResultWrap"></div>

            <div class="col-span-12 lg:col-span-6 mt-8">
                <div class="intro-y block sm:flex items-center h-10">
                    <h2 class="text-lg font-medium truncate mr-5">Report</h2>
                    <div class="sm:ml-auto mt-3 sm:mt-0 relative text-slate-500">
                        <i data-lucide="calendar" class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0"></i>
                        <input type="text" id="reportPicker" class="form-control sm:w-56 box pl-10">
                    </div>
                </div>
                <div class="intro-y box p-5 mt-12 sm:mt-5">
                    <div class="flex flex-col md:flex-row md:items-center">
                        <div class="flex">
                            <div>
                                <div class="text-success text-lg xl:text-xl font-medium">{{ $chartData['totalInc'] }}</div>
                                <div class="mt-0.5 text-slate-500">Incomes</div>
                            </div>
                            <div class="w-px h-12 border border-r border-dashed border-slate-200 dark:border-darkmode-300 mx-4 xl:mx-5"></div>
                            <div>
                                <div class="text-danger text-lg xl:text-xl font-medium">{{ $chartData['totalExp'] }}</div>
                                <div class="mt-0.5 text-slate-500">Expenses</div>
                            </div>
                        </div>
                    </div>
                    <div class="report-chart">
                        <div class="h-[275px]">
                            <canvas 
                                id="report-line-chart" 
                                data-months={{ (isset($chartData['months']) && !empty($chartData['months']) ? json_encode($chartData['months']) : '') }} 
                                data-incomes={{ (isset($chartData['incomes']) && !empty($chartData['incomes']) ? json_encode($chartData['incomes']) : '') }} 
                                data-expense={{ (isset($chartData['expense']) && !empty($chartData['expense']) ? json_encode($chartData['expense']) : '') }} 
                                class="mt-6 -mb-6">
                            </canvas>
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
