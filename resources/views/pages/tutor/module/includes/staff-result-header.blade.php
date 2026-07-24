@php
    $activeResultMenu = $activeResultMenu ?? 'submission';
    $currentAssessmentPlanId = isset($AssessmentPlan) && isset($AssessmentPlan->id) ? $AssessmentPlan->id : null;

    $initialsFor = function ($name) {
        $clean = preg_replace('/^(Mr|Mrs|Ms|Miss|Dr|Md)\.?\s+/i', '', trim((string) $name));
        $parts = preg_split('/\s+/', $clean ?: 'London Churchill');
        $first = mb_substr($parts[0] ?? 'L', 0, 1);
        $last = mb_substr($parts[count($parts) - 1] ?? 'C', 0, 1);
        return mb_strtoupper($first . $last);
    };

    $realPhoto = function ($person) {
        $url = $person->photo_url ?? null;
        return ($url && !\Illuminate\Support\Str::startsWith($url, 'data:')) ? $url : null;
    };

    $moduleTitle = $data->module ?? ($plan->creations->module_name ?? 'Module');
    $moduleTitlePrefix = $moduleTitle;
    $moduleTitleSuffix = '';
    if (preg_match('/^(.*?)(\s*\([^)]+\))$/', $moduleTitle, $matches)) {
        $moduleTitlePrefix = trim($matches[1]);
        $moduleTitleSuffix = $matches[2];
    }

    $courseTitle = $data->course ?? ($plan->course->name ?? '');
    $termTitle = $data->term_name ?? ($plan->attenTerm->name ?? '');
    $classType = isset($plan->class_type) && !empty($plan->class_type) ? $plan->class_type : ($data->classType ?? '');
    $tutorEmployee = $plan->tutor->employee ?? null;
    $personalTutorEmployee = $plan->personalTutor->employee ?? null;

    $people = [];
    if ($tutorEmployee) {
        $people[] = ['name' => $tutorEmployee->full_name, 'role' => 'Tutor', 'photo' => $realPhoto($tutorEmployee)];
    }
    if ($personalTutorEmployee) {
        $people[] = ['name' => $personalTutorEmployee->full_name, 'role' => 'Personal Tutor', 'photo' => $realPhoto($personalTutorEmployee)];
    }
@endphp

<div class="sr-titlebar">
    <span class="sr-titlebar__icon">
        <i data-lucide="file-check-2" class="w-5 h-5"></i>
    </span>
    <div>
        <div class="sr-titlebar__kicker">Assessment · Grading</div>
        <h1 class="sr-titlebar__title">Staff Result Submission</h1>
    </div>
</div>

<div class="sr-hero-card">
    <div class="sr-hero">
        <div class="sr-hero__grid"></div>
        <div class="sr-hero__inner">
            <div class="sr-hero__main">
                <div class="sr-kicker">{{ $courseTitle }}{{ $termTitle ? ' · '.$termTitle : '' }}</div>
                <h2 class="sr-module-title">
                    {{ $moduleTitlePrefix }}
                    @if($moduleTitleSuffix)
                        <span>{{ $moduleTitleSuffix }}</span>
                    @endif
                </h2>

                <div class="sr-meta">
                    <div class="sr-meta__card">
                        <span class="sr-meta__icon is-gold"><i data-lucide="layers" class="w-5 h-5"></i></span>
                        <span>
                            <span class="sr-meta__label">Group</span>
                            <span class="sr-meta__value">{{ $data->group ?? '' }}</span>
                        </span>
                    </div>
                    <div class="sr-meta__card">
                        <span class="sr-meta__icon is-green"><i data-lucide="users" class="w-5 h-5"></i></span>
                        <span>
                            <span class="sr-meta__label">Student</span>
                            <span class="sr-meta__value is-count">{{ $studentCount }}</span>
                        </span>
                    </div>
                    <div class="sr-meta__card">
                        <span class="sr-meta__icon is-slate"><i data-lucide="calendar-days" class="w-5 h-5"></i></span>
                        <span>
                            <span class="sr-meta__label">Class Type</span>
                            <span class="sr-meta__value">{{ $classType }}</span>
                        </span>
                    </div>
                </div>
            </div>

            @if(count($people) > 0)
                <div class="sr-team">
                    <div class="sr-team__label">Tutor</div>
                    <div class="sr-team__list">
                        @foreach($people as $person)
                            <div class="sr-person">
                                <span class="sr-person__avatar">
                                    @if($person['photo'])
                                        <img alt="{{ $person['name'] }}" src="{{ $person['photo'] }}">
                                    @else
                                        {{ $initialsFor($person['name']) }}
                                    @endif
                                </span>
                                <span>
                                    <span class="sr-person__name">{{ $person['name'] }}</span>
                                    <span class="sr-person__role">{{ $person['role'] }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="sr-result-menu">
        <a id="submission-tab" href="{{ route('results-staff-submission.show', $plan->id) }}" class="sr-result-menu__item {{ $activeResultMenu === 'submission' ? 'is-active' : '' }}" aria-selected="{{ $activeResultMenu === 'submission' ? 'true' : 'false' }}">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            <span>Result Submission</span>
        </a>

        <div id="comparison-tab" class="dropdown sr-result-menu__dropdown">
            <button type="button" class="dropdown-toggle sr-result-menu__item {{ $activeResultMenu === 'comparison' ? 'is-active' : '' }}" aria-expanded="false" data-tw-toggle="dropdown">
                <i data-lucide="badge-check" class="w-4 h-4"></i>
                <span>Result Comparison</span>
                <i data-lucide="chevron-down" class="w-4 h-4 sr-chevron"></i>
            </button>
            <div class="dropdown-menu sr-comparison-menu w-80">
                <div class="dropdown-content">
                    <div class="sr-comparison-menu__header">View Submission List</div>
                    @if($submissionAssessment->count() > 0)
                        @foreach ($submissionAssessment as $submission)
                            @php
                                if(isset($submission->published_at) && !empty($submission->published_at)) {
                                    $publishedAt = \Carbon\Carbon::parse($submission->published_at)->format('jS M y H:i');
                                } else {
                                    $publishedAt = 'Not Published';
                                }
                                $isCurrentSubmission = (string) $currentAssessmentPlanId === (string) $submission->id;
                                $submissionLabel = trim(($submission->courseModuleBase->assesment_code ?? '') . ' · ' . ($submission->courseModuleBase->assesment_name ?? ''), ' ·');
                            @endphp
                            <a href="{{ route('result.comparison', [$submission->plan_id, $submission->id]) }}" class="sr-comparison-menu__item {{ $isCurrentSubmission ? 'is-active' : '' }}">
                                <span class="sr-comparison-menu__icon"><i data-lucide="check-square" class="w-4 h-4"></i></span>
                                <span class="sr-comparison-menu__title">{{ $submissionLabel }} — {{ $publishedAt }}</span>
                            </a>
                        @endforeach
                    @else
                        <div class="sr-empty-state is-compact">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            <span>There are no submissions yet.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
