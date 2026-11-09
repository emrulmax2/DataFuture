@extends('pages.attendance-integrity.shell')

@section('aim_page', 'reports')
@section('aim_eyebrow', 'Casework')

@section('aim_sub')
    Every report uses the filters above. Each file opens with the filters, when and by whom it was made and short notes, and marks confirmed facts, anomalies and missing evidence. Every export is logged.
@endsection

@section('aim_content')
    @php
        $icons = ['term_summary' => 'layout-dashboard', 'staff' => 'users', 'students' => 'graduation-cap', 'retrospective' => 'alarm-clock', 'amendments' => 'history',
            'bulk' => 'layers', 'patterns' => 'network', 'timetable' => 'calendar-x', 'deleted' => 'trash-2', 'cases' => 'folder-search'];
    @endphp
    <div class="aim-list">
        @foreach($reports as $key => [$reportTitle, $about])
            <div class="aim-item">
                <span class="aim-item__icon"><i data-lucide="{{ $icons[$key] ?? 'file-text' }}"></i></span>
                <div class="aim-item__text">
                    <h4>{{ $loop->iteration }}. {{ $reportTitle }}</h4>
                    <p>{{ $about }}</p>
                </div>
                @include('pages.attendance-integrity.partials.export', ['report' => $key])
            </div>
        @endforeach
    </div>
    <p class="aim-small" style="margin-top: 12px;">PDF files hold up to 1,500 rows; use Excel or CSV for the full list.</p>
@endsection
