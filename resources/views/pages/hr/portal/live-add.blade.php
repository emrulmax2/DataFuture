@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('body_class', 'hr-live-add-body')

@section('subcontent')
    <div class="hr-live-add-page">
        <div class="hr-live-add-page__inner">
            <section class="hr-live-add-title-card">
                <span class="hr-live-add-title-card__accent"></span>
                <div class="hr-live-add-title-card__main">
                    <span class="hr-live-add-title-icon">
                        <i data-lucide="clock-3"></i>
                    </span>
                    <div>
                        <div class="hr-live-add-eyebrow">Manual Entry</div>
                        <h1>Add Attendance</h1>
                    </div>
                </div>
                <div class="hr-live-add-actions">
                    <a href="{{ route('hr.portal.live.attedance') }}" class="hr-live-add-btn hr-live-add-btn--ghost">
                        <i data-lucide="chevron-left"></i>
                        <span>Back To Live</span>
                    </a>
                    <button type="button" id="saveLiveAttendance" class="hr-live-add-btn hr-live-add-btn--primary liveAttendanceSaveBtn">
                        <i data-lucide="save"></i>
                        <span>Save Attendance</span>
                        <svg class="hr-live-add-btn__spinner" width="18" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".35" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
            </section>

            <form method="post" action="#" id="attendanceLiveForm">
                <section class="hr-live-add-selector-card">
                    <div class="hr-live-add-selector-grid">
                        <div class="hr-live-add-field">
                            <label for="liveAttendanceDate" class="hr-live-add-label">Date <span>*</span></label>
                            <label class="hr-live-add-date-field" for="liveAttendanceDate">
                                <i data-lucide="calendar-days"></i>
                                <input id="liveAttendanceDate" name="the_date" value="{{ date('d-m-Y') }}" type="text" placeholder="DD-MM-YYYY">
                            </label>
                            <div id="liveAttendanceDateLong" class="hr-live-add-date-note">{{ date('l, j F Y') }}</div>
                        </div>
                        <div class="hr-live-add-field">
                            <label for="employeeIDS" class="hr-live-add-label">
                                Employees <span>*</span>
                                <em class="hr-live-add-selected-count">&middot; 0 selected</em>
                            </label>
                            <div class="hr-live-add-tom-wrap">
                                <select id="employeeIDS" name="employees[]" class="w-full tom-selects hr-live-add-employee-select" multiple>
                                    @if(!empty($employee))
                                        @foreach($employee as $emp)
                                            @php
                                                $jobTitle = $emp->employment?->employeeJobTitle?->name ?? '';
                                                $department = $emp->employment?->department?->name ?? '';
                                                $role = trim($jobTitle.(!empty($department) ? ' - '.$department : ''));
                                                $photo = trim((string) ($emp->photo ?? ''));
                                                $photoPath = !empty($photo) ? 'public/employees/'.$emp->id.'/'.$photo : '';
                                                $photoUrl = (!empty($photoPath) && \Illuminate\Support\Facades\Storage::disk('local')->exists($photoPath))
                                                    ? \Illuminate\Support\Facades\Storage::disk('local')->url($photoPath)
                                                    : '';
                                            @endphp
                                            <option value="{{ $emp->id }}" data-role="{{ $role }}" data-photo="{{ $photoUrl }}">{{ $emp->full_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="liveAttendanceRowsCard" class="hr-live-add-entry-card">
                    <div class="hr-live-add-table-scroll relative">
                        <table id="addLiveAttendanceTable" class="hr-live-add-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th><span class="hr-live-add-heading-dot hr-live-add-heading-dot--in"></span>Clock In</th>
                                    <th><span class="hr-live-add-heading-dot hr-live-add-heading-dot--break"></span>Breaks</th>
                                    <th><span class="hr-live-add-heading-dot hr-live-add-heading-dot--out"></span>Clock Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="noticeRow">
                                    <td colspan="4">
                                        <div class="hr-live-add-empty" role="status">
                                            <span>
                                                <i data-lucide="user-plus"></i>
                                            </span>
                                            <strong>No employees selected</strong>
                                            <small>Pick one or more staff above to log their clock-in and clock-out times.</small>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="leaveTableLoader">
                            <svg width="30" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="rgb(255, 255, 255)" class="w-10 h-10 text-danger">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </div>
                    </div>
                    <div class="hr-live-add-table-footer">
                        <span><strong id="attendanceSelectedCountFooter">0</strong> employee <span class="hr-live-add-record-word">records</span> ready to save</span>
                        <button type="button" id="saveLiveAttendanceFooter" class="hr-live-add-btn hr-live-add-btn--primary liveAttendanceSaveBtn">
                            <i data-lucide="save"></i>
                            <span>Save Attendance</span>
                            <svg class="hr-live-add-btn__spinner" width="18" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".35" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </div>
    
    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="NONE" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    
    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="NONE" class="warningCloser btn btn-danger w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

@endsection
@section('script')
    @vite('resources/js/hr-add-live-attendance.js')
@endsection
