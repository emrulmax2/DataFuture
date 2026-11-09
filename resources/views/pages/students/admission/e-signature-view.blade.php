@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        The sign request ("View request") on the admission portal shell: the
        request card (from / file owner / initialised / finalised), the
        signature, the two signers with their verification details and maps,
        document details, signing order and the audit trail
        (applicant_e_signature_events). Download signed PDF keeps
        #downloadEsignBtn (admission-global.js → applicant.e.signature.download).
    --}}
    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    @php
        $adpStatusId = (int) ($applicant->status_id ?? 0);
        $adpPriv = auth()->user()->priv();
        $adpNext = \App\Support\AdmissionStatusFlow::next($applicant, $allStatuses, $adpPriv);
        $adpStatusHelp = \App\Support\AdmissionStatusFlow::HELP;
        $adpTab = 'esign';

        $adpPaths = [
            'sign' => '<path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.6 7.6"/><circle cx="11" cy="11" r="2"/>',
            'send' => '<path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
            'ok' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>',
            'cog' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
            'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
            'flag' => '<path d="M4 22V4a1 1 0 0 1 1-1h11l-1 4 1 4H5"/>',
            'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
            'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
            'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
            'mailopen' => '<path d="M3 9.5 12 3l9 6.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="m3 9.5 9 6 9-6"/>',
            'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
            'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'lock' => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
            'edit' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
            'note' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
            'back' => '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
        ];
        $adpIcon = fn ($name, $size = 16) => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$adpPaths[$name].'</svg>';
        $adpSpin = '<svg class="adm-btn-loader adp-btn-spin" style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".4" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';

        $adpLong = function ($value) {
            if (empty($value)) return null;
            try { return \Carbon\Carbon::parse($value); } catch (\Throwable $e) { return null; }
        };
        $adpStamp = fn ($value) => ($d = $adpLong($value)) ? $d->format('D jS M, Y · H:i') : '—';
        $adpDay = fn ($value) => ($d = $adpLong($value)) ? $d->format('D jS M, Y') : '—';
        $adpTime = fn ($value) => ($d = $adpLong($value)) ? $d->format('H:i').' '.$d->format('T') : '';

        $adminUser = $adminEsign?->user;
        $applicantEmail = $applicant->users->email ?? '—';
        $applicantName = trim(implode(' ', array_filter([$applicant->title->name ?? null, $applicant->first_name ?? null, $applicant->last_name ?? null]))) ?: 'Applicant';
        $adminEmail = $adminUser?->email ?? ($adminEsign?->smtp_email ?: '—');
        $adminName = $adminUser?->full_name ?? $adminUser?->name ?? 'London Churchill College';
        $fromEmail = $adminEsign?->smtp_email ?: '—';
        $adminIni = \App\Support\Avatar::initialsOnly($adminName);
        $applicantIni = \App\Support\Avatar::initialsOnly($applicantName);
        // Real photos when they exist (employee / user photo, applicant photo), initials otherwise.
        $adpPhoto = function ($url) { return !empty($url) && !\App\Support\Avatar::isGenerated($url) ? $url : null; };
        try { $adminPhoto = $adpPhoto($adminUser?->employee?->photo_url ?: $adminUser?->photo_url); } catch (\Throwable $e) { $adminPhoto = null; }
        try { $applicantPhoto = $adpPhoto($applicant->photo_url); } catch (\Throwable $e) { $applicantPhoto = null; }
        // Map of where each signer was: the Google static map when coordinates were recorded, the generic map otherwise.
        $adpMapFallback = asset('build/assets/images/report_icons/google-map.jpg');
        $adminMapSrc = $adminMap ?: $adpMapFallback;
        $applicantMapSrc = $applicantMap ?: $adpMapFallback;
        // How the request came about: the staff button, or the application submission (applicant / agent).
        $createdEvent = $applicantEsignEvents->first(fn ($e) => $e->event_type === 'sign_request_created');
        $trigger = $createdEvent->extra_field ?? null;
        $triggerText = null;
        if (is_array($trigger) && !empty($trigger['trigger'])) {
            $triggerText = 'on the application submission'.(!empty($trigger['by_type']) ? ' by the '.$trigger['by_type'] : '').(!empty($trigger['by_email']) ? ' · '.$trigger['by_email'] : '');
        } elseif ($adminEsign && $adminEsign->device === 'Task Automation') {
            $triggerText = 'by task automation';
        }
        // Each opening of the link by the applicant is an event; the card sums them up.
        $applicantViews = $applicantEsignEvents->filter(fn ($e) => $e->user_type === 'applicant' && $e->event_type === 'viewed')->values();
        $sentAt = $adminEsign->created_at ?? $applicantEsign->created_at ?? null;
        $signedAt = $finalizedEvent->created_at ?? $applicantEsign->signed_date ?? null;
        $signatureSrc = $esignSigned ? asset($applicantEsign->signature) : null;
        $applicantIp = $applicantEsign->ip_address ?? null;
        $applicantUa = collect([$applicantEsign->browser ?? null, $applicantEsign->os ?? null])->filter()->implode(', ');
        $adminIp = $adminEsign->ip_address ?? null;
        $adminUa = collect([$adminEsign->browser ?? null, $adminEsign->os ?? null])->filter()->implode(', ');

        // Audit trail: icon per event type, prototype wording for the labels.
        $adpEventIcon = [
            'sign_request_created' => 'file', 'email_sent' => 'mail', 'email_read' => 'mailopen', 'viewed' => 'eye',
            'location_verified' => 'pin', 'consented_to_esign' => 'check', 'finalized' => 'sign', 'modified' => 'edit',
            'sign_request_finalized' => 'lock', 'renamed' => 'edit',
        ];
        $adpEventLabel = [
            'sign_request_created' => 'Sign request created', 'email_sent' => 'Email sent', 'email_read' => 'Email read', 'viewed' => 'Viewed',
            'location_verified' => 'Location verified', 'consented_to_esign' => 'Consented to e-sign', 'finalized' => 'Finalised', 'modified' => 'Modified',
            'sign_request_finalized' => 'Sign request finalised', 'renamed' => 'Renamed',
        ];
        $adpEventMeta = function ($event) {
            $parts = [];
            if ($event->event_type === 'email_sent' && $event->created_at) $parts[] = $event->created_at->diffForHumans();
            if (!empty($event->ip_address)) $parts[] = $event->ip_address;
            $device = collect([$event->browser ?? null, $event->os ?? null])->filter()->reject(fn ($v) => $v === 'System')->implode(', ');
            if ($device !== '') $parts[] = $device;
            if (!empty($event->latitude) && !empty($event->longitude)) $parts[] = $event->latitude_d_m_s.' '.$event->longitude_d_m_s;
            return implode(' · ', $parts);
        };
    @endphp

    @include('pages.students.admission.partials.portal-record')

    <div class="adp-esign-bar">
        <a href="{{ route('admission.show.e.signature', $applicant->id) }}" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('back', 13) !!}E-signature overview</a>
        <span class="adp-esign-bar__spacer"></span>
        @if($esignSigned)
            <button type="button" id="downloadEsignBtn" data-id="{{ $applicant->id }}" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('download', 14) !!}Download signed PDF {!! $adpSpin !!}</button>
        @endif
    </div>

    <section class="adp-panel adp-esign-req">
        <div class="adp-esign-head">
            <span class="adp-doc-ico adp-doc-ico--esign">{!! $adpIcon('send', 20) !!}</span>
            <div>
                <small class="adp-eyebrow">Sign request</small>
                <h2 class="adp-esign-head__title">{{ $esignSigned ? 'View signature' : 'Awaiting signature' }}</h2>
            </div>
            <span class="adp-pill {{ $esignSigned ? 'adp-pill--green' : 'adp-pill--amber' }}"><i></i>{{ $esignSigned ? 'Finalised' : 'Requested' }}</span>
        </div>
        <div class="adp-esign-meta">
            <div><small>From</small><b>London Churchill College</b><span>{{ $fromEmail }}</span></div>
            <div><small>File owner</small><b>London Churchill College</b><span>{{ $esignDoc }}</span></div>
            <div><small>Initialised</small><b>{{ $adpStamp($sentAt) }}</b><span>{{ trim(($esignVia ? 'via '.$esignVia : '').($triggerText ? ($esignVia ? ' · ' : '').$triggerText : '')) }}</span></div>
            <div><small>Finalised</small><b>{{ $esignSigned ? $adpStamp($signedAt) : '—' }}</b><span>{{ $esignSigned ? 'All signers complete' : 'Waiting for the applicant' }}</span></div>
        </div>
    </section>

    <div class="adp-esign-grid">
        <div class="adp-esign-col">
            <section class="adp-panel">
                <h3 class="adp-h3">Signature</h3>
                @if($esignSigned)
                    <div class="adp-sig-box"><img src="{{ $signatureSrc }}" alt="{{ $applicantName }} signature"></div>
                    <span class="adp-flag adp-flag--ink">{!! $adpIcon('note', 13) !!}Signed by {{ $applicantName }}</span>
                @else
                    <div class="adp-empty"><b>Not signed yet.</b><span>The signature appears here once the applicant finalises the request.</span></div>
                @endif
            </section>

            <section class="adp-panel">
                <div class="adp-panel__head"><h3 class="adp-h3">Signers</h3><span class="adp-hint">2 signers</span></div>
                <div class="adp-signers">
                    <div class="adp-signer">
                        <div class="adp-signer__h">
                            <span class="adp-avatar adp-signer__avatar">@if($adminPhoto)<img src="{{ $adminPhoto }}" alt="{{ $adminName }}">@else{{ $adminIni }}@endif</span>
                            <div>
                                <small class="adp-eyebrow">Signer #1</small>
                                <b class="adp-signer__email">{{ $adminEmail }}</b>
                                <span class="adp-signer__name">{{ $adminName }}</span>
                            </div>
                        </div>
                        <div class="adp-signer__tags">
                            <span class="adp-flag adp-flag--ok">{!! $adpIcon('ok', 12) !!}Verified email</span>
                            <span class="adp-chip adp-chip--sm">{!! $adpIcon('cog', 12) !!}{{ $adminIp ? 'IP '.$adminIp : 'IP not recorded' }}</span>
                            @if($adminUa !== '' && $adminUa !== 'System, System')<span class="adp-chip adp-chip--sm">{{ $adminUa }}</span>@endif
                            <span class="adp-chip adp-chip--sm">{!! $adpIcon('flag', 12) !!}{{ $adminDMS ?: 'Location not available' }}</span>
                        </div>
                        <img class="adp-maptile" src="{{ $adminMapSrc }}" alt="Where the request was sent from">
                    </div>
                    <div class="adp-signer">
                        <div class="adp-signer__h">
                            <span class="adp-avatar adp-signer__avatar">@if($applicantPhoto)<img src="{{ $applicantPhoto }}" alt="{{ $applicantName }}">@else{{ $applicantIni }}@endif</span>
                            <div>
                                <small class="adp-eyebrow">Signer #2</small>
                                <b class="adp-signer__email">{{ $applicantEmail }}</b>
                                <span class="adp-signer__name">{{ $applicantName }}</span>
                            </div>
                        </div>
                        @if($applicantEsign)
                            {{-- The applicant has opened the request: what was recorded so far (IP, device, location), signed or not. --}}
                            <div class="adp-signer__tags">
                                <span class="adp-flag {{ $esignSigned ? 'adp-flag--ok' : 'adp-flag--warn' }}">{!! $adpIcon('ok', 12) !!}{{ $esignSigned ? 'Verified email' : 'Opened via '.($applicantEsign->viewed_via ?: 'link').', not signed yet' }}</span>
                                @if($applicantViews->count() > 0)
                                    <span class="adp-chip adp-chip--sm" title="Every opening is listed in the audit trail">{!! $adpIcon('eye', 12) !!}Opened {{ $applicantViews->count() }} {{ $applicantViews->count() === 1 ? 'time' : 'times' }} · last {{ $adpStamp($applicantViews->last()->created_at) }}</span>
                                @endif
                                <span class="adp-chip adp-chip--sm">{!! $adpIcon('cog', 12) !!}{{ $applicantIp ? 'IP '.$applicantIp : 'IP not recorded' }}</span>
                                @if($applicantUa !== '')<span class="adp-chip adp-chip--sm">{{ $applicantUa }}</span>@endif
                                <span class="adp-chip adp-chip--sm">{!! $adpIcon('flag', 12) !!}{{ $applicantDMS ?: 'Location not available' }}</span>
                            </div>
                            <img class="adp-maptile" src="{{ $applicantMapSrc }}" alt="Where the applicant {{ $esignSigned ? 'signed' : 'opened the request' }}">
                        @else
                            <div class="adp-empty adp-empty--small adp-signer__wait"><span>Awaiting this signer.</span></div>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        <div class="adp-esign-col adp-esign-col--side">
            <section class="adp-panel">
                <h3 class="adp-h3">Document details</h3>
                <dl class="adp-kv adp-dd-list">
                    <dt>{!! $adpIcon('file', 14) !!}Document</dt><dd>{{ $esignDoc }}</dd>
                    <dt>{!! $adpIcon('list', 14) !!}Signature ID</dt><dd class="adp-mono">{{ $esignRef }}</dd>
                    <dt>{!! $adpIcon('flag', 14) !!}Integrity</dt><dd>@if($esignSigned)<span class="adp-flag adp-flag--ok">{!! $adpIcon('ok', 13) !!}Tamper-proof</span>@else<span class="adp-dd-muted">Pending</span>@endif</dd>
                </dl>
            </section>
            <section class="adp-panel">
                <h3 class="adp-h3">Signing order</h3>
                <div class="adp-vrows">
                    <div class="adp-vrow adp-vrow--signer"><span class="adp-avatar adp-avatar--sm">{{ $adminIni }}</span><span><b>{{ $adminEmail }}</b><small>{{ $adminName }}</small></span></div>
                    <div class="adp-vrow adp-vrow--signer"><span class="adp-avatar adp-avatar--sm">{{ $applicantIni }}</span><span><b>{{ $applicantEmail }}</b><small>{{ $applicantName }}</small></span></div>
                </div>
            </section>
        </div>
    </div>

    <section class="adp-panel">
        <div class="adp-panel__head"><h3 class="adp-h3">Audit trail</h3><span class="adp-hint">{{ $applicantEsignEvents->count() }} {{ $applicantEsignEvents->count() === 1 ? 'event' : 'events' }}</span></div>
        <div class="adp-audit">
            @forelse($applicantEsignEvents as $event)
                @php $meta = $adpEventMeta($event); @endphp
                <div class="adp-audit__row">
                    <span class="adp-audit__ico">{!! $adpIcon($adpEventIcon[$event->event_type] ?? 'file', 14) !!}</span>
                    <div class="adp-audit__body">
                        <b>{{ $adpEventLabel[$event->event_type] ?? \Illuminate\Support\Str::headline($event->event_type) }}</b>
                        <span>{{ $event->event_description ?: 'No event description recorded.' }}@if(isset($event->extra_field['opened']) && $event->extra_field['opened'] === true) <em class="adp-audit__opened">Opened</em>@endif</span>
                        @if($meta !== '')<small>{{ $meta }}</small>@endif
                    </div>
                    <div class="adp-audit__when"><b>{{ $adpDay($event->created_at) }}</b><small>{{ $adpTime($event->created_at) }}</small></div>
                </div>
            @empty
                <div class="adp-empty adp-empty--small"><span>No e-signature events have been recorded yet.</span></div>
            @endforelse
        </div>
    </section>

    @include('pages.students.admission.partials.portal-record-modals')
    @include('pages.students.admission.partials.portal-dialogs')
@endsection

@section('script')
    {{-- The record header's Change status dialog (admission-show.js guards its other blocks). --}}
    @vite('resources/js/admission-show.js')
    @vite('resources/js/admission-vue.js')
@endsection
