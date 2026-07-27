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
            <span>Workplacement Settings</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="sliders-horizontal"></i>
                    </span>
                    <div>
                        <h1>Workplacement Settings</h1>
                        <p>Group the option values used across placement records, then manage the types available in each group.</p>
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
                                <h2>Workplacement Settings</h2>
                                <span>{{ $workplacement_settings->count() }} {{ Str::plural('setting group', $workplacement_settings->count()) }}</span>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addWpSettingModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add Workplacement Setting
                            </button>
                        </div>

                        <div class="ss-wp-list">
                            @forelse($workplacement_settings as $wp_setting)
                                <div class="ss-wp-node accordion">
                                    <div id="settingsAccordion-{{ $wp_setting->id }}" class="ss-wp-node__head accordion-header">
                                        <button class="accordion-button collapsed ss-wp-node__toggle"
                                            type="button"
                                            data-target="#settingsAccordion-collapse-{{ $wp_setting->id }}"
                                            aria-expanded="false"
                                            aria-controls="settingsAccordion-collapse-{{ $wp_setting->id }}">
                                            <span class="ss-wp-toggle-icon">
                                                <i data-lucide="plus" class="accordion-icon-plus"></i>
                                                <i data-lucide="minus" class="accordion-icon-minus hidden"></i>
                                            </span>
                                            <span class="ss-wp-node__title">{{ $wp_setting->name }}</span>
                                        </button>
                                        <div class="ss-wp-node__actions">
                                            <button data-id="{{ $wp_setting->id }}" data-tw-toggle="modal" data-tw-target="#addWpSettingTypeModal" type="button" class="addWpSettingType_btn ss-wp-soft-btn">
                                                <i data-lucide="plus"></i>
                                                Add Setting Type
                                            </button>
                                            <button data-id="{{ $wp_setting->id }}" data-tw-toggle="modal" data-tw-target="#editWpSettingModal" type="button" class="editWpSetting_btn ss-wp-icon-btn ss-wp-icon-btn--edit" aria-label="Edit {{ $wp_setting->name }}">
                                                <i data-lucide="pencil"></i>
                                            </button>
                                            <button data-route="{{ route('workplacement-settings.destory', $wp_setting->id) }}" data-id="{{ $wp_setting->id }}" type="button" class="wpSettingDelete_btn ss-wp-icon-btn ss-wp-icon-btn--delete" aria-label="Delete {{ $wp_setting->name }}">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div id="settingsAccordion-collapse-{{ $wp_setting->id }}" class="accordion-collapse collapse ss-wp-node__body"
                                        aria-labelledby="settingsAccordion-{{ $wp_setting->id }}">
                                        @forelse($wp_setting->workplacement_settng_types as $wp_setting_type)
                                            <div class="ss-wp-leaf ss-wp-leaf--strong">
                                                <span class="ss-wp-leaf__label">{{ $wp_setting_type->type }}</span>
                                                <div class="ss-wp-node__actions">
                                                    <button data-id="{{ $wp_setting_type->id }}" data-tw-toggle="modal" data-tw-target="#editWpSettingTypeModal" type="button" class="editWpSettingType_btn ss-wp-icon-btn ss-wp-icon-btn--edit" aria-label="Edit {{ $wp_setting_type->type }}">
                                                        <i data-lucide="pencil"></i>
                                                    </button>
                                                    <button data-route="{{ route('workplacement-setting.types.destory', $wp_setting_type->id) }}" data-id="{{ $wp_setting_type->id }}" type="button" class="wpSettingTypeDelete_btn ss-wp-icon-btn ss-wp-icon-btn--delete" aria-label="Delete {{ $wp_setting_type->type }}">
                                                        <i data-lucide="trash-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="ss-wp-inline-empty">
                                                <span><i data-lucide="tag"></i></span>
                                                No setting types added yet
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <div class="ss-wp-empty">
                                    <span><i data-lucide="sliders-horizontal"></i></span>
                                    <strong>No workplacement settings found</strong>
                                    <p>Add a setting group to start collecting the types used on placement records.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </main>

        @include('pages.settings.workplacement.wp-settings-modal')
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/wp-settings.js')
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
            });
        })();
    </script>
@endsection
