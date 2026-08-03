@php
    $profileAgentUser = $employee->AgentUser ?? ($user ?? $userData ?? null);
    $agentProfileName = trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
    $agentProfileName = $agentProfileName !== '' ? $agentProfileName : 'Agent';
    $agentProfileOrganisation = trim((string) ($employee->organization ?? ''));
    $agentProfileCode = trim((string) ($employee->code ?? ''));
    $agentProfileEmail = trim((string) ($profileAgentUser->email ?? $employee->email ?? ''));
    $agentProfileMobile = trim((string) ($employee->mobile ?? ''));
    $agentProfileKind = !empty($profileAgentUser?->parent_id) ? 'Sub Agent' : 'Agent';
    $agentProfileActive = (int) ($profileAgentUser->active ?? 0) === 1;
    $agentProfileVerified = !empty($profileAgentUser?->email_verified_at);

    if(empty($agentProfileInitials)) {
        $agentWords = preg_split('/\s+/', preg_replace('/\s+/', ' ', $agentProfileName));
        $agentProfileInitials = strtoupper(mb_substr($agentWords[0] ?? 'A', 0, 1).mb_substr(count($agentWords) > 1 ? end($agentWords) : ($agentWords[0] ?? 'G'), 0, 1));
    }

    $agentAddress = $employee->address ?? null;
    $agentAddressLines = [];
    foreach ([
        $agentAddress?->address_line_1,
        $agentAddress?->address_line_2,
        collect([$agentAddress?->city, $agentAddress?->state])->filter()->implode(', '),
        $agentAddress?->post_code,
        $agentAddress?->country,
    ] as $addressPart) {
        $addressPart = trim((string) $addressPart);
        if($addressPart !== '') {
            $agentAddressLines[] = $addressPart;
        }
    }
    $agentHasAddress = !empty($agentAddressLines);
@endphp

<section class="agm-profile-hero">
    <div class="agm-profile-hero__copy">
        <span class="agm-profile-hero__icon" aria-hidden="true">
            <i data-lucide="user"></i>
        </span>
        <div>
            <span class="agm-eyebrow">{{ $agentProfileKind }} Profile</span>
            <h1>Profile of <span>{{ $agentProfileName }}</span></h1>
            <p>{{ $agentProfileOrganisation ?: 'Organisation not set' }} @if($agentProfileCode !== '') &middot; Code {{ $agentProfileCode }} @endif</p>
        </div>
    </div>

    @if(isset(auth()->user()->priv()['login_as_user']) && auth()->user()->priv()['login_as_user'] == 1)
        <a target="__blank" href="{{ route('impersonate', ['id' => $employee->agent_user_id, 'guardName' => 'agent']) }}" class="agm-btn agm-btn--dark">
            <i data-lucide="log-in"></i>
            Login As Agent
        </a>
    @endif
</section>

