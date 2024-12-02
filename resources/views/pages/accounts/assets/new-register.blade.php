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
                        <strong><u>New Assets Register</u></strong>
                    </h2>
                </div>
                <div class="ml-auto flex justify-end">
                    <a href="{{ route('accounts.assets.register') }}" class="btn btn-primary text-white w-auto"><i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>Back to List</a>
                </div>
            </div>
            <div class="intro-y box mt-5 p-5">
                <div class="overflow-x-auto">
                    <table class="table table-bordered" id="newAssetsRegTable">
                        <thead>
                            <tr>
                                <th>Purchase Date</th>
                                <th>Price</th>
                                <th>Supplier</th>
                                <th>Description / Assets Name</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Serial No.</th>
                                <th>Barcode No.</th>
                                <th>Life Span</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($openedAssetList->count() > 0)
                                @foreach($openedAssetList as $list)
                                    <tr class="assets_row assets_row_{{ $list->id }}" id="assets_row_{{ $list->id }}" data-id="{{ $list->id }}">
                                        <td class="whitespace-nowrap text-xs">
                                            <div class="font-medium text-success mb-1">
                                                {{ isset($list->trans->transaction_date_2) && !empty($list->trans->transaction_date_2) ? date('jS M, Y', strtotime($list->trans->transaction_date_2)) : ''}}
                                            </div>
                                            <div class="text-slate-500 flex justify-start items-center">
                                                @if(isset($list->trans->transaction_doc_name) && !empty($list->trans->transaction_doc_name))
                                                    <a data-id="{{ $list->acc_transaction_id }}" href="javascript:void(0);" target="_blank" class="downloadTransDoc text-success mr-2" style="position: relative; top: -1px;"><i data-lucide="hard-drive-download" class="w-4 h-4"></i></a>
                                                @endif
                                                {{ isset($list->trans->transaction_code) && !empty($list->trans->transaction_code) ? $list->trans->transaction_code : ''}}
                                            </div>
                                        </td>
                                        <td class="text-xs font-medium text-slate-500">{{ isset($list->trans->transaction_amount) && $list->trans->transaction_amount > 0 ? '£'.number_format($list->trans->transaction_amount, 2) : '£0.00'}}</td>
                                        <td class="text-xs text-slate-500">{{ isset($list->trans->detail) && !empty($list->trans->detail) ? $list->trans->detail : ''}}</td>
                                        <td>
                                            <input type="text" name="assets[{{ $list->id }}]['description']" class="w-full form-control description"/>
                                            <div class="acc__input-error text-danger mt-1"></div>
                                        </td>
                                        <td>
                                            <select class="form-control acc_asset_type_id w-[140px]" name="assets[{{ $list->id }}]['acc_asset_type_id']">
                                                <option value="">Please Select</option>
                                                @if($types->count() > 0)
                                                    @foreach($types as $typ)
                                                        <option value="{{ $typ->id }}">{{ $typ->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error text-danger mt-1"></div>
                                        </td>
                                        <td>
                                            <input type="text" name="assets[{{ $list->id }}]['location']" class="w-full form-control location"/>
                                        </td>
                                        <td>
                                            <input type="text" name="assets[{{ $list->id }}]['serial']" class="w-full form-control serial"/>
                                        </td>
                                        <td>
                                            <input type="text" readonly name="assets[{{ $list->id }}]['barcode']" value="{{ random_int(10000000, 99999999) }}" class="w-full form-control barcode"/>
                                        </td>
                                        <td>
                                            <select class="form-control life w-[140px]" name="assets[{{ $list->id }}]['life']">
                                                <option value="">Please Select</option>
                                                @if($types->count() > 0)
                                                    @for($i = 1; $i <= 20; $i++)
                                                        <option value="{{ $i }}">{{ ($i == 1 ? $i.' Year' : $i.' Years') }}</option>
                                                    @endfor
                                                @endif
                                            </select>
                                        </td>
                                        <td class="text-right">
                                            <button data-id="{{ $list->id }}" type="button" disabled class="save_row btn btn-success rounded-full h-[35px] w-[35px] text-white px-0 py-0 relative">
                                                <i data-lucide="save" class="w-4 h-4 iconSave"></i>
                                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                    stroke="white" class="w-4 h-4 iconLoading absolute l-0 r-0 t-0 b-0 m-auto">
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
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
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
                        <button type="button" data-action="NONE" data-redirect="NONE" class="btn btn-primary successCloser w-24">Ok</button>
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
    @vite('resources/js/accounts-assets-register-new.js')
@endsection
