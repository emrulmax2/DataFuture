{{-- One key figure. Params: label, value, hint, icon, basis (fact|anomaly|unavailable|null), tone (alert|warn|null), href (null = not a link), html (raw value markup). --}}
@php $tag = isset($href) && $href ? 'a' : 'div'; @endphp
<{{ $tag }} class="aim-kpi {{ !empty($tone) ? 'aim-kpi--'.$tone : '' }}" @if($tag === 'a') href="{{ $href }}" @endif>
    <div class="aim-kpi__label"><span>{{ $label }}</span>@if(!empty($icon))<i data-lucide="{{ $icon }}"></i>@endif</div>
    <div class="aim-kpi__value">{!! $html ?? e($value) !!}</div>
    @if(!empty($hint))<div class="aim-kpi__hint">{{ $hint }}</div>@endif
    <div class="aim-kpi__foot">
        @if(!empty($basis))<span class="aim-tag aim-tag--{{ $basis }}">{{ ['fact' => 'Fact', 'anomaly' => 'Anomaly', 'unavailable' => 'Not recorded'][$basis] ?? $basis }}</span>@else<span></span>@endif
        @if($tag === 'a')<span class="aim-small">View &rarr;</span>@endif
    </div>
</{{ $tag }}>
