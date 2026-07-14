@extends('../layout/my-account')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
    @php
        $valueOrEmpty = fn ($value) => filled($value) ? $value : '-';
        $employeeTitle = isset($employee->title->name) ? $employee->title->name.' ' : '';
        $employeeName = trim($employeeTitle.($employee->full_name ?? ''));
        $dateOfBirth = filled($employee->date_of_birth ?? null) ? date('jS M, Y', strtotime($employee->date_of_birth)) : null;
        $employeeAge = filled($employee->date_of_birth ?? null) ? ($employee->age ?? null) : null;
        $personalFields = [
            ['label' => 'Name', 'value' => $employeeName],
            ['label' => 'Date of Birth', 'value' => $dateOfBirth],
            ['label' => 'Age', 'value' => $employeeAge],
            ['label' => 'Sex Identifier / Gender', 'value' => $employee->sex->name ?? null],
            ['label' => 'Nationality', 'value' => $employee->nationality->name ?? null],
            ['label' => 'Ethnicity', 'value' => $employee->ethnicity->name ?? null],
            ['label' => 'NI Number', 'value' => $employee->ni_number ?? null],
            ['label' => 'Disabilities?', 'value' => $employee->disability_status ?? null],
            ['label' => 'Car Reg Number', 'value' => $employee->car_reg_number ?? null],
            ['label' => 'Driving Licence', 'value' => $employee->drive_license_number ?? null],
        ];

        $emergency = $emergencyContacts ?? null;
        $emergencyAddressCityLine = collect([
            optional(optional($emergency)->address)->city,
            optional(optional($emergency)->address)->state,
            optional(optional($emergency)->address)->post_code,
        ])->filter(fn ($item) => filled($item))->implode(', ');
        $emergencyAddressLines = collect([
            optional(optional($emergency)->address)->address_line_1,
            optional(optional($emergency)->address)->address_line_2,
            $emergencyAddressCityLine,
            filled(optional(optional($emergency)->address)->country) ? strtoupper(optional(optional($emergency)->address)->country) : null,
        ])->filter(fn ($item) => filled($item))->values();
        $emergencyFields = [
            ['label' => 'Name', 'value' => optional($emergency)->emergency_contact_name],
            ['label' => 'Relation', 'value' => optional(optional($emergency)->kin)->name],
            ['label' => 'Telephone', 'value' => optional($emergency)->emergency_contact_telephone],
            ['label' => 'Mobile', 'value' => optional($emergency)->emergency_contact_mobile],
            ['label' => 'Email', 'value' => optional($emergency)->emergency_contact_email],
        ];
    @endphp

    @include('pages.users.my-account.show-info')

    <section class="my-account-card my-account-card--profile" data-screen-label="Personal Details">
        <div class="my-account-card__header">
            <span class="my-account-card__icon">
                <i data-lucide="user"></i>
            </span>
            <h2>Personal Details</h2>
        </div>

        <div class="my-account-field-grid">
            @foreach($personalFields as $field)
                <div class="my-account-field">
                    <span>{{ $field['label'] }}</span>
                    <strong class="{{ filled($field['value']) ? '' : 'is-empty' }}">{{ $valueOrEmpty($field['value']) }}</strong>
                </div>
            @endforeach

            @if(isset($employee->disability_status) && $employee->disability_status == 'Yes')
                <div class="my-account-field my-account-field--wide">
                    <span>Disabilities</span>
                    @if(isset($employee->disability) && !empty($employee->disability))
                        <ul class="my-account-check-list">
                            @foreach($employee->disability as $disability)
                                <li>
                                    <i data-lucide="check-circle"></i>
                                    {{ $disability->name }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <strong class="is-empty">-</strong>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <section class="my-account-card my-account-card--emergency" data-screen-label="Emergency Contacts">
        <div class="my-account-card__header">
            <span class="my-account-card__icon">
                <i data-lucide="shield"></i>
            </span>
            <h2>Emergency Contacts</h2>
        </div>

        <div class="my-account-emergency-grid">
            <div class="my-account-detail-list">
                @foreach($emergencyFields as $field)
                    <span>{{ $field['label'] }}</span>
                    <strong class="{{ filled($field['value']) ? '' : 'is-empty' }}">{{ $valueOrEmpty($field['value']) }}</strong>
                @endforeach
            </div>

            <div class="my-account-address-block">
                <i data-lucide="map-pin"></i>
                <div>
                    @if($emergencyAddressLines->isNotEmpty())
                        @foreach($emergencyAddressLines as $addressLine)
                            <span>{{ $addressLine }}</span>
                        @endforeach
                    @else
                        <span class="is-empty">Not Set Yet!</span>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
