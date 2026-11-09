{{-- Export buttons for one report under the current filters. Params: report, extra (array, optional). --}}
@if($can['export'])
    <div class="aim-btn-group" role="group" aria-label="Export">
        @foreach(['xlsx' => 'Excel', 'csv' => 'CSV', 'pdf' => 'PDF'] as $format => $label)
            <a href="{{ route('attendance.integrity.reports.export', ['report' => $report] + ($extra ?? []) + $query + ['format' => $format]) }}" class="aim-btn aim-btn--sm">@if($loop->first)<i data-lucide="download"></i>@endif {{ $label }}</a>
        @endforeach
    </div>
@endif
