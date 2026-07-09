@if(isset($stats['state']) && $stats['state'] === 'ready')
    <div class="ep-holiday-stats-card">
        <div class="ep-holiday-stats-hero">
            <div>
                <div class="ep-holiday-stats-eyebrow">{{ $stats['is_overbooked'] ? 'Overtaken' : 'Left This Year' }}</div>
                <div class="ep-holiday-stats-value{{ $stats['is_overbooked'] ? ' is-negative' : '' }}">{{ $stats['left_display'] }}</div>
            </div>
            <div class="ep-holiday-stats-icon">
                <i data-lucide="{{ $stats['is_overbooked'] ? 'alert-triangle' : 'shield' }}" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="ep-holiday-stats-meter">
            @foreach($stats['segments'] as $segment)
                <span class="ep-holiday-stats-meter__segment is-{{ $segment['tone'] }}" style="width: {{ $segment['percent'] }}%"></span>
            @endforeach
        </div>
        <div class="ep-holiday-stats-legend">
            @foreach($stats['segments'] as $segment)
                <span class="ep-holiday-stats-legend__item">
                    <span class="ep-holiday-stats-legend__dot is-{{ $segment['tone'] }}"></span>
                    {{ $segment['label'] }} {{ $segment['value'] }}
                </span>
            @endforeach
        </div>
        <div class="ep-holiday-stats-rows">
            <div class="ep-holiday-stats-row">
                <span>Opening This Year</span>
                <strong>{{ $stats['opening_display'] }}</strong>
            </div>
            <div class="ep-holiday-stats-row">
                <span>Bank Holiday Auto Book</span>
                <strong>{{ $stats['bank_display'] }}</strong>
            </div>
            <div class="ep-holiday-stats-row">
                <span>Taken</span>
                <strong>{{ $stats['taken_display'] }}</strong>
            </div>
            <div class="ep-holiday-stats-row">
                <span>Booked</span>
                <strong>{{ $stats['booked_display'] }}</strong>
            </div>
            <div class="ep-holiday-stats-row">
                <span>Requested</span>
                <strong>{{ $stats['requested_display'] }}</strong>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> {{ $stats['message'] ?? 'Working pattern not found!' }}
    </div>
@endif
