@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $hour = (int) date('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        $currentTermName = (isset($termList[$currenTerm]->name)) ? $termList[$currenTerm]->name : null;

        /* Only parent plans are real modules — child plans are the tutorial
           sessions hanging off them, already flagged on the parent card. */
        $currentModules = [];

        if (!empty($data) && isset($data[$currenTerm])) {
            foreach ($data[$currenTerm] as $module) {
                if ($module->parent_id == 0) {
                    $currentModules[] = $module;
                }
            }
        }

        /* A module is delivered as a Theory plan; the Tutorial and Seminar
           plans alongside it are sessions, not separate modules. Only the
           Theory ones are counted. */
        $theoryModules = array_values(array_filter($currentModules, function ($module) {
            return strcasecmp(trim((string) $module->classType), 'Theory') === 0;
        }));

        $snapshot = isset($termSnapshot) ? $termSnapshot : null;

        /* A stable, non-random tint per module so the same module keeps the
           same colour between visits. */
        $iconVariant = function ($name) {
            $variants = ['', ' spf-module__icon--v1', ' spf-module__icon--v2', ' spf-module__icon--v3', ' spf-module__icon--v4'];

            return 'spf-module__icon' . $variants[crc32((string) $name) % count($variants)];
        };

        $initials = function ($name) {
            $words = preg_split('/\s+/', trim((string) $name));
            $letters = '';

            foreach ($words as $word) {
                if ($word !== '' && ctype_alpha($word[0])) {
                    $letters .= strtoupper($word[0]);
                }

                if (strlen($letters) === 2) {
                    break;
                }
            }

            return $letters !== '' ? $letters : 'M';
        };

        /* Forms still inside their publication window. */
        $liveForms = collect($doItOnline)->filter(function ($form) {
            return empty($form->end_to) || $form->end_to === '0000-00-00'
                || strtotime($form->end_to) > strtotime(date('Y-m-d'));
        })->values();

        /* Reporting an IT issue is a portal service, not a downloadable form,
           and the design pins it to the quick links — so it is held out of the
           truncated list below rather than being lost to the cut. */
        $quickForms = $liveForms->reject(function ($form) {
            return $form->form_name === 'Report any IT issues on campus';
        })->take(7);

        $formLink = function ($form) use ($reportItAll) {
            if ($form->form_name == 'Document / ID Card Replacement request / Printer Balance Top up') {
                return route('students.document-request-form.products');
            }

            if ($form->form_name == 'Report any IT issues on campus' && isset($reportItAll) && count($reportItAll) > 0) {
                return route('students.report-any-it-issues');
            }

            return $form->form_link;
        };
    @endphp

    <div class="spf-page-head">
        <div>
            <div class="spf-eyebrow">
                {{ date('l j F Y') }}@if($currentTermName) &middot; {{ $currentTermName }} term @endif
            </div>
            <h1 class="spf-h1">{{ $greeting }}, {{ $student->first_name }}</h1>
        </div>
        <div class="spf-spacer"></div>
        @include('pages.students.frontend.dashboard.head-chips')
    </div>

    @include('pages.students.frontend.dashboard.show-info')

    <div class="spf-stats">
        <div class="spf-stat">
            <div class="spf-stat__label">Modules this term</div>
            <div class="spf-stat__value">{{ count($theoryModules) }}</div>
        </div>
        <div class="spf-stat">
            <div class="spf-stat__label">Results published</div>
            <div class="spf-stat__value">
                {{ $snapshot ? $snapshot['results_published'] : 0 }}
                <small>of {{ $snapshot ? $snapshot['results_total'] : 0 }}</small>
            </div>
        </div>
        <div class="spf-stat">
            <div class="spf-stat__label">Term performance</div>
            <div class="spf-stat__value spf-stat__value--{{ $snapshot && $snapshot['tone'] === 'green' ? 'green' : 'rust' }}">
                {{ $snapshot ? round($snapshot['percent']) : 0 }}%
            </div>
        </div>
    </div>

    <section class="spf-section">
        <div class="spf-section__head">
            <h2 class="spf-h2">My modules</h2>
            <div class="spf-spacer"></div>
            @if($currentTermName)
                <span class="spf-chip spf-chip--cream spf-chip--lg">{{ $currentTermName }}</span>
            @endif
        </div>

        @if(count($currentModules) > 0)
            <div class="spf-modules">
                @foreach($currentModules as $module)
                    <a href="{{ route('students.dashboard.plan.module.show', $module->id) }}" target="_blank" rel="noopener" class="spf-module">
                        <div class="spf-module__top">
                            <span class="{{ $iconVariant($module->module) }}">{{ $initials($module->module) }}</span>
                            <div class="spf-module__body">
                                <div class="spf-module__name">{{ $module->module }}</div>
                                <div class="spf-module__sub">
                                    {{ $module->classType ?: 'Unknown' }}{{ $module->has_tutorial ? ' · Tutorial' : '' }}
                                </div>
                            </div>
                        </div>
                        <div class="spf-module__foot">
                            <div class="spf-avatars">
                                @if($module->tutor_photo)
                                    <img src="{{ $module->tutor_photo }}" alt="Module tutor">
                                @endif
                                @if($module->has_tutorial && $module->p_tutor_photo)
                                    <img src="{{ $module->p_tutor_photo }}" alt="Tutorial tutor">
                                @endif
                                @if($module->personal_tutor_photo)
                                    <img src="{{ $module->personal_tutor_photo }}" alt="Personal tutor">
                                @endif
                            </div>
                            <div class="spf-spacer"></div>
                            @if(!empty($module->group))
                                <span class="spf-chip spf-chip--grey">{{ strtoupper($module->group) }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="spf-empty">No modules are assigned for this term yet.</div>
        @endif
    </section>

    <section class="spf-section">
        <div class="spf-section__head">
            <h2 class="spf-h2">Do it online</h2>
            <span class="spf-section__note">Quick links</span>
            <div class="spf-spacer"></div>
            <a href="{{ route('students.dashboard.forms') }}" class="spf-linkbtn">All forms &rarr;</a>
        </div>

        @if($liveForms->count() > 0)
            <div class="spf-quicklinks">
                @foreach($quickForms as $form)
                    <a href="{{ $formLink($form) }}" class="spf-quicklink">
                        <i data-lucide="file-text" class="w-4 h-4"></i>{{ $form->form_name }}
                    </a>
                @endforeach
                <a href="{{ route('students.report-any-it-issues') }}" class="spf-quicklink">
                    <i data-lucide="wrench" class="w-4 h-4"></i>Report an IT issue
                </a>
                <a href="{{ route('students.dashboard.forms') }}" class="spf-btn spf-btn--dark">View all forms &rarr;</a>
            </div>
        @else
            <div class="spf-empty">No online forms are available at the moment.</div>
        @endif
    </section>

    @if (session('verifySuccessMessage'))
        <!-- BEGIN: Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Success !</div>
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
    @vite('resources/js/student-frontend-dashboard.js')
@endsection
