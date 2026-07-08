@php
    $epFullName = $employee->title->name.' '.$employee->first_name.' '.$employee->last_name;
    $epJob      = $employment->employeeJobTitle->name ?? null;
    $epDept     = $employment->department->name ?? null;
    $epWorks    = $employment->works_number ?? null;
    $epRoleBits = array_filter([$epJob, $epDept, ($epWorks ? 'Employee No. '.$epWorks : null)]);
    $epActive   = isset($employee->status) && $employee->status == 1;
    $epCanLogin = isset(auth()->user()->priv()['login_as_user']) && auth()->user()->priv()['login_as_user'] == 1;
    $epCanHr    = isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1;
    $epCanSet   = in_array(auth()->user()->id, [1, 7, 8]);
@endphp

<div class="ep-cover">
    <div class="ep-cover__inner">

        {{-- Breadcrumb --}}
        <nav class="ep-crumb">
            <a href="{{ route('staff.dashboard') }}">Dashboard</a>
            <i data-lucide="chevron-right" class="w-[11px] h-[11px]"></i>
            <span class="is-current">Profile</span>
        </nav>

        <div class="ep-cover__row">

            {{-- Avatar --}}
            <div class="ep-cover__avatar">
                <img alt="{{ $epFullName }}" src="{{ $employee->brand_photo_url }}">
                <button data-tw-toggle="modal" data-tw-target="#addStudentPhotoModal" type="button" title="Change photo" class="ep-cover__cam">
                    <i data-lucide="camera" class="w-[13px] h-[13px]"></i>
                </button>
            </div>

            {{-- Identity --}}
            <div class="ep-cover__id">
                <div class="ep-cover__eyebrow">
                    <span class="ep-cover__label">Profile of</span>
                    <span class="lcc-badge has-dot {{ $epActive ? 'lcc-badge--active' : 'lcc-badge--inactive' }}">{{ $epActive ? 'Active' : 'Inactive' }}</span>
                </div>
                <h1 class="ep-cover__name">{{ $epFullName }}</h1>
                @if(count($epRoleBits))
                    <div class="ep-cover__role">{{ implode(' · ', $epRoleBits) }}</div>
                @endif

                <div class="ep-cover__chips">
                    <span class="ep-chip"><i data-lucide="mail" class="w-[13px] h-[13px]"></i>{{ $employee->email ?: '—' }}</span>
                    <span class="ep-chip"><i data-lucide="phone" class="w-[13px] h-[13px]"></i>{{ $employee->telephone ?: '—' }} <em>Phone</em></span>
                    <span class="ep-chip"><i data-lucide="smartphone" class="w-[13px] h-[13px]"></i>{{ $employee->mobile ?: '—' }} <em>Mobile</em></span>
                    @php
                        $epAddr = [];
                        if(isset($employee->address)) {
                            foreach(['address_line_1','city','post_code','country'] as $ak) {
                                if(!empty($employee->address->{$ak})) { $epAddr[] = $employee->address->{$ak}; }
                            }
                        }
                    @endphp
                    @if(count($epAddr))
                        <span class="ep-chip"><i data-lucide="map-pin" class="w-[13px] h-[13px]"></i>{{ implode(', ', $epAddr) }}</span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="ep-cover__actions">
                @if($epCanLogin)
                    <a href="{{ route('impersonate', $employee->user_id) }}" class="ep-btn ep-btn--light">
                        Login As User <i data-lucide="log-in" class="w-4 h-4"></i>
                    </a>
                @endif
                @if($epCanHr)
                    <button data-id="{{ $employee->address_id }}" data-type="employee" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler ep-btn ep-btn--ghost">
                        <i data-lucide="pencil" class="w-[14px] h-[14px]"></i> Edit Contact &amp; Address
                    </button>
                @endif
                @if($epCanSet)
                    <div class="dropdown inline-block" data-tw-placement="bottom-end" id="profileSettingsDropdown">
                        <button class="dropdown-toggle ep-btn ep-btn--ghost !px-3" aria-expanded="false" data-tw-toggle="dropdown" title="Profile settings">
                            <i data-lucide="user-cog" class="w-[15px] h-[15px]"></i>
                        </button>
                        <div class="dropdown-menu w-64">
                            <div class="dropdown-content text-slate-700 dark:text-slate-300">
                                <h6 class="dropdown-header">Profile Settings</h6>
                                <hr class="dropdown-divider">
                                <form method="post" action="#" id="profileSettingsForm" class="p-2">
                                    <div>
                                        <div class="form-check form-switch">
                                            <label class="form-check-label ml-0 mr-5" for="can_access_all">Can access all Profile</label>
                                            <input id="can_access_all" {{ (isset($employee->can_access_all) && $employee->can_access_all == 1 ? 'checked' : '') }} class="form-check-input ml-auto" name="can_access_all" value="1" type="checkbox">
                                        </div>
                                    </div>
                                    <div class="mt-3 mb-4">
                                        <div class="form-check form-switch">
                                            <label class="form-check-label ml-0 mr-5" for="locked_profile">Locked profile</label>
                                            <input {{ (isset($employee->locked_profile) && $employee->locked_profile == 1 ? 'checked' : '') }} id="locked_profile" class="form-check-input ml-auto" name="locked_profile" value="1" type="checkbox">
                                        </div>
                                    </div>
                                    <hr class="dropdown-divider">
                                    <div class="flex justify-between items-center mt-3">
                                        <button type="button" class="dismisProfSetDropdown btn btn-secondary btn-sm w-auto mr-auto">Close</button>
                                        <button type="submit" id="saveProfileSettingBtn" class="btn btn-primary btn-sm w-auto ml-auto">
                                            Save
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
                                    </div>
                                    <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- BEGIN: Upload Photo Modal -->
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
                <form method="post" action="{{ route('employee.upload.photo') }}" class="dropzone" id="addStudentPhotoForm" style="padding: 5px;" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input name="documents" type="file" />
                    </div>
                    <div class="dz-message" data-dz-message>
                        <div class="text-lg font-medium">Drop file here or click to upload.</div>
                        <div class="text-slate-500">Select .jpg, .png, or .gif formate image. Max file size should be 5MB.</div>
                    </div>
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                <button type="button" id="uploadStudentPhotoBtn" class="btn btn-primary w-auto">
                    Upload
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
            </div>
        </div>
    </div>
</div>
<!-- END: Upload Photo Modal -->
