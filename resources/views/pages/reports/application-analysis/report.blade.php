{{--
    Admission Report body: page 1 "Are we on target", page 2 "Who accepted".

    Shared by the screen (injected into #applicantAnalysisReptWrap, styled by
    the `.aar-*` rules in resources/css/admission-redesign.css) and the PDF
    (pdf.blade.php, styled there for DomPDF). DomPDF has no flexbox or grid,
    so every side-by-side layout is a table; the screen CSS turns those tables
    into grids and stacks them on narrow screens.

    $report  App\Services\Reports\ApplicationAnalysis::build()
    $medium  'screen' | 'pdf'
    $author, $date  for the header
--}}
@php
    $isPdf = ($medium === 'pdf');
    $t = $report['totals'];
    $signed = function (int $n) { return ($n > 0 ? '+'.$n : ($n < 0 ? '−'.abs($n) : '0')); };
    // A gap only means something against a target: without one it shows "–".
    $gapText = function (int $gap, int $target) use ($signed) { return ($target > 0 ? $signed($gap) : '–'); };
    $gapTone = function (int $gap, int $target) { return ($target < 1 ? 'is-none' : ($gap < 0 ? 'is-down' : 'is-up')); };
    $share = function (int $n, int $of) { return ($of > 0 ? round($n / $of * 100, 2) : 0); };

    $segments = array_values(array_filter([
        ['n' => $t['accepted'], 'color' => '#0e5a54', 'title' => 'Accepted', 'sub' => 'Offer accepted'],
        ['n' => $t['pending'], 'color' => '#e0a030', 'title' => 'Pending', 'sub' => (!empty($report['pending_parts']) ? implode(' · ', $report['pending_parts']) : 'Nothing waiting')],
        ['n' => $t['rejected'], 'color' => '#7b8490', 'title' => 'Rejected', 'sub' => 'By the college'],
        ['n' => $t['declined'], 'color' => '#c3c9d1', 'title' => 'Declined', 'sub' => 'By the applicant'],
        ($t['canceled'] > 0 ? ['n' => $t['canceled'], 'color' => '#e3e7ea', 'title' => 'Canceled', 'sub' => 'Application canceled'] : null),
    ]));
    $legendRows = array_chunk($segments, 2);

    $cohort = $report['cohort'];
    $gender = $cohort['gender'];
    $genderParts = array_values(array_filter([
        ['n' => $gender['male'], 'label' => 'Male', 'class' => 'aar-gender__seg--male'],
        ['n' => $gender['female'], 'label' => 'Female', 'class' => 'aar-gender__seg--female'],
        ['n' => $gender['other'], 'label' => 'Other', 'class' => 'aar-gender__seg--other'],
        ['n' => $gender['unknown'], 'label' => 'Not recorded', 'class' => 'aar-gender__seg--unknown'],
    ], function ($part) { return $part['n'] > 0; }));
    $entryUnknown = array_sum(array_column($cohort['entry'], 'unknown'));
    $entryUnknownIds = array_merge([], ...array_column($cohort['entry'], 'unknown_ids'));
@endphp

