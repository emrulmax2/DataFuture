@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        /* One helper for the six places an address is printed, so a missing
           line never leaves a stray comma behind. */
        $formatAddress = function ($address) {
            if (!$address) {
                return null;
            }

            $parts = array_filter([
                $address->address_line_1 ?? null,
                $address->address_line_2 ?? null,
                $address->city ?? null,
                $address->state ?? null,
                $address->post_code ?? null,
                $address->country ?? null,
            ], function ($part) {
                return !empty(trim((string) $part));
            });

            return count($parts) ? implode(', ', $parts) : null;
        };

        $termAddress = (isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
            ? $formatAddress($student->contact->termaddress) : null;
        $permanentAddress = (isset($student->contact->permanent_address_id) && $student->contact->permanent_address_id > 0)
            ? $formatAddress($student->contact->permaddress) : null;
        $kinAddress = (isset($student->kin->address_id) && $student->kin->address_id > 0)
            ? $formatAddress($student->kin->address) : null;

        $blank = '—';
    @endphp

    <div class="spf-page-head">
        <h1 class="spf-h1">Profile</h1>
    </div>


    {{-- The contact panel below opens these; show-info used to supply them. --}}
    @include('pages.students.frontend.modals.index')

    <div class="spf-panel">
        <div class="spf-panel__head">
            <h2 class="spf-h3">Personal information</h2>
        </div>
        <div class="spf-fieldgrid">
            <div class="spf-field">
                <div class="spf-field__label">Full name</div>
                <div class="spf-field__value">{{ (isset($student->title->name) ? $student->title->name.' ' : '') }}{{ $student->first_name }} {{ $student->last_name }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Date of birth</div>
                <div class="spf-field__value">{{ !empty($student->date_of_birth) ? date('j F Y', strtotime($student->date_of_birth)) : $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Sex identifier / gender</div>
                <div class="spf-field__value">{{ $student->sexid->name ?? $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Nationality</div>
                <div class="spf-field__value">{{ $student->nation->name ?? $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Country of birth</div>
                <div class="spf-field__value">{{ $student->country->name ?? $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Ethnicity</div>
                <div class="spf-field__value">{{ $student->other->ethnicity->name ?? $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Care leaver</div>
                <div class="spf-field__value">{{ optional($student->other->leaver)->name ?? $blank }}</div>
            </div>
        </div>
    </div>

    <div class="spf-panel">
        <div class="spf-panel__head">
            <h2 class="spf-h3">Other personal information</h2>
        </div>
        <div class="spf-fieldgrid">
            <div class="spf-field">
                <div class="spf-field__label">Sexual orientation</div>
                <div class="spf-field__value">{{ !empty($student->other->sexori->name) ? $student->other->sexori->name : $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Gender identity</div>
                <div class="spf-field__value">{{ !empty($student->other->gender->name) ? $student->other->gender->name : $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Religion or belief</div>
                <div class="spf-field__value">{{ !empty($student->other->religion->name) ? $student->other->religion->name : $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Disability status</div>
                <div class="spf-field__value">
                    @if(isset($student->other->disability_status) && $student->other->disability_status == 1)
                        <span class="spf-chip spf-chip--green">YES</span>
                    @else
                        <span class="spf-chip spf-chip--grey">NO</span>
                    @endif
                </div>
            </div>

            @if(isset($student->other->disability_status) && $student->other->disability_status == 1)
                <div class="spf-field">
                    <div class="spf-field__label">Disabilities</div>
                    <div class="spf-field__value">
                        @if(isset($student->disability) && count($student->disability) > 0)
                            <div class="spf-taglist">
                                @foreach($student->disability as $dis)
                                    <span class="spf-chip spf-chip--outline">{{ $dis->disabilities->name }}</span>
                                @endforeach
                            </div>
                        @else
                            {{ $blank }}
                        @endif
                    </div>
                </div>
                <div class="spf-field">
                    <div class="spf-field__label">Allowance claimed</div>
                    <div class="spf-field__value">
                        @if(isset($student->other->disabilty_allowance) && $student->other->disabilty_allowance == 1)
                            <span class="spf-chip spf-chip--green">YES</span>
                        @else
                            <span class="spf-chip spf-chip--grey">NO</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="spf-panel" id="residency-status">
        <div class="spf-panel__head">
            <h2 class="spf-h3">Residency status &amp; criminal convictions</h2>
        </div>
        <div class="spf-fieldgrid spf-fieldgrid--2">
            <div class="spf-field">
                <div class="spf-field__label">Residency status</div>
                <div class="spf-field__value">{{ optional(optional($student->residency)->residencyStatus)->name ?? $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Criminal conviction</div>
                <div class="spf-field__value">
                    @if(isset($student->criminalConviction->have_you_been_convicted) && (int) $student->criminalConviction->have_you_been_convicted === 1)
                        <span class="spf-chip spf-chip--rust">YES</span>
                    @elseif(isset($student->criminalConviction->have_you_been_convicted))
                        <span class="spf-chip spf-chip--grey">NO</span>
                    @else
                        {{ $blank }}
                    @endif
                </div>
            </div>
            @if(isset($student->criminalConviction->have_you_been_convicted) && (int) $student->criminalConviction->have_you_been_convicted === 1)
                <div class="spf-field spf-field--full">
                    <div class="spf-field__label">Conviction details</div>
                    <div class="spf-field__value">{{ !empty($student->criminalConviction->criminal_conviction_details) ? $student->criminalConviction->criminal_conviction_details : $blank }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="spf-panel">
        <div class="spf-panel__head">
            <h2 class="spf-h3">Contact &amp; correspondence</h2>
            <div class="spf-spacer"></div>
            <div class="spf-dd">
                <button type="button" class="spf-pillbtn" data-spf-dd="spfProfileContactMenu">UPDATE DETAILS &#9662;</button>
                <div id="spfProfileContactMenu" class="spf-dd__menu" style="width:212px">
                    <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#confirmPersonalEmailUpdateModal" class="spf-dd__item">
                        <i data-lucide="mail" class="w-4 h-4"></i> Change Email
                    </a>
                    <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#confirmPersonalMobileUpdateModal" class="spf-dd__item">
                        <i data-lucide="smartphone" class="w-4 h-4"></i> Change Mobile
                    </a>
                    <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#addressUpdateModal" class="spf-dd__item">
                        <i data-lucide="map-pin" class="w-4 h-4"></i> Change Address
                    </a>
                </div>
            </div>
        </div>
        <div class="spf-fieldgrid">
            <div class="spf-field">
                <div class="spf-field__label">Institutional / login email</div>
                <div class="spf-field__value">
                    {{ $student->users->email }}
                    @if($student->users->email_verified_at === null)
                        <span class="spf-chip spf-chip--rust">UNVERIFIED</span>
                    @elseif(isset($tempEmail->applicant_id) && $tempEmail->applicant_id > 0 && isset($tempEmail->status) && $tempEmail->status == 'Pending')
                        <span class="spf-chip spf-chip--cream">AWAITING VERIFICATION</span>
                        <div class="spf-field__value--muted">({{ $tempEmail->email }})</div>
                    @else
                        <span class="spf-chip spf-chip--green">VERIFIED</span>
                    @endif
                </div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Personal email</div>
                <div class="spf-field__value">
                    {{ !empty($student->contact->personal_email) ? $student->contact->personal_email : $blank }}
                    @if(!empty($student->contact->personal_email))
                        <span class="spf-chip spf-chip--green">VERIFIED</span>
                    @endif
                </div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Mobile</div>
                <div class="spf-field__value">
                    {{ !empty($student->contact->mobile) ? $student->contact->mobile : $blank }}
                    @if($student->contact->mobile_verification == 1)
                        <span class="spf-chip spf-chip--green">VERIFIED</span>
                    @else
                        <span class="spf-chip spf-chip--rust">UNVERIFIED</span>
                    @endif
                </div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Home phone</div>
                <div class="spf-field__value">{{ !empty($student->contact->home) ? $student->contact->home : $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Term time accommodation type</div>
                <div class="spf-field__value">{{ !empty($student->contact->ttacom->name) ? $student->contact->ttacom->name : $blank }}</div>
            </div>
            <div class="spf-field"></div>
            <div class="spf-field spf-field--full">
                <div class="spf-field__label">Term time / correspondence address</div>
                <div class="spf-field__value">
                    @if($termAddress)
                        {{ $termAddress }}
                    @else
                        <span style="color:var(--spf-rust);font-weight:600">Not set yet</span>
                    @endif
                </div>
            </div>
            <div class="spf-field spf-field--full">
                <div class="spf-field__label">Permanent address</div>
                <div class="spf-field__value">
                    @if($permanentAddress)
                        {{ $permanentAddress }}
                    @else
                        <span style="color:var(--spf-rust);font-weight:600">Not set yet</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="spf-panel">
        <div class="spf-panel__head">
            <h2 class="spf-h3">Course details</h2>
        </div>
        <div class="spf-fieldgrid">
            <div class="spf-field spf-field--full">
                <div class="spf-field__label">Course &amp; semester</div>
                <div class="spf-field__value">
                    {{ $student->crel->creation->course->name ?? $blank }}
                    @if(isset($student->crel->propose->semester->name)) &middot; {{ $student->crel->propose->semester->name }} @endif
                </div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Venue</div>
                <div class="spf-field__value">{{ !empty($venue) ? $venue : $blank }}</div>
            </div>
            @if(isset($studentCourseAvailability) && $studentCourseAvailability->count() > 0)
                @foreach($studentCourseAvailability as $availability)
                    <div class="spf-field">
                        <div class="spf-field__label">Course start</div>
                        <div class="spf-field__value">{{ $availability->course_start_date }}</div>
                    </div>
                    <div class="spf-field">
                        <div class="spf-field__label">Course end</div>
                        <div class="spf-field__value">{{ $availability->course_end_date }}</div>
                    </div>
                @endforeach
            @endif
            <div class="spf-field">
                <div class="spf-field__label">Awarding body</div>
                <div class="spf-field__value">{{ $student->crel->creation->course->body->name ?? 'Unknown' }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Awarding body reference</div>
                <div class="spf-field__value">{{ !empty($student->crel->abody->reference) ? $student->crel->abody->reference : $blank }}</div>
            </div>
        </div>
    </div>

    <div class="spf-panel">
        <div class="spf-panel__head">
            <h2 class="spf-h3">Next of kin</h2>
        </div>
        <div class="spf-fieldgrid">
            <div class="spf-field">
                <div class="spf-field__label">Name</div>
                <div class="spf-field__value">{{ !empty($student->kin->name) ? $student->kin->name : $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Relation</div>
                <div class="spf-field__value">{{ !empty($student->kin->relation->name) ? $student->kin->relation->name : $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Mobile</div>
                <div class="spf-field__value">{{ !empty($student->kin->mobile) ? $student->kin->mobile : $blank }}</div>
            </div>
            <div class="spf-field">
                <div class="spf-field__label">Email</div>
                <div class="spf-field__value">{{ !empty($student->kin->email) ? $student->kin->email : $blank }}</div>
            </div>
            <div class="spf-field spf-field--full">
                <div class="spf-field__label">Address</div>
                <div class="spf-field__value">
                    @if($kinAddress)
                        {{ $kinAddress }}
                    @else
                        <span style="color:var(--spf-rust);font-weight:600">Not set yet</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="spf-panel">
        <div class="spf-panel__head">
            <h2 class="spf-h3">Communication consent</h2>
        </div>
        @if(!empty($stdConsentIds) && $consent->count() > 0)
            <div class="spf-fieldgrid spf-fieldgrid--2">
                @foreach($consent as $con)
                    @if(in_array($con->id, $stdConsentIds))
                        <div class="spf-field">
                            <div class="spf-field__label">
                                <span class="spf-chip spf-chip--green" style="letter-spacing:0.06em">AGREED</span>
                            </div>
                            <div class="spf-field__value" style="font-weight:600;margin-top:6px">{{ $con->name }}</div>
                            <div class="spf-field__value spf-field__value--muted" style="font-size:12.5px;margin-top:4px">{{ $con->description }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="spf-empty">Consent has not been recorded yet.</div>
        @endif
    </div>
@endsection

@section('script')
    @vite('resources/js/student-frontend-dashboard.js')
@endsection
