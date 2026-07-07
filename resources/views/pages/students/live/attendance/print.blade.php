@extends('../layout/print')

@section('subhead')
    <title>{{ $title }} - Print</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page { margin: 0.5in 0.3in; }

        html, body { background:#fff; }
        body {
            font-family: 'Public Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            color:#152528;
            font-size:10pt;
            line-height:1.5;
            padding:0;
            margin:0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        * { box-sizing:border-box; -webkit-print-color-adjust:exact; print-color-adjust:exact; }

        .atn-wrap { max-width:920px; margin:0 auto; padding:24px 20px 32px; }

        /* Running header band */
        .atn-runhead {
            display:flex; align-items:center; gap:10px;
            font-size:8.5pt; color:#7C8D91;
            padding-bottom:6px; border-bottom:1px solid #E5EBEC; margin-bottom:14px;
            flex-wrap:wrap;
        }
        .atn-runhead .brand { font-weight:700; color:#0F252D; }
        .atn-runhead .who { margin-left:auto; }

        /* Title block */
        .atn-title { display:flex; align-items:flex-start; gap:20px; margin-top:2px; }
        .atn-title .logo__image { width:150px; height:auto; }
        .atn-title .title-right { margin-left:auto; text-align:right; }
        .atn-title h1 { margin:0; font-size:16pt; font-weight:700; color:#0F252D; line-height:1.2; }
        .atn-title .gen { font-size:9pt; color:#7C8D91; margin-top:3px; }
        .atn-rule { height:3px; background:linear-gradient(90deg,#C9992E,#A31621 55%,#0B6B66); border-radius:2px; margin:14px 0 18px; }

        /* Meta grid */
        .atn-meta {
            display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px 24px;
            border:1px solid #E5EBEC; border-radius:8px; padding:14px 18px; break-inside:avoid;
        }
        .atn-meta .lbl { font-size:7.5pt; letter-spacing:0.06em; text-transform:uppercase; color:#7C8D91; font-weight:700; }
        .atn-meta .val { font-weight:600; }
        .atn-meta .span2 { grid-column:span 2; }

        /* Overall attendance */
        .atn-overall {
            display:flex; align-items:center; gap:18px; flex-wrap:wrap;
            background:#0F252D; border-radius:8px; padding:12px 18px; margin-top:14px; break-inside:avoid;
        }
        .atn-overall .lbl { font-size:8pt; letter-spacing:0.12em; text-transform:uppercase; color:#8FA6AC; font-weight:700; }
        .atn-overall .pct { font-size:16pt; font-weight:700; color:#E5B94E; }
        .atn-overall .dist { margin-left:auto; font-size:8.5pt; color:#B7C8CC; }

        /* Term cards — allow terms to break across pages where needed */
        .term-block { margin-top:16px; break-inside:auto; }
        .term-head { display:flex; align-items:center; gap:12px; padding:8px 14px; border-radius:6px 6px 0 0; flex-wrap:wrap; break-inside:avoid; break-after:avoid; }
        .atn-colhead { break-inside:avoid; break-after:avoid; }
        .atn-mod-row, .tabledataset { break-inside:avoid; }
        .term-head .name { font-size:11pt; font-weight:700; color:#0F252D; }
        .term-head .pct { font-size:10pt; font-weight:700; }
        .term-head .stats { font-size:8pt; color:#5B6E72; }
        .term-head .range { margin-left:auto; font-size:8pt; color:#7C8D91; }
        .term-body { border:1px solid #E5EBEC; border-top:none; font-size:8.5pt; }
        .atn-grid { display:grid; grid-template-columns:2.4fr 1fr 1.1fr 1.2fr 1.4fr 0.7fr; gap:10px; }
        .atn-colhead {
            padding:6px 12px; background:#FAFBFB; border-bottom:1px solid #E5EBEC;
            font-size:7pt; letter-spacing:0.07em; text-transform:uppercase; color:#7C8D91; font-weight:700;
        }
        .atn-mod-row { padding:6px 12px; border-bottom:1px solid #F2F5F5; align-items:start; }
        .atn-mod-title { font-weight:600; }
        .atn-mod-code { font-size:7.5pt; color:#9AA7AA; font-family:ui-monospace,Menlo,monospace; }
        .atn-mod-avg { text-align:right; font-weight:700; white-space:nowrap; }
        .tablepoint-toggle { cursor:pointer; }
        .plusminus { display:inline-block; width:12px; height:12px; vertical-align:-1px; margin-right:3px; color:#7C8D91; }
        .hidden { display:none; }

        /* Detail table */
        .tabledataset { padding:0 12px 10px; border-bottom:1px solid #F2F5F5; }
        table.atn-detail { width:100%; border-collapse:collapse; font-size:8pt; margin-top:2px; }
        table.atn-detail th, table.atn-detail td { border:1px solid #E5EBEC; padding:4px 7px; text-align:left; }
        table.atn-detail thead th {
            background:#FAFBFB; color:#5B6E72; text-transform:uppercase;
            font-size:6.5pt; letter-spacing:0.05em; font-weight:700;
        }
        table.atn-detail tbody tr:nth-child(even) td { background:#FAFBFB; }
        table.atn-detail tfoot th { background:#FAFBFB; font-weight:700; }

        /* Footer legend */
        .atn-footer {
            margin-top:18px; padding-top:10px; border-top:1px solid #E5EBEC;
            font-size:8pt; color:#9AA7AA; display:flex; gap:12px; flex-wrap:wrap;
        }
        .atn-footer .site { margin-left:auto; }

        /* Toolbar */
        .atn-toolbar { display:flex; justify-content:center; gap:10px; margin:16px 0; }
        .atn-toolbar button {
            font-family:inherit; font-size:9pt; font-weight:600; cursor:pointer;
            border-radius:6px; padding:8px 16px; border:1px solid #E5EBEC; background:#fff; color:#152528;
        }
        .atn-toolbar button.primary { background:#0F252D; color:#fff; border-color:#0F252D; }

        @media print {
            .no-print { display:none !important; }
            .atn-wrap { padding:0; max-width:none; }
        }
    </style>
@endsection

@section('subcontent')
    <div class="atn-toolbar no-print">
        <button class="primary" onclick="window.print()">Print</button>
        <button onclick="window.location.href='{{ route('student.attendance',$student->id) }}'">Back to Attendances</button>
    </div>

    <div class="atn-wrap">

        {{-- Running header --}}
        <div class="atn-runhead">
            <span class="brand">London Churchill College</span>
            <span>· Student Attendance Details</span>
            <span class="who">{{ $student->full_name ?? ($student->name ?? 'Student') }} · {{ $student->registration_no }}</span>
        </div>

        {{-- Title block --}}
        <div class="atn-title">
            <img alt="London Churchill College" class="logo__image" src="{{ asset('build/assets/images/L1_logo.svg') }}">
            <div class="title-right">
                <h1>{{ $title }}</h1>
                <div class="gen">Generated on {{ date('jS F, Y') }}</div>
            </div>
        </div>
        <div class="atn-rule"></div>

        {{-- Student meta --}}
        <div class="atn-meta">
            <div>
                <div class="lbl">Student</div>
                <div class="val">{{ $student->full_name ?? ($student->name ?? 'Student') }}</div>
            </div>
            <div>
                <div class="lbl">Registration No</div>
                <div class="val">{{ $student->registration_no }}</div>
            </div>
            <div>
                <div class="lbl">Date of Birth</div>
                <div class="val">{{ !empty($student->date_of_birth) ? date('jS F, Y', strtotime($student->date_of_birth)) : 'N/A' }}</div>
            </div>
            <div class="span2">
                <div class="lbl">Course</div>
                <div class="val">{{ $student->crel->propose->creation->course->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="lbl">Semester</div>
                <div class="val">{{ $student->crel->semester->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="lbl">Address</div>
                <div class="val">
                    @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                        @php
                            $addr = [];
                            if(!empty($student->contact->termaddress->address_line_1)) $addr[] = $student->contact->termaddress->address_line_1;
                            if(!empty($student->contact->termaddress->address_line_2)) $addr[] = $student->contact->termaddress->address_line_2;
                            if(!empty($student->contact->termaddress->city)) $addr[] = $student->contact->termaddress->city;
                            if(!empty($student->contact->termaddress->state)) $addr[] = $student->contact->termaddress->state;
                            if(!empty($student->contact->termaddress->post_code)) $addr[] = $student->contact->termaddress->post_code;
                            if(!empty($student->contact->termaddress->country)) $addr[] = $student->contact->termaddress->country;
                        @endphp
                        {{ count($addr) ? implode(', ', $addr) : 'Not Set Yet!' }}
                    @else
                        <span style="color:#B3392E; font-weight:600;">Not Set Yet!</span>
                    @endif
                </div>
            </div>
            <div>
                <div class="lbl">Awarding Body</div>
                <div class="val">{{ $student->crel->creation->course->body->name ?? 'Unknown' }}{{ isset($student->crel->abody->reference) && !empty($student->crel->abody->reference) ? ' · '.$student->crel->abody->reference : '' }}</div>
            </div>
            <div>
                <div class="lbl">Date of Award</div>
                <div class="val">{{ isset($student->awarded->date_of_award) && !empty($student->awarded->date_of_award) ? $student->awarded->date_of_award : 'N/A' }}</div>
            </div>
        </div>

        {{-- Overall attendance --}}
        @if($term_id=="")
            <div class="atn-overall">
                <div class="lbl">Overall Attendance</div>
                <div class="pct">{{ $finalAverage }}%</div>
                <div class="dist">
                    @if(!empty($codeDistribution)){{ $codeDistributionString }} &nbsp;—&nbsp; @endif
                    Total {{ array_sum($totalClassFullSet) }} days of class
                </div>
            </div>
        @endif

        {{-- Terms --}}
        @foreach($dataSet as $termId => $dataStartPoint)
            @php $planId = 1; @endphp
            @if(isset($term_id) && $term_id>0)
                @if($term_id == $termId)
                    @include('pages.students.live.attendance.print-partial')
                    @break
                @endif
            @else
                @include('pages.students.live.attendance.print-partial')
            @endif
        @endforeach

        <div class="atn-footer">
            <span>Attendance codes: P Present · O Online Present · A Absent · L Late · E Excused · LE Left Early · M Medical · H Holiday</span>
            <span class="site">London Churchill College · lcc.ac.uk</span>
        </div>

    </div>
@endsection

@section('script')
    <script type="module">
        (function () {
            // Auto-trigger print when opening the print view in a new tab/window.
            window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 250); });

            $(".tablepoint-toggle").on('click', function(e) {
                e.preventDefault();
                var $t = $(this);
                var $icons = $t.find('.plusminus');
                if ($icons.length === 0) {
                    $icons = $t.find('[data-lucide]');
                }
                if ($icons.length >= 2) {
                    $icons.eq(0).toggleClass('hidden');
                    $icons.eq(1).toggleClass('hidden');
                } else if ($icons.length === 1) {
                    $icons.eq(0).toggleClass('hidden');
                }

                var $dataset = $t.parent().siblings('div.tabledataset');
                if ($dataset.length === 0) {
                    $dataset = $t.closest('.term-body').find('div.tabledataset').first();
                }
                if ($dataset.length) $dataset.slideToggle();
            });
        })();
    </script>
@endsection
