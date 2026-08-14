{{--
    Rows for the "Staff absence today" panel.

    Shared by the first paint and by the infinite-scroll endpoint, so an
    appended page is indistinguishable from the initial one.
--}}
@forelse($absents as $employee_id => $absent)
    <div class="pgd-absence__row" data-pgd-absence-row data-name="{{ strtolower($absent['full_name']) }}">
        <span class="pgd-avatar pgd-avatar--sm" style="background: {{ \App\Support\Avatar::soft($absent['full_name']) }};">
            @if(!\App\Support\Avatar::isGenerated($absent['photo_url'] ?? null))
                <img src="{{ $absent['photo_url'] }}" alt="{{ $absent['full_name'] }}">
            @else
                {{ \App\Support\Avatar::initialsOnly($absent['full_name']) }}
            @endif
        </span>
        <span class="pgd-absence__copy">
            <span class="pgd-absence__name">{{ $absent['full_name'] }}</span>
            <span class="pgd-absence__reason">Not clocked in · {{ $absent['date'] }}</span>
        </span>
        <span class="pgd-absence__time">{{ $absent['hourMinute'] }}</span>
    </div>
@empty
    @if(($showEmpty ?? true))
        <div class="pgd-absence__empty" data-pgd-absence-empty>Everyone expected on site today has clocked in.</div>
    @endif
@endforelse
