{{--
    Tab strip for the applicant screens. Rendered as the footer of the hero
    card (see show-info.blade.php), so it must not carry its own margin.

    #downloadEsignBtn and #sendEsignBtn keep their ids — admission-global.js
    binds the blob download and the offer-acceptance modal to them.
--}}
<div class="adm-tabs-row">
    <div class="adm-tabs">
        <a href="{{ route('admission.show', $applicant->id) }}" class="adm-tab {{ Route::currentRouteName() == 'admission.show' ? 'adm-tab--active' : '' }}">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path><path d="M14 2v5a1 1 0 0 0 1 1h5"></path><path d="M16 22a4 4 0 0 0-8 0"></path><circle cx="12" cy="15" r="3"></circle></svg>
            <span>
                <span class="adm-tab__title">Information</span>
                <span class="adm-tab__desc">Details</span>
            </span>
        </a>

        <a href="{{ route('admission.communication', $applicant->id) }}" class="adm-tab {{ Route::currentRouteName() == 'admission.communication' ? 'adm-tab--active' : '' }}">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M17 19a1 1 0 0 1-1-1v-2a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a1 1 0 0 1-1 1z"></path><path d="M17 21v-2"></path><path d="M19 14V6.5a1 1 0 0 0-7 0v11a1 1 0 0 1-7 0V10"></path><path d="M21 21v-2"></path><path d="M3 5V3"></path><path d="M4 10a2 2 0 0 1-2-2V6a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a2 2 0 0 1-2 2z"></path><path d="M7 5V3"></path></svg>
            <span>
                <span class="adm-tab__title">Communication</span>
                <span class="adm-tab__desc">{{ $applicant->emails->count() + $applicant->letters->count() + $applicant->sms->count() }} Contents</span>
            </span>
        </a>

        <a href="{{ route('admission.uploads', $applicant->id) }}" class="adm-tab {{ Route::currentRouteName() == 'admission.uploads' ? 'adm-tab--active' : '' }}">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M19 22v-6"></path><path d="M21 12.536V5"></path><path d="m22 19-3-3-3 3"></path><path d="M3 12A9 3 0 0 0 14.457 14.886"></path><path d="M3 5V19A9 3 0 0 0 13.318 21.968"></path><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse></svg>
            <span>
                <span class="adm-tab__title">Uploaded Files</span>
                <span class="adm-tab__desc">{{ $applicant->docses->count() }} Items</span>
            </span>
        </a>

        <a href="{{ route('admission.notes', $applicant->id) }}" class="adm-tab {{ Route::currentRouteName() == 'admission.notes' ? 'adm-tab--active' : '' }}">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4"></path><path d="M2 6h4"></path><path d="M2 10h4"></path><path d="M2 14h4"></path><path d="M2 18h4"></path><path d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"></path></svg>
            <span>
                <span class="adm-tab__title">Notes</span>
                <span class="adm-tab__desc">{{ $applicant->notes->count() }} Items</span>
            </span>
        </a>

        <a href="{{ route('admission.process', $applicant->id) }}" class="adm-tab {{ Route::currentRouteName() == 'admission.process' ? 'adm-tab--active' : '' }}">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"></path><path d="M5 2h14"></path><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"></path><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"></path></svg>
            <span>
                <span class="adm-tab__title">Processes</span>
                <span class="adm-tab__desc">{{ $applicant->pendingTasks->count() }} Pendings</span>
            </span>
        </a>

        {{-- Only meaningful once a student conversion (status 7) has been
             dispatched, so the tab stays hidden until log rows exist. --}}
        @php
            $admConversionLogs = $applicant->conversionLogs;
            $admConversionProblems = $admConversionLogs->whereIn('status', ['failed', 'cancelled'])->count();
        @endphp
        @if($admConversionLogs->count() > 0 || Route::currentRouteName() == 'admission.conversion.log')
            <a href="{{ route('admission.conversion.log', $applicant->id) }}" class="adm-tab {{ Route::currentRouteName() == 'admission.conversion.log' ? 'adm-tab--active' : '' }}">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M12 11h4"></path><path d="M12 16h4"></path><path d="M8 11h.01"></path><path d="M8 16h.01"></path></svg>
                <span>
                    <span class="adm-tab__title">Conversion Log</span>
                    <span class="adm-tab__desc">{{ $admConversionProblems > 0 ? $admConversionProblems.' Problems' : $admConversionLogs->count().' Steps' }}</span>
                </span>
            </a>
        @endif
    </div>

    @if(isset(auth()->user()->priv()['e_signature_request']) && auth()->user()->priv()['e_signature_request'] == 1)
        @if(isset($esignature) && !empty($esignature->signature))
            @if(Route::currentRouteName() === 'admission.show.e.signature')
                <button id="downloadEsignBtn" data-id="{{ $applicant->id }}" type="button" class="adm-esign adm-esign--view">
                    <span class="adm-esign__copy">
                        <span class="adm-esign__title">E-Signature</span>
                        <span class="adm-esign__desc">Download Sign</span>
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 17-2.156-1.868A.5.5 0 0 0 18 15.5v.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1c0-2.545-3.991-3.97-8.5-4a1 1 0 0 0 0 5c4.153 0 4.745-11.295 5.708-13.5a2.5 2.5 0 1 1 3.31 3.284"></path><path d="M3 21h18"></path></svg>
                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
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
            @else
                <a href="{{ route('admission.show.e.signature', $applicant->id) }}" class="adm-esign adm-esign--view">
                    <span class="adm-esign__copy">
                        <span class="adm-esign__title">E-Signature</span>
                        <span class="adm-esign__desc">View Sign</span>
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 17-2.156-1.868A.5.5 0 0 0 18 15.5v.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1c0-2.545-3.991-3.97-8.5-4a1 1 0 0 0 0 5c4.153 0 4.745-11.295 5.708-13.5a2.5 2.5 0 1 1 3.31 3.284"></path><path d="M3 21h18"></path></svg>
                </a>
            @endif
        @else
            <button data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#sendOfferAcceptanceModal" type="button" id="sendEsignBtn" class="adm-esign">
                <span class="adm-esign__copy">
                    <span class="adm-esign__title">E-Signature</span>
                    <span class="adm-esign__desc">Send Request</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path></svg>
            </button>
        @endif
    @endif
</div>
