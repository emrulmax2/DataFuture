@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        {{--<div class="col-span-12 xl:col-span-3 2xl:col-span-3 relative z-10">
            <!-- BEGIN: Profile Info -->
            @include('pages.accounts.sidebar')
            <!-- END: Profile Info -->
        </div>--}}
        <div class="col-span-12 xl:col-span-12 2xl:col-span-12 z-10 pt-6">
            <div class="flex items-center">
                <div class="mr-auto inline-flex justify-start items-center">
                    <h2 class="font-medium text-lg mr-auto">
                        <strong><u>Assets Register</u></strong> 
                        @if(auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && in_array(auth()->user()->priv()['access_account_type'], [1, 3]))
                        {!! (isset($openedAssets) && $openedAssets > 0 ? '<a href="'.route('accounts.assets.register.new').'" class="text-primary underline">('.$openedAssets.')</a>' : '') !!}
                        @endif
                    </h2>
                </div>
                <div class="ml-auto flex justify-end">
                    <a href="{{ route('accounts') }}" class="btn btn-primary text-white w-auto"><i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>Back to Acconts</a>
                </div>
            </div>
            <div class="intro-y box mt-5">
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Date</label>
                                <input id="query_date" name="query_date" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Type</label>
                                <select id="acc_asset_type_id" name="acc_asset_type_id" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="">Please Select</option>
                                    @if($types->count() > 0)
                                        @foreach($types as $typ)
                                            <option value="{{ $typ->id }}">{{ $typ->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="2">Active</option>
                                    <option value="0">In Active</option>
                                    <option value="3">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <div class="dropdown w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export or Print <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-xl" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XL <i data-loading-icon="oval" class="w-4 h-4 ml-2 loadingIcon hidden" style="display: none;"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-print-pdf" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print PDF <i data-loading-icon="oval" class="w-4 h-4 ml-2 loadingIcon hidden" style="display: none;;"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="assetsRegisterListTable" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Edit Modal -->
    <div id="editAssetRegistryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editAssetRegistryForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Assets</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="description" class="form-label">Description / Assets Name <span class="text-danger">*</span></label>
                            <textarea class="form-control description w-full" rows="2" name="description"></textarea>
                            <div class="acc__input-error error-description text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 grid grid-cols-12 gap-x-5 gap-y-2">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="acc_asset_type_id" class="form-label">Assets Type <span class="text-danger">*</span></label>
                                <select class="form-control acc_asset_type_id w-full" name="acc_asset_type_id">
                                    <option value="">Please Select</option>
                                    @if($types->count() > 0)
                                        @foreach($types as $typ)
                                            <option value="{{ $typ->id }}">{{ $typ->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-acc_asset_type_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control location w-full" name="location"/>
                                <div class="acc__input-error error-location text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="serial" class="form-label">Serial</label>
                                <input type="text" class="form-control serial w-full" name="serial"/>
                                <div class="acc__input-error error-serial text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="barcode" class="form-label">Barcode</label>
                                <input readonly type="text" class="form-control barcode w-full" name="barcode"/>
                                <div class="acc__input-error error-barcode text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="life" class="form-label">Life Span</label>
                                <select class="form-control life w-full" name="life">
                                    <option value="">Please Select</option>
                                    @if($types->count() > 0)
                                        @for($i = 1; $i <= 20; $i++)
                                            <option value="{{ $i }}">{{ ($i == 1 ? $i.' Year' : $i.' Years') }}</option>
                                        @endfor
                                    @endif
                                </select>
                                <div class="acc__input-error error-life text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateAssetsBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="active" value="0"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->
    
    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary successCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
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
    @vite('resources/js/accounts.js')
    @vite('resources/js/accounts-assets-register.js')
@endsection
