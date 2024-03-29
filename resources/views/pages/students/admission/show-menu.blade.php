<div class="intro-y mt-5 flex justify-start">
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