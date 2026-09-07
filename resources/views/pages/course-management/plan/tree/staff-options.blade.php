{{-- The <option> list behind the tree's staff pickers.

     With `avatar` set, every option also carries what TomSelect needs to draw
     the person to the left of their name: the uploaded photo where there is
     one, and the name's own colour and initials where there is not.

     The fallback is two data attributes rather than the Avatar::initials()
     data URI the rest of the app uses, because that is roughly half a kilobyte
     of base64 SVG per option and this list runs to every active member of
     staff. Avatar's own docblock points the same way — a module that draws its
     initials chip in CSS keeps the bright Arial SVG out of a designed type
     scale. --}}
@php $avatar = $avatar ?? false; @endphp
@foreach($users as $u)
    @php
        $name = $u->full_name;
        // Only the employee record carries a photo, and only a real upload is
        // worth linking: the accessor hands back a generated data URI when
        // there is none, which is precisely what the chip replaces.
        $photo = '';
        if($avatar && !empty($u->employee) && !empty($u->employee->photo)):
            $candidate = $u->employee->photo_url;
            $photo = (\App\Support\Avatar::isGenerated($candidate) ? '' : $candidate);
        endif;
    @endphp
    <option value="{{ $u->id }}"
        @if($avatar)
            data-initials="{{ \App\Support\Avatar::initialsOnly($name) }}"
            data-color="{{ \App\Support\Avatar::soft($name) }}"
            @if($photo !== '') data-photo="{{ $photo }}" @endif
        @endif
    >{{ $name }}</option>
@endforeach
