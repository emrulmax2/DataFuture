@extends('pages.attendance-integrity.shell')

@section('aim_page', 'students')
@section('aim_eyebrow', 'Analysis')

@section('aim_sub')
    Every student with attendance under these filters, highest score first. There is no card swipe or QR check-in; a missing record is never treated as proof of absence.
@endsection

@section('aim_actions')
    @include('pages.attendance-integrity.partials.export', ['report' => 'students'])
@endsection

@section('aim_content')
    <div class="aim-panel">
        <div class="aim-panel__head">
            <input type="search" class="aim-input" style="max-width: 320px;" id="aimTableSearch" placeholder="Search registration number or name" aria-label="Search students">
            <div class="aim-count" id="aimCount">Loading…</div>
        </div>
        <div id="aimStudentsTable" data-url="{{ route('attendance.integrity.students.data', $query) }}" data-show="{{ route('attendance.integrity.students.show', ['student' => '__ID__'] + $query) }}"></div>
    </div>
@endsection
