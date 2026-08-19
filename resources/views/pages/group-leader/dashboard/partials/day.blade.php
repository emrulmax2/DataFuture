@php use App\Support\GroupLeaderPresenter as GL; @endphp

@php $c = $day['counts']; @endphp

<div class="gl-day__head">
    <div>
        <div class="gl-day__title">{{ date('l j F Y', strtotime($day['date'])) }}</div>
        <div class="gl-day__sub">
            {{ $group->name ?? '' }} · {{ count($day['classes']) }} {{ Str::plural('class', count($day['classes'])) }}
            · {{ $c['rooms'] }} {{ Str::plural('room', $c['rooms']) }}
            @if($day['date'] === date('Y-m-d')) · time now {{ date('H:i') }} @endif
        </div>
    </div>

    <div class="gl-day__pills">
        @if($c['live'] > 0)
            <span class="gl-day__pill is-blue"><span class="gl-dot" style="background:#3b82f6;"></span>{{ $c['live'] }} in progress</span>
        @endif
        @if($c['late'] > 0)
            <span class="gl-day__pill is-red"><span class="gl-dot is-red"></span>{{ $c['late'] }} not started</span>
        @endif
        @if($c['feedMissing'] > 0)
            <span class="gl-day__pill is-amber"><span class="gl-dot is-amber"></span>{{ $c['feedMissing'] }} attendance not taken</span>
        @endif
        @if($c['cover'] > 0)
            <span class="gl-day__pill is-amber"><span class="gl-dot is-amber"></span>{{ $c['cover'] }} on cover</span>
        @endif
        @if($c['cancelled'] > 0)
            <span class="gl-day__pill is-grey"><span class="gl-dot is-grey"></span>{{ $c['cancelled'] }} cancelled</span>
        @endif
        <span class="gl-day__pill is-green"><span class="gl-dot is-green"></span>{{ $c['finished'] }} finished</span>
    </div>
</div>

@if($c['late'] > 0 || $c['feedMissing'] > 0)
    <div class="gl-needsyou" style="margin-top:16px;">
        <strong>Needs you now:</strong>
        @if($c['late'] > 0)
            {{ $c['late'] }} {{ Str::plural('class', $c['late']) }} {{ $c['late'] > 1 ? 'have' : "hasn't" }}{{ $c['late'] > 1 ? "n't started" : ' started' }} (tutor not marked in).
        @endif
        @if($c['feedMissing'] > 0)
            {{ $c['feedMissing'] }} finished/live {{ Str::plural('class', $c['feedMissing']) }} {{ $c['feedMissing'] > 1 ? 'have' : 'has' }} no attendance marked — it won't count until it's taken.
        @endif
    </div>
@endif

@forelse($day['slots'] as $time => $classes)
    @php $done = count(array_filter($classes, fn ($x) => $x['state'] === 'finished')); @endphp
    <div class="gl-slot" style="margin-top:16px;">
        <button type="button" class="gl-slot__head" data-gl-slot>
            <span class="gl-slot__mark" data-gl-slot-mark>▾</span>
            <span class="gl-slot__time gl-mono">{{ $time }}</span>
            <span class="gl-slot__count">{{ count($classes) }} {{ Str::plural('class', count($classes)) }}</span>
            <span class="gl-slot__done">{{ $done }}/{{ count($classes) }} finished</span>
        </button>

        <div class="gl-slot__body">
            @foreach($classes as $class)
                <div class="gl-class">
                    <div style="min-width:0;">
                        <div class="gl-class__module">
                            @if($class['type'])
                                <span class="gl-chip {{ GL::typeClass($class['type']) }}">{{ $class['type'] }}</span>
                            @endif
                            {{ $class['module'] }}
                        </div>
                        <div class="gl-class__course">{{ $class['course'] }}</div>
                    </div>

                    <div class="gl-class__groupcell">
                        <span class="gl-chip is-plain gl-mono">{{ $class['group'] }}</span>
                    </div>

                    <div class="gl-class__tutor">
                        <span class="gl-avatar">{{ GL::initials($class['tutor']) }}</span>
                        <span style="min-width:0;">
                            @if($class['replacing'])
                                <span class="gl-class__replacing" style="display:block;">{{ $class['replacing'] }}</span>
                            @endif
                            <span class="gl-class__name" style="display:block;">{{ $class['tutor'] ?: '—' }}</span>
                            @if($class['replacing'])
                                <span class="gl-class__covering">COVERING</span>
                            @endif
                        </span>
                    </div>

                    <div class="gl-class__room gl-class__roomcell">{{ $class['room'] }}</div>

                    <div>
                        @if($class['state'] === 'finished')
                            <div class="gl-state">
                                <span class="gl-state__badge is-green">✓</span>
                                <span class="gl-state__text gl-mono">
                                    {{ $class['start'] ?: $class['scheduled'] }} – {{ $class['end'] ?: '—' }}
                                </span>
                            </div>
                        @elseif($class['state'] === 'live')
                            <div class="gl-state">
                                <span class="gl-live"><span class="gl-live__ping"></span><span class="gl-live__dot"></span></span>
                                <span class="gl-state__text">
                                    <strong style="color:#1d4ed8;">LIVE</strong>
                                    <span class="gl-mono" style="color:#64748b;">from {{ $class['start'] }}</span>
                                </span>
                            </div>
                        @elseif($class['state'] === 'late')
                            <div class="gl-state">
                                <span class="gl-state__badge is-red">!</span>
                                <span class="gl-state__text">
                                    <strong style="color:#be123c;">Not started</strong>
                                    <span class="gl-mono" style="color:#64748b;">due {{ $class['scheduled'] }}</span>
                                </span>
                            </div>
                        @elseif($class['state'] === 'cancelled')
                            <div class="gl-state">
                                <span class="gl-state__badge is-grey">✕</span>
                                <span class="gl-state__text" style="color:#64748b;">Cancelled</span>
                            </div>
                        @else
                            <div class="gl-state">
                                <span class="gl-state__badge is-grey">○</span>
                                <span class="gl-state__text gl-mono" style="color:#64748b;">Scheduled {{ $class['scheduled'] }}</span>
                            </div>
                        @endif

                        @if(in_array($class['state'], ['finished', 'live']))
                            <div class="gl-feed {{ $class['feedGiven'] ? 'is-ok' : 'is-bad' }}">
                                <span class="gl-dot {{ $class['feedGiven'] ? 'is-green' : 'is-amber' }}"></span>
                                {{ $class['feedGiven'] ? 'Attendance taken' : 'Attendance not taken' }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="gl-card gl-empty" style="margin-top:16px;">No classes are scheduled for this group on this date.</div>
@endforelse
