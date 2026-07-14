@php
    /*
     | Shared HR dashboard list avatar.
     |
     | Shows the employee's uploaded profile picture when there is one, otherwise
     | falls back to the coloured initials chip.
     |
     | Employee::photo_url returns a real storage URL when a photo exists, and a
     | data: SVG URI (generated initials) when it does not — so a "data:" prefix
     | is how we detect "no real photo".
     |
     | Params:
     |   $name      string   full name (used for initials + colour + alt text)
     |   $photoUrl  ?string  the employee's photo_url (may be null or a data: URI)
     */
    $hrdHasPhoto = !empty($photoUrl) && !str_starts_with($photoUrl, 'data:');

    $hrdAvInitials = function ($n) {
        $n = trim(preg_replace('/^(Mrs|Mr|Miss|Ms|Dr)\.?\s+/i', '', (string) $n));
        $parts = preg_split('/\s+/', $n);
        return strtoupper(substr($parts[0] ?? 'L', 0, 1).substr($parts[1] ?? 'C', 0, 1));
    };
    $hrdAvColor = function ($n) {
        $palette = ['#0F7B76', '#3B5BB5', '#7A3FB0', '#C4432F', '#187A45', '#B07E14', '#2A6FA8', '#B0357E', '#0E7C86', '#8A5A2B'];
        return $palette[abs(crc32((string) $n)) % count($palette)];
    };
@endphp
@if($hrdHasPhoto)
    <span class="hrd-avatar hrd-avatar--sm hrd-avatar--photo">
        <img src="{{ $photoUrl }}" alt="{{ $name }}">
    </span>
@else
    <span class="hrd-avatar hrd-avatar--sm" style="background: {{ $hrdAvColor($name) }}">{{ $hrdAvInitials($name) }}</span>
@endif
