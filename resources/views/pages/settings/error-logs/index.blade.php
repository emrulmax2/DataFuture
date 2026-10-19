@extends('../layout/site-settings')

@section('body_class', 'site-settings-isolated')

@section('subhead')
    <title>{{ $title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
@endsection

@section('styles')
    @vite('resources/css/site-settings-redesign.css')
    @vite('resources/css/error-logs.css')
@endsection

@section('content')
    <div id="siteSettingsPage" class="ss-page ss-error-logs-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Site Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Error Logs</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="bug"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>The application log files, readable: repeats of the same error are grouped, and every entry opens with its stack trace.</p>
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
                        $settingsSidebarIcon = 'bug';
                        $settingsSidebarSubtitle = 'Developer log viewer';
                    @endphp
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    @if(empty($files))
                        <div class="ss-table-card el-card">
                            <div class="el-empty">
                                <i data-lucide="file-search"></i>
                                <h2>No log files yet</h2>
                                <p>Nothing has been written to storage/logs on this server.</p>
                            </div>
                        </div>
                    @else
                        <div class="ss-table-card el-card" id="errorLogApp">
                            <div class="ss-table-card__header el-head">
                                <div class="el-head__file">
                                    <h2 id="elFileTitle">{{ $files[0]['name'] }}</h2>
                                    <p id="elFileMeta">{{ $files[0]['size_human'] }} · updated {{ $files[0]['modified_human'] }}</p>
                                </div>
                                <div class="el-head__actions">
                                    <label class="ss-filter-select el-file-select" for="elFile">
                                        <span class="sr-only">Log file</span>
                                        <select id="elFile">
                                            @foreach($files as $file)
                                                <option value="{{ $file['name'] }}">{{ $file['name'] }} ({{ $file['size_human'] }})</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                    <button id="elRefresh" type="button" class="ss-btn ss-btn--light ss-btn--tool" title="Read anything written since the page loaded">
                                        <i data-lucide="refresh-cw"></i>
                                        Refresh
                                    </button>
                                    <a id="elDownload" href="{{ route('error.logs.download', ['file' => $files[0]['name']]) }}" class="ss-btn ss-btn--light ss-btn--tool" title="Download the whole file">
                                        <i data-lucide="download"></i>
                                        Download
                                    </a>
                                </div>
                            </div>

                            <div class="el-progress" id="elProgress" hidden>
                                <div class="el-progress__track"><span id="elProgressBar"></span></div>
                                <p>Reading <strong id="elProgressFile"></strong>… <span id="elProgressPct">0%</span></p>
                            </div>

                            <div class="el-summary">
                                <div class="el-levels" id="elLevels" role="group" aria-label="Filter by level"></div>
                                <div class="el-mode" role="group" aria-label="View">
                                    <button type="button" class="el-mode__btn is-active" data-mode="groups" aria-pressed="true">
                                        <i data-lucide="layers"></i>
                                        Grouped
                                    </button>
                                    <button type="button" class="el-mode__btn" data-mode="entries" aria-pressed="false">
                                        <i data-lucide="list"></i>
                                        All entries
                                    </button>
                                </div>
                            </div>

                            <div class="ss-table-tools el-tools">
                                <form id="elFilterForm" class="ss-table-filter el-filter" autocomplete="off">
                                    <label class="ss-filter-input el-search" for="elQuery">
                                        <i data-lucide="search"></i>
                                        <input id="elQuery" type="text" placeholder="Message, exception or file…">
                                    </label>
                                    <label class="ss-filter-select" for="elEnv">
                                        <select id="elEnv">
                                            <option value="">All environments</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                    <label class="el-date" for="elFrom">
                                        <span>From</span>
                                        <input id="elFrom" type="date">
                                    </label>
                                    <label class="el-date" for="elTo">
                                        <span>To</span>
                                        <input id="elTo" type="date">
                                    </label>
                                    <button type="submit" class="ss-btn ss-btn--primary ss-btn--tool">Apply</button>
                                    <button id="elReset" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                                </form>
                            </div>

                            <div class="el-scope" id="elScope" hidden>
                                <i data-lucide="filter"></i>
                                <span>Every occurrence of <strong id="elScopeText"></strong></span>
                                <button type="button" id="elScopeClear">
                                    <i data-lucide="x"></i>
                                    Back to all
                                </button>
                            </div>

                            <div class="ss-tabulator-wrap el-table-wrap">
                                <div id="errorLogTable" class="ss-tabulator table-report table-report--tabulator"></div>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </main>

        <div id="errorLogEntryModal" class="modal ss-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog el-modal-dialog">
                <div class="modal-content ss-settings-modal el-modal">
                    <div class="ss-settings-modal__header el-modal__header">
                        <div>
                            <span></span>
                            <h2 id="elEntryTitle">Log entry</h2>
                        </div>
                        <div class="el-modal__actions">
                            <a id="elEntryDownload" href="#" class="ss-btn ss-btn--light ss-btn--tool" title="Download this entry exactly as it is in the file">
                                <i data-lucide="download"></i>
                                Entry
                            </a>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body el-modal__body" id="elEntryBody"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/error-logs.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
