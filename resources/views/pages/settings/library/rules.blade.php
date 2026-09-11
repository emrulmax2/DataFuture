{{--
    Shared shell for the three Library Management rule screens (Deposit Rule,
    Loan Rule, Fine & Charges). Everything that differs between them arrives in
    $page from Settings\LibrarySettingController, so the three routes cannot
    drift apart in layout or in how they save.
--}}
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
    <div id="siteSettingsPage" class="ss-page ss-library-rules-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Site Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Library Management</span>
            <i data-lucide="chevron-right"></i>
            <span>{{ $subtitle }}</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="{{ $page['icon'] }}"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>{{ $page['lead'] }}</p>
                    </div>
                </div>
                <a href="{{ route('site.setting') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Settings
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php
                        $settingsSidebarIcon = 'library';
                        $settingsSidebarSubtitle = 'Library Management';
                    @endphp
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <form method="post" action="{{ route('library.settings.save', ['group' => $group]) }}" class="ss-library-rules-form">
                        @csrf

                        @if(session('success'))
                            <div class="ss-library-flash" role="status">
                                <i data-lucide="check-circle"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        <div class="ss-form-card">
                            <div class="ss-card-heading">
                                <span></span>
                                <div>
                                    <h2>{{ $page['card'] }}</h2>
                                    <p>{{ $page['cardLead'] }}</p>
                                </div>
                            </div>

                            <div class="ss-field-grid">
                                @foreach($page['fields'] as $field)
                                    <div class="ss-field">
                                        <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                                        @if(($field['type'] ?? 'number') === 'toggle')
                                            <div class="ss-input-select">
                                                <i data-lucide="{{ $field['icon'] }}"></i>
                                                <select id="{{ $field['name'] }}" name="{{ $field['name'] }}" class="ss-input">
                                                    @foreach($field['options'] as $value => $label)
                                                        <option value="{{ $value }}" @selected((string) old($field['name'], $values[$field['name']] ?? '0') === (string) $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <i data-lucide="chevron-down"></i>
                                            </div>
                                        @else
                                        <div class="ss-input-wrap{{ isset($field['suffix']) ? ' ss-input-wrap--suffixed' : '' }}">
                                            <i data-lucide="{{ $field['icon'] }}"></i>
                                            <input
                                                id="{{ $field['name'] }}"
                                                type="number"
                                                inputmode="decimal"
                                                min="0"
                                                step="{{ $field['step'] }}"
                                                name="{{ $field['name'] }}"
                                                class="ss-input"
                                                placeholder="0"
                                                value="{{ old($field['name'], $values[$field['name']] ?? '') }}"
                                            >
                                            @isset($field['suffix'])
                                                <span class="ss-input-suffix">{{ $field['suffix'] }}</span>
                                            @endisset
                                        </div>
                                        @endif
                                        @error($field['name'])
                                            <span class="ss-field-error">{{ $message }}</span>
                                        @else
                                            <span class="ss-field-hint">{{ $field['hint'] }}</span>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>

                            <div class="ss-form-actions">
                                <a href="{{ route('site.setting') }}" class="ss-btn ss-btn--danger-soft">
                                    <i data-lucide="x"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="ss-btn ss-btn--primary">
                                    <i data-lucide="save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
@endsection

@section('script')
    {{-- settings.js carries the `.settingsMenu li.hasChild` accordion the shared
         sidebar needs; without it the Library Management group cannot expand. --}}
    @vite('resources/js/settings.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
