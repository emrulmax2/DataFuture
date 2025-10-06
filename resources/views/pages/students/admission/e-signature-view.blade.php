@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Application Ref No. <u><strong>{{ (isset($applicant->application_no) && !empty($applicant->application_no) ? $applicant->application_no : '---') }}</strong></u></h2>
        
        <div class="ml-auto flex justify-end">
            <button data-tw-toggle="modal" data-tw-target="#progressBarModal" type="button" class="add_btn btn btn-danger shadow-md mr-2 hidden">Progress Bar</button>
            <a style="float: right;" href="{{ route('applicantprofile.print',$applicant->id) }}" data-id="{{ $applicant->id }}" class="btn btn-success text-white w-auto mr-1">Download Pdf</a>
            <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

            @if(isset(auth()->user()->priv()['login_as_applicant']) && auth()->user()->priv()['login_as_applicant'] == 1)
                <a target="__blank" href="{{ route('impersonate', ['id' =>$applicant->applicant_user_id,'guardName' =>'applicant']) }}" class="btn btn-warning min-w-max">
                    Login As Applicant <i data-lucide="log-in" class="w-4 h-4 ml-2"></i>
                </a>
            @endif
        </div>
        
    </div>
    <!-- BEGIN: Profile Info -->
    @include('pages.students.admission.show-info')
    @include('pages.students.admission.show-menu')
    
    <!-- END: Profile Info -->
   <div class="intro-y bg-white shadow rounded-lg p-6">
    <div class="max-w-4xl mx-auto">
        <table class="w-full text-sm text-gray-700 border-collapse">
            <tr>
                <td colspan="2" class="pb-3">
                    This document is a 
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-md font-medium">FINALIZED</span> sign request.
                </td>
            </tr>
            <tr class="border-b">
                <td class="font-semibold w-52 py-2">From</td>
                <td class="py-2">London Churchill College ({{ isset($adminEsign->smtp_email) && !empty($adminEsign->smtp_email) ? $adminEsign->smtp_email : 'N/A' }})</td>
            </tr>
            <tr class="border-b">
                <td class="font-semibold py-2">File Owner</td>
                <td class="py-2">London Churchill College</td>
            </tr>
            <tr class="border-b">
                <td class="font-semibold py-2">Signing Order</td>
                <td class="py-2">
                    <ol class="list-decimal list-inside">
                        <li>{{ isset($adminEsign->user->email) && !empty($adminEsign->user->email) ? $adminEsign->user->email : 'N/A' }}</li>
                        <li>{{ isset($applicant->users->email) && !empty($applicant->users->email) ? $applicant->users->email : 'N/A' }}</li>
                    </ol>
                </td>
            </tr>
            <tr class="border-b">
                <td class="font-semibold py-2">Initialized</td>
                <td class="py-2">{{ isset($adminEsign->created_at) && !empty($adminEsign->created_at) ? date('M d, Y', strtotime($adminEsign->created_at)).' '.date('h:i A T', strtotime($adminEsign->created_at)) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="font-semibold py-2">Finalized</td>
                <td class="py-2">{{ isset($finalizedEvent->created_at) && !empty($finalizedEvent->created_at) ? date('M d, Y', strtotime($finalizedEvent->created_at)).' '.date('h:i A T', strtotime($finalizedEvent->created_at)) : 'N/A' }}</td>
            </tr>
        </table>

        @if(isset($applicantEsign->signature) && !empty($applicantEsign->signature))
        <div class="mt-6 text-center">
            <img src="{{ isset($applicantEsign->signature) && !empty($applicantEsign->signature) ? asset($applicantEsign->signature) : '' }}" alt="" class="mx-auto h-44 object-contain">
            <p class="text-gray-600 mt-2 text-sm">Signature</p>
        </div>
        @endif

        <h2 class="text-lg font-semibold text-gray-800 mt-10 mb-4">Signers</h2>

        <div class="space-y-10">
            <div>
                <div class="flex items-center gap-4 mb-3">
                    <img src="{{ (isset($adminEsign->user->photo) && !empty($adminEsign->user->photo) && Storage::disk('local')->exists('public/users/' . $adminEsign->user->id . '/' . $adminEsign->user->photo) ? asset('storage/users/' . $adminEsign->user->id . '/' . $adminEsign->user->photo) : asset('build/assets/images/placeholders/200x200.jpg')) }}"
                        alt="Admin"
                        class="w-14 h-14 rounded-full border shadow-sm">
                    <div>
                        <div class="font-semibold text-gray-800">{{ isset($adminEsign->user->email) && !empty($adminEsign->user->email) ? $adminEsign->user->email : 'N/A' }}</div>
                        <div class="text-xs text-gray-500">Signer #1 - p.murphy+un0wqq</div>
                    </div>
                </div>
                <div class="text-sm text-gray-600 space-y-2">
                    <div class="flex flex-wrap gap-3">
                        <span class="flex items-center gap-1"><img src="{{ asset('build/assets/images/report_icons/verified-icon.svg') }}" class="w-4 h-4"/> Verified Email</span>
                        <span class="flex items-center gap-1"><img src="{{ asset('build/assets/images/report_icons/verified-icon.svg') }}" class="w-4 h-4"/> Verified IP {{ isset($adminEsign->ip_address) && !empty($adminEsign->ip_address) ? $adminEsign->ip_address : '0.0.0.0' }}</span>
                        <span class="flex items-center gap-1"><img src="{{ asset('build/assets/images/report_icons/verified-icon.svg') }}" class="w-4 h-4"/> Verified consent to Esign</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <img src="{{ asset('build/assets/images/report_icons/verified-icon.svg') }}" class="w-4 h-4"/>
                        <span>Verified geolocation {{ $adminDMS }} (66661 m)</span>
                    </div>
                    <div class="mt-3">
                        <img src="{{ $adminMap }}" alt="Admin Map" class="rounded-lg border shadow-sm w-full">
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-4 mb-3">
                    <img src="{{ (isset($applicant->photo) && !empty($applicant->photo) && Storage::disk('local')->exists('public/applicants/' . $applicant->id . '/' . $applicant->photo) ? asset('storage/applicants/' . $applicant->id . '/' . $applicant->photo) : asset('build/assets/images/placeholders/200x200.jpg')) }}"
                        alt="Applicant"
                        class="w-14 h-14 rounded-full border shadow-sm">
                    <div>
                        <div class="font-semibold text-gray-800">{{ $applicant->users->email }}</div>
                        <div class="text-xs text-gray-500">Signer #2 - p.murphy+un0wqq</div>
                    </div>
                </div>
                <div class="text-sm text-gray-600 space-y-2">
                    <div class="flex flex-wrap gap-3">
                        <span class="flex items-center gap-1"><img src="{{ asset('build/assets/images/report_icons/verified-icon.svg') }}" class="w-4 h-4"/> Verified Email</span>
                        <span class="flex items-center gap-1"><img src="{{ asset('build/assets/images/report_icons/verified-icon.svg') }}" class="w-4 h-4"/> Verified IP {{ isset($applicantEsign->ip_address) && !empty($applicantEsign->ip_address) ? $applicantEsign->ip_address : '0.0.0.0' }}</span>
                        <span class="flex items-center gap-1"><img src="{{ asset('build/assets/images/report_icons/verified-icon.svg') }}" class="w-4 h-4"/> Verified consent to Esign</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <img src="{{ asset('build/assets/images/report_icons/verified-icon.svg') }}" class="w-4 h-4"/>
                        <span>Verified geolocation {{ $applicantDMS }} (66661 m)</span>
                    </div>
                    <div class="mt-3">
                        <img src="{{ $applicantMap }}" alt="Applicant Map" class="rounded-lg border shadow-sm w-full">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-12">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Audit Trail</h2>
            <table class="w-full text-sm text-gray-700 border-collapse">
                <tbody>
                    @foreach ($applicantEsignEvents as $event)
                    <tr class="border-b last:border-0">
                        <td class="py-3 pr-3">
                            
                            @if($event->event_type === \App\Enums\EsignEventType::SIGN_REQUEST_CREATED->value)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-notebook-text-icon lucide-notebook-text"><path d="M2 6h4"/>
                                    <path d="M2 10h4"/><path d="M2 14h4"/><path d="M2 18h4"/><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9.5 8h5"/><path d="M9.5 12H16"/><path d="M9.5 16H14"/>
                                </svg>
                            @endif
                            @if($event->event_type === \App\Enums\EsignEventType::EMAIL_SENT->value)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                            @endif
                            @if($event->event_type === \App\Enums\EsignEventType::VIEWED->value)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                            @endif
                            @if($event->event_type === \App\Enums\EsignEventType::LOCATION_VERIFIED->value)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pinned-icon lucide-map-pinned"><path d="M18 8c0 3.613-3.869 7.429-5.393 8.795a1 1 0 0 1-1.214 0C9.87 15.429 6 11.613 6 8a6 6 0 0 1 12 0"/><circle cx="12" cy="8" r="2"/><path d="M8.714 14h-3.71a1 1 0 0 0-.948.683l-2.004 6A1 1 0 0 0 3 22h18a1 1 0 0 0 .948-1.316l-2-6a1 1 0 0 0-.949-.684h-3.712"/></svg>
                            @endif
                            @if($event->event_type === \App\Enums\EsignEventType::CONSENTED_TO_ESIGN->value)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-check-icon lucide-square-check"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                            @endif
                            @if($event->event_type === \App\Enums\EsignEventType::FINALIZED->value)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-check-icon lucide-file-check"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m9 15 2 2 4-4"/></svg>
                            @endif
                            @if($event->event_type === \App\Enums\EsignEventType::EMAIL_READ->value)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-open-icon lucide-mail-open"><path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z"/><path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10"/></svg>
                            @endif
                        </td>
                        <td class="w-36 py-3 pr-3 font-medium text-gray-800">
                            {{ \App\Enums\EsignEventType::fromValue($event->event_type)?->label() ?? $event->event_type }}
                        </td>
                        <td class="py-3 pr-3">
                            {{ $event->event_description }}
                            @if(isset($event->extra_field['opened']) && $event->extra_field['opened'] === true)
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-md font-medium">OPENED</span>
                            @endif
                            @if (
                                $event->event_type === \App\Enums\EsignEventType::SIGN_REQUEST_CREATED->value ||
                                $event->event_type === \App\Enums\EsignEventType::VIEWED->value ||
                                $event->event_type === \App\Enums\EsignEventType::CONSENTED_TO_ESIGN->value
                            )
                                @if(isset($event->ip_address) && !empty($event->ip_address))
                                    <div class="text-xs text-gray-500 mt-1">
                                        IP {{ $event->ip_address }}, {{ $event->browser }}, {{ $event->os }}
                                    </div>
                                @endif
                            @endif
                            @if($event->event_type === \App\Enums\EsignEventType::EMAIL_SENT->value)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $event->created_at->diffForHumans() }}, {{ $event->created_at->format('M d, Y h:i A T') }}
                                </div>
                            @endif
                            @if($event->event_type === \App\Enums\EsignEventType::LOCATION_VERIFIED->value)
                                <div class="text-xs text-gray-500 mt-1">
                                    IP {{ $event->ip_address }}, {{ $event->browser }}, {{ $event->os }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                     {{ $event->latitude_d_m_s }} {{ $event->longitude_d_m_s }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3 text-right whitespace-nowrap text-xs text-gray-600">
                            {{ date('M d, Y', strtotime($event->created_at)) }}<br>
                            {{ date('h:i A T', strtotime($event->created_at)) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

    @include('pages.students.admission.show-modals')

@endsection

@section('script')
    @vite('resources/js/admission.js')
    @vite('resources/js/admission-vue.js')
@endsection