@extends('../layout/site-settings')

@section('body_class', 'site-settings-isolated')

@section('subhead')
    <title>{{ $title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@600;700&display=swap" rel="stylesheet">
@endsection

@section('styles')
    @vite('resources/css/site-settings-redesign.css')
@endsection

@section('content')
    <div id="siteSettingsPage" class="ss-page ss-workplacement-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Workplacement</span>
            <i data-lucide="chevron-right"></i>
            <span>Workplacement Details</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="navigation"></i>
                    </span>
                    <div>
                        <h1>Workplacement Details</h1>
                        <p>Define placement records per course, then break each one down into level hours and learning hours.</p>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Dashboard
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php($settingsSidebarIcon = 'settings-2')
                    @php($settingsSidebarSubtitle = 'Global configuration')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-wp-card">
                        <div class="ss-table-card__header">
                            <div class="ss-wp-card__heading">
                                <h2>Workplacement Details</h2>
                                <span>{{ $workplacement_details->count() }} {{ Str::plural('record', $workplacement_details->count()) }}</span>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#workplacementAddModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add Workplacement Details
                            </button>
                        </div>

                        <div class="ss-wp-list">
                            @forelse($workplacement_details as $workplacement_detail)
                                <div class="ss-wp-node accordion">
                                    <div id="workplacementAccordion-{{ $workplacement_detail->id }}" class="ss-wp-node__head accordion-header">
                                        <button class="accordion-button collapsed ss-wp-node__toggle"
                                            type="button"
                                            data-target="#workplacementAccordion-collapse-{{ $workplacement_detail->id }}"
                                            aria-expanded="false"
                                            aria-controls="workplacementAccordion-collapse-{{ $workplacement_detail->id }}">
                                            <span class="ss-wp-toggle-icon">
                                                <i data-lucide="plus" class="accordion-icon-plus"></i>
                                                <i data-lucide="minus" class="accordion-icon-minus hidden"></i>
                                            </span>
                                            <span class="ss-wp-node__title">{{ $workplacement_detail->name }}</span>
                                        </button>
                                        <div class="ss-wp-node__actions">
                                            <button data-id="{{ $workplacement_detail->id }}" data-tw-toggle="modal" data-tw-target="#addLevelHoursModal" type="button" class="addLevelHours_btn ss-wp-soft-btn">
                                                <i data-lucide="plus"></i>
                                                Add Level Hours
                                            </button>
                                            <button data-id="{{ $workplacement_detail->id }}" data-tw-toggle="modal" data-tw-target="#workplacementEditModal" type="button" class="editWorkPlacement_btn ss-wp-icon-btn ss-wp-icon-btn--edit" aria-label="Edit {{ $workplacement_detail->name }}">
                                                <i data-lucide="pencil"></i>
                                            </button>
                                            <button data-route="{{ route('workplacement.delete', $workplacement_detail->id) }}" data-id="{{ $workplacement_detail->id }}" type="button" class="delete_btn ss-wp-icon-btn ss-wp-icon-btn--delete" aria-label="Delete {{ $workplacement_detail->name }}">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div id="workplacementAccordion-collapse-{{ $workplacement_detail->id }}" class="accordion-collapse collapse ss-wp-node__body"
                                        aria-labelledby="workplacementAccordion-{{ $workplacement_detail->id }}">
                                        @forelse($workplacement_detail->level_hours as $level_hour)
                                            <div class="ss-wp-level">
                                                <div id="nestedAccordion-{{ $workplacement_detail->id }}-{{ $level_hour->id }}" class="ss-wp-level__head accordion-header">
                                                    <button class="accordion-button collapsed ss-wp-level__toggle"
                                                        type="button"
                                                        data-target="#nestedAccordion-collapse-{{ $workplacement_detail->id }}-{{ $level_hour->id }}"
                                                        aria-expanded="false"
                                                        aria-controls="nestedAccordion-collapse-{{ $workplacement_detail->id }}-{{ $level_hour->id }}">
                                                        <span class="ss-wp-toggle-icon ss-wp-toggle-icon--sm">
                                                            <i data-lucide="plus" class="accordion-icon-plus"></i>
                                                            <i data-lucide="minus" class="accordion-icon-minus hidden"></i>
                                                        </span>
                                                        <span class="ss-wp-level__title">
                                                            {{ $level_hour->name }}
                                                            <em>(Hours: {{ $level_hour->hours }})</em>
                                                        </span>
                                                    </button>
                                                    <div class="ss-wp-node__actions">
                                                        <button data-id="{{ $level_hour->id }}" data-tw-toggle="modal" data-tw-target="#addLearningHoursModal" type="button" class="addLearningHours_btn ss-wp-soft-btn">
                                                            <i data-lucide="plus"></i>
                                                            Add Learning Hours
                                                        </button>
                                                        <button data-id="{{ $level_hour->id }}" data-tw-toggle="modal" data-tw-target="#levelHoursEditModal" type="button" class="editLevelHours_btn ss-wp-icon-btn ss-wp-icon-btn--edit" aria-label="Edit {{ $level_hour->name }}">
                                                            <i data-lucide="pencil"></i>
                                                        </button>
                                                        <button data-route="{{ route('level.hours.delete', $level_hour->id) }}" data-id="{{ $level_hour->id }}" type="button" class="delete_btn ss-wp-icon-btn ss-wp-icon-btn--delete" aria-label="Delete {{ $level_hour->name }}">
                                                            <i data-lucide="trash-2"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div id="nestedAccordion-collapse-{{ $workplacement_detail->id }}-{{ $level_hour->id }}" class="accordion-collapse collapse ss-wp-level__body"
                                                    aria-labelledby="nestedAccordion-{{ $workplacement_detail->id }}-{{ $level_hour->id }}">
                                                    @forelse($level_hour->learning_hours as $learning_hour)
                                                        <div class="ss-wp-leaf">
                                                            <span class="ss-wp-leaf__label">
                                                                {{ $learning_hour->name }}
                                                                <em>(Hours: {{ $learning_hour->hours }})</em>
                                                            </span>
                                                            <div class="ss-wp-node__actions">
                                                                <button data-id="{{ $learning_hour->id }}" data-tw-toggle="modal" data-tw-target="#editLearningHoursModal" type="button" class="editLearningHours_btn ss-wp-icon-btn ss-wp-icon-btn--edit" aria-label="Edit {{ $learning_hour->name }}">
                                                                    <i data-lucide="pencil"></i>
                                                                </button>
                                                                <button data-route="{{ route('learning.hours.delete', $learning_hour->id) }}" data-id="{{ $learning_hour->id }}" type="button" class="delete_btn ss-wp-icon-btn ss-wp-icon-btn--delete" aria-label="Delete {{ $learning_hour->name }}">
                                                                    <i data-lucide="trash-2"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="ss-wp-inline-empty">
                                                            <span><i data-lucide="clock"></i></span>
                                                            No learning hours added yet
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @empty
                                            <div class="ss-wp-inline-empty">
                                                <span><i data-lucide="layers"></i></span>
                                                No level hours added yet
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <div class="ss-wp-empty">
                                    <span><i data-lucide="navigation"></i></span>
                                    <strong>No workplacement details found</strong>
                                    <p>Create a workplacement record to start recording level and learning hours.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </main>

        @include('pages.settings.workplacement.modal')
    </div>
@endsection

@section('script')
@vite('resources/js/settings.js')
@vite('resources/js/workplacement-details.js')
@vite('resources/js/site-settings-redesign.js')
<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                collapse.classList.add('collapse');
            });

            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = button.getAttribute('data-target');
                    const targetContent = document.querySelector(targetId);
                    const plusIcon = button.querySelector('.accordion-icon-plus');
                    const minusIcon = button.querySelector('.accordion-icon-minus');

                    const isExpanded = button.getAttribute('aria-expanded') === 'true';

                    if (isExpanded) {
                        plusIcon.classList.remove('hidden');
                        minusIcon.classList.add('hidden');
                    } else {
                        plusIcon.classList.add('hidden');
                        minusIcon.classList.remove('hidden');
                    }

                    if (!isExpanded) {
                        targetContent.classList.remove('collapse');
                        targetContent.classList.add('show');
                        button.setAttribute('aria-expanded', 'true');
                        button.classList.remove('collapsed');
                    } else {
                        targetContent.classList.remove('show');
                        targetContent.classList.add('collapse');
                        button.setAttribute('aria-expanded', 'false');
                        button.classList.add('collapsed');
                    }
                });
            });

            // Keep the "Module Required?" switch copy in step with its checkbox,
            // the same way the Term Type modals handle their active toggle.
            const syncModuleToggle = (input) => {
                const copy = input.closest('.ss-status-toggle')?.querySelector('.ss-status-toggle__copy');

                if (!copy) {
                    return;
                }

                copy.querySelector('strong').textContent = input.checked ? 'Required' : 'Not required';
                copy.querySelector('small').textContent = input.checked
                    ? 'A module must be linked before these hours can be logged'
                    : 'These hours are logged without a linked module';
            };

            document.querySelectorAll('.ss-status-toggle input[name="module_required"]').forEach((input) => {
                syncModuleToggle(input);
                input.addEventListener('change', () => syncModuleToggle(input));
            });

            // The edit modal is populated over Ajax, so re-sync as it opens.
            document.getElementById('editLearningHoursModal')?.addEventListener('shown.tw.modal', function () {
                this.querySelectorAll('input[name="module_required"]').forEach(syncModuleToggle);
            });
        });
    })();
</script>
@endsection
