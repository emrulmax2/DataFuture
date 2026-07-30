@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    @php
        $formatDate = function ($value, $format = 'M d, Y') {
            if (empty($value)) {
                return 'N/A';
            }

            try {
                return \Carbon\Carbon::parse($value)->format($format);
            } catch (\Throwable $e) {
                return 'N/A';
            }
        };

        $formatDateTime = fn ($value) => $formatDate($value, 'M d, Y h:i A T');
        $formatTime = fn ($value) => $formatDate($value, 'h:i A T');

        $initials = function ($value) {
            $parts = preg_split('/\s+/', trim((string) $value));
            $letters = collect($parts)->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');

            return mb_strtoupper($letters ?: '?');
        };

        $adminUser = $adminEsign?->user;
        $applicantEmail = $applicant->users->email ?? 'N/A';
        $adminEmail = $adminUser?->email ?? 'N/A';
        $applicantName = trim(($applicant->title->name ?? '').' '.($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'Applicant';
        $adminName = $adminUser?->full_name ?? $adminUser?->name ?? 'London Churchill College';
        $fromEmail = $adminEsign?->smtp_email ?: 'N/A';
        $signatureStatus = isset($applicantEsign->signature) && !empty($applicantEsign->signature)
            ? 'Finalized'
            : ucfirst($applicantEsign->status ?? 'Pending');
        $signatureRef = $applicantEsign?->id ? 'ESIG-'.$applicant->id.'-'.$applicantEsign->id : 'N/A';

        $adminPhoto = $adminUser?->employee?->photo_url ?: $adminUser?->photo_url ?: \App\Support\Avatar::initials($adminName);
        $applicantPhoto = $applicant->brand_photo_url;

        $eventSkins = [
            \App\Enums\EsignEventType::SIGN_REQUEST_CREATED->value => ['icon' => 'file-text', 'tint' => '#eef4fb', 'color' => '#2f6ea5'],
            \App\Enums\EsignEventType::EMAIL_SENT->value => ['icon' => 'mail', 'tint' => '#fdf7ea', 'color' => '#a9842d'],
            \App\Enums\EsignEventType::EMAIL_READ->value => ['icon' => 'mail-open', 'tint' => '#eef4fb', 'color' => '#2f6ea5'],
            \App\Enums\EsignEventType::VIEWED->value => ['icon' => 'eye', 'tint' => '#f4f1fa', 'color' => '#6d4bb0'],
            \App\Enums\EsignEventType::LOCATION_VERIFIED->value => ['icon' => 'map-pin', 'tint' => '#eaf4ee', 'color' => '#1e6b4e'],
            \App\Enums\EsignEventType::CONSENTED_TO_ESIGN->value => ['icon' => 'check-square', 'tint' => '#eaf4ee', 'color' => '#1e6b4e'],
            \App\Enums\EsignEventType::FINALIZED->value => ['icon' => 'file-check', 'tint' => '#eaf4ee', 'color' => '#1e6b4e'],
            \App\Enums\EsignEventType::MODIFIED->value => ['icon' => 'edit-3', 'tint' => '#fff4e8', 'color' => '#c06f2c'],
            \App\Enums\EsignEventType::SIGN_REQUEST_FINALIZED->value => ['icon' => 'lock', 'tint' => '#eaf4ee', 'color' => '#1e6b4e'],
            \App\Enums\EsignEventType::RENAMED->value => ['icon' => 'type', 'tint' => '#eef4fb', 'color' => '#2f6ea5'],
        ];

        $eventMeta = function ($event) {
            $parts = [];

            if ($event->event_type === \App\Enums\EsignEventType::EMAIL_SENT->value && $event->created_at) {
                $parts[] = $event->created_at->diffForHumans();
            }

            if (!empty($event->ip_address)) {
                $parts[] = 'IP '.$event->ip_address;
            }

            $device = collect([$event->browser ?? null, $event->os ?? null])->filter()->implode(', ');
            if (!empty($device)) {
                $parts[] = $device;
            }

            if (
                $event->event_type === \App\Enums\EsignEventType::LOCATION_VERIFIED->value
                && !empty($event->latitude)
                && !empty($event->longitude)
            ) {
                $parts[] = $event->latitude_d_m_s.' '.$event->longitude_d_m_s;
            }

            return implode(' · ', array_filter($parts));
        };
    @endphp

    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    <!-- BEGIN: Profile Info -->
    @include('pages.students.admission.show-info')
    
    <!-- END: Profile Info -->
    <div class="adm-sign">
        <section class="adm-sign-summary adm-card">
            <div class="adm-sign-summary__head">
                <div class="adm-sign-title">
                    <span class="adm-sign-title__icon">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </span>
                    <span>
                        <span class="adm-sign-title__eyebrow">Sign Request</span>
                        <span class="adm-sign-title__text">View Signature</span>
                    </span>
                    <span class="adm-sign-badge {{ strtolower($signatureStatus) === 'finalized' || strtolower($signatureStatus) === 'accepted' ? 'adm-sign-badge--success' : 'adm-sign-badge--pending' }}">
                        <i data-lucide="{{ strtolower($signatureStatus) === 'finalized' || strtolower($signatureStatus) === 'accepted' ? 'check' : 'clock' }}" class="w-3 h-3"></i>
                        {{ $signatureStatus }}
                    </span>
                </div>

            </div>

            <div class="adm-sign-meta-grid">
                <div class="adm-sign-meta">
                    <span class="adm-sign-meta__label">From</span>
                    <span class="adm-sign-meta__value">London Churchill College</span>
                    <span class="adm-sign-meta__sub">{{ $fromEmail }}</span>
                </div>
                <div class="adm-sign-meta">
                    <span class="adm-sign-meta__label">File Owner</span>
                    <span class="adm-sign-meta__value">London Churchill College</span>
                    <span class="adm-sign-meta__sub">Signed Application Form</span>
                </div>
                <div class="adm-sign-meta">
                    <span class="adm-sign-meta__label">Initialized</span>
                    <span class="adm-sign-meta__value">{{ $formatDate($adminEsign?->created_at) }}</span>
                    <span class="adm-sign-meta__sub">{{ $formatTime($adminEsign?->created_at) }}</span>
                </div>
                <div class="adm-sign-meta">
                    <span class="adm-sign-meta__label">Finalized</span>
                    <span class="adm-sign-meta__value">{{ $formatDate($finalizedEvent?->created_at) }}</span>
                    <span class="adm-sign-meta__sub">{{ $formatTime($finalizedEvent?->created_at) }}</span>
                </div>
            </div>
        </section>

        <div class="adm-sign-layout">
            <div class="adm-sign-main">
                @if(isset($applicantEsign->signature) && !empty($applicantEsign->signature))
                    <section class="adm-card adm-sign-signature">
                        <div class="adm-card__head">
                            <h2 class="adm-card__title">Signature</h2>
                        </div>
                        <div class="adm-card__body">
                            <div class="adm-sign-signature__panel">
                                <img src="{{ asset($applicantEsign->signature) }}" alt="{{ $applicantName }} signature">
                            </div>
                            <div class="adm-sign-signature__caption">
                                <i data-lucide="pen-line" class="w-4 h-4"></i>
                                Signed by {{ $applicantName }}
                            </div>
                        </div>
                    </section>
                @endif

                <section class="adm-card">
                    <div class="adm-card__head">
                        <h2 class="adm-card__title">Signers</h2>
                        <span class="adm-sign-count">{{ collect([$adminEmail, $applicantEmail])->filter(fn ($email) => $email !== 'N/A')->count() }} signers</span>
                    </div>
                    <div class="adm-card__body">
                        <div class="adm-sign-signers">
                            <article class="adm-sign-signer">
                                <div class="adm-sign-signer__head">
                                    <img src="{{ $adminPhoto }}" alt="Admin signer">
                                    <div>
                                        <span class="adm-sign-signer__role">Signer #1</span>
                                        <span class="adm-sign-signer__name">{{ $adminEmail }}</span>
                                        <span class="adm-sign-signer__ref">{{ $adminName }}</span>
                                    </div>
                                </div>
                                <div class="adm-sign-checks">
                                    <span class="adm-sign-check"><i data-lucide="check" class="w-3 h-3"></i>Verified Email</span>
                                    <span class="adm-sign-check"><i data-lucide="shield-check" class="w-3 h-3"></i>IP {{ $adminEsign?->ip_address ?: '0.0.0.0' }}</span>
                                    <span class="adm-sign-check"><i data-lucide="map-pin" class="w-3 h-3"></i>{{ $adminDMS ?: 'Location not available' }}</span>
                                </div>
                                <div class="adm-sign-map">
                                    <img src="{{ $adminMap }}" alt="Admin signer verified map">
                                </div>
                            </article>

                            <article class="adm-sign-signer">
                                <div class="adm-sign-signer__head">
                                    <img src="{{ $applicantPhoto }}" alt="Applicant signer">
                                    <div>
                                        <span class="adm-sign-signer__role">Signer #2</span>
                                        <span class="adm-sign-signer__name">{{ $applicantEmail }}</span>
                                        <span class="adm-sign-signer__ref">{{ $applicantName }}</span>
                                    </div>
                                </div>
                                <div class="adm-sign-checks">
                                    <span class="adm-sign-check"><i data-lucide="check" class="w-3 h-3"></i>Verified Email</span>
                                    <span class="adm-sign-check"><i data-lucide="shield-check" class="w-3 h-3"></i>IP {{ $applicantEsign?->ip_address ?: '0.0.0.0' }}</span>
                                    <span class="adm-sign-check"><i data-lucide="map-pin" class="w-3 h-3"></i>{{ $applicantDMS ?: 'Location not available' }}</span>
                                </div>
                                <div class="adm-sign-map">
                                    <img src="{{ $applicantMap }}" alt="Applicant signer verified map">
                                </div>
                            </article>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="adm-sign-side">
                <section class="adm-card adm-sign-doc">
                    <div class="adm-card__head">
                        <h2 class="adm-card__title">Document Details</h2>
                    </div>
                    <div class="adm-card__body">
                        <div class="adm-sign-doc__item">
                            <span><i data-lucide="file-text" class="w-4 h-4"></i>Document</span>
                            <strong>Signed Application Form</strong>
                        </div>
                        <div class="adm-sign-doc__item">
                            <span><i data-lucide="hash" class="w-4 h-4"></i>Signature ID</span>
                            <strong>{{ $signatureRef }}</strong>
                        </div>
                        <div class="adm-sign-doc__item">
                            <span><i data-lucide="shield-check" class="w-4 h-4"></i>Integrity</span>
                            <strong class="adm-sign-ok">Tamper-proof</strong>
                        </div>
                    </div>
                </section>

                <section class="adm-card adm-sign-order">
                    <div class="adm-card__head">
                        <h2 class="adm-card__title">Signing Order</h2>
                    </div>
                    <div class="adm-card__body">
                        <ol class="adm-sign-order__list">
                            <li>
                                <span class="adm-sign-order__avatar">{{ $initials($adminName ?: $adminEmail) }}</span>
                                <span>
                                    <strong>{{ $adminEmail }}</strong>
                                    <em>{{ $adminName }}</em>
                                </span>
                            </li>
                            <li>
                                <span class="adm-sign-order__avatar">{{ $initials($applicantName) }}</span>
                                <span>
                                    <strong>{{ $applicantEmail }}</strong>
                                    <em>{{ $applicantName }}</em>
                                </span>
                            </li>
                        </ol>
                    </div>
                </section>
            </aside>
        </div>

        <section class="adm-card adm-sign-audit">
            <div class="adm-card__head">
                <h2 class="adm-card__title">Audit Trail</h2>
                <span class="adm-sign-count">{{ $applicantEsignEvents->count() }} events</span>
            </div>
            <div class="adm-card__body">
                @forelse ($applicantEsignEvents as $event)
                    @php
                        $skin = $eventSkins[$event->event_type] ?? ['icon' => 'activity', 'tint' => '#eef2f3', 'color' => '#5a6b74'];
                        $eventType = \App\Enums\EsignEventType::fromValue($event->event_type);
                        $eventLabel = $eventType?->label() ?? $event->event_type;
                        $meta = $eventMeta($event);
                    @endphp
                    <article class="adm-sign-event" style="--adm-sign-event-tint: {{ $skin['tint'] }}; --adm-sign-event-color: {{ $skin['color'] }};">
                        <div class="adm-sign-event__rail">
                            <span class="adm-sign-event__icon">
                                <i data-lucide="{{ $skin['icon'] }}" class="w-4 h-4"></i>
                            </span>
                        </div>
                        <div class="adm-sign-event__content">
                            <div class="adm-sign-event__copy">
                                <h3>{{ $eventLabel }}</h3>
                                <p>
                                    {{ $event->event_description ?: 'No event description recorded.' }}
                                    @if(isset($event->extra_field['opened']) && $event->extra_field['opened'] === true)
                                        <span class="adm-sign-opened">Opened</span>
                                    @endif
                                </p>
                                @if(!empty($meta))
                                    <span class="adm-sign-event__meta">{{ $meta }}</span>
                                @endif
                            </div>
                            <time class="adm-sign-event__time" datetime="{{ $event->created_at?->toIso8601String() }}">
                                <strong>{{ $formatDate($event->created_at) }}</strong>
                                <span>{{ $formatTime($event->created_at) }}</span>
                            </time>
                        </div>
                    </article>
                @empty
                    <div class="adm-sign-empty">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                        No e-signature audit events have been recorded yet.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @include('pages.students.admission.show-modals')

@endsection

@section('script')
    @vite('resources/js/admission.js')
    @vite('resources/js/admission-vue.js')
@endsection
