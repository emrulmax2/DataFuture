@php
    /*
     * The reporter is either an Employee or a Student and the two carry their
     * contact details on completely different relations. Everything is
     * normalised here so the card below is written once.
     */
    if (isset($employee)) {
        $ritName = trim(($employee->title->name ?? '') . ' ' . $employee->first_name . ' ' . $employee->last_name);
        $ritRole = $employee->employment->employeeJobTitle->name ?? null;
        $ritPhoto = $employee->photo_url ?? null;
        $ritTag = $employee->status == 1 ? 'Active' : 'Inactive';
        $ritTagOff = $employee->status != 1;
        $ritEmail = $employee->email ?? null;
        $ritPhone = $employee->telephone ?? null;
        $ritMobile = $employee->mobile ?? null;
        $ritAddr = $employee->address ?? null;
    } elseif (isset($student)) {
        $ritName = trim(($student->title->name ?? '') . ' ' . $student->first_name . ' ' . $student->last_name);
        $ritRole = $student->crel->creation->course->name ?? null;
        $ritPhoto = $student->photo_url ?? null;
        $ritTag = $student->registration_no ?: 'Student';
        $ritTagOff = false;
        $ritEmail = $student->users->email ?? null;
        $ritPhone = $student->contact->home ?? null;
        $ritMobile = $student->contact->mobile ?? null;
        $ritAddr = $student->contact->termaddress ?? null;
    } else {
        $ritName = $reportItAll->employee_name ?? 'Unknown';
        $ritRole = $ritPhoto = $ritEmail = $ritPhone = $ritMobile = $ritAddr = null;
        $ritTag = null;
        $ritTagOff = false;
    }

    // `photo_url` falls back to an Avatar::initials() data URI when nobody has
    // uploaded a picture, so that prefix is what decides between the photo and
    // our own green-and-gold disc.
    $ritHasPhoto = !empty($ritPhoto) && !str_starts_with($ritPhoto, 'data:');

    $ritInitials = collect(preg_split('/\s+/', trim($ritName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: '?';

    $ritAddressLines = collect([
        $ritAddr->address_line_1 ?? null,
        $ritAddr->address_line_2 ?? null,
        collect([$ritAddr->city ?? null, $ritAddr->state ?? null, $ritAddr->post_code ?? null])
            ->filter()->implode(', ') ?: null,
        $ritAddr->country ?? null,
    ])->filter()->values();
@endphp

<div class="rit-profile">
    <div class="rit-profile__who">
        <span class="rit-profile__avatar">
            @if($ritHasPhoto)
                <img alt="{{ $ritName }}" src="{{ $ritPhoto }}">
            @else
                {{ $ritInitials }}
            @endif
        </span>
        <div class="min-w-0">
            <div class="rit-profile__name">{{ $ritName }}</div>
            @if($ritRole)
                <div class="rit-profile__role">{{ $ritRole }}</div>
            @endif
            @if($ritTag)
                <span class="rit-profile__tag {{ $ritTagOff ? 'rit-profile__tag--off' : '' }}">{{ $ritTag }}</span>
            @endif
        </div>
    </div>

    <div class="rit-profile__col">
        <div class="rit-profile__label">Contact details</div>
        <div class="rit-profile__lines">
            <span class="rit-profile__line {{ $ritEmail ? '' : 'rit-profile__line--empty' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5.5" width="18" height="13" rx="2"></rect><path d="m3.5 7.5 8.5 6 8.5-6"></path></svg>
                {{ $ritEmail ?: 'Not set' }}
            </span>
            <span class="rit-profile__line {{ $ritPhone ? '' : 'rit-profile__line--empty' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h2L9 8l-2 1.5a11 11 0 0 0 5.5 5.5L14 13l4 1.5v2A1.5 1.5 0 0 1 16.5 18C10 18 4 12 4 5.5z"></path></svg>
                {{ $ritPhone ?: 'Not set' }}
            </span>
            <span class="rit-profile__line {{ $ritMobile ? '' : 'rit-profile__line--empty' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="3" width="10" height="18" rx="2"></rect><path d="M11 18.5h2"></path></svg>
                {{ $ritMobile ?: 'Not set' }}
            </span>
        </div>
    </div>

    <div class="rit-profile__col">
        <div class="rit-profile__label">Address</div>
        @if($ritAddressLines->isNotEmpty())
            <span class="rit-profile__line">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"></path><circle cx="12" cy="10" r="2.4"></circle></svg>
                <span>{!! $ritAddressLines->map(fn ($line) => e($line))->implode('<br>') !!}</span>
            </span>
        @else
            <span class="rit-profile__line rit-profile__line--missing">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"></path><circle cx="12" cy="10" r="2.4"></circle></svg>
                Not set yet
            </span>
        @endif
    </div>
</div>
