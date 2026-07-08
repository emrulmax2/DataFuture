@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <!-- BEGIN: Page Header -->
    <div class="intro-y flex flex-wrap items-center justify-between gap-3 mt-8 mb-2">
        <div>
            <h2 class="font-display text-2xl font-semibold text-slate-800 dark:text-white leading-tight tracking-tight">Visa Expiry Report</h2>
            <p class="text-sm text-slate-400 mt-1">Work-permit &amp; visa renewals &middot; London Churchill College</p>
        </div>
        <a href="{{ route('hr.portal') }}" class="flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80 px-3 py-2 rounded-lg hover:bg-primary/10 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Portal
        </a>
    </div>
    <!-- END: Page Header -->

    <!-- BEGIN: Toolbar -->
    <div class="intro-y box mt-5 flex flex-col sm:flex-row sm:items-center gap-4 px-5 py-4">
        <div class="mr-auto">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Report</div>
            <div class="text-sm font-semibold text-slate-600 dark:text-slate-300 mt-0.5">Visa &amp; work-permit expiry &middot; {{ $records->count() }} {{ \Illuminate\Support\Str::plural('record', $records->count()) }}</div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button id="list-print" type="button" class="btn btn-outline-secondary h-[42px] text-sm">
                <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i> Print
            </button>
            <div class="dropdown">
                <button class="dropdown-toggle btn btn-outline-secondary h-[42px] text-sm" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-lucide="download" class="w-4 h-4 mr-1.5"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-1.5"></i>
                </button>
                <div class="dropdown-menu w-40">
                    <ul class="dropdown-content">
                        <li><a id="list-export-csv" href="javascript:;" class="dropdown-item"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV</a></li>
                        <li><a id="list-export-xlsx" href="javascript:;" class="dropdown-item"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Toolbar -->

    <!-- BEGIN: Expiry Cards -->
    <div id="visaExpiryCards" class="intro-y grid grid-cols-1 lg:grid-cols-2 gap-3 mt-5">
        @forelse($records as $rec)
            @php
                $expiryDate = date('Y-m-d', strtotime($rec->workpermit_expire));
                $isExpired = date('Y-m-d') > $expiryDate;
                $diffDays = \Carbon\Carbon::parse($expiryDate)->diffInDays(\Carbon\Carbon::now());
                $designation = (isset($rec->employee->employment->employeeJobTitle->name) ? $rec->employee->employment->employeeJobTitle->name : '');
            @endphp
            <a href="{{ route('profile.employee.view', $rec->employee_id) }}" class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-darkmode-600 rounded-xl border border-slate-100 dark:border-darkmode-400 shadow-sm hover:border-primary/30 transition-colors">
                <div class="flex-none w-11 h-11 overflow-hidden rounded-full image-fit ring-2 ring-slate-100 dark:ring-darkmode-400">
                    <img src="{{ $rec->employee->photo_url }}" alt="{{ $rec->employee->first_name.' '.$rec->employee->last_name }}">
                </div>
                <div class="min-w-0 flex-1">
                    <div class="font-semibold text-sm text-slate-700 dark:text-slate-200 uppercase truncate">{{ $rec->employee->first_name.' '.$rec->employee->last_name }}</div>
                    <div class="text-xs text-slate-400 mt-0.5 truncate">{{ $designation ?: 'Unknown' }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">No. {{ $rec->workpermit_number ?: '—' }} &middot; Expires {{ date('jS M, Y', strtotime($rec->workpermit_expire)) }}</div>
                </div>
                <span class="lcc-badge {{ $isExpired ? 'lcc-badge--critical' : 'lcc-badge--warning' }} flex-none">{{ $diffDays }} {{ $diffDays == 1 ? 'Day' : 'Days' }}</span>
            </a>
        @empty
            <div class="lg:col-span-2 flex items-center px-4 py-6 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm">
                <i data-lucide="alert-triangle" class="w-4 h-4 mr-2 flex-none"></i> No visa / work-permit expiries within the next 60 days.
            </div>
        @endforelse
    </div>
    <!-- END: Expiry Cards -->


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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary successCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

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
    <script>window.__expiryExport = @json($exportRows); window.__expiryName = 'visa-expiry'; window.__expirySheet = 'Visa Expiry';</script>
    @vite('resources/js/hr-visa-expiry.js')
@endsection