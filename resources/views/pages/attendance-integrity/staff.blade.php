@extends('pages.attendance-integrity.shell')

@section('aim_page', 'staff')
@section('aim_eyebrow', 'Analysis')

@section('aim_sub')
    Everyone who entered or changed attendance under these filters, highest score first. Counts come straight from the records (minimums, since each record keeps only its first and last editor). Scores rank what to look at first; they are not proof.
@endsection

@section('aim_actions')
    @include('pages.attendance-integrity.partials.export', ['report' => 'staff'])
@endsection

@section('aim_content')
    <div class="aim-panel">
        <div class="aim-panel__head">
            <input type="search" class="aim-input" style="max-width: 320px;" id="aimTableSearch" placeholder="Search name, job title or department" aria-label="Search staff">
            <div class="aim-count" id="aimCount">Loading…</div>
        </div>
        <div id="aimStaffTable" data-url="{{ route('attendance.integrity.staff.data', $query) }}" data-show="{{ route('attendance.integrity.staff.show', ['user' => '__ID__'] + $query) }}"></div>
    </div>
    <p class="aim-small" style="margin-top: 12px;">
        <strong>Changes per 100</strong>: records they changed per 100 records they entered or changed; the grey figure is the median of everyone listed.
        <strong>Not their class</strong>: records in classes where they are not the tutor, personal tutor or cover tutor and do not hold Edit Attendance (today's privileges).
    </p>
@endsection
