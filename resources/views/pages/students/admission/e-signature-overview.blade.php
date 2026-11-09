@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        E-signature overview (the E-signature tab) on the admission portal shell.
        No request yet: the empty state with Send request (#sendOfferAcceptanceModal,
        shared record modal, handled by admission-global.js, which reloads this page
        afterwards because of data-adp-esign-reload). Request sent: the summary row
        with View request (admission.show.e.signature.request), Resend while the
        signature is awaited, and Download sign (#downloadEsignBtn,
        applicant.e.signature.download) once it is in.
    --}}
    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    @php
        $adpStatusId = (int) ($applicant->status_id ?? 0);
        $adpPriv = auth()->user()->priv();
        $adpNext = \App\Support\AdmissionStatusFlow::next($applicant, $allStatuses, $adpPriv);
        $adpStatusHelp = \App\Support\AdmissionStatusFlow::HELP;
        $adpTab = 'esign';
        $adpCanRequest = isset($adpPriv['e_signature_request']) && $adpPriv['e_signature_request'] == 1;

        $adpPaths = [
            'sign' => '<path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.6 7.6"/><circle cx="11" cy="11" r="2"/>',
            'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'send' => '<path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
        ];
        $adpIcon = fn ($name, $size = 16) => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$adpPaths[$name].'</svg>';
        $adpSpin = '<svg class="adm-btn-loader adp-btn-spin" style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".4" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
        $adpLong = function ($value) {
            if (empty($value)) return '—';
            try { return \Carbon\Carbon::parse($value)->format('D jS M, Y · H:i'); } catch (\Throwable $e) { return (string) $value; }
        };
        $sentAt = $adminEsign->created_at ?? $applicantEsign->created_at ?? null;
        $signedAt = $finalizedEvent->created_at ?? $applicantEsign->signed_date ?? null;
    @endphp

    @include('pages.students.admission.partials.portal-record')

    <section class="adp-panel adp-esign" data-adp-esign-overview data-adp-esign-reload>
        <h3 class="adp-h3">E-signature</h3>

        @if(!$esignRequested)
            <div class="adp-empty">
                <b>No request sent.</b>
                <span>Send the applicant a secure link to sign the {{ $adpStatusId >= 6 ? 'offer acceptance form' : 'application form' }}.</span>
                @if($adpCanRequest)
                    <button type="button" class="adp-btn adp-btn--primary adp-btn--sm adp-empty__act" id="sendEsignBtn" data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#sendOfferAcceptanceModal">{!! $adpIcon('sign', 14) !!}Send request</button>
                @endif
            </div>
        @else
            <div class="adp-esign-sum">
                <div class="adp-esign-sum__l">
                    <span class="adp-doc-ico adp-doc-ico--esign">{!! $adpIcon('sign', 20) !!}</span>
                    <div>
                        <b>{{ $esignDoc }}</b>
                        <small>Request <span class="adp-mono">{{ $esignRef }}</span> · sent {{ $adpLong($sentAt) }}{{ $esignVia ? ' via '.$esignVia : '' }}{{ $esignSigned ? ' · signed '.$adpLong($signedAt) : '' }}{{ $esignResends > 0 ? ' · resent '.$esignResends.' time'.($esignResends > 1 ? 's' : '') : '' }}{{ ($esignViews ?? 0) > 0 ? ' · opened '.$esignViews.' time'.($esignViews > 1 ? 's' : '') : '' }}</small>
                    </div>
                </div>
                <div class="adp-esign-sum__acts">
                    <span class="adp-pill {{ $esignSigned ? 'adp-pill--green' : 'adp-pill--amber' }}"><i></i>{{ $esignSigned ? 'Finalised' : 'Awaiting signature' }}</span>
                    <a href="{{ route('admission.show.e.signature.request', $applicant->id) }}" class="adp-btn adp-btn--primary adp-btn--sm">{!! $adpIcon('eye', 14) !!}View request</a>
                    @if($esignSigned)
                        <button type="button" id="downloadEsignBtn" data-id="{{ $applicant->id }}" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('download', 14) !!}Download sign {!! $adpSpin !!}</button>
                    @elseif($adpCanRequest)
                        <button type="button" id="sendEsignBtn" data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#sendOfferAcceptanceModal" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('send', 14) !!}Resend</button>
                    @endif
                </div>
            </div>
        @endif
    </section>

    @include('pages.students.admission.partials.portal-record-modals')
    @include('pages.students.admission.partials.portal-dialogs')
@endsection

@section('script')
    {{-- The record header's Change status dialog (admission-show.js guards its other blocks). --}}
    @vite('resources/js/admission-show.js')
    @vite('resources/js/admission-vue.js')
@endsection
