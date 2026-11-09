{{--
    Attendance Integrity: the shell every screen extends (inside the
    layout/attendance-integrity console). Page header, notices and the filter
    toolbar. Pages fill: aim_page (JS page key), aim_sub (header text),
    aim_actions (header buttons), aim_extra_filters, aim_content.
--}}
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="aim" id="aimRoot" data-page="@yield('aim_page')" data-query='@json($query ?? [])' data-can='@json($can ?? [])'>
        <div class="aim-page-head">
            <div>
                <div class="aim-eyebrow">@yield('aim_eyebrow', 'Attendance Integrity')</div>
                <h1>{{ $pageTitle }}</h1>
                <p>@yield('aim_sub', 'Findings are patterns that need an explanation, not proof of wrongdoing. Times are UK time.')</p>
            </div>
            <div class="aim-page-actions aim-no-print">@yield('aim_actions')</div>
        </div>

        @if(session('aim_success'))
            <div class="aim-callout aim-callout--ok" role="status"><i data-lucide="check-circle-2"></i><div>{{ session('aim_success') }}</div></div>
        @endif
        @if(session('aim_error'))
            <div class="aim-callout aim-callout--error" role="alert"><i data-lucide="alert-triangle"></i><div>{{ session('aim_error') }}</div></div>
        @endif
        @if($errors->any())
            <div class="aim-callout aim-callout--error" role="alert"><i data-lucide="alert-triangle"></i><div>{{ $errors->first() }}</div></div>
        @endif

        @if($showFilters)
            @include('pages.attendance-integrity.partials.filters')
        @endif

        @yield('aim_content')
    </div>
@endsection

@section('script')
    @vite('resources/js/attendance-integrity.js')
@endsection
