{{--
    One student-conversion batch as the design's jobs table (ConversionLog in the
    prototype: # · Job · Status, plus the message / attempts / timing columns the
    old Tabulator page showed). Needs $batch (AdmissionController::conversionBatches),
    $compact, $adpLogPill and $adpStamp from the page. The stored exceptions ride
    along in <template data-adp-exception> for the "View error" dialog.
--}}
<div class="adp-dtable">
    <table class="adp-plain-table adp-convlog__table {{ $compact ? 'adp-convlog__table--compact' : '' }}" data-adp-convlog-table data-batch="{{ $batch['id'] }}">
        <thead>
            <tr>
                <th>#</th>
                <th>Job</th>
                <th>Status</th>
                <th>Message</th>
                <th class="adp-num">Attempts</th>
                <th>Started</th>
                <th>Finished</th>
            </tr>
        </thead>
        <tbody>
            @foreach($batch['rows'] as $i => $row)
                @php
                    $pill = $adpLogPill[$row->status] ?? [ucfirst($row->status), 'muted'];
                    $hasErr = !empty($row->exception);
                @endphp
                <tr data-adp-row data-id="{{ $row->id }}" data-status="{{ $row->status }}" data-search="{{ \Illuminate\Support\Str::lower($row->job_name.' '.class_basename($row->job_class).' '.$row->message.' '.$row->status) }}">
                    <td class="adp-mono adp-convlog__n">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="adp-convlog__job"><b>{{ $row->job_name }}</b><small class="adp-mono">{{ class_basename($row->job_class) }}</small></td>
                    <td><span class="adp-pill adp-pill--{{ $pill[1] }}" data-adp-cell="status"><i></i>{{ $pill[0] }}</span></td>
                    <td class="adp-convlog__msg"><span data-adp-cell="message" title="{{ $row->message }}">{{ $row->message ?: '—' }}</span>@if($hasErr) <button type="button" class="adp-btn-link adp-btn-link--danger" data-adp-error="{{ $row->id }}" data-job="{{ $row->job_name }}">View error</button>@endif</td>
                    <td class="adp-mono adp-num" data-adp-cell="attempts">{{ (int) $row->attempts }}</td>
                    <td class="adp-mono" data-adp-cell="started">{{ $adpStamp($row->started_at) }}</td>
                    <td class="adp-mono" data-adp-cell="finished">{{ $adpStamp($row->finished_at) }}</td>
                </tr>
            @endforeach
            <tr data-adp-none hidden><td colspan="7" class="adp-convlog__none">No jobs match the filter.</td></tr>
        </tbody>
    </table>
    @foreach($batch['rows'] as $row)
        @if(!empty($row->exception))<template data-adp-exception="{{ $row->id }}">{{ $row->exception }}</template>@endif
    @endforeach
</div>
