{{--
    A Yes/No switch for the application form, drawn as the form's chosen pill:
    a round dot with a tick and "Yes" when on; a cross and "No" in muted,
    disabled-looking colours when off.

    Posts `name=1` when on and `name=0` when off: the hidden 0 comes first in
    the form and the checked box, when it is, overrides it (last value wins).
    The box keeps the field's id, so the scripts' `#field` / `input[name=field]`
    handlers keep working unchanged.
--}}
@props(['name', 'id' => null, 'checked' => false, 'on' => 'Yes', 'off' => 'No'])
<label class="apd-switch">
    <input type="hidden" name="{{ $name }}" value="0">
    <input class="sr" type="checkbox" id="{{ $id ?? $name }}" name="{{ $name }}" value="1" {{ $checked ? 'checked' : '' }}>
    <span class="apd-switch__dot" aria-hidden="true">
        <svg class="apd-switch__on" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        <svg class="apd-switch__off" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </span>
    <span class="apd-switch__text" data-on="{{ $on }}" data-off="{{ $off }}"></span>
</label>
