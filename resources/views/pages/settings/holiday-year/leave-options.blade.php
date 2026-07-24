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
    @php
        $availableLeaveOptions = [
            1 => ['title' => 'Holiday / Vacation', 'note' => 'Standard planned leave'],
            2 => ['title' => 'Unauthorised Absent', 'note' => 'Absence without approval'],
            3 => ['title' => 'Sick Leave', 'note' => 'Health-related leave'],
            4 => ['title' => 'Authorised Unpaid', 'note' => 'Approved unpaid leave'],
            5 => ['title' => 'Authorised Paid', 'note' => 'Approved paid leave'],
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page ss-holiday-year-page ss-holiday-leave-options-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <a href="{{ route('holiday.year') }}">Holiday Years</a>
            <i data-lucide="chevron-right"></i>
            <span>Leave Options</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="list-checks"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Choose the leave types available for {{ $holidayYear->holiday_year ?? 'this holiday year' }}.</p>
                    </div>
                </div>
                <a href="{{ route('holiday.year') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Holiday Years
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php($settingsSidebarIcon = 'calendar')
                    @php($settingsSidebarSubtitle = 'HR holiday setup')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <form method="POST" action="#" id="holidayYearLeaveOptionForm" autocomplete="off">
                        <div class="ss-table-card ss-holiday-leave-card">
                            <div class="ss-table-card__header">
                                <div>
                                    <h2>Leave Options</h2>
                                    <p>{{ $holidayYear->holiday_year ?? 'Holiday year' }}</p>
                                </div>
                                <button type="submit" id="updateLO" class="ss-btn ss-btn--primary ss-btn--compact">
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
                                    <i data-lucide="check"></i>
                                    Update Options
                                </button>
                                <input type="hidden" name="hr_holiday_year_id" value="{{ $holidayYear->id }}">
                            </div>

                            <div class="ss-holiday-leave-body">
                                <div class="ss-holiday-leave-grid">
                                    @foreach($availableLeaveOptions as $value => $option)
                                        <label class="ss-status-toggle" for="leave_option_{{ $value }}">
                                            <input {{ in_array($value, $leaveOptions) ? 'checked' : '' }} id="leave_option_{{ $value }}" name="leave_options[]" value="{{ $value }}" type="checkbox" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>{{ $option['title'] }}</strong>
                                                <small>{{ $option['note'] }}</small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
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
    @vite('resources/js/leave-options.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
