{{--
    The applicant's e-signature page, opened from the email / SMS link
    (ApplicanESignatureController@index). A standalone page on the approved
    "LCC E-Signature" design: the application read back section by section,
    the two consents, a signature (drawn or uploaded) and Submit. The script
    (resources/js/esignature.js) keeps the existing behaviour - location
    captured on load and on submit, signature posted as a PNG data URL to
    applicant.e.signature.store, the audit trail written server-side.
--}}
@php
    $dash = '—';
    $v = fn($x) => (isset($x) && trim((string) $x) !== '') ? $x : $dash;
    $date = function ($x) use ($dash) {
        if (!isset($x) || trim((string) $x) === '') return $dash;
        foreach (['d-m-Y', 'Y-m-d', 'Y-m-d H:i:s'] as $f) { try { return \Carbon\Carbon::createFromFormat($f, (string) $x)->format('d M Y'); } catch (\Throwable $e) {} }
        try { return \Carbon\Carbon::parse($x)->format('d M Y'); } catch (\Throwable $e) { return (string) $x; }
    };
    $month = function ($x) use ($dash) { /* employment dates are stored as MM-YYYY */
        if (!isset($x) || trim((string) $x) === '') return $dash;
        try { return \Carbon\Carbon::createFromFormat('d-m-Y', '01-'.$x)->format('M Y'); } catch (\Throwable $e) { return (string) $x; }
    };
    $yn = fn($x) => (isset($x) && (int) $x === 1) ? 'Yes' : 'No';
    $addr = function ($o) use ($dash) {
        if (!$o) return $dash;
        $parts = array_filter([$o->address_line_1 ?? null, $o->address_line_2 ?? null, $o->city ?? null, $o->state ?? null, $o->post_code ?? null, $o->country ?? null], fn($p) => isset($p) && trim((string) $p) !== '');
        return $parts ? implode(', ', $parts) : $dash;
    };

    $fullName = trim(implode(' ', array_filter([optional($applicant->title)->name, $applicant->first_name, $applicant->last_name])));
    $isOffer = (int) $applicant->status_id >= 6;
    $other = $applicant->other; $course = $applicant->course; $contact = $applicant->contact; $kin = $applicant->kin;
    $hasDisability = isset($other->disability_status) && (int) $other->disability_status === 1;
    $hasQuals = isset($other->is_edication_qualification) && (int) $other->is_edication_qualification === 1;
    $showJobs = isset($other->employment_status) && !in_array($other->employment_status, ['Unemployed', 'Contractor', 'Consultant', 'Office Holder'], true);
    $conv = $applicant->criminalConviction;
    $convicted = isset($conv->have_you_been_convicted) && (int) $conv->have_you_been_convicted === 1;

    $logoFile = \App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    $logo = ($logoFile && Storage::disk('local')->exists('public/'.$logoFile)) ? url('storage/'.$logoFile) : asset('build/assets/images/red_and_white_logo.png');
    $portalUrl = route('applicant.dashboard');
    $email = optional($applicant->users)->email;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $isOffer ? 'Offer acceptance' : 'Application form' }} &middot; {{ $fullName }} &middot; London Churchill College</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('build/assets/images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,650;12..96,700&family=Hanken+Grotesk:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@500;600&family=Source+Serif+4:ital,opsz,wght@0,8..60,500;0,8..60,600;1,8..60,500&display=swap" rel="stylesheet">
    @vite(['resources/css/esignature.css'])
</head>
<body class="esign-body">
    <div class="esign-page">
        <div class="esign-doc">
            <header class="esign-top">
                <a class="esign-logo" href="https://lcc.ac.uk" aria-label="London Churchill College">
                    <img src="{{ $logo }}" alt="London Churchill College">
                </a>
                <div style="text-align:right">
                    <span class="pill muted">{{ $isOffer ? 'Offer acceptance' : 'Application form' }}</span>
                    @if(!empty($applicant->application_no))
                        <div class="ref">Application Ref: <b class="mono">{{ $applicant->application_no }}</b></div>
                    @endif
                </div>
            </header>

            <div id="esignBody">
                @if($alreadyAccepted)
                    <div class="esign-done">
                        <div class="notice ok" style="width:100%">
                            <b>This e-signature has already been recorded</b>
                            <div>Admissions have your signed document. Nothing more is needed from you here.</div>
                        </div>
                        <a class="btn btn-primary" href="{{ $portalUrl }}">Back to your portal</a>
                    </div>
                @else
                    <form id="esignForm" method="POST" action="{{ route('applicant.e.signature.store', $hashedId) }}" novalidate>
                        @csrf
                        <input type="hidden" name="applicant_id" value="{{ $hashedId }}">
                        <input type="hidden" name="signature" id="signatureInput" value="">

                        <div class="esign-intro">
                            <div class="notice">Please review all the details of the application carefully and sign if everything is correct. If you notice any incorrect information, do not sign the document and kindly inform us.</div>
                        </div>

                        <div class="esign-sections">
                            <details class="accordion" open>
                                <summary>Personal details<span class="caret">&#9662;</span></summary>
                                <div class="body">
                                    <dl class="kv">
                                        <dt>Full name</dt><dd>{{ $v($fullName) }}</dd>
                                        <dt>Date of birth</dt><dd>{{ $date($applicant->date_of_birth) }}</dd>
                                        <dt>Gender</dt><dd>{{ $v(optional($applicant->sexid)->name) }}</dd>
                                        <dt>Nationality</dt><dd>{{ $v(optional($applicant->nation)->name) }}</dd>
                                        <dt>Country of birth</dt><dd>{{ $v(optional($applicant->country)->name) }}</dd>
                                        <dt>Ethnicity</dt><dd>{{ $v(optional(optional($other)->ethnicity)->name) }}</dd>
                                        <dt>Disability status</dt><dd>{{ $hasDisability ? 'Yes' : 'No' }}</dd>
                                        @if($hasDisability)
                                            <dt>Allowance claimed</dt><dd>{{ $yn($other->disabilty_allowance ?? 0) }}</dd>
                                            <dt>Disabilities</dt><dd>{{ $v(collect($applicant->disability ?? [])->map(fn($d) => optional($d->disabilities)->name)->filter()->implode(', ')) }}</dd>
                                        @endif
                                    </dl>
                                </div>
                            </details>

                            <details class="accordion">
                                <summary>Contact details<span class="caret">&#9662;</span></summary>
                                <div class="body">
                                    <dl class="kv">
                                        <dt>Email</dt><dd>{{ $v($email) }}</dd>
                                        @if(!empty(optional($contact)->home))<dt>Home phone</dt><dd>{{ $contact->home }}</dd>@endif
                                        <dt>Mobile</dt><dd>{{ $v(optional($contact)->mobile) }}</dd>
                                        <dt>Address</dt><dd>{{ $addr($contact) }}</dd>
                                    </dl>
                                </div>
                            </details>

                            <details class="accordion">
                                <summary>Next of kin<span class="caret">&#9662;</span></summary>
                                <div class="body">
                                    <dl class="kv">
                                        <dt>Name</dt><dd>{{ $v(optional($kin)->name) }}@if(optional(optional($kin)->relation)->name) ({{ $kin->relation->name }})@endif</dd>
                                        <dt>Mobile</dt><dd>{{ $v(optional($kin)->mobile) }}</dd>
                                        <dt>Email</dt><dd>{{ $v(optional($kin)->email) }}</dd>
                                        <dt>Address</dt><dd>{{ $addr($kin) }}</dd>
                                    </dl>
                                </div>
                            </details>

                            <details class="accordion" open>
                                <summary>Proposed course<span class="caret">&#9662;</span></summary>
                                <div class="body">
                                    <dl class="kv">
                                        <dt>Start</dt><dd>{{ $v(optional(optional($course)->semester)->name) }}</dd>
                                        <dt>Course</dt><dd>{{ $v(optional(optional(optional($course)->creation)->course)->name) }}</dd>
                                        <dt>Venue</dt><dd>{{ $v(optional(optional($course)->venue)->name) }}</dd>
                                        <dt>Funding</dt><dd>{{ optional($course)->student_loan === 'Others' ? 'Other: '.$v($course->other_funding) : $v(optional($course)->student_loan) }}</dd>
                                        @if(optional($course)->student_loan === 'Student Loan')
                                            <dt>SFE applied</dt><dd>{{ $yn($course->student_finance_england ?? 0) }}</dd>
                                            @if((int) ($course->student_finance_england ?? 0) === 1)
                                                <dt>Funds received</dt><dd>{{ $yn($course->fund_receipt ?? 0) }}</dd>
                                            @endif
                                            <dt>Previous SLC / government loan</dt><dd>{{ $yn($course->applied_received_fund ?? 0) }}</dd>
                                        @endif
                                        @if((int) (optional(optional($course)->creation)->has_evening_and_weekend ?? 0) === 1)
                                            <dt>Evening &amp; weekend</dt><dd>{{ $yn($course->full_time ?? 0) }}</dd>
                                        @endif
                                        <dt>Fee eligibility</dt><dd>{{ $v(optional(optional($applicant->feeeligibility)->elegibility)->name) }}</dd>
                                    </dl>
                                </div>
                            </details>

                            <details class="accordion">
                                <summary>Educational qualification<span class="caret">&#9662;</span></summary>
                                <div class="body">
                                    @if($hasQuals && $applicant->quals && $applicant->quals->count())
                                        <div class="tablewrap">
                                            <table class="table">
                                                <thead><tr><th>Qualification</th><th>Awarding body</th><th>Subjects</th><th>Result</th><th>Awarded</th></tr></thead>
                                                <tbody>
                                                    @foreach($applicant->quals as $q)
                                                        <tr><td>{{ $v($q->highest_academic) }}</td><td>{{ $v($q->awarding_body) }}</td><td>{{ $v($q->subjects) }}</td><td>{{ $v($q->result) }}</td><td>{{ $date($q->degree_award_date) }}</td></tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <dl class="kv"><dt>Formal qualification</dt><dd>No</dd></dl>
                                    @endif
                                </div>
                            </details>

                            <details class="accordion">
                                <summary>Employment history<span class="caret">&#9662;</span></summary>
                                <div class="body">
                                    <dl class="kv"><dt>Status</dt><dd>{{ $v(optional($other)->employment_status) }}</dd></dl>
                                    @if($showJobs && $applicant->employment && $applicant->employment->count())
                                        @foreach($applicant->employment as $m)
                                            @php $ref = optional($m->reference)->first(); @endphp
                                            <dl class="kv">
                                                <dt>Organisation</dt><dd>{{ $v($m->company_name) }}</dd>
                                                <dt>Phone</dt><dd>{{ $v($m->company_phone) }}</dd>
                                                <dt>Position</dt><dd>{{ $v($m->position) }}</dd>
                                                <dt>From</dt><dd>{{ $month($m->start_date) }}</dd>
                                                <dt>To</dt><dd>{{ empty($m->end_date) ? 'Continuing' : $month($m->end_date) }}</dd>
                                                <dt>Contact person</dt><dd>{{ $v(optional($ref)->name) }}@if(optional($ref)->position) ({{ $ref->position }})@endif</dd>
                                                <dt>Contact phone</dt><dd>{{ $v(optional($ref)->phone) }}</dd>
                                                <dt>Address</dt><dd>{{ $addr($m) }}</dd>
                                            </dl>
                                        @endforeach
                                    @endif
                                </div>
                            </details>

                            <details class="accordion">
                                <summary>Residency status &amp; criminal convictions<span class="caret">&#9662;</span></summary>
                                <div class="body">
                                    <dl class="kv">
                                        <dt>Residency</dt><dd>{{ $v(optional(optional($applicant->residency)->residencyStatus)->name) }}</dd>
                                        <dt>Convicted</dt><dd>{{ $convicted ? 'Yes' : 'No' }}</dd>
                                        @if($convicted)<dt>Details</dt><dd>{{ $v($conv->criminal_conviction_details) }}</dd>@endif
                                    </dl>
                                </div>
                            </details>

                            <details class="accordion">
                                <summary>Others<span class="caret">&#9662;</span></summary>
                                <div class="body">
                                    <dl class="kv"><dt>Referral code</dt><dd class="mono">{{ $v($applicant->referral_code) }}</dd></dl>
                                </div>
                            </details>

                            <details class="accordion" open>
                                <summary>Consents<span class="caret">&#9662;</span></summary>
                                <div class="body">
                                    <div class="esign-consents" style="padding:8px 0 6px">
                                        <label class="opt chk">
                                            <input class="sr" type="checkbox" id="video_consent" name="video_consent" value="1">
                                            <span class="box"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                                            <span>I hereby authorise the filming and utilisation of recordings featuring my person, conducted by members or staff of London Churchill College, exclusively for admission purposes.</span>
                                        </label>
                                        <label class="opt chk">
                                            <input class="sr" type="checkbox" id="declaration" name="declaration" value="1">
                                            <span class="box"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                                            <span>I hereby verify the accuracy and truthfulness of the information provided in this form to the best of my knowledge. It is my responsibility to stay informed about the terms and conditions as well as the policies of the college, and I commit to comply with them. I have thoroughly reviewed the college's terms and conditions and student privacy policy and pledge to adhere to them throughout my entire course of study.</span>
                                        </label>
                                    </div>
                                </div>
                            </details>
                        </div>

                        <div class="esign-foot">
                            <button type="button" class="sign-area" id="signArea" aria-label="Add signature">
                                <span class="sign-tag">Sign</span>
                                <span class="sign-cta" id="signCta">
                                    <span class="dz-ico"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.6 7.6"/></svg></span>
                                    <b>Click here to sign</b>
                                    <small>Draw your signature or upload an image</small>
                                </span>
                                <img id="signPreview" src="" alt="Your signature" hidden>
                                <span class="sign-change" id="signChange" hidden>Click to change</span>
                            </button>
                            <div class="notice bad" id="esignError" hidden></div>
                            <div class="esign-actions">
                                <a class="btn btn-ghost" href="{{ $portalUrl }}">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="esignSubmit">Submit</button>
                            </div>
                            <p class="esign-audit">Your signature, the time, your IP address, browser and location are recorded as an audit trail.</p>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- what the page shows once the signature is recorded --}}
    <template id="esignDoneTpl">
        <div class="esign-done">
            <div class="notice ok" style="width:100%">
                <b>Thank you &mdash; your signature has been recorded</b>
                <div>Admissions have been notified{{ $email ? ' and will write to '.$email.' about the next steps' : '' }}. You can close this page.</div>
            </div>
            <a class="btn btn-primary" href="{{ $portalUrl }}">Back to your portal</a>
        </div>
    </template>

    {{-- signature: draw or upload --}}
    <div class="overlay" id="sigModal" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-label="Add signature">
            <header><h3>Add signature</h3><button type="button" class="btn btn-ghost btn-sm" id="sigClose" aria-label="Close">&#10005;</button></header>
            <div class="body" style="display:grid;gap:14px">
                <div class="tabs" id="sigTabs">
                    <button type="button" class="on" data-tab="draw">Draw</button>
                    <button type="button" data-tab="image">Image</button>
                </div>
                <div id="sigDraw" style="display:grid;gap:10px">
                    <div class="sig-pad"><canvas id="sigCanvas"></canvas><span class="sig-hint" id="sigHint">Sign here with your mouse or finger</span></div>
                    <div style="text-align:center"><button type="button" class="btn-link" id="sigClear">Clear signature</button></div>
                </div>
                <div id="sigImage" hidden style="display:grid;gap:10px">
                    <label class="sig-pad drop" id="sigDrop">
                        <input type="file" accept="image/png,image/jpeg,image/jpg,.png,.jpg,.jpeg" class="sr-only" id="sigFile">
                        <img id="sigImgPreview" src="" alt="Signature preview" style="max-height:200px;max-width:100%" hidden>
                        <span class="btn btn-ghost" id="sigPick">Select image</span>
                    </label>
                    <div style="text-align:center;font-size:13px;color:var(--muted)" id="sigImgNote">Select or drop an image (PNG or JPG)</div>
                </div>
            </div>
            <footer>
                <button type="button" class="btn btn-ghost" id="sigCancel">Cancel</button>
                <button type="button" class="btn btn-primary" id="sigDone" disabled>Done</button>
            </footer>
        </div>
    </div>

    {{-- location: asked on load and needed to submit --}}
    <div class="overlay" id="locModal" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-label="Location permission required" style="width:min(460px,100%)">
            <header><h3 id="locTitle">Location permission required</h3></header>
            <div class="body"><p id="locText">We record where the document is signed as part of the audit trail. Please allow location access to continue.</p></div>
            <footer><button type="button" class="btn btn-primary" id="locAllow">Allow location</button></footer>
        </div>
    </div>

    @routes
    @vite(['resources/js/esignature.js'])
    <script>
        /* in-app browsers (Facebook, Instagram, Messenger...) block location and uploads */
        if (/FBAN|FBAV|Instagram|Line|Messenger|wv/.test(navigator.userAgent)) {
            alert("To continue, please open this link in Chrome / Safari for full functionality (location and form submission).");
        }
    </script>
</body>
</html>
