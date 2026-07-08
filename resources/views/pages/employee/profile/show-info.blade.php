@php
    $jobTitle   = $employment->employeeJobTitle->name ?? null;
    $deptName   = $employment->department->name ?? null;
    $worksNo    = $employment->works_number ?? null;
    $roleBits   = array_filter([$jobTitle, $deptName, ($worksNo ? 'No. '.$worksNo : null)]);
@endphp

<div class="intro-y box mt-5 overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(280px,1.3fr)_minmax(240px,1fr)_minmax(220px,.9fr)] gap-6 lg:gap-7 p-6">

        {{-- Identity --}}
        <div class="flex items-center gap-5">
            <div class="relative flex-none w-24 h-24 image-fit">
                <img alt="{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}" class="rounded-full ring-4 ring-slate-100 dark:ring-darkmode-400" src="{{ $employee->brand_photo_url }}">
                <button data-tw-toggle="modal" data-tw-target="#addStudentPhotoModal" type="button" title="Change photo" class="absolute -bottom-0.5 -right-0.5 flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white border-2 border-white dark:border-darkmode-600 hover:bg-primary-hover transition-colors">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="min-w-0">
                <div class="font-display text-2xl font-semibold text-slate-800 dark:text-white leading-tight">{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}</div>
                @if(count($roleBits))
                <div class="text-[13px] font-semibold text-slate-500 dark:text-slate-400 mt-1.5">{{ implode(' · ', $roleBits) }}</div>
                @endif
                <div class="mt-3">
                    <span class="lcc-badge has-dot {{ $employee->status == 1 ? 'lcc-badge--active' : 'lcc-badge--inactive' }}">{{ $employee->status == 1 ? 'Active' : 'Inactive' }}</span>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="lg:border-l border-slate-100 dark:border-darkmode-400 lg:pl-7 border-t lg:border-t-0 pt-5 lg:pt-0">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">Contact Details</div>
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <i data-lucide="mail" class="w-4 h-4 text-primary flex-none"></i>
                    <span class="text-[13.5px] font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $employee->email ?: '—' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <i data-lucide="phone" class="w-4 h-4 text-primary flex-none"></i>
                    <span class="text-[13.5px] font-semibold text-slate-700 dark:text-slate-200">{{ $employee->telephone ?: '—' }}</span>
                    <span class="text-[11px] font-semibold text-slate-400">Phone</span>
                </div>
                <div class="flex items-center gap-3">
                    <i data-lucide="smartphone" class="w-4 h-4 text-primary flex-none"></i>
                    <span class="text-[13.5px] font-semibold text-slate-700 dark:text-slate-200">{{ $employee->mobile ?: '—' }}</span>
                    <span class="text-[11px] font-semibold text-slate-400">Mobile</span>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="lg:border-l border-slate-100 dark:border-darkmode-400 lg:pl-7 border-t lg:border-t-0 pt-5 lg:pt-0 addressWrap" id="employeeAddress">
            <div class="flex items-center justify-between mb-3">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Address</div>
                @if(isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
                <button data-id="{{ $employee->address_id }}" data-type="employee" data-tw-toggle="modal" data-tw-target="#addressModal" title="Edit address" class="addressPopupToggler inline-flex items-center justify-center w-7 h-7 rounded-md border border-slate-200 dark:border-darkmode-400 bg-white dark:bg-darkmode-600 text-slate-500 hover:text-primary hover:border-primary transition-colors">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                </button>
                @endif
            </div>
            <div class="flex items-start gap-2.5">
                <i data-lucide="map-pin" class="w-4 h-4 text-primary flex-none mt-0.5"></i>
                <span class="uppercase text-[13.5px] font-semibold text-slate-700 dark:text-slate-200 leading-relaxed addresses">
                    @if(isset($employee->address->address_line_1) && $employee->address->address_line_1 > 0)
                        @if(isset($employee->address->address_line_1) && !empty($employee->address->address_line_1))
                            <span class="font-medium">{{ $employee->address->address_line_1 }}</span><br/>
                        @endif
                        @if(isset($employee->address->address_line_2) && !empty($employee->address->address_line_2))
                            <span class="font-medium">{{ $employee->address->address_line_2 }}</span><br/>
                        @endif
                        @if(isset($employee->address->city) && !empty($employee->address->city))
                            <span class="font-medium">{{ $employee->address->city }}</span>,
                        @endif
                        @if(isset($employee->address->state) && !empty($employee->address->state))
                            <span class="font-medium">{{ $employee->address->state }}</span>,
                        @endif
                        @if(isset($employee->address->post_code) && !empty($employee->address->post_code))
                            <span class="font-medium">{{ $employee->address->post_code }}</span>,<br/>
                        @endif
                        @if(isset($employee->address->country) && !empty($employee->address->country))
                            <span class="font-medium">{{ $employee->address->country }}</span><br/>
                        @endif
                    @else
                        <span class="font-medium text-warning normal-case italic">Not Set Yet!</span><br/>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Tabs / section navigation --}}
    <div class="border-t border-slate-100 dark:border-darkmode-400 px-3">
        @include('pages.employee.profile.show-menu')
    </div>
</div>

<!-- BEGIN: Import Modal -->
<div id="addStudentPhotoModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Upload Profile Photo</h2>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div class="modal-body">
                <form method="post"  action="{{ route('employee.upload.photo') }}" class="dropzone" id="addStudentPhotoForm" style="padding: 5px;" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input name="documents" type="file" />
                    </div>
                    <div class="dz-message" data-dz-message>
                        <div class="text-lg font-medium">Drop file here or click to upload.</div>
                        <div class="text-slate-500">
                            Select .jpg, .png, or .gif formate image. Max file size should be 5MB.
                        </div>
                    </div>
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                <button type="button" id="uploadStudentPhotoBtn" class="btn btn-primary w-auto">
                    Upload
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
            </div>
        </div>
    </div>
</div>
<!-- END: Import Modal -->