<section class="agm-profile-card">
    <div class="agm-profile-card__grid">
        <div class="agm-profile-card__pane agm-profile-card__pane--identity">
            <span class="agm-profile-avatar" style="background: #0f252d;">
                @if(!empty($agentProfilePhotoUrl))
                    <img src="{{ $agentProfilePhotoUrl }}" alt="{{ $agentProfileName }}">
                @else
                    {{ $agentProfileInitials }}
                @endif
            </span>
            <div class="agm-profile-identity">
                <span>Organisation</span>
                <strong>{{ $agentProfileOrganisation ?: 'Not set' }}</strong>
                <b>{{ $agentProfileName }}</b>
                <div class="agm-profile-chips">
                    @if($agentProfileCode !== '')
                        <span class="agm-profile-chip agm-profile-chip--gold">
                            <i data-lucide="credit-card"></i>
                            {{ $agentProfileCode }}
                        </span>
                    @endif
                    <span class="agm-profile-chip {{ $agentProfileActive ? 'agm-profile-chip--green' : 'agm-profile-chip--red' }}">
                        <i></i>
                        {{ $agentProfileActive ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="agm-profile-card__pane">
            <div class="agm-profile-pane-head">
                <span>
                    <i data-lucide="phone"></i>
                    Contact Details
                </span>
                <button data-id="{{ $employee->id }}" data-type="employee" data-tw-toggle="modal" data-tw-target="#editContactModal" class="editPopupToggler agm-profile-mini-btn" type="button">
                    <i data-lucide="pencil"></i>
                    Edit
                </button>
            </div>

            <div class="agm-profile-contact-list">
                <div class="agm-profile-contact-card">
                    <span class="agm-profile-contact-icon"><i data-lucide="mail"></i></span>
                    <div>
                        <small>Email</small>
                        <strong>{{ $agentProfileEmail ?: 'Not set' }}</strong>
                    </div>
                    @if($agentProfileVerified)
                        <span class="agm-profile-verified is-verified">
                            <i data-lucide="check"></i>
                            Verified
                        </span>
                    @else
                        <span class="agm-profile-verified is-unverified">
                            <i data-lucide="x"></i>
                            Unverified
                        </span>
                        @if(!empty($profileAgentUser?->id))
                            <form id="resendverification-staff" method="post" action="" class="agm-profile-resend-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $profileAgentUser->id }}" />
                                <button type="submit" id="resend-mail-agent" class="agm-profile-resend-btn">
                                    <i data-lucide="send" class="theSend"></i>
                                    <i data-loading-icon="oval" data-color="white" class="theLoading hidden"></i>
                                    Resend
                                </button>
                            </form>
                        @endif
                    @endif
                </div>

                <div class="agm-profile-contact-card agm-profile-contact-card--muted">
                    <span class="agm-profile-contact-icon"><i data-lucide="phone"></i></span>
                    <div>
                        <small>Mobile</small>
                        <strong>{{ $agentProfileMobile ?: 'Not set' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="agm-profile-card__pane addressWrap" id="employeeAddress">
            <div class="agm-profile-pane-head">
                <span>
                    <i data-lucide="map-pin"></i>
                    Address
                </span>
                <button data-id="{{ $employee->address_id }}" data-type="employee" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler agm-profile-mini-btn" type="button">
                    <i data-lucide="pencil"></i>
                    Edit
                </button>
                <input type="hidden" class="address_id_field" value="{{ (int) ($employee->address_id ?? 0) }}">
            </div>

            @if($agentHasAddress)
                <div class="agm-profile-address-card is-set">
                    <i data-lucide="map-pin"></i>
                    <div class="addresses">
                        @foreach($agentAddressLines as $line)
                            <span>{{ $line }}</span>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="agm-profile-address-card">
                    <strong>
                        <i data-lucide="map-pin"></i>
                        Not Set Yet!
                    </strong>
                    <p>No registered address on file for this agent.</p>
                    <button data-id="{{ $employee->address_id }}" data-type="employee" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler agm-btn agm-btn--primary" type="button">
                        <i data-lucide="plus"></i>
                        Add Address
                    </button>
                    <input type="hidden" class="address_id_field" value="{{ (int) ($employee->address_id ?? 0) }}">
                </div>
            @endif
        </div>
    </div>

    @include('pages.agent.profile.show-menu')
</section>

<div id="addStudentPhotoModal" class="modal agm-agent-modal agm-profile-photo-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Upload Profile Photo</h2>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x"></i>
                </a>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('employee.upload.photo') }}" class="dropzone agm-profile-dropzone" id="addStudentPhotoForm" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input name="documents" type="file" />
                    </div>
                    <div class="dz-message" data-dz-message>
                        <span class="agm-profile-dropzone__icon">
                            <i data-lucide="camera"></i>
                        </span>
                        <strong>Drop image here or click to upload.</strong>
                        <small>Select a JPG, PNG, or GIF image. Max file size should be 5MB.</small>
                    </div>
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                    <i data-lucide="x"></i>
                    Cancel
                </button>
                <button type="button" id="uploadStudentPhotoBtn" class="btn btn-primary">
                    <i data-lucide="upload"></i>
                    Upload
                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4">
                        <g fill="none" fill-rule="evenodd">
                            <g transform="translate(1 1)" stroke-width="4">
                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                </path>
                            </g>
                        </g>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
