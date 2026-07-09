@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
<style type="text/css">
    body{
        background-color: rgb(238, 241, 244) !important;
    }
</style>
@include('pages.employee.profile.partials.cover-header')

@include('pages.employee.profile.partials.side-tabs')

<div class="ep-grid">
    <div class="ep-col">
    <!-- BEGIN: Profile Info -->
    <!-- END: Profile Info -->

    @php
        $cardGrid = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-8 gap-y-5 px-5 sm:px-6 py-5';
        $editBtn  = 'inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg border border-primary/25 bg-primary/5 text-primary text-[13px] font-bold hover:bg-primary/10 transition-colors';
    @endphp

    <div class="flex flex-col gap-5 mt-5">

        {{-- ═══ Personal Details ═══ --}}
        <div class="intro-y box border-l-4 border-primary overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-darkmode-400">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-[10px] bg-primary/10 text-primary flex-none"><i data-lucide="user" class="w-[18px] h-[18px]"></i></span>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white tracking-tight">Personal Details</h2>
                </div>
                <button data-tw-toggle="modal" data-tw-target="#editAdmissionPersonalDetailsModal" type="button" class="editPersonalDetails {{ $editBtn }}">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit Personal Details
                </button>
            </div>
            <div class="{{ $cardGrid }}">
                @include('pages.employee.profile.partials.field', ['label' => 'Name', 'value' => $employee->title->name.' '.$employee->full_name])
                @include('pages.employee.profile.partials.field', ['label' => 'Date of Birth', 'value' => (isset($employee->date_of_birth) && !empty($employee->date_of_birth) ? date('jS M, Y', strtotime($employee->date_of_birth)) : null)])
                @include('pages.employee.profile.partials.field', ['label' => 'Age', 'value' => $employee->age ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'NI Number', 'value' => $employee->ni_number ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Sex Identifier/Gender', 'value' => $employee->sex->name ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Nationality', 'value' => $employee->nationality->name ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Ethnicity', 'value' => $employee->ethnicity->name ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Does this employee have a disability?', 'value' => $employee->disability_status ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Car Reg Number', 'value' => $employee->car_reg_number ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Driving License', 'value' => $employee->drive_license_number ?? null])

                @if(isset($employee->disability_status) && $employee->disability_status == "Yes")
                    <div class="min-w-0 col-span-full">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400 mb-2">Disabilities</div>
                        @if(isset($employee->disability) && !empty($employee->disability))
                            <ul class="flex flex-wrap gap-x-6 gap-y-1.5">
                                @foreach($employee->disability as $dis)
                                    <li class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200"><i data-lucide="check-circle" class="w-4 h-4 text-success flex-none"></i>{{ $dis->name }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══ Employment ═══ --}}
        <div class="intro-y box border-l-4 border-primary overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-darkmode-400">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-[10px] bg-primary/10 text-primary flex-none"><i data-lucide="briefcase" class="w-[18px] h-[18px]"></i></span>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white tracking-tight">Employment</h2>
                </div>
                <button data-tw-toggle="modal" data-tw-target="#editEmploymentDetailsModal" type="button" class="editPersonalDetails {{ $editBtn }}">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit Employment Details
                </button>
            </div>
            <div class="{{ $cardGrid }}">
                @include('pages.employee.profile.partials.field', ['label' => 'Started on', 'value' => (isset($employment->started_on) && !empty($employment->started_on) ? date('jS M, Y', strtotime($employment->started_on)) : null)])
                @if(isset($employment->ended_on) && !empty($employment->ended_on))
                    @include('pages.employee.profile.partials.field', ['label' => 'Ended on', 'value' => date('jS M, Y', strtotime($employment->ended_on))])
                @endif
                @include('pages.employee.profile.partials.field', ['label' => 'Employee type', 'value' => $employment->employeeWorkType->name ?? null])
                @if(isset($employment->employee_work_type_id) && $employment->employee_work_type_id == 2)
                    @include('pages.employee.profile.partials.field', ['label' => 'UTR Number', 'value' => $employment->utr_number ?? null])
                @endif
                @include('pages.employee.profile.partials.field', ['label' => 'Punch number', 'value' => $employment->punch_number ?? null])
                @if(isset($employment->employeeWorkType->name) && $employment->employeeWorkType->name == "Employee")
                    @include('pages.employee.profile.partials.field', ['label' => 'Works number', 'value' => $employment->works_number ?? null])
                @endif
                @include('pages.employee.profile.partials.field', ['label' => 'Job Title', 'value' => $employment->employeeJobTitle->name ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Department', 'value' => $employment->department->name ?? null])

                <div class="min-w-0">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400 mb-1.5">Site locations</div>
                    @if(count($employee->venues))
                        <ul class="flex flex-col gap-1.5">
                            @foreach($employee->venues as $dis)
                                <li class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200"><i data-lucide="check-circle" class="w-4 h-4 text-success flex-none"></i>{{ $dis->name }}</li>
                            @endforeach
                        </ul>
                    @else
                        <span class="text-sm font-semibold italic text-slate-400">N/A</span>
                    @endif
                </div>

                @include('pages.employee.profile.partials.field', ['label' => 'Office telephone', 'value' => $employment->office_telephone ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Mobile', 'value' => $employment->mobile ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Email (username)', 'value' => $employee->user->email ?? null])
            </div>
        </div>

        {{-- ═══ Eligibilities ═══ --}}
        <div class="intro-y box border-l-4 border-accent overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-darkmode-400">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-[10px] bg-accent/15 text-accent flex-none"><i data-lucide="shield-check" class="w-[18px] h-[18px]"></i></span>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white tracking-tight">Eligibilities</h2>
                </div>
                <button data-tw-toggle="modal" data-tw-target="#editEligibilitesDetailsModal" type="button" class="editPersonalDetails {{ $editBtn }}">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit Eligibility Details
                </button>
            </div>
            <div class="{{ $cardGrid }}">
                @php
                    $isBritish = isset($employeeEligibilites->employeeWorkPermitType->name) && $employeeEligibilites->employeeWorkPermitType->name == "British Citizen";
                    $wpExpire  = (isset($employeeEligibilites->workpermit_expire) && !empty($employeeEligibilites->workpermit_expire)) ? $employeeEligibilites->workpermit_expire : null;
                    $wpBadge   = null;
                    if($wpExpire) {
                        $daysToExpiry = \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($wpExpire)->startOfDay(), false);
                        if($daysToExpiry >= 0 && $daysToExpiry <= 60) {
                            $wpBadge = '<span class="flex-none text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-warning/10 text-warning mt-0.5">Expiring soon</span>';
                        }
                    }
                @endphp
                @include('pages.employee.profile.partials.field', ['label' => 'Eligible To Work', 'value' => $employeeEligibilites->eligible_to_work ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Proof of ID Type', 'value' => $employeeEligibilites->employeeDocType->name ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Employee Work Permit', 'value' => $employeeEligibilites->employeeWorkPermitType->name ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'ID Number', 'value' => $employeeEligibilites->doc_number ?? null])
                @if(!$isBritish)
                    @include('pages.employee.profile.partials.field', ['label' => 'Workpermit Number', 'value' => $employeeEligibilites->workpermit_number ?? null])
                @endif
                @include('pages.employee.profile.partials.field', ['label' => 'Expiry Date', 'value' => (isset($employeeEligibilites->doc_expire) && !empty($employeeEligibilites->doc_expire) ? date('jS M, Y', strtotime($employeeEligibilites->doc_expire)) : null)])
                @if(!$isBritish)
                    @include('pages.employee.profile.partials.field', ['label' => 'Workpermit Expire', 'value' => ($wpExpire ? date('jS M, Y', strtotime($wpExpire)) : null), 'badge' => $wpBadge])
                @endif
                @include('pages.employee.profile.partials.field', ['label' => 'Issuing Country', 'value' => $employeeEligibilites->docIssueCountry->name ?? null])
            </div>
        </div>

        {{-- ═══ Emergency Contacts ═══ --}}
        <div class="intro-y box border-l-4 border-primary overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-darkmode-400">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-[10px] bg-primary/10 text-primary flex-none"><i data-lucide="life-buoy" class="w-[18px] h-[18px]"></i></span>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white tracking-tight">Emergency Contacts</h2>
                </div>
                <button data-tw-toggle="modal" data-tw-target="#editEmergencyContactDetailsModal" type="button" class="editPersonalDetails {{ $editBtn }}">
                    @if(isset($emergencyContacts))
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit Emergency Contacts Details
                    @else
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add a emergency contact
                    @endif
                </button>
            </div>
            @if(isset($emergencyContacts))
            <div class="{{ $cardGrid }}">
                @include('pages.employee.profile.partials.field', ['label' => 'Name', 'value' => $emergencyContacts->emergency_contact_name ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Relation', 'value' => $emergencyContacts->kin->name ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Telephone', 'value' => $emergencyContacts->emergency_contact_telephone ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Mobile', 'value' => $emergencyContacts->emergency_contact_mobile ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Email', 'value' => $emergencyContacts->emergency_contact_email ?? null])

                <div class="min-w-0 sm:col-span-2">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400 mb-1.5">Address</div>
                    <div class="flex items-start gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-primary flex-none mt-0.5"></i>
                        <span class="uppercase text-sm font-semibold text-slate-700 dark:text-slate-200 leading-relaxed">
                            @if(isset($emergencyContacts->address->address_line_1) && $emergencyContacts->address->address_line_1 > 0)
                                @if(!empty($emergencyContacts->address->address_line_1))<span>{{ $emergencyContacts->address->address_line_1 }}</span><br/>@endif
                                @if(!empty($emergencyContacts->address->address_line_2))<span>{{ $emergencyContacts->address->address_line_2 }}</span><br/>@endif
                                @if(!empty($emergencyContacts->address->city))<span>{{ $emergencyContacts->address->city }}</span>, @endif
                                @if(!empty($emergencyContacts->address->state))<span>{{ $emergencyContacts->address->state }}</span>, @endif
                                @if(!empty($emergencyContacts->address->post_code))<span>{{ $emergencyContacts->address->post_code }}</span>,<br/>@endif
                                @if(!empty($emergencyContacts->address->country))<span>{{ $emergencyContacts->address->country }}</span><br/>@endif
                            @else
                                <span class="normal-case italic text-warning">Not Set Yet!</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @else
            <div class="px-5 sm:px-6 py-8 flex items-center gap-2.5 text-sm font-semibold text-slate-400">
                <i data-lucide="info" class="w-4 h-4 flex-none"></i> No Emergency Contact Found
            </div>
            @endif
        </div>

        {{-- ═══ Educational Qualification ═══ --}}
        <div class="intro-y box border-l-4 border-primary overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-darkmode-400">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-[10px] bg-primary/10 text-primary flex-none"><i data-lucide="graduation-cap" class="w-[18px] h-[18px]"></i></span>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white tracking-tight">Educational Qualification</h2>
                </div>
                <button data-tw-toggle="modal" data-tw-target="#storeEducationalQualisModal" type="button" class="{{ $editBtn }}">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit Educational Qualification
                </button>
            </div>
            @if(isset($employee->education->id) && $employee->education->id > 0)
            <div class="{{ $cardGrid }}">
                @include('pages.employee.profile.partials.field', ['label' => 'Highest Educational Qualification', 'value' => $employee->education->qual->name ?? null, 'span' => 'sm:col-span-2 xl:col-span-2'])
                @include('pages.employee.profile.partials.field', ['label' => 'Qualification Name', 'value' => $employee->education->qualification_name ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Award Body', 'value' => $employee->education->award_body ?? null])
                @include('pages.employee.profile.partials.field', ['label' => 'Award Date', 'value' => (isset($employee->education->award_date) && !empty($employee->education->award_date) ? date('F, Y', strtotime($employee->education->award_date)) : null)])
            </div>
            @else
            <div class="px-5 sm:px-6 py-8 flex items-center gap-2.5 text-sm font-semibold text-slate-400">
                <i data-lucide="info" class="w-4 h-4 flex-none"></i> Educational Qualification data not available.
            </div>
            @endif
        </div>

        {{-- ═══ Employment Terms ═══ --}}
        <div class="intro-y box border-l-4 border-primary overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-darkmode-400">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-[10px] bg-primary/10 text-primary flex-none"><i data-lucide="file-text" class="w-[18px] h-[18px]"></i></span>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white tracking-tight">Employment Terms</h2>
                </div>
                <button data-tw-toggle="modal" data-tw-target="#editTermDetailsModal" type="button" class="editPersonalDetails {{ $editBtn }}">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit Terms Details
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-5 px-5 sm:px-6 py-5">
                @include('pages.employee.profile.partials.field', ['label' => 'Employement Notice Period', 'value' => (isset($employeeTerms->notice->name) ? 'Employee must give '.$employeeTerms->notice->name.' notice' : null)])
                @include('pages.employee.profile.partials.field', ['label' => 'Employement Period', 'value' => (isset($employeeTerms->period->name) && !empty($employeeTerms->period->name) ? 'This employment is '.$employeeTerms->period->name : null)])
                @include('pages.employee.profile.partials.field', ['label' => 'Employement SSP terms', 'value' => (isset($employeeTerms->SSP->name) ? 'Employee receives '.$employeeTerms->SSP->name : null)])
                @if(isset($employeeTerms->employment_period_id) && $employeeTerms->employment_period_id == 3 && isset($employeeTerms->provision_end) && !empty($employeeTerms->provision_end))
                    @include('pages.employee.profile.partials.field', ['label' => 'Probation End', 'value' => date('jS F, Y', strtotime($employeeTerms->provision_end))])
                @endif
            </div>
        </div>

    </div>

    @include('pages.employee.profile.show-modals')
</div>
</div>
@endsection

@section('script')
    @vite('resources/js/employee-global.js')
    @vite('resources/js/employee-profile.js')
@endsection
