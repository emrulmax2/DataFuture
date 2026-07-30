{{--
    The assessment toggle list injected into the two assessment modals on the
    term module detail screen.

    It is rendered server-side and returned as an HTML string, because that is
    the contract those endpoints already have with the client. It lives here
    rather than being concatenated inside the controller so it can share the
    module's styling with the wizard's step 2.

    Required:
      $assessRows     [['id' => 1, 'name' => …, 'code' => …, 'on' => bool], …]
      $assessOwnerId  Module creation id, to keep the input ids unique

    Optional:
      $assessClass    Hook class on each checkbox. Defaults to `cmb_assessment`;
                      the add-module flow watches `cmb_assessment_indv`.
      $assessIdPrefix Prefix for the input ids, matching that same split.
--}}
@php
    $assessClass = $assessClass ?? 'cmb_assessment';
    $assessIdPrefix = $assessIdPrefix ?? 'cmb_assessment';
@endphp
<div class="cm-asslist">
    @if(!empty($assessRows) && count($assessRows) > 0)
        <div class="cm-asslist__head">
            <span>#</span>
            <span>Name</span>
            <span>Code</span>
            <span></span>
        </div>

        @foreach($assessRows as $assessRow)
            <div class="cm-asslist__row">
                <span class="cm-asslist__n">{{ $loop->index + 1 }}</span>
                <span class="cm-asslist__name">{{ $assessRow['name'] }}</span>
                <span class="cm-asslist__code">{{ $assessRow['code'] }}</span>
                <span class="cm-asslist__toggle">
                    <label class="cm-switchmini" title="Include this assesment">
                        <input class="cm-switchmini__input {{ $assessClass }}"
                               id="{{ $assessIdPrefix }}_{{ $assessOwnerId }}_{{ $assessRow['id'] }}"
                               name="cmb_assessment[]"
                               value="{{ $assessRow['id'] }}"
                               type="checkbox"
                               @if(!empty($assessRow['on'])) checked @endif>
                        <span class="cm-switchmini__track">
                            <span class="cm-switchmini__knob">
                                <svg data-cm-switch-on width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                <svg data-cm-switch-off width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </span>
                        </span>
                    </label>
                </span>
            </div>
        @endforeach
    @else
        <div class="cm-asslist__empty">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
            No assesments found for this module
        </div>
    @endif
</div>
