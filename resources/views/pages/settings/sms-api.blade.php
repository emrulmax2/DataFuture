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
    <div id="siteSettingsPage" class="ss-page ss-sms-api-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Communication Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>SMS API Settings</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="smartphone"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Configure the SMS gateway used for student, applicant and attendance communications.</p>
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
                    @php($settingsSidebarIcon = 'smartphone')
                    @php($settingsSidebarSubtitle = 'Communication settings')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    @if(isset(auth()->user()->priv()['communication_settings']) && auth()->user()->priv()['communication_settings'] == 1)
                        <form method="post" action="#" id="companySettingsForm" enctype="multipart/form-data">
                            <div class="ss-form-card ss-address-card ss-sms-api-card">
                                <div class="ss-card-heading">
                                    <span></span>
                                    <div>
                                        <h2>Update SMS API Settings</h2>
                                        <p>Select the active SMS provider and store the credentials used when messages are sent.</p>
                                    </div>
                                </div>

                                <div class="ss-field-grid ss-address-grid">
                                    <div class="ss-field">
                                        <label for="active_api">Active API</label>
                                        <div class="ss-input-select">
                                            <i data-lucide="send"></i>
                                            <select id="active_api" name="active_api">
                                                <option value="">Please Select</option>
                                                <option {{ (isset($opt['active_api']) && $opt['active_api'] == '1' ? 'selected' : '' ) }} value="1">Text Local</option>
                                                <option {{ (isset($opt['active_api']) && $opt['active_api'] == '2' ? 'selected' : '' ) }} value="2">SMS Eagle</option>
                                            </select>
                                            <i data-lucide="chevron-down"></i>
                                        </div>
                                    </div>

                                    <div class="ss-field">
                                        <label for="textlocal_api">
                                            Textlocal API
                                            <span>(textlocal.com)</span>
                                        </label>
                                        <div class="ss-input-wrap">
                                            <i data-lucide="key"></i>
                                            <input id="textlocal_api" type="text" name="textlocal_api" class="ss-input" placeholder="Textlocal API" value="{{ old('textlocal_api', $opt['textlocal_api'] ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="ss-field">
                                        <label for="smseagle_api">
                                            SMSEagle API
                                            <span>(smseagle.eu)</span>
                                        </label>
                                        <div class="ss-input-wrap">
                                            <i data-lucide="key-round"></i>
                                            <input id="smseagle_api" type="text" name="smseagle_api" class="ss-input" placeholder="SMSEagle API" value="{{ old('smseagle_api', $opt['smseagle_api'] ?? '') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="ss-form-actions ss-form-actions--start">
                                    <button type="submit" id="updateCINF" class="ss-btn ss-btn--primary">
                                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="ss-spinner">
                                            <g fill="none" fill-rule="evenodd">
                                                <g transform="translate(1 1)" stroke-width="4">
                                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                    </path>
                                                </g>
                                            </g>
                                        </svg>
                                        <i data-lucide="save"></i>
                                        Update
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="category" value="SMS">
                        </form>
                    @else
                        <div class="ss-empty-state" role="alert">
                            <span><i data-lucide="alert-triangle"></i></span>
                            <div>
                                <h2>Permission Required</h2>
                                <p>You do not have enough permission to view this page's content. Please navigate to the menus on the left.</p>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </main>

        <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ss-success-modal">
                    <div class="modal-body p-0">
                        <div class="ss-success-modal__body">
                            <i data-lucide="check-circle" class="ss-success-modal__icon"></i>
                            <div class="successModalTitle"></div>
                            <p class="successModalDesc"></p>
                        </div>
                        <div class="ss-success-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
