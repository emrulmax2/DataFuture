@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Conversion log (admission portal shell): the runs of the student
        conversion batch for this applicant (student_conversion_logs, grouped
        per batch by AdmissionController::conversionBatches). The latest run is
        the panel — summary chips + the jobs table — and earlier runs sit in an
        accordion. Search / status filter work on the rendered rows
        (admission-conversion-log.js), "View error" opens the stored exception,
        Print / Export CSV / XLSX cover the filtered rows, and while the latest
        run still has queued / processing jobs the rows refresh from
        admission.conversion.log.list every few seconds.
    --}}
    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    @php
        $adpStatusId = (int) ($applicant->status_id ?? 0);
        $adpPriv = auth()->user()->priv();
        $adpNext = \App\Support\AdmissionStatusFlow::next($applicant, $allStatuses, $adpPriv);
        $adpStatusHelp = \App\Support\AdmissionStatusFlow::HELP;
        $adpTab = 'log';

        $adpPaths = [
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'print' => '<path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/>',
            'export' => '<path d="M12 3v12"/><path d="m7 8 5-5 5 5"/><path d="M5 21h14"/>',
            'copy' => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
            'refresh' => '<path d="M21 12a9 9 0 1 1-2.6-6.4"/><path d="M21 3v6h-6"/>',
        ];
        $adpIcon = fn ($name, $size = 16) => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$adpPaths[$name].'</svg>';
        $adpCaret = '<svg class="adp-acc-caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
        $adpClose = '<button type="button" class="adp-btn adp-btn--ghost adp-btn--sm adp-modal__close" data-tw-dismiss="modal" aria-label="Close">✕</button>';

        // Job statuses (student_conversion_logs.status) and the run states derived from them.
        $adpLogPill = ['queued' => ['Queued', 'muted'], 'processing' => ['Processing', 'amber'], 'completed' => ['Completed', 'green'], 'failed' => ['Failed', 'red'], 'cancelled' => ['Cancelled', 'muted']];
        $adpStatePill = ['running' => ['Running', 'amber'], 'failed' => ['Stopped', 'red'], 'completed' => ['Completed', 'green'], 'cancelled' => ['Cancelled', 'muted']];
        $adpLong = function ($value) {
            if (empty($value)) return '—';
            try { return \Carbon\Carbon::parse($value)->format('D jS M, Y · H:i'); } catch (\Throwable $e) { return (string) $value; }
        };
        $adpStamp = function ($value) {
            if (empty($value)) return '—';
            try { return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s'); } catch (\Throwable $e) { return (string) $value; }
        };
        $adpDuration = function ($from, $to) {
            if (empty($from) || empty($to)) return '—';
            $s = \Carbon\Carbon::parse($from)->diffInSeconds(\Carbon\Carbon::parse($to));
            return $s < 60 ? $s.' s' : intdiv($s, 60).' min '.($s % 60).' s';
        };
        $adpJobCount = count(\App\Models\StudentConversionLog::JOB_SEQUENCE);
        $latest = $batches[0] ?? null;
        $failedJob = $latest ? $latest['rows']->firstWhere('status', 'failed') : null;
    @endphp

    @include('pages.students.admission.partials.portal-record')

    <section class="adp-panel adp-convlog" data-adp-convlog data-applicant="{{ $applicant->id }}" data-list-url="{{ route('admission.conversion.log.list') }}">
        @if(!$latest)
            <h3 class="adp-h3">Conversion log</h3>
            <div class="adp-empty">
                <b>Not converted yet.</b>
                <span>When the status is set to Offer Accepted, {{ $adpJobCount }} queued jobs copy the applicant into the student tables. Their progress and result show here.</span>
            </div>
        @else
            <div class="adp-panel__head adp-convlog__head">
                <h3 class="adp-h3">Conversion log</h3>
                <span class="adp-convlog__meta">Batch <span class="adp-mono" title="{{ $latest['id'] }}">{{ $latest['ref'] }}</span> · {{ $adpLong($latest['started_at']) }}@if($latest['student_id']) · Student ID <a class="adp-mono" href="{{ route('student.show', $latest['student_id']) }}"><b>{{ $latest['student_id'] }}</b></a>@endif
                    @if($latest['run_by']) · run by {{ $latest['run_by'] }}@endif
                </span>
            </div>

            <div class="adp-convlog__sum" data-adp-sum-wrap>
                <span class="adp-pill adp-pill--{{ $adpStatePill[$latest['state']][1] }}" data-adp-sum="state"><i></i>{{ $adpStatePill[$latest['state']][0] }}</span>
                <span class="adp-chip" data-adp-sum="done">{{ $latest['counts']['completed'] }} of {{ $latest['total'] }} jobs done</span>
                <span class="adp-chip adp-chip--red" data-adp-sum="failed" {{ $latest['counts']['failed'] ? '' : 'hidden' }}>{{ $latest['counts']['failed'] }} failed</span>
                <span class="adp-chip" data-adp-sum="cancelled" {{ $latest['counts']['cancelled'] ? '' : 'hidden' }}>{{ $latest['counts']['cancelled'] }} cancelled</span>
                <span class="adp-chip adp-chip--amber" data-adp-sum="pending" {{ $latest['pending'] ? '' : 'hidden' }}>{!! $adpIcon('refresh', 12) !!}<span data-adp-sum="pending-text">{{ $latest['pending'] }} to go · refreshing</span></span>
                <span class="adp-convlog__time">{{ $adpStamp($latest['started_at']) }} → <span data-adp-sum="finished">{{ $adpStamp($latest['finished_at']) }}</span> · <span data-adp-sum="duration">{{ $adpDuration($latest['started_at'], $latest['finished_at']) }}</span></span>
            </div>

            <div class="adp-notice adp-notice--bad" data-adp-stopped {{ $latest['state'] === 'failed' ? '' : 'hidden' }}>
                <b>Conversion stopped</b>
                <div>"<span data-adp-stopped-job>{{ $failedJob->job_name ?? '' }}</span>" failed, so the jobs after it were cancelled and no student record was created. Fix the cause (the error is under View error), then set the status to Offer Accepted again to re-run the batch.</div>
            </div>

            <div class="adp-tabletools adp-tabletools--convlog">
                <form class="adp-tabletools__filters" data-adp-filter>
                    <input id="query-CL" name="query" type="text" class="adp-ctl" placeholder="Search jobs…" autocomplete="off">
                    <select id="status-CL" name="status" class="adp-ctl adp-select" aria-label="Status">
                        <option value="" selected>All statuses</option>
                        <option value="queued">Queued</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button id="tabulator-html-filter-go-CL" type="submit" class="adp-btn adp-btn--primary adp-btn--sm">{!! $adpIcon('search', 13) !!}Go</button>
                    <button id="tabulator-html-filter-reset-CL" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">Reset</button>
                    <span class="adp-hint" data-adp-filter-count></span>
                </form>
                <div class="adp-tabletools__actions">
                    <button type="button" id="tabulator-print-CL" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('print', 13) !!}Print</button>
                    <button type="button" id="tabulator-export-csv-CL" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('export', 13) !!}Export CSV</button>
                    <button type="button" id="tabulator-export-xlsx-CL" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('export', 13) !!}Export XLSX</button>
                </div>
            </div>

            @include('pages.students.admission.partials.portal-conversion-table', ['batch' => $latest, 'compact' => false])

            @if(count($batches) > 1)
                <details class="adp-accordion adp-accordion--earlier" data-adp-earlier>
                    <summary><span>Earlier runs <span class="adp-cnt adp-cnt--muted">{{ count($batches) - 1 }}</span></span>{!! $adpCaret !!}</summary>
                    <div class="adp-acc-body adp-convlog__earlier">
                        @foreach(array_slice($batches, 1) as $batch)
                            <div class="adp-convlog__run">
                                <div class="adp-convlog__runhead">
                                    <span class="adp-pill adp-pill--{{ $adpStatePill[$batch['state']][1] }}"><i></i>{{ $adpStatePill[$batch['state']][0] }}</span>
                                    <span class="adp-convlog__meta">Batch <span class="adp-mono" title="{{ $batch['id'] }}">{{ $batch['ref'] }}</span> · {{ $adpLong($batch['started_at']) }} · {{ $batch['counts']['completed'] }} of {{ $batch['total'] }} done{{ $batch['counts']['failed'] ? ' · '.$batch['counts']['failed'].' failed' : '' }}{{ $batch['student_id'] ? ' · Student ID '.$batch['student_id'] : '' }}{{ $batch['run_by'] ? ' · run by '.$batch['run_by'] : '' }}</span>
                                </div>
                                @include('pages.students.admission.partials.portal-conversion-table', ['batch' => $batch, 'compact' => true])
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        @endif
    </section>

    {{-- ============================ Error details ============================ --}}
    <div id="viewConversionErrorModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h3>Error details <span class="adp-convlog__errjob" id="conversionErrorTitle"></span></h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <p class="adp-copy" data-adp-error-meta></p>
                    <pre id="conversionErrorContent" class="adp-convlog__exc"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="adp-btn adp-btn--ghost" data-adp-error-copy>{!! $adpIcon('copy', 13) !!}Copy</button>
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--primary">Close</button>
                </div>
            </div>
        </div>
    </div>

    @include('pages.students.admission.partials.portal-record-modals')
    @include('pages.students.admission.partials.portal-dialogs')
@endsection

@section('script')
    @vite('resources/js/admission-conversion-log.js')
    @vite('resources/js/admission-vue.js')
    {{-- The record header's Change status dialog (admission-show.js guards its other blocks). --}}
    @vite('resources/js/admission-show.js')
@endsection
