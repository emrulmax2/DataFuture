@extends('pages.attendance-integrity.shell')

@section('aim_page', 'records')
@section('aim_eyebrow', 'Dashboard · Records')

@section('aim_sub')
    The attendance records behind a dashboard figure. Each record keeps only its first and last editor; earlier changes appear in Attendance Amendments from the day the history was switched on.
@endsection

@section('aim_actions')
    <a href="{{ route('attendance.integrity.dashboard', $query) }}" class="aim-btn"><i data-lucide="arrow-left"></i> Dashboard</a>
    @include('pages.attendance-integrity.partials.export', ['report' => 'records', 'extra' => ['metric' => $metric]])
@endsection

@section('aim_content')
    <div class="aim-panel">
        <div class="aim-panel__head">
            <div class="aim-seg">
                @foreach($metrics as $key => $label)
                    <a href="{{ route('attendance.integrity.records', ['metric' => $key] + $query) }}" class="aim-btn aim-btn--sm {{ $key === $metric ? 'is-on' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="aim-count" id="aimCount"></div>
        </div>
        <div id="aimRecordsTable" data-url="{{ route('attendance.integrity.records.data', ['metric' => $metric] + $query) }}"></div>
    </div>
@endsection