@if($isPdf)
    <div class="aar-runhead">
        <table class="aar-head">
            <tr>
                <td>
                    {{-- PDF only: the Site Settings dark logo takes the place of the two text lines. --}}
                    @if(!empty($logo))
                        <img class="aar-head__logo" src="{{ $logo }}" alt="London Churchill College">
                    @else
                        <div class="aar-eyebrow">London Churchill College</div>
                        <div class="aar-head__title">Admission Report</div>
                    @endif
                </td>
                <td class="aar-head__meta">
                    <div class="aar-head__intake">{{ $report['intake'] }}</div>
                    <div class="aar-head__by">{{ $author }} · {{ $date }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="aar-runfoot">
        <table class="aar-foot">
            <tr>
                <td>London Churchill College · Admission Report · <span class="aar-pagenum"></span></td>
                <td class="aar-foot__right">Confidential, internal use only</td>
            </tr>
        </table>
    </div>
@endif

<div class="aar">
    @if(!$report['has_data'])
        <div class="aar-sheet">
            <div class="aar-empty">
                <div class="aar-empty__title">Nothing to report yet</div>
                <div class="aar-empty__text">This intake has no course targets and no submitted applications.</div>
            </div>
        </div>
    @else

    {{-- ============================ Page 1 ============================ --}}
    <div class="aar-sheet aar-page1">
        @unless($isPdf)
            <table class="aar-head">
                <tr>
                    <td>
                        <div class="aar-eyebrow">London Churchill College</div>
                        <div class="aar-head__title">Admission Report</div>
                    </td>
                    <td class="aar-head__meta">
                        <div class="aar-head__intake">{{ $report['intake'] }}</div>
                        <div class="aar-head__by">{{ $author }} · {{ $date }}</div>
                    </td>
                </tr>
            </table>
        @endunless

        <table class="aar-split">
            <tr>
                <td class="aar-split__left">
                    <div class="aar-eyebrow">Accepted against target</div>
                    <table class="aar-hero">
                        <tr>
                            <td class="aar-hero__figure">
                                <span class="aar-hero__big">{{ $t['accepted'] }}</span>
                                <span class="aar-hero__of">{{ $t['target'] > 0 ? 'of '.$t['target'].' target' : 'accepted · no target set' }}</span>
                            </td>
                            @if($t['target'] > 0)
                                <td class="aar-hero__chipcell">
                                    <div class="aar-hero__chip {{ $t['gap'] < 0 ? 'is-down' : 'is-up' }}">
                                        <div class="aar-hero__gap">{{ $signed($t['gap']) }}</div>
                                        <div class="aar-hero__pct">{{ $t['of_target'] }}%</div>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    </table>
                    <div class="aar-summary">{{ $report['summary'] }}</div>

                    <div class="aar-h2 aar-h2--spaced">Where the {{ $t['applied'] }} applications stand</div>
                    @if($t['applied'] > 0)
                        <table class="aar-stack">
                            <tr>
                                @php $lastSeg = max(array_keys(array_filter($segments, function ($seg) { return $seg['n'] > 0; }))); @endphp
                                @foreach($segments as $i => $seg)
                                    @if($seg['n'] > 0)
                                        <td class="aar-stack__seg{{ $i === 0 ? ' is-first' : '' }}{{ $i === $lastSeg ? ' is-last' : '' }}" style="width: {{ $share($seg['n'], $t['applied']) }}%; background: {{ $seg['color'] }};">
                                            @if($i === 0 && $t['accepted_share'] >= 22)
                                                {{ $t['accepted'] }} accepted · {{ $t['accepted_share'] }}%
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        </table>
                    @endif
                    <table class="aar-legend">
                        @foreach($legendRows as $pair)
                            <tr>
                                @foreach($pair as $seg)
                                    <td>
                                        <table class="aar-legend__item">
                                            <tr>
                                                <td class="aar-legend__swatchcell"><span class="aar-legend__swatch" style="background: {{ $seg['color'] }};"></span></td>
                                                <td>
                                                    <div class="aar-legend__title">{{ $seg['n'] }} {{ $seg['title'] }}</div>
                                                    <div class="aar-legend__sub">{{ $seg['sub'] }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                @endforeach
                                @if(count($pair) === 1)
                                    <td></td>
                                @endif
                            </tr>
                        @endforeach
                    </table>
                </td>
                <td class="aar-split__gap"></td>
                <td class="aar-split__right">
                    <table class="aar-sechead">
                        <tr>
                            <td class="aar-h2">Target vs accepted by course</td>
                            <td class="aar-sechead__meta">Bar = accepted · Black line = target</td>
                        </tr>
                    </table>
                    @foreach($report['courses'] as $course)
                        <div class="aar-course{{ $loop->last ? ' is-last' : '' }}">
                            <table class="aar-course__row">
                                <tr>
                                    <td class="aar-course__id">
                                        <div class="aar-course__name">{{ $course['name'] }}</div>
                                        @if($course['venues'] !== '')
                                            <div class="aar-course__venue">{{ $course['venues'] }}</div>
                                        @endif
                                    </td>
                                    <td class="aar-course__barcell">
                                        <div class="aar-track" role="img" aria-label="{{ $course['accepted'] }} accepted against a target of {{ $course['target'] }}">
                                            <div class="aar-track__fill" style="width: {{ $course['fill'] }}%;"></div>
                                            @if($course['target'] > 0)
                                                <div class="aar-track__tick" style="left: {{ $course['tick'] }}%;"></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="aar-course__fig"><span class="aar-course__accepted">{{ $course['accepted'] }}</span> <span class="aar-course__target">/ {{ $course['target'] }}</span></td>
                                    <td class="aar-course__gapcell"><div class="aar-gap {{ $gapTone($course['gap'], $course['target']) }}">{{ $gapText($course['gap'], $course['target']) }}</div></td>
                                </tr>
                            </table>
                            <div class="aar-course__note{{ $course['note_warn'] ? ' is-warn' : '' }}">{{ $course['note'] }}</div>
                        </div>
                    @endforeach
                </td>
            </tr>
        </table>

        <div class="aar-h2 aar-h2--table">By delivery pattern</div>
        <div class="aar-tablewrap">
            <table class="aar-table">
                <thead>
                    <tr>
                        <th class="aar-table__label">Course · delivery</th>
                        <th>Target</th>
                        <th>Applied</th>
                        <th>Accepted</th>
                        <th>Pending</th>
                        <th>Rejected</th>
                        <th>Declined</th>
                        @if($t['canceled'] > 0)
                            <th>Canceled</th>
                        @endif
                        <th>Gap</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['delivery'] as $row)
                        <tr class="{{ $loop->last ? 'is-last' : '' }}">
                            <td class="aar-table__label">{{ $row['label'] }}</td>
                            <td>{{ $row['target'] }}</td>
                            <td>{{ $row['applied'] }}</td>
                            <td class="is-strong">{{ $row['accepted'] }}</td>
                            <td>{{ $row['pending'] }}</td>
                            <td>{{ $row['rejected'] }}</td>
                            <td>{{ $row['declined'] }}</td>
                            @if($t['canceled'] > 0)
                                <td>{{ $row['canceled'] }}</td>
                            @endif
                            <td class="is-strong {{ $gapTone($row['gap'], $row['target']) }}">{{ $gapText($row['gap'], $row['target']) }}</td>
                        </tr>
                    @endforeach
                    <tr class="aar-table__total">
                        <td class="aar-table__label">Total</td>
                        <td>{{ $t['target'] }}</td>
                        <td>{{ $t['applied'] }}</td>
                        <td>{{ $t['accepted'] }}</td>
                        <td>{{ $t['pending'] }}</td>
                        <td>{{ $t['rejected'] }}</td>
                        <td>{{ $t['declined'] }}</td>
                        @if($t['canceled'] > 0)
                            <td>{{ $t['canceled'] }}</td>
                        @endif
                        <td class="{{ $gapTone($t['gap'], $t['target']) }}">{{ $gapText($t['gap'], $t['target']) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================ Page 2 ============================ --}}
    <div class="aar-sheet aar-page2">
        <table class="aar-titlerow">
            <tr>
                <td>
                    <div class="aar-eyebrow">Accepted students</div>
                    <div class="aar-title">Who accepted</div>
                </td>
                <td class="aar-titlerow__note">Profile of the {{ $cohort['total'] }} students who accepted an offer, not all {{ $t['applied'] }} applicants.</td>
            </tr>
        </table>

        @if($cohort['total'] < 1)
            <div class="aar-empty">
                <div class="aar-empty__title">No one has accepted an offer yet</div>
                <div class="aar-empty__text">The profile appears once applicants accept their offers.</div>
            </div>
        @else
        <table class="aar-cols">
            <tr>
                <td class="aar-cols__first">
                    <table class="aar-sechead">
                        <tr><td class="aar-h2">Gender</td></tr>
                    </table>
                    <table class="aar-gender">
                        <tr>
                            @foreach($genderParts as $i => $part)
                                @php $partShare = $share($part['n'], $cohort['total']); @endphp
                                <td class="aar-gender__seg {{ $part['class'] }}{{ $i === 0 ? ' is-first' : '' }}{{ $loop->last ? ' is-last' : '' }}" style="width: {{ $partShare }}%;">
                                    @if($partShare >= 18)
                                        {{ $part['label'] }} {{ $part['n'] }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </table>
                    <div class="aar-caption">{{ $gender['caption'] }}</div>

                    <table class="aar-sechead aar-sechead--spaced">
                        <tr><td class="aar-h2">Entry route</td></tr>
                    </table>
                    <table class="aar-entry">
                        <thead>
                            <tr>
                                <th class="aar-entry__course">Course</th>
                                <th>Mature</th>
                                <th>Academic</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cohort['entry'] as $row)
                                <tr>
                                    <td class="aar-entry__course">{{ $row['subject'] }}</td>
                                    <td>{{ $row['mature'] }}</td>
                                    <td>{{ $row['academic'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($entryUnknown > 0)
                        {{-- Not in either column: on screen the count opens the list of those applicants. --}}
                        <div class="aar-caption is-warn">
                            @if($isPdf)
                                {{ $entryUnknown }}
                            @else
                                <a href="javascript:void(0);" data-ids="{{ implode(',', $entryUnknownIds) }}" data-tw-toggle="modal" data-tw-target="#viewUnknownEntryModal" class="viewUnknownEntryBtn">{{ $entryUnknown }}</a>
                            @endif
                            {{ $entryUnknown === 1 ? 'student' : 'students' }} not counted above: no qualification or job recorded.
                        </div>
                    @endif
                </td>
                <td class="aar-cols__gap"></td>
                <td class="aar-cols__mid">
                    <table class="aar-sechead">
                        <tr>
                            <td class="aar-h2">Nationality</td>
                            <td class="aar-sechead__meta">{{ $cohort['nationality']['meta'] }}</td>
                        </tr>
                    </table>
                    @include('pages.reports.application-analysis.partials.share-rows', ['rows' => $cohort['nationality']['rows']])
                </td>
                <td class="aar-cols__gap"></td>
                <td class="aar-cols__last">
                    <table class="aar-sechead">
                        <tr>
                            <td class="aar-h2">Age</td>
                            <td class="aar-sechead__meta">{{ $cohort['age']['mean'] !== null ? 'Mean age '.$cohort['age']['mean'] : 'No dates of birth' }}</td>
                        </tr>
                    </table>
                    @include('pages.reports.application-analysis.partials.share-rows', ['rows' => $cohort['age']['rows']])
                </td>
            </tr>
        </table>
        @endif
    </div>
    @endif
</div>
