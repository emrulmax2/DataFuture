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
    <div id="siteSettingsPage" class="ss-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Accounts Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Accounts Setting</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="landmark"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Set the defaults applied across invoicing and accounts documents.</p>
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
                    @if(isset(auth()->user()->priv()['access_account']) && auth()->user()->priv()['access_account'] == 1)
                        <form method="post" action="#" id="accountSettingsForm" enctype="multipart/form-data">
                            <div class="ss-form-card ss-accounts-card">
                                <div class="ss-card-heading">
                                    <span></span>
                                    <div>
                                        <h2>Update Account Settings</h2>
                                        <p>These defaults are applied to invoices and other accounts documents.</p>
                                    </div>
                                </div>

                                <div class="ss-field-stack">
                                    <div class="ss-field">
                                        <label for="payment_term">Payment Term</label>
                                        <textarea rows="3" id="payment_term" name="payment_term" class="ss-textarea" placeholder="Please send payment within 30 days of receiving this invoice.">{{ old('payment_term', $opt['payment_term'] ?? '') }}</textarea>
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

                            <input type="hidden" name="category" value="ACC_SETTINGS">
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
    @vite('resources/js/account-settings.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
