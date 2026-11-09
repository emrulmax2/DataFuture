@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Time line (admission portal shell): every action on the application,
        oldest first, built by App\Services\Admission\ApplicantTimeline from the
        status archive, task logs, documents, notes, emails / SMS / letters,
        admissions notifications, the e-signature audit, DocuSeal, Task
        Automation, interviews, the student conversion and staff record views.
        Rendered server-side; admission-timeline.js filters by category,
        person and text, flips the order and prints.
    --}}
    @php
        $adpStatusId = (int) ($applicant->status_id ?? 0);
        $adpPriv = auth()->user()->priv();
        $adpNext = \App\Support\AdmissionStatusFlow::next($applicant, $allStatuses, $adpPriv);
        $adpStatusHelp = \App\Support\AdmissionStatusFlow::HELP;
        $adpTab = 'timeline';

        $tl = $timeline;
        $tlEvents = $tl['events'];
        $tlActors = $tl['actors'];
        $tlCats = $tl['categories'];

        $tlIconPaths = [
            'status'  => '<path d="M4 22V4a1 1 0 0 1 1-1h11l-1 4 1 4H5"/>',
            'task'    => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
            'doc'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
            'comms'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
            'note'    => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
            'esign'   => '<path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.6 7.6"/><circle cx="11" cy="11" r="2"/>',
            'details' => '<circle cx="10" cy="8" r="4"/><path d="M2 21c0-4 3.6-6 8-6 1.4 0 2.7.2 3.8.6"/><path d="m17.5 14.5 3 3L16 22h-3v-3z"/>',
            'views'   => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'system'  => '<path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/>',
            'search'  => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'print'   => '<path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/>',
            'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'check'   => '<path d="M20 6 9 17l-5-5"/>',
            'file'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
            'arrow'   => '<path d="M7 17 17 7"/><path d="M7 7h10v10"/>',
        ];
        $tlIcon = fn ($name, $size = 14) => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($tlIconPaths[$name] ?? '').'</svg>';
        $tlAvatar = function ($actor, $size = 26) {
            return '<span class="adp-atl-av adp-atl-av--'.e($actor['kind']).'" style="width:'.$size.'px;height:'.$size.'px;font-size:'.($size < 26 ? 9 : 10).'px">'.e($actor['ini']).'</span>';
        };

        $tlName = trim(($applicant->title->name ?? '').' '.$applicant->first_name.' '.$applicant->last_name);
        $tlAgent = $tlActors['agent']['name'] ?? null;
        $tlCourse = $applicant->course->creation->course->name ?? null;
        $tlSemester = $applicant->course->semester->name ?? null;
        $tlStatusTone = $tl['outcome']['tone'];
        $tlInitials = \App\Support\Avatar::initialsOnly($applicant->first_name.' '.$applicant->last_name);
        $tlMax = 3; // items shown before "Show all"
    @endphp

    @include('pages.students.admission.partials.portal-record')

    <section class="adp-panel adp-atl" data-adp-atl data-total="{{ count($tlEvents) }}">
        {{-- Print only: the header card the printed time line opens with. --}}
        <div class="adp-atl__print-head" aria-hidden="true">
            <span class="adp-atl-av adp-atl-av--applicant" style="width:44px;height:44px;font-size:15px">{{ $tlInitials }}</span>
            <div>
                <small>Application time line</small>
                <b>{{ $tlName }}</b>
                <span>Ref <b>{{ $applicant->application_no ?: '—' }}</b>{{ $tlCourse ? ' · '.$tlCourse : '' }}{{ $tlSemester ? ' · '.$tlSemester : '' }}{{ $tlAgent ? ' · '.$tlAgent : '' }}</span>
            </div>
            <span class="adp-pill adp-pill--{{ $tlStatusTone }}"><i></i>{{ $tl['outcome']['status'] }}</span>
        </div>

        <div class="adp-atl__head">
            <div>
                <h3 class="adp-h3">Time line</h3>
                <p class="adp-atl__sub">Every action on this application, from {{ $applicant->submission_date ? 'submission' : 'the first draft' }} to {{ $tl['outcome']['final'] ? 'its outcome' : 'now' }} — what happened, when, and who did it.</p>
            </div>
            <div class="adp-atl__head-tools">
                <div class="adp-seg" aria-label="Order">
                    <button type="button" class="on" aria-pressed="true" data-adp-atl-order="asc">Oldest first</button>
                    <button type="button" aria-pressed="false" data-adp-atl-order="desc">Newest first</button>
                </div>
                <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-adp-atl-print>{!! $tlIcon('print', 15) !!}Print</button>
            </div>
        </div>

        @if(count($tlEvents) === 0)
            <div class="adp-empty"><b>Nothing recorded yet.</b><span>Actions on this application will show here as they happen.</span></div>
        @else
            <div class="adp-atl__sum">
                <div><small>Started</small><b>{{ $tl['summary']['started'] }}</b></div>
                <div><small>Latest activity</small><b>{{ $tl['summary']['latest'] }}</b></div>
                <div><small>Elapsed</small><b>{{ $tl['summary']['elapsed'] }} day{{ $tl['summary']['elapsed'] === 1 ? '' : 's' }}</b></div>
                <div><small>Events</small><b>{{ $tl['summary']['events'] }}</b></div>
                <div class="adp-atl__people"><small>People involved</small>
                    <span class="adp-atl__stack">
                        @foreach($tl['people'] as $p)
                            <span data-tip="{{ $p['name'] }} · {{ $p['n'] }} event{{ $p['n'] === 1 ? '' : 's' }}">{!! $tlAvatar($p, 28) !!}</span>
                        @endforeach
                    </span>
                </div>
            </div>

            <div class="adp-atl__filters">
                <div class="adp-atl__chips" role="group" aria-label="Show categories">
                    @foreach($tlCats as $key => $label)
                        @if($tl['counts'][$key] > 0)
                            <button type="button" class="adp-atl-chip c-{{ $key }}" aria-pressed="false" data-adp-atl-cat="{{ $key }}">{!! $tlIcon($key, 13) !!}{{ $label }}<b>{{ $tl['counts'][$key] }}</b></button>
                        @endif
                    @endforeach
                </div>
                <div class="adp-atl__tools">
                    <label class="adp-atl__search">{!! $tlIcon('search', 15) !!}<input type="search" placeholder="Search activity" aria-label="Search activity" data-adp-atl-q autocomplete="off"></label>
                    <select class="adp-ctl adp-select adp-atl__who-select" aria-label="Who" data-adp-atl-who>
                        <option value="">Everyone</option>
                        @foreach($tl['people'] as $p)
                            <option value="{{ $p['key'] }}">{{ $p['name'] }} ({{ $p['n'] }})</option>
                        @endforeach
                    </select>
                    <button type="button" class="adp-btn-link" data-adp-atl-clear hidden>Clear</button>
                </div>
            </div>
            <p class="adp-atl__count" data-adp-atl-count hidden></p>

            <ol class="adp-atl__days" data-adp-atl-days>
                @foreach($tl['days'] as $day)
                    <li class="adp-atl__day" data-adp-atl-day>
                        <div class="adp-atl__dayh"><b>{{ $day['label'] }}</b><span class="adp-atl__daynum">Day {{ $day['n'] }}</span><i></i><span data-adp-atl-daycount>{{ count($day['events']) }} event{{ count($day['events']) === 1 ? '' : 's' }}</span></div>
                        <ol class="adp-atl__list">
                            @foreach($day['events'] as $i)
                                @php
                                    $ev = $tlEvents[$i];
                                    $who = $tlActors[$ev['who']];
                                    $cls = 'adp-atl__ev c-'.$ev['cat'].($ev['milestone'] ? ' ms' : '').($ev['tone'] ? ' tone-'.$ev['tone'] : '');
                                    $nItems = count($ev['items']);
                                @endphp
                                <li class="{{ $cls }}" data-adp-atl-ev data-cat="{{ $ev['cat'] }}" data-who="{{ $ev['who'] }}" data-search="{{ $ev['search'] }}">
                                    <time class="adp-atl__time adp-mono" datetime="{{ str_replace(' ', 'T', $ev['at']) }}" @if(!$ev['time']) title="Time not recorded" @endif>{{ $ev['time'] ?? '—' }}@if($ev['end'])<small>–{{ $ev['end'] }}</small>@endif</time>
                                    <span class="adp-atl__node" aria-hidden="true">{!! $tlIcon($ev['cat'], $ev['milestone'] ? 15 : 13) !!}</span>
                                    <div class="adp-atl__card">
                                        <div class="adp-atl__main">
                                            <span class="adp-atl__cat">{{ $tlCats[$ev['cat']] }}</span>
                                            <b class="adp-atl__title">{{ $ev['title'] }}</b>
                                            @if($ev['detail'] !== '')<span class="adp-atl__detail">{{ $ev['detail'] }}</span>@endif
                                            @if($nItems > 0)
                                                <div class="adp-atl__items" data-adp-atl-items>
                                                    <ul>
                                                        @foreach($ev['items'] as $k => $it)
                                                            <li class="{{ $k >= $tlMax ? 'is-more' : '' }}">@if(!empty($it['time']))<span class="adp-mono">{{ $it['time'] }}</span>@endif<b>{{ $it['title'] }}</b>@if($it['detail'] !== '')<span>{{ $it['detail'] }}</span>@endif</li>
                                                        @endforeach
                                                    </ul>
                                                    @if($nItems > $tlMax)
                                                        <button type="button" class="adp-btn-link" data-adp-atl-more data-n="{{ $nItems }}">Show all {{ $nItems }}</button>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($ev['file'])<span class="adp-atl__file">{!! $tlIcon('file', 12) !!}{{ $ev['file'] }}</span>@endif
                                            @if($ev['href'])<a class="adp-atl__link" href="{{ $ev['href'] }}">{{ $ev['hrefLabel'] ?: 'Open' }}{!! $tlIcon('arrow', 12) !!}</a>@endif
                                        </div>
                                        <span class="adp-atl__who">{!! $tlAvatar($who, 24) !!}<span><b>{{ $who['name'] }}</b><small>{{ $who['role'] }}</small></span></span>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </li>
                @endforeach

                {{-- Where the application stands now; hidden while a filter is on. --}}
                <li class="adp-atl__day adp-atl__now" data-adp-atl-now>
                    <div class="adp-atl__ev ms c-now">
                        <time class="adp-atl__time adp-mono">{{ $tl['outcome']['final'] ? 'End' : 'Now' }}</time>
                        <span class="adp-atl__node" aria-hidden="true">{!! $tlIcon($tl['outcome']['final'] ? 'check' : 'clock', 14) !!}</span>
                        <div class="adp-atl__card">
                            <div class="adp-atl__main">
                                <span class="adp-atl__cat">{{ $tl['outcome']['final'] ? 'Outcome' : 'Current status' }}</span>
                                <b class="adp-atl__title"><span class="adp-pill adp-pill--{{ $tlStatusTone }}"><i></i>{{ $tl['outcome']['status'] }}</span></b>
                                <span class="adp-atl__detail">{{ $tl['outcome']['text'] }}</span>
                            </div>
                        </div>
                    </div>
                </li>
            </ol>

            <div class="adp-atl__empty" data-adp-atl-empty hidden>{!! $tlIcon('clock', 22) !!}<b>No activity matches</b><span>Try another category or person.</span></div>
        @endif
    </section>

    @include('pages.students.admission.partials.portal-record-modals')
    @include('pages.students.admission.partials.portal-dialogs')
@endsection

@section('script')
    @vite('resources/js/admission-timeline.js')
    @vite('resources/js/admission-vue.js')
    {{-- The record header's Change status dialog (admission-show.js guards its other blocks). --}}
    @vite('resources/js/admission-show.js')
@endsection
