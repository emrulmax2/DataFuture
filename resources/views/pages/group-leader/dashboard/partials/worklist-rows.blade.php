@php use App\Support\GroupLeaderPresenter as GL; @endphp

@forelse($rows as $row)
    @php $tone = GL::tone($row['attendance']); @endphp
    <button type="button" class="gl-row" data-gl-student="{{ $row['id'] }}">
        <span class="gl-avatar">{{ GL::initials($row['name']) }}</span>

        <span class="gl-row__main">
            <span class="gl-row__name">
                {{ $row['name'] }}
                @if($row['consecutive'] >= 3)
                    <span class="gl-chip" style="background:#ffe4e6;color:#be123c;">{{ $row['consecutive'] }} in a row</span>
                @endif
            </span>
            <span class="gl-row__sub">
                {{ $row['personalTutor'] ? 'PT: '.$row['personalTutor'] : 'No personal tutor' }} ·
                @if($row['lastContact'])
                    Last contact {{ $row['lastContact'] }}
                @else
                    <span class="gl-row__flag">Not contacted</span>
                @endif
            </span>
        </span>

        <span class="gl-row__meter gl-meter">
            <span class="gl-meter__head">
                <span class="gl-meter__label">Att</span>
                <span class="gl-meter__value is-{{ $tone }}">{{ $row['attendance'] === null ? '—' : round($row['attendance']).'%' }}</span>
            </span>
            <span class="gl-meter__track" style="display:block;">
                <span class="gl-bar is-{{ $tone }}" style="display:block;height:100%;width: {{ min(100, max(0, (int) $row['attendance'])) }}%;"></span>
            </span>
        </span>

        <span class="gl-row__subs">
            <span class="gl-row__subs-label">Subs</span>
            <span class="gl-row__subs-value {{ ($row['submissionPct'] !== null && $row['submissionPct'] < 75) ? 'is-red' : '' }}">
                {{ $row['due'] > 0 ? $row['submitted'].'/'.$row['due'] : '—' }}
            </span>
        </span>

        <span class="gl-row__chev">›</span>
    </button>
@empty
    <div class="gl-empty">Nothing here — this list is clear.</div>
@endforelse
