@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php $mcTotal = !empty($moduleCreations) ? count($moduleCreations) : 0; @endphp

    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @if($mcTotal === 0)
                <div class="cm-card">
                    <div class="cm-empty">
                        <span class="cm-empty__icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                        </span>
                        <div class="cm-empty__title cm-serif">No modules to configure</div>
                        <div class="cm-empty__text">This term has no module creations yet. Pick the modules first, then set their assessments here.</div>
                    </div>
                </div>
            @else
                {{-- One step per module; all render, only the current one is
                     visible. Stepping is handled in js/course-term-module-details.js. --}}
                <div id="termModulCreationsStepWizard" data-cm-wizard data-cm-total="{{ $mcTotal }}">
                    @foreach($moduleCreations as $mc)
                        @php
                            $mcIndex = $loop->index + 1;
                            $mcAssessments = $mc->module->assesments ?? null;
                        @endphp

                        <div class="cm-card cm-step"
                             data-cm-step-index="{{ $mcIndex }}"
                             @if(!$loop->first) hidden @endif
                             @if($loop->last) data-cm-step-last @endif>

                            <div class="cm-stephead">
                                <div class="cm-stephead__meta">
                                    <span class="cm-stephead__pill">Step 2 of 2</span>
                                    <span class="cm-stephead__sub">{{ $mcIndex }} of {{ $mcTotal }}</span>
                                </div>
                                <h2 class="cm-stephead__title cm-serif">Module {{ str_pad($mcIndex, 2, '0', STR_PAD_LEFT) }}</h2>
                            </div>

                            <form action="#" method="post" role="form" id="moduleCreationStepForms_{{ $mc->id }}" enctype="multipart/form-data">
                                <div style="padding:24px 26px;">
                                    <div class="cm-readonly">
                                        <div class="cm-readonly__item">
                                            <span class="cm-readonly__label">Module Name</span>
                                            <span class="cm-readonly__value cm-readonly__value--lead">{{ $mc->module_name }}</span>
                                        </div>
                                        <div class="cm-readonly__item">
                                            <span class="cm-readonly__label">Module Level</span>
                                            <span class="cm-readonly__value">{{ $mc->module_level_id > 0 ? ($mc->level->name ?? '—') : '—' }}</span>
                                        </div>
                                        <div class="cm-readonly__item">
                                            <span class="cm-readonly__label">Credit Value</span>
                                            <span class="cm-readonly__value">{{ $mc->credit_value !== '' && $mc->credit_value !== null ? $mc->credit_value : '—' }}</span>
                                        </div>
                                        <div class="cm-readonly__item">
                                            <span class="cm-readonly__label">Unit Value</span>
                                            <span class="cm-readonly__value">{{ $mc->unit_value !== '' && $mc->unit_value !== null ? $mc->unit_value : '—' }}</span>
                                        </div>
                                        <div class="cm-readonly__item">
                                            <span class="cm-readonly__label">Code</span>
                                            <span class="cm-readonly__value">{{ $mc->code ?: '—' }}</span>
                                        </div>
                                        <div class="cm-readonly__item">
                                            <span class="cm-readonly__label">Course Status</span>
                                            <span class="cm-readonly__value">{{ $mc->status ? ucfirst($mc->status) : '—' }}</span>
                                        </div>
                                    </div>

                                    <div style="margin-top:26px;">
                                        <div class="cm-picker__label" style="display:block; margin-bottom:11px;">Course Module Base Assesments</div>

                                        <div class="cm-asslist">
                                            @if($mcAssessments && $mcAssessments->count() > 0)
                                                <div class="cm-asslist__head">
                                                    <span>#</span>
                                                    <span>Name</span>
                                                    <span>Code</span>
                                                    <span></span>
                                                </div>
                                                @foreach($mcAssessments as $ass)
                                                    <div class="cm-asslist__row">
                                                        <span class="cm-asslist__n">{{ $loop->index + 1 }}</span>
                                                        <span class="cm-asslist__name">{{ $ass->assesment_name }}</span>
                                                        <span class="cm-asslist__code">{{ $ass->assesment_code }}</span>
                                                        <span class="cm-asslist__toggle">
                                                            <label class="cm-switchmini" title="Include this assesment">
                                                                <input class="cm-switchmini__input cmb_assessment" id="cmb_assessment_{{ $mc->id }}_{{ $ass->id }}" name="cmb_assessment[]" value="{{ $ass->id }}" type="checkbox">
                                                                <span class="cm-switchmini__track">
                                                                    <span class="cm-switchmini__knob">
                                                                        <svg data-cm-switch-on width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                                                        <svg data-cm-switch-off width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                                                    </span>
                                                                </span>
                                                            </label>
                                                        </span>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="cm-asslist__empty">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                                                    No assesments found for this module
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="cm-modal__foot" style="justify-content:space-between;">
                                    @if($loop->first)
                                        <a href="{{ route('term.module.creation') }}" class="cm-btn cm-btn--keep">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                                            Back to modules
                                        </a>
                                    @else
                                        <button type="button" class="cm-btn cm-btn--keep" data-cm-step-prev>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                                            Previous module
                                        </button>
                                    @endif

                                    <button type="button" class="cm-btn cm-btn--save" data-cm-step-next id="stepNext_{{ $mc->id }}">
                                        @include('pages.course-management.partials.save-glyphs')
                                        Save &amp; {{ $loop->last ? 'Exit' : 'Next' }}
                                    </button>
                                    <input type="hidden" name="module_creation_id" value="{{ $mc->id }}">
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-term-module-details.js')
@endsection
