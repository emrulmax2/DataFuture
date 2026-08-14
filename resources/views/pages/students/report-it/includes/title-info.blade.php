@php
    $ritStatusPill = [
        'Pending' => 'rit-statuspill--pending',
        'In Progress' => 'rit-statuspill--progress',
        'Resolved' => 'rit-statuspill--resolved',
        'Rejected' => 'rit-statuspill--rejected',
    ][$reportItAll->status] ?? '';
@endphp

<div class="rit-refhead">
    <div class="rit-refhead__ref">
        <span class="rit-refhead__label">Ref no</span>
        <span class="rit-refhead__value">{{ $reportItAll->report_number }}</span>
    </div>
    <div class="rit-refhead__actions">
        <a href="{{ route('report.it.all') }}" class="rit-btn rit-btn--ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 6l-6 6 6 6"></path></svg>
            Back
        </a>
        {{-- Read-only: closing and re-opening are the two buttons in the
             Report logs header below. --}}
        <span class="rit-statuspill {{ $ritStatusPill }}">{{ $reportItAll->status }}</span>
    </div>
</div>
