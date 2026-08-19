@php use App\Support\GroupLeaderPresenter as GL; @endphp

@if(!empty($cards))
    <div class="gl-groups">
        @foreach($cards as $card)
            @php $tone = GL::tone($card['attendance']); @endphp
            <a class="gl-group" href="{{ route('gl.dashboard.group', ['group' => $card['id'], 'term' => $card['termId']]) }}">
                <div class="gl-group__head">
                    <div style="min-width:0;">
                        <div class="gl-group__code gl-mono">{{ $card['name'] }}</div>
                        <div class="gl-group__course">{{ $card['course'] }}</div>
                    </div>
                    <span class="gl-pill is-{{ $tone }}">
                        <span class="gl-dot is-{{ $tone }}"></span>
                        {{ $card['attendance'] === null ? '—' : $card['attendance'].'%' }}
                    </span>
                </div>

                <div class="gl-group__tiles">
                    <div class="gl-tile">
                        <div class="gl-tile__value">{{ $card['modules'] }}</div>
                        <div class="gl-tile__label">Modules</div>
                    </div>
                    <div class="gl-tile">
                        <div class="gl-tile__value">{{ $card['students'] }}</div>
                        <div class="gl-tile__label">Students</div>
                    </div>
                    <div class="gl-tile {{ $card['below60'] > 0 ? 'is-danger' : '' }}">
                        <div class="gl-tile__value">{{ $card['below60'] }}</div>
                        <div class="gl-tile__label">Att &lt; 60%</div>
                    </div>
                </div>

                <div class="gl-group__meter gl-meter">
                    <div class="gl-meter__head">
                        <span class="gl-meter__label">Group attendance</span>
                        <span class="gl-meter__value is-{{ $tone }}">{{ $card['attendance'] === null ? 'No data' : $card['attendance'].'%' }}</span>
                    </div>
                    <div class="gl-meter__track">
                        <span class="gl-bar is-{{ $tone }}" style="width: {{ min(100, max(0, (int) $card['attendance'])) }}%;"></span>
                    </div>
                </div>

                <div class="gl-group__foot">
                    <span class="gl-group__term">
                        {{ $card['termType'] ?: $card['term'] }}{{ $card['evening'] ? ' · Eve/Wknd' : '' }}
                    </span>
                    <span class="gl-group__go">Open dashboard →</span>
                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="gl-card gl-empty">No groups assigned in this term.</div>
@endif
