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
        $actionOptions = [
            0 => 'Select Action',
            1 => 'Contract',
            2 => 'Actual',
            3 => 'Blank',
        ];

        $clockInRows = [
            ['key' => 1, 'label' => 'Up to', 'note' => 'Grace window', 'record' => $clockIn_1],
            ['key' => 2, 'label' => 'Till', 'note' => 'Late window', 'record' => $clockIn_2],
            ['key' => 3, 'label' => 'After', 'note' => 'Late threshold', 'record' => $clockIn_3],
            ['key' => 4, 'label' => 'No Clock In', 'note' => 'Missing entry', 'record' => $clockIn_4],
        ];

        $clockOutRows = [
            ['key' => 1, 'label' => 'Before', 'note' => 'Early exit', 'record' => $clockOut_1],
            ['key' => 2, 'label' => 'After', 'note' => 'Late exit', 'record' => $clockOut_2],
            ['key' => 3, 'label' => 'No Clock Out', 'note' => 'Missing exit', 'record' => $clockOut_3],
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page ss-hr-condition-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Site Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>HR Conditions</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="clock-3"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Set the time windows used by staff attendance and time keeping.</p>
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
                    @php
                        $settingsSidebarIcon = 'contact-2';
                        $settingsSidebarSubtitle = 'HR attendance setup';
                    @endphp
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <form method="POST" action="#" id="hrConditionForm" autocomplete="off">
                        <div class="ss-hr-condition-toolbar">
                            <div>
                                <h2>Attendance Conditions</h2>
                                <p>Clock-in and clock-out rules</p>
                            </div>
                            <button type="submit" id="updateHRC1" class="updateHRC ss-btn ss-btn--primary">
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
                                Save Settings
                            </button>
                        </div>

                        <div class="ss-hr-condition-grid">
                            <section class="ss-condition-card">
                                <div class="ss-condition-card__header">
                                    <span><i data-lucide="log-in"></i></span>
                                    <div>
                                        <h2>Clock In Conditions</h2>
                                        <p>Entry attendance</p>
                                    </div>
                                </div>
                                <div class="ss-condition-table" role="table" aria-label="Clock in conditions">
                                    <div class="ss-condition-row ss-condition-row--head" role="row">
                                        <span>Time Frame</span>
                                        <span>Minutes</span>
                                        <span>Notify</span>
                                        <span>Action</span>
                                    </div>
                                    @foreach($clockInRows as $row)
                                        @php
                                            $minutes = data_get($row['record'], 'minutes', '');
                                            $notify = data_get($row['record'], 'notify') == 1;
                                            $action = data_get($row['record'], 'action', 0);
                                        @endphp
                                        <div class="ss-condition-row" role="row">
                                            <div class="ss-condition-frame">
                                                <strong>{{ $row['label'] }}</strong>
                                                <small>{{ $row['note'] }}</small>
                                            </div>
                                            <label class="ss-condition-minutes">
                                                <input type="number" min="0" value="{{ $minutes }}" name="clockin[{{ $row['key'] }}][time]" placeholder="0">
                                                <span>min</span>
                                            </label>
                                            <label class="ss-condition-notify" aria-label="{{ $row['label'] }} notify">
                                                <input {{ $notify ? 'checked' : '' }} value="1" name="clockin[{{ $row['key'] }}][notify]" type="checkbox" autocomplete="off">
                                                <span>
                                                    <i data-lucide="check" class="ss-condition-notify__on"></i>
                                                    <i data-lucide="x" class="ss-condition-notify__off"></i>
                                                </span>
                                            </label>
                                            <label class="ss-modal-select ss-condition-action">
                                                <select name="clockin[{{ $row['key'] }}][action]">
                                                    @foreach($actionOptions as $value => $label)
                                                        <option {{ (int) $action === $value ? 'selected' : '' }} value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <i data-lucide="chevron-down"></i>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            <section class="ss-condition-card">
                                <div class="ss-condition-card__header">
                                    <span><i data-lucide="log-out"></i></span>
                                    <div>
                                        <h2>Clock Out Conditions</h2>
                                        <p>Exit attendance</p>
                                    </div>
                                </div>
                                <div class="ss-condition-table" role="table" aria-label="Clock out conditions">
                                    <div class="ss-condition-row ss-condition-row--head" role="row">
                                        <span>Time Frame</span>
                                        <span>Minutes</span>
                                        <span>Notify</span>
                                        <span>Action</span>
                                    </div>
                                    @foreach($clockOutRows as $row)
                                        @php
                                            $minutes = data_get($row['record'], 'minutes', '');
                                            $notify = data_get($row['record'], 'notify') == 1;
                                            $action = data_get($row['record'], 'action', 0);
                                        @endphp
                                        <div class="ss-condition-row" role="row">
                                            <div class="ss-condition-frame">
                                                <strong>{{ $row['label'] }}</strong>
                                                <small>{{ $row['note'] }}</small>
                                            </div>
                                            <label class="ss-condition-minutes">
                                                <input type="number" min="0" value="{{ $minutes }}" name="clockout[{{ $row['key'] }}][time]" placeholder="0">
                                                <span>min</span>
                                            </label>
                                            <label class="ss-condition-notify" aria-label="{{ $row['label'] }} notify">
                                                <input {{ $notify ? 'checked' : '' }} value="1" name="clockout[{{ $row['key'] }}][notify]" type="checkbox" autocomplete="off">
                                                <span>
                                                    <i data-lucide="check" class="ss-condition-notify__on"></i>
                                                    <i data-lucide="x" class="ss-condition-notify__off"></i>
                                                </span>
                                            </label>
                                            <label class="ss-modal-select ss-condition-action">
                                                <select name="clockout[{{ $row['key'] }}][action]">
                                                    @foreach($actionOptions as $value => $label)
                                                        <option {{ (int) $action === $value ? 'selected' : '' }} value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <i data-lucide="chevron-down"></i>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
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
    @vite('resources/js/hr-conditions.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
