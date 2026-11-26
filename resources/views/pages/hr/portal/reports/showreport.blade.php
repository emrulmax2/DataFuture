@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter, as in the design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-employment-reports.css')
@endsection

@section('subcontent')
    {{-- Employment Reports (user's employment-reports design, 26 Nov 2026): one card per row of
         employment_reports, grouped by category, with a search box and category chips. The cards are
         drawn by hr-employment-reports.js from the JSON below. --}}
    <div class="erp" data-erp>
        <div class="erp-titlebar">
            <div>
                <h1 class="erp-title">{{ $subtitle }}</h1>
                <div class="erp-sub" data-erp-sub>{{ count($reports) }} {{ count($reports) === 1 ? 'report' : 'reports' }} for the HR team</div>
            </div>
            <a class="erp-btn" href="{{ route('hr.portal') }}">
                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                Back to HR Portal
            </a>
        </div>

        <div class="erp-bar">
            <label class="erp-big">
                <span class="erp-sr">Find a report</span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="9" cy="9" r="6"/><path d="M14 14l4 4"/></svg>
                <input type="search" data-erp-q placeholder="Find a report, such as holiday, phone or visa" autocomplete="off">
            </label>
            <div class="erp-cats" data-erp-cats role="group" aria-label="Report category"></div>
        </div>

        <div class="erp-app" data-erp-app aria-live="polite"></div>

        <script type="application/json" data-erp-data>{!! json_encode(['reports' => $reports, 'categories' => $categories], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>
    </div>
@endsection

@section('script')
    @vite('resources/js/hr-employment-reports.js')
@endsection
