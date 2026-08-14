@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - London Churchill College</title>
@endsection

@section('body_class', 'lccd-page')

@section('styles')
    @vite('resources/css/staff-dashboard.css')
@endsection
@if(Auth::guard('applicant')->check())
  
@elseif(Auth::guard('student')->check())

@elseif(Auth::guard('agent')->check())

@else
    @php $employeeUser = cache()->get('employeeCache'.Auth::id()) ?? Auth::user()->load('employee'); @endphp
@endif

@section('subcontent')
    @php
        // Module tiles. Each entry keeps the exact route + privilege gate the
        // previous image-based grid used; only the presentation changed.
        $lccdPriv = auth()->user()->priv();
        $lccdUnlocked = !$work_history_lock && auth()->user()->remote_access;

        $lccdModules = [
            ['label' => 'My HR', 'icon' => 'user', 'href' => route('user.account')],
        ];

        $lccdGated = [
            ['applicant', 'Applicant', 'file-plus', 'admission', $applicant],
            ['live', 'Students', 'graduation-cap', 'student', null],
            ['tutor_2', 'Tutor', 'presentation', 'tutor-dashboard.show.new', null],
            ['personal_tutor', 'Personal Tutor', 'heart-handshake', 'pt.dashboard', null],
            ['hr_porta', 'Human Resources', 'users', 'hr.portal', null],
            ['programme_dashboard', 'Manager', 'layout-dashboard', 'programme.dashboard', null],
            ['access_account', 'Accounts', 'wallet', 'accounts', null],
            ['library_management', 'Library', 'library', 'library.management.index', null],
            ['budget_manager', 'Budget', 'pound-sterling', 'budget.management', null],
            ['news_events', 'News & Events', 'megaphone', 'news.updates', null],
            ['file_manager', 'File Manager', 'folder-open', 'file.manager', null],
        ];

        foreach ($lccdGated as [$lccdKey, $lccdLabel, $lccdIcon, $lccdRoute, $lccdCount]) {
            if ($lccdUnlocked && isset($lccdPriv[$lccdKey]) && $lccdPriv[$lccdKey] == 1) {
                $lccdModules[] = [
                    'label' => $lccdLabel,
                    'icon' => $lccdIcon,
                    'href' => route($lccdRoute),
                    'count' => $lccdCount,
                ];
            }
        }

        // Shift panel visibility — unchanged from the previous markup.
        $lccdShowShift = Auth::user()
            && (Route::currentRouteName() == 'dashboard' || Route::currentRouteName() == 'staff.dashboard')
            && !empty($home_work_history_btns)
            && (
                (!in_array(auth()->user()->last_login_ip, $venue_ips) && isset($home_work) && $home_work)
                || (in_array(auth()->user()->last_login_ip, $venue_ips) && isset($desktop_login) && $desktop_login)
            );

        $lccdShowReportAll = !$work_history_lock && isset($reportItAll) && $reportItAll->count() > 0;
        $lccdCan = fn ($key) => isset($lccdPriv[$key]) && $lccdPriv[$key] == 1;

        // Only draw the quick-actions panel when at least one row survives its gate.
        $lccdHasQuickActions = $myfollowups > 0 || $lccdShowReportAll || (!$work_history_lock && (
            trim($internal_link_buttons) !== ''
            || $lccdCan('group_email')
            || $lccdCan('student_due_rep')
            || ($lccdCan('expired_docs') && $hasDocumentReminder)
            || $lccdCan('report_it_all')
        ));

        // Processes with something outstanding — the only ones the old grid drew.
        $lccdProcesses = [];
        $lccdQueue = 0;
        if (!$work_history_lock) {
            foreach ($myPendingTask as $lccdProcessId => $lccdProcess) {
                if (($lccdProcess['outstanding_tasks'] ?? 0) > 0) {
                    $lccdProcesses[$lccdProcessId] = $lccdProcess;
                    $lccdQueue += $lccdProcess['outstanding_tasks'];
                }
            }
        }
    @endphp

    <div class="lccd">
        @if($work_history_lock)
            @php $lccdOnBreak = $work_history_lock_no != 1; @endphp
            <section class="lccd-locked">
                <span class="lccd-locked__icon"><i data-lucide="lock"></i></span>
                <div class="lccd-locked__copy">
                    <h2 class="lccd-locked__title">{{ $lccdOnBreak ? 'You are on a break' : 'You are not clocked in' }}</h2>
                    <p class="lccd-locked__body">
                        Your applications, quick actions and processes stay locked until you
                        {{ $lccdOnBreak ? 'return to work' : 'clock in' }}.
                    </p>
                </div>
                @if($lccdShowShift)
                    {{-- While locked this is the page's only call to action, so the shift
                         buttons sit here instead of in the rail. --}}
                    <div class="lccd-shift logBtns">
                        {!! $home_work_history_btns !!}
                    </div>
                @endif
            </section>
        @endif

        @if($proxyClasses->count() > 0)
            <section class="lccd-section">
                <div class="lccd-section__head">
                    <h2 class="lccd-section__title">Today&rsquo;s classes</h2>
                    <span class="lccd-section__meta">{{ $proxyClasses->count() }} {{ Str::plural('class', $proxyClasses->count()) }}</span>
                </div>

                <div class="lccd-classes">
                    @foreach($proxyClasses as $class)
                        @php
                            $showClass = 0;
                            if(in_array(auth()->user()->last_login_ip, $venue_ips)):
                                $listStart = date('Y-m-d').' '.$class->plan->start_time;
                                $listEnd = date('Y-m-d').' '.$class->plan->end_time;
                                $classStart = date('Y-m-d H:i:s', strtotime('-15 minutes', strtotime($listStart)));
                                $classEnd = date('Y-m-d H:i:s', strtotime($listEnd));
                                $currentTime = date('Y-m-d H:i:s');
                                if($currentTime >= $classStart && $currentTime <= $classEnd):
                                    $showClass = 1;
                                elseif($currentTime < $classStart):
                                    $showClass = 2;
                                endif;
                            endif;
                        @endphp
                        <div class="lccd-class">
                            <div class="lccd-class__head">
                                <div class="lccd-class__name">
                                    {{ $class->plan->creations->module_name }}
                                    ({{ $class->plan->group->name }})
                                    {{ (isset($class->plan->class_type) && !empty($class->plan->class_type) ? ' - '.$class->plan->class_type : '') }}
                                </div>
                                <div class="lccd-class__time">{{ (isset($class->plan->start_time) && !empty($class->plan->start_time) ? date('h:i A', strtotime($class->plan->start_time)) : '') }}</div>
                            </div>
                            <div class="lccd-class__actions">
                                @if(isset($class->attendanceInformation->id) && $class->attendanceInformation->id > 0)
                                    @if($class->feed_given == 1)
                                        <a data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" href="{{ route('tutor-dashboard.attendance', [$class->proxy_tutor_id, $class->id, 3]) }}" class="start-punch lccd-btn lccd-btn--outline">
                                            <i data-lucide="view"></i><span>View Attendance</span>
                                        </a>
                                    @else
                                        <a href="{{ route('tutor-dashboard.attendance', [$class->proxy_tutor_id, $class->id, 3]) }}" data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" class="start-punch lccd-btn lccd-btn--go">
                                            <i data-lucide="view"></i><span>Feed Attendance</span>
                                        </a>
                                    @endif
                                    @if($class->feed_given == 1 && $class->attendanceInformation->end_time == null && $class->status == 'Ongoing')
                                        <a data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" data-tw-toggle="modal" data-tw-target="#endClassModal" class="endClassBtn lccd-btn lccd-btn--danger">
                                            <i data-lucide="x-circle"></i><span>End Class</span>
                                        </a>
                                    @endif
                                @else
                                    @if($showClass == 1)
                                        <a data-tw-toggle="modal" data-id="{{ $class['id'] }}" data-tw-target="#startProxyClassModal" class="startClassBtn lccd-btn lccd-btn--go">
                                            <i data-lucide="play"></i><span>Start Class</span>
                                        </a>
                                    @elseif($showClass == 2)
                                        <div class="lccd-note">
                                            <i data-lucide="alert-triangle"></i>
                                            <span>Class Start Button appears 15 minutes before the scheduled time.</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="lccd__cols">
            <div class="lccd__main">
                <section class="lccd-section">
                    <div class="lccd-section__head">
                        <h2 class="lccd-section__title">Applications</h2>
                        <span class="lccd-section__meta">{{ count($lccdModules) }} {{ Str::plural('module', count($lccdModules)) }}</span>
                    </div>

                    <div class="lccd-tiles">
                        @foreach($lccdModules as $lccdModule)
                            <a href="{{ $lccdModule['href'] }}" class="lccd-tile">
                                <span class="lccd-tile__icon"><i data-lucide="{{ $lccdModule['icon'] }}"></i></span>
                                <span class="lccd-tile__label">{{ $lccdModule['label'] }}</span>
                                @if(!empty($lccdModule['count']))
                                    <span class="lccd-chip">{{ $lccdModule['count'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>

                @if(!empty($lccdProcesses))
                    <section class="lccd-card">
                        <div class="lccd-card__head">
                            <h2 class="lccd-card__title">Processes</h2>
                            <span class="lccd-card__hint">Select a process to view its sub-processes</span>
                            <span class="lccd-card__meta">{{ $lccdQueue }} {{ Str::plural('item', $lccdQueue) }} in queue</span>
                        </div>

                        @foreach($lccdProcesses as $process_id => $process)
                            <div class="lccd-process">
                                <a href="javascript:void(0);" class="lccd-process__row processParents process_{{ $process_id }}" data-process="{{ $process_id }}">
                                    <span class="lccd-process__count">{{ $process['outstanding_tasks'] }}</span>
                                    <span class="lccd-process__copy">
                                        <span class="lccd-process__name">{{ $process['name'] }}</span>
                                        <span class="lccd-process__subcount">{{ count($process['tasks'] ?? []) }} {{ Str::plural('sub-process', count($process['tasks'] ?? [])) }}</span>
                                    </span>
                                    <span class="lccd-process__toggle">
                                        <span class="lccd-when-closed">Show<i data-lucide="plus-circle"></i></span>
                                        <span class="lccd-when-open">Hide<i data-lucide="minus-circle"></i></span>
                                    </span>
                                </a>

                                @if(isset($process['tasks']) && !empty($process['tasks']))
                                    <div class="lccd-process__subs">
                                        @foreach($process['tasks'] as $task_id => $pts)
                                            <a href="{{ route('task.manager.show', $task_id) }}" class="lccd-process__sub processTask process_{{ $process_id }}_task" style="display: none;">
                                                <span class="lccd-process__sub-name">{{ $pts->name }}</span>
                                                <span class="lccd-process__sub-count">{{ $pts->pending_task }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </section>
                @endif
            </div>

            <aside class="lccd__rail">
                @if($lccdShowShift && !$work_history_lock)
                    <div>
                        <div class="lccd-rail__label">My shift</div>
                        <div class="lccd-shift logBtns">
                            {!! $home_work_history_btns !!}
                        </div>
                    </div>
                @endif

                @if($lccdHasQuickActions)
                    <div>
                        <div class="lccd-rail__label">Quick actions</div>
                        <div class="lccd-actions">
                            @if(!$work_history_lock)
                                {!! $internal_link_buttons !!}

                                @if($lccdCan('group_email'))
                                    <a href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#senGroupMailModal" class="lccd-action">
                                        <i data-lucide="mails"></i>
                                        <span class="lccd-action__label">Group Email</span>
                                    </a>
                                @endif

                                @if($lccdCan('student_due_rep'))
                                    <a href="{{ route('report.student.due') }}" class="lccd-action">
                                        <i data-lucide="receipt"></i>
                                        <span class="lccd-action__label">Student Due</span>
                                    </a>
                                @endif

                                @if($lccdCan('expired_docs') && $hasDocumentReminder)
                                    <a href="{{ route('file.manager.reminder') }}" class="lccd-action">
                                        <i data-lucide="file-clock"></i>
                                        <span class="lccd-action__label">Expired Documents</span>
                                    </a>
                                @endif
                            @endif

                            @if($myfollowups > 0)
                                <a href="{{ route('followups') }}" class="lccd-action">
                                    <i data-lucide="message-square"></i>
                                    <span class="lccd-action__label">Pending Followups</span>
                                    <span class="lccd-chip">{{ $myfollowups }}</span>
                                    @if($myunreadcomments > 0)
                                        <span class="lccd-chip lccd-chip--count">{{ $myunreadcomments }}</span>
                                    @endif
                                </a>
                            @endif

                            @if($lccdShowReportAll)
                                <a href="{{ route('report.it.all') }}" target="__blank" class="lccd-action">
                                    <i data-lucide="list-checks"></i>
                                    <span class="lccd-action__label">Report IT for all</span>
                                    <span class="lccd-chip">{{ $reportItAll->count() }}</span>
                                </a>
                            @endif

                            @if(!$work_history_lock && $lccdCan('report_it_all'))
                                <a href="{{ route('report.any.it.employee') }}" class="lccd-action lccd-action--danger">
                                    <i data-lucide="life-buoy"></i>
                                    <span class="lccd-action__label">Report Issue</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>

    {{--
        Dashboard modals.

        Every id, hook class (`agreeWith`, `successCloser`, `plan_date_list_id`, …),
        field name and `data-tw-*` attribute is unchanged — only presentation.
        Two rules to keep in mind when editing:
          * the theme relocates `.modal` to <body> on show, so styling hangs off
            the `lccd-modal` class on the modal itself;
          * the submit handlers reach for `#<button> svg` to toggle their spinner,
            so no icon may appear before that spinner inside those buttons.
    --}}

    <!-- BEGIN: Class Start Modal Start -->
    <div id="startProxyClassModal" class="modal lccd-modal lccd-modal--confirm lccd-modal--go" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="startProxyClassForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="lccd-modal__medallion">
                            <i data-lucide="play-circle"></i>
                        </div>
                        <div class="lccd-modal__title confModTitle">Start this class?</div>
                        <div class="lccd-modal__meta">{{ date('l, j F') }}</div>
                        <p class="lccd-modal__body">Your students will be able to have their attendance taken as soon as the class starts.</p>

                        <div class="lccd-modal__field">
                            <label for="proxy_class_tutor_note" class="form-label">Note (optional)</label>
                            <textarea id="proxy_class_tutor_note" name="proxy_class_tutor_note" class="form-control w-full" placeholder="Anything worth recording about this class" rows="3"></textarea>
                        </div>

                        <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">

                        <button type="submit" id="startProxyBtn" class="lccd-modal__primary save">
                            Start class
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
                        <button type="button" data-tw-dismiss="modal" class="lccd-modal__ghost">Not now</button>

                        <input type="hidden" value="{{ $user->employee->id }}" name="employee_id"/>
                        <input type="hidden" name="user_id" value="{{ $user->id }}" />
                        <input type="hidden" name="type" value="3" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Class Start Modal End -->

    <!-- BEGIN: End Class Modal -->
    <div id="endClassModal" class="modal lccd-modal lccd-modal--confirm lccd-modal--danger" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="endClassModalForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="lccd-modal__medallion">
                            <i data-lucide="x-circle"></i>
                        </div>
                        <div class="lccd-modal__title confModTitle">End this class?</div>
                        <div class="lccd-modal__meta">{{ date('l, j F') }}</div>
                        <p class="lccd-modal__body confModDesc">Attendance closes when the class ends and the register can no longer be edited.</p>

                        <input class="plan_date_list_id" type="hidden" name="plan_date_list_id" value="0">
                        <input class="attendance_information_id" type="hidden" name="attendance_information_id" value="0">

                        <button type="submit" id="endClassBtn" class="lccd-modal__primary">
                            Yes, end the class
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
                        <button type="button" data-tw-dismiss="modal" class="lccd-modal__ghost">Not now</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: End Class Modal -->

    <!-- BEGIN: Send Group Mail Modal -->
    <div id="senGroupMailModal" class="modal lccd-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="senGroupMailForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="mr-auto">Send email</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-x-4">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="department_ids" class="form-label">Department</label>
                                <select id="department_ids" name="department_ids[]" class="w-full tom-selects" multiple>
                                    @if($departments->count() > 0)
                                        @foreach($departments as $dpt)
                                            <option value="{{ $dpt->id }}">{{ $dpt->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-department_ids mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="groups_ids" class="form-label">Groups</label>
                                <select id="groups_ids" name="groups_ids[]" class="w-full tom-selects" multiple>
                                    @if($groups->count() > 0)
                                        @foreach($groups as $gr)
                                            <option value="{{ $gr->id }}">{{ $gr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-groups_ids mt-2"></div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="employee_ids" class="form-label">Members <span class="text-danger">*</span></label>
                            <select id="employee_ids" name="employee_ids[]" class="w-full tom-selects" multiple>
                                @if($employees->count() > 0)
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employee_ids mt-2"></div>
                        </div>
                        <div class="mt-4">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input id="subject" type="text" name="subject" class="form-control w-full">
                            <div class="acc__input-error error-subject mt-2"></div>
                        </div>
                        <div class="mt-4">
                            <label for="mailEditor" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="mail_body" id="mailEditor"></textarea>
                            <div class="acc__input-error error-mail_body mt-2"></div>
                        </div>
                        <div class="mt-4 flex justify-start items-center relative">
                            <label for="sendMailsDocument" class="lccd-modal__upload">
                                <i data-lucide="paperclip"></i> Upload attachments
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" multiple name="documents[]" class="absolute w-0 h-0 overflow-hidden opacity-0" id="sendMailsDocument"/>
                        </div>
                        <div id="sendMailsDocumentNames" class="sendMailsDocumentNames mt-3" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="lccd-modal__ghost">Cancel</button>
                        <button type="submit" id="sentMailBtn" class="lccd-modal__primary">
                            Send mail
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
            </form>
        </div>
    </div>
    <!-- END: Send Group Mail Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal lccd-modal lccd-modal--confirm lccd-modal--go" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="lccd-modal__medallion">
                        <i data-lucide="check-circle"></i>
                    </div>
                    <div class="lccd-modal__title successModalTitle"></div>
                    <p class="lccd-modal__body successModalDesc"></p>
                    <button type="button" data-action="DISMISS" class="successCloser lccd-modal__primary">Ok</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal lccd-modal lccd-modal--confirm lccd-modal--danger" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="lccd-modal__medallion">
                        <i data-lucide="alert-octagon"></i>
                    </div>
                    <div class="lccd-modal__title warningModalTitle"></div>
                    <p class="lccd-modal__body warningModalDesc"></p>
                    <button type="button" data-tw-dismiss="modal" class="warningCloser lccd-modal__primary">Ok</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content Data-->
    @if(!auth()->user()->isImpersonated())
        @if($work_history_lock && $work_history_lock_no > 0 && (Session::has('work_history_lock_first_time') == null || Session::get('work_history_lock_first_time') != 1) && ((!in_array(auth()->user()->last_login_ip, $venue_ips) && isset($home_work) && $home_work) || (in_array(auth()->user()->last_login_ip, $venue_ips) && isset($desktop_login) && $desktop_login)))
        @php
            $lccdOnBreak = $work_history_lock_no != 1;
        @endphp
        <!-- BEGIN: Clock-in Confirm Modal -->
        <div id="attendanceHistoryLocModal" class="modal lccd-modal lccd-modal--confirm lccd-modal--{{ $lccdOnBreak ? 'gold' : 'danger' }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="lccd-modal__medallion lccd-modal__medallion--clock">
                            <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
                                <circle cx="24" cy="24" r="21" stroke="currentColor" stroke-width="2.4"/>
                                <path d="M24 5 V8 M43 24 H40 M24 43 V40 M5 24 H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" opacity=".45"/>
                                <path d="M24 24 L15.4 21.2" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                                <path d="M24 24 L16.4 34.5" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"/>
                                <circle cx="24" cy="24" r="1.9" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="lccd-modal__time">{{ date('g:i A') }}</div>
                        <div class="lccd-modal__meta">{{ date('l, j F') }} &middot; {{ $lccdOnBreak ? 'On break' : 'Not clocked in' }}</div>
                        <p class="lccd-modal__body">{{ $lccdOnBreak ? 'It seems you\'re on break. Are you returning to work now?' : 'Looks like you are not clocked in. Ready to start today\'s shift?' }}</p>

                        <button type="button" data-value="{{$work_history_lock_no}}" class="agreeWith actionBtn lccd-modal__primary">{{ $lccdOnBreak ? 'Return to work' : 'Clock in now' }}</button>
                        <button type="button" class="disagreeWith actionBtn lccd-modal__ghost">Not now</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Clock-in Confirm Modal -->
        @endif
    @endif
    @if (session('verifySuccessMessage'))
        <!-- BEGIN: Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Success!</div>
                <div class="text-slate-500 mt-1">{{ session('verifySuccessMessage') }}</div>
            </div>
        </div>
        <!-- END: Notification Content -->
        <!-- BEGIN: Notification Toggle -->
        <button id="success-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
        <!-- END: Notification Toggle -->
    @endif

    
@endsection

@section('script')
    @vite('resources/js/jquery-stopwatch.js')
    @vite('resources/js/staff-dashboard.js')
@endsection