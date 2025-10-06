<div class="intro-y mt-5 flex justify-between items-center">
    <div>
        <a href="{{ route('admission.show', $applicant->id) }}" class="btn shadow-lg border-0 bg-white mr-3 inline-block p-5 text-left w-56 {{ Route::currentRouteName() == 'admission.show' ? 'active-bg-success active-text-white' : '' }} hover-bg-success hover-text-white">
            <span class="block text-lg text-dark font-semibold">Information</span>
            <span class="block text-base font-normal text-slate-500">Details</span>
        </a>
        <a href="{{ route('admission.communication', $applicant->id) }}" class="btn shadow-lg border-0 bg-white mr-3 inline-block p-5 text-left w-56 {{ Route::currentRouteName() == 'admission.communication' ? 'active-bg-success active-text-white' : '' }} hover-bg-success hover-text-white">
            <span class="block text-lg text-dark font-semibold">Communication</span>
            <span class="block text-base font-normal text-slate-500">{{ $applicant->emails->count() + $applicant->letters->count() + $applicant->sms->count() }} Contents</span>
        </a>
        <a href="{{ route('admission.uploads', $applicant->id) }}" class="btn shadow-lg border-0 bg-white mr-3 inline-block p-5 text-left w-56 {{ Route::currentRouteName() == 'admission.uploads' ? 'active-bg-success active-text-white' : '' }} hover-bg-success hover-text-white">
            <span class="block text-lg text-dark font-semibold">Uploaded Files</span>
            <span class="block text-base font-normal text-slate-500">{{ $applicant->docses->count() }} Items</span>
        </a>
        <a href="{{ route('admission.notes', $applicant->id) }}" class="btn shadow-lg border-0 bg-white mr-3 inline-block p-5 text-left w-56 {{ Route::currentRouteName() == 'admission.notes' ? 'active-bg-success active-text-white' : '' }} hover-bg-success hover-text-white">
            <span class="block text-lg text-dark font-semibold">Notes</span>
            <span class="block text-base font-normal text-slate-500">{{ $applicant->notes->count() }} Items</span>
        </a>
        <a href="{{ route('admission.process', $applicant->id) }}" class="btn shadow-lg border-0 bg-white mr-3 inline-block p-5 text-left w-56 {{ Route::currentRouteName() == 'admission.process' ? 'active-bg-success active-text-white' : '' }} hover-bg-success hover-text-white">
            <span class="block text-lg text-dark font-semibold">Processes</span>
            <span class="block text-base font-normal text-slate-500">{{ $applicant->pendingTasks->count() }} Pendings</span>
        </a>
    </div>
    @if(isset(auth()->user()->priv()['e_signature_request']) && auth()->user()->priv()['e_signature_request'] == 1)
    <div class="flex gap-2">
       @if(isset($esignature) && !empty($esignature->signature))
        <div class="flex justify-end mb-4 mt-3">
            @if(Route::currentRouteName() === 'admission.show.e.signature')
                <button id="downloadEsignBtn" data-id="{{ $applicant->id }}" class="font-semibold items-center flex text-[18px] justify-start btn shadow-lg border-0 p-5 text-left bg-primary text-white h-[92px]">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i> Download E-Signature
                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                        stroke="white" class="w-4 h-4 ml-2">
                        <g fill="none" fill-rule="evenodd">
                            <g transform="translate(1 1)" stroke-width="4">
                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                        to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                </path>
                            </g>
                        </g>
                    </svg>
                </button>
            @else
                <a href="{{ route('admission.show.e.signature', $applicant->id) }}"
                class="font-semibold items-center flex text-[18px] justify-start btn shadow-lg border-0 p-5 text-left bg-success text-white h-[92px]">
                    <i data-lucide="eye" class="w-4 h-4 mr-2"></i> View E-Signature
                </a>
            @endif
        </div>
        @else
            <div class="w-full flex justify-end mb-4 mt-3">
                <button data-applicant="{{ $applicant->id }}"
                        data-tw-toggle="modal"
                        data-tw-target="#sendOfferAcceptanceModal"
                        type="button"
                        id="sendEsignBtn"
                        class="font-semibold items-center flex text-[18px] justify-start btn shadow-lg border-0 p-5 text-left bg-primary text-white h-[92px]">
                    <i data-lucide="send" class="w-4 h-4 mr-2"></i> Send E-Signature Request
                </button>
            </div>
        @endif
    </div>
    @endif
</div>