@extends('../layout/' . $layout)

@section('body_class', 'tutor-result-body')

@section('subhead')
    <title>{{ $title }}- </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:wght@400;500;600;700&display=swap" rel="stylesheet">
@endsection

@php
    $initialsFor = function ($name) {
        $clean = preg_replace('/^(Mr|Mrs|Ms|Miss|Dr|Md)\.?\s+/i', '', trim((string) $name));
        $parts = preg_split('/\s+/', $clean ?: 'London Churchill');
        return strtoupper(substr($parts[0] ?? 'L', 0, 1) . substr($parts[count($parts) - 1] ?? 'C', 0, 1));
    };

    $avatarFor = function ($seed) {
        $arr = ['#7a4fa3', '#137a70', '#2f8f5b', '#c94f7c', '#b5602f', '#2f5fa1', '#a13f6b', '#4a7a2f', '#b3261e', '#0d7c73'];
        $h = 0;
        $seed = (string) $seed;
        for ($i = 0; $i < strlen($seed); $i++) {
            $h = ($h * 31 + ord($seed[$i])) % 4294967296;
        }
        return $arr[$h % count($arr)];
    };

    $moduleTitle = $data->module ?? ($plan->creations->module_name ?? 'Module');

    // The demo prints a trailing "(RQF 2023)"-style qualifier in gold.
    $moduleTitleMain = $moduleTitle;
    $moduleTitleTail = '';
    if (preg_match('/^(.*?)\s*(\([^()]*\))\s*$/', trim($moduleTitle), $m)) {
        $moduleTitleMain = $m[1];
        $moduleTitleTail = $m[2];
    }

    $courseTitle = $data->course ?? ($plan->course->name ?? '');
    $termTitle = $data->term_name ?? '';
    $classType = $data->classType ?? '';
    $assessmentTitle = trim(($assessmentPlan->courseModuleBase->type->name ?? '') . ' - ' . ($assessmentPlan->courseModuleBase->type->code ?? ''), ' -');
    $tutorName = $plan->tutor->employee->full_name ?? $data->tutor ?? null;
    $personalTutorName = $plan->personalTutor->employee->full_name ?? $data->personalTutor ?? null;
    $hasResults = isset($result) && count($result) > 0;
@endphp

@section('subcontent')
<div id="tutorResultDetails">

    {{-- title strip --}}
    <div class="rd-titlebar">
        <span class="rd-titlebar__icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
        </span>
        <div>
            <div class="rd-titlebar__kicker">Assessment · Grading</div>
            <h2 class="rd-titlebar__title">Result Details</h2>
        </div>
    </div>

    {{-- module hero --}}
    <div class="rd-shell">
        <div class="rd-hero">
            <div class="rd-hero__glow-gold"></div>
            <div class="rd-hero__glow-green"></div>
            <div class="rd-hero__grid"></div>

            <div class="rd-hero__inner">
                <div class="rd-hero__main">
                    <div class="rd-kicker">{{ $courseTitle }}{{ !empty($termTitle) ? ' · ' . $termTitle : '' }}</div>
                    <h2 class="rd-title">{{ $moduleTitleMain }}@if(!empty($moduleTitleTail)) <span>{{ $moduleTitleTail }}</span>@endif</h2>
                    <div class="rd-badges">
                        <span class="rd-badge is-gold">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>{{ $data->group }}
                        </span>
                        @if(!empty($assessmentTitle))
                            <span class="rd-badge is-green">
                                <span class="rd-badge-dot"></span>{{ $assessmentTitle }}
                            </span>
                        @endif
                    </div>
                </div>

                @if(!empty($tutorName) || !empty($personalTutorName))
                    <div class="rd-team">
                        <div class="rd-team__label">Tutor</div>
                        <div class="rd-team__list">
                            @if($plan->tutor_id > 0 && !empty($tutorName))
                                <div class="rd-person">
                                    <span class="rd-person__avatar">
                                        @if(isset($plan->tutor->employee->photo_url))
                                            <img alt="{{ $tutorName }}" src="{{ $plan->tutor->employee->photo_url }}">
                                        @else
                                            {{ $initialsFor($tutorName) }}
                                        @endif
                                    </span>
                                    <span>
                                        <span class="rd-person__name">{{ $tutorName }}</span>
                                        <span class="rd-person__role">Tutor</span>
                                    </span>
                                </div>
                            @endif

                            @if($plan->personal_tutor_id > 0 && !empty($personalTutorName))
                                <div class="rd-person">
                                    <span class="rd-person__avatar" style="background:#c94f7c;">
                                        @if(isset($plan->personalTutor->employee->photo_url))
                                            <img alt="{{ $personalTutorName }}" src="{{ $plan->personalTutor->employee->photo_url }}">
                                        @else
                                            {{ $initialsFor($personalTutorName) }}
                                        @endif
                                    </span>
                                    <span>
                                        <span class="rd-person__name">{{ $personalTutorName }}</span>
                                        <span class="rd-person__role">Personal Tutor</span>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="rd-meta">
                <div class="rd-meta__card">
                    <span class="rd-meta__icon is-gold">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                    </span>
                    <span>
                        <span class="rd-meta__label">Group</span>
                        <span class="rd-meta__value is-mono">{{ $data->group }}</span>
                    </span>
                </div>
                <div class="rd-meta__card">
                    <span class="rd-meta__icon is-green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path></svg>
                    </span>
                    <span>
                        <span class="rd-meta__label">Student</span>
                        <span class="rd-meta__value is-count">{{ $studentCount }}</span>
                    </span>
                </div>
                <div class="rd-meta__card">
                    <span class="rd-meta__icon is-slate">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2.5"></rect><path d="M8 2v4M16 2v4M3 9h18"></path></svg>
                    </span>
                    <span>
                        <span class="rd-meta__label">Class Type</span>
                        <span class="rd-meta__value">{{ $data->classType }}</span>
                    </span>
                </div>
            </div>
        </div>

        <ul class="rd-tabs" role="tablist">
            <li id="availabilty-tab" role="presentation">
                <a href="javascript:void(0);" class="rd-tab active" data-tw-target="#availabilty" aria-controls="availabilty" aria-selected="true" role="tab">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 2 7l10 5 10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>Result
                </a>
            </li>
        </ul>
    </div>

    <form id="resultBulkInsert" method="post" action="{{ route('result.store') }}">
        <div class="tab-content">
            <div id="availabilty" class="tab-pane active" role="tabpanel" aria-labelledby="availabilty-tab">
                <div class="rd-panel">

                    <div class="rd-panel__bar is-head">
                        <h3 class="rd-panel__title">{{ $assessmentTitle }}</h3>
                        <div class="rd-panel__actions">
                            <a href="{{ route('tutor-dashboard.plan.module.show', $plan->id) }}" class="rd-btn is-ghost">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"></path></svg>Back To Assessment
                            </a>

                            @if($hasResults)
                                <button type="submit" class="update_all_result rd-btn is-green">
                                    <i data-lucide="upload-cloud" class="w-4 h-4"></i> Update Bulk Results <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white"></i>
                                </button>
                                <button type="button" data-assessmentPlan="{{ $assessmentPlan->id }}" class="delete_all_result rd-btn is-danger">
                                    <i data-lucide="trash" class="w-4 h-4"></i> Delete All <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden"></i>
                                </button>
                            @else
                                <button type="submit" id="insertAllResult" class="insert_all_result rd-btn is-amber">
                                    <i data-lucide="upload-cloud" class="w-4 h-4"></i> Insert Bulk Results <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    @include('pages.tutor.module.includes.result.index')

                    <div class="rd-panel__bar is-foot">
                        <h3 class="rd-panel__title">{{ $assessmentTitle }}</h3>
                        <div class="rd-panel__actions">
                            <a href="{{ route('tutor-dashboard.plan.module.show', $plan->id) }}" class="rd-btn is-ghost">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"></path></svg>Back To Assessment
                            </a>

                            @if($hasResults)
                                <button type="submit" class="update_all_result rd-btn is-green">
                                    <i data-lucide="upload-cloud" class="w-4 h-4"></i> Update Bulk Results <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white"></i>
                                </button>
                                <button type="button" data-assessmentPlan="{{ $assessmentPlan->id }}" class="delete_all_result rd-btn is-danger">
                                    <i data-lucide="trash" class="w-4 h-4"></i> Delete All <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden"></i>
                                </button>
                            @else
                                <button type="submit" class="insert_all_result rd-btn is-amber">
                                    <i data-lucide="upload-cloud" class="w-4 h-4"></i> Insert Bulk Results <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
@include('pages.tutor.module.component.result.modal')
@endsection

@section('script')
    @vite('resources/js/plan-tasks.js')
@endsection
