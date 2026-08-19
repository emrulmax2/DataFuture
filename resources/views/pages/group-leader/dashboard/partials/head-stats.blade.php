@php
    $glBelow = array_sum(array_column($cards, 'below60'));
@endphp

<div class="gl-headstats">
    <div>
        <span class="gl-headstat__value">{{ count($cards) }}</span>
        <span class="gl-headstat__label">Groups assigned</span>
    </div>
    <div>
        <span class="gl-headstat__value">{{ array_sum(array_column($cards, 'students')) }}</span>
        <span class="gl-headstat__label">Students</span>
    </div>
    <div>
        <span class="gl-headstat__value">{{ array_sum(array_column($cards, 'modules')) }}</span>
        <span class="gl-headstat__label">Modules</span>
    </div>
    <div>
        <span class="gl-headstat__value {{ $glBelow > 0 ? 'is-danger' : '' }}">{{ $glBelow }}</span>
        <span class="gl-headstat__label">Attendance below 60%</span>
    </div>
</div>
