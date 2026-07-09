{{--
    Reusable profile field — uppercase muted label over a value.
    Props:
      $label  string   Field label
      $value  mixed    Value; null / '' renders a muted "N/A" placeholder.
                       A "Yes"/"No" value auto-renders a green/red status badge.
      $span   ?string  Optional grid column class (e.g. 'sm:col-span-2', 'col-span-full')
      $check  ?bool    Show a teal check icon before the value
      $badge  ?string  Optional trailing badge HTML (e.g. "Expiring soon")
--}}
@php
    $has     = isset($value) && $value !== '' && $value !== null;
    $valStr  = $has ? trim((string) $value) : '';
    $isYesNo = $has && in_array(strtolower($valStr), ['yes', 'no'], true);
    $isYes   = $isYesNo && strtolower($valStr) === 'yes';
@endphp
<div class="min-w-0 {{ $span ?? '' }}">
    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400 mb-1.5">{{ $label }}</div>
    <div class="flex items-start gap-1.5">
        @if($isYesNo)
            <span class="ep-status-badge {{ $isYes ? 'ep-status-badge--yes' : 'ep-status-badge--no' }}"><i data-lucide="{{ $isYes ? 'check' : 'x' }}"></i><span>{{ $isYes ? 'Yes' : 'No' }}</span></span>
        @else
            @if(($check ?? false) && $has)
                <i data-lucide="check-circle" class="w-4 h-4 text-success flex-none mt-0.5"></i>
            @endif
            <span class="text-sm font-semibold leading-relaxed {{ $has ? 'text-slate-700 dark:text-slate-200' : 'italic text-slate-400' }}">{{ $has ? $value : 'N/A' }}</span>
        @endif
        @if(!empty($badge)){!! $badge !!}@endif
    </div>
</div>
