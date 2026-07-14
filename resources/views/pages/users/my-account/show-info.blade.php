@php
    $employeeTitle = isset($employee->title->name) ? $employee->title->name.' ' : '';
    $employeeName = trim($employeeTitle.($employee->first_name ?? '').' '.($employee->last_name ?? ''));
    $employeeName = $employeeName ?: ($employee->full_name ?? 'Employee');
    $nameForInitials = preg_replace('/^(Mr|Mrs|Ms|Miss|Dr)\.?\s+/i', '', $employeeName);
    $nameParts = preg_split('/\s+/', trim($nameForInitials), -1, PREG_SPLIT_NO_EMPTY);
    $firstInitial = isset($nameParts[0]) ? substr($nameParts[0], 0, 1) : 'E';
    $lastInitial = count($nameParts) > 1 ? substr($nameParts[count($nameParts) - 1], 0, 1) : '';
    $employeeInitials = strtoupper($firstInitial.$lastInitial);
    $photoUrl = $employee->brand_photo_url ?? $employee->photo_url ?? '';
    $employment = $employee->employment ?? null;
    $jobTitle = optional(optional($employment)->employeeJobTitle)->name;
    $department = optional(optional($employment)->department)->name;
    $jobLine = collect([$jobTitle, $department])->filter(fn ($item) => filled($item))->implode(' - ');
    $employeeNumber = optional($employment)->punch_number;
    $employeeStatus = (isset($employee->status) && in_array((string) $employee->status, ['0', 'Inactive', 'inactive'], true)) ? 'Inactive' : 'Active';
    $addressCityLine = collect([
        optional($employee->address)->city,
        optional($employee->address)->state,
        optional($employee->address)->post_code,
    ])->filter(fn ($item) => filled($item))->implode(', ');
    $addressLines = collect([
        optional($employee->address)->address_line_1,
        optional($employee->address)->address_line_2,
        $addressCityLine,
        filled(optional($employee->address)->country) ? strtoupper(optional($employee->address)->country) : null,
    ])->filter(fn ($item) => filled($item))->values();
    $addressInline = $addressLines->isNotEmpty() ? $addressLines->implode(', ') : 'Not Set Yet!';
    $valueOrEmpty = fn ($value) => filled($value) ? $value : '-';
    $emailLines = collect([
        $employee->email ?? null,
        optional($employment)->email,
    ])->filter(fn ($item) => filled($item))->map(fn ($item) => strtolower($item))->values();
@endphp

<section class="my-account-profile-card">
    <div class="my-account-profile-card__hero">
        <div class="my-account-profile-card__content">
            <div class="my-account-profile-card__avatar">
                @if(!empty($photoUrl))
                    <img src="{{ $photoUrl }}" alt="{{ $employeeName }}">
                @else
                    <span>{{ $employeeInitials }}</span>
                @endif
            </div>
            <div class="my-account-profile-card__identity">
                <div class="my-account-profile-card__name-row">
                    <h1>{{ $employeeName }}</h1>
                    <span class="my-account-status my-account-status--{{ strtolower($employeeStatus) }}">
                        <span></span>{{ $employeeStatus }}
                    </span>
                </div>
                <div class="my-account-profile-card__meta">
                    <span>{{ $jobLine ?: 'Staff Member' }}</span>
                    @if(filled($employeeNumber))
                        <span class="my-account-profile-card__number">Employee No. {{ $employeeNumber }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="my-account-profile-card__strip">
        <div class="my-account-profile-card__info">
            <span class="my-account-profile-card__info-icon">
                <i data-lucide="mail"></i>
            </span>
            <div>
                <span>Email</span>
                <div class="my-account-profile-card__email-list">
                    @forelse($emailLines as $emailLine)
                        <strong>{{ $emailLine }}</strong>
                    @empty
                        <strong class="is-empty">-</strong>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="my-account-profile-card__info">
            <span class="my-account-profile-card__info-icon">
                <i data-lucide="phone"></i>
            </span>
            <div>
                <span>Phone</span>
                <strong>{{ $valueOrEmpty($employee->telephone ?? null) }}</strong>
            </div>
        </div>
        <div class="my-account-profile-card__info">
            <span class="my-account-profile-card__info-icon">
                <i data-lucide="smartphone"></i>
            </span>
            <div>
                <span>Mobile</span>
                <strong>{{ $valueOrEmpty($employee->mobile ?? null) }}</strong>
            </div>
        </div>
        <div class="my-account-profile-card__info my-account-profile-card__info--address">
            <span class="my-account-profile-card__info-icon">
                <i data-lucide="map-pin"></i>
            </span>
            <div>
                <span>Address</span>
                <strong>{{ $addressInline }}</strong>
            </div>
        </div>
    </div>

    @include('pages.users.my-account.show-menu')
</section>
