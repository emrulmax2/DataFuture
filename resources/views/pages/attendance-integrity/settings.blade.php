{{--
    Attendance Integrity > Settings. Two tabs:
      Rules  - every attendance setting, one panel per group from
               AttendanceSettings::groups(); fields drawn by type (int, bool,
               time, date, statuses, weekdays, tiers). Saved by saveSettings().
      Scans  - queue a scan for past terms, recent scans, the history seal.
    Moved here from Site Settings > Attendance Settings (3 Nov 2026).
--}}
@extends('pages.attendance-integrity.shell')

@section('aim_page', 'settings')
@section('aim_eyebrow', 'Admin')

@section('aim_sub')
    The rules behind late corrections, saving a register and every Attendance Integrity check. Corrections and registers use a change at once; findings change at the next scan. Existing reports are not affected.
@endsection

@section('aim_actions')
    @if($tab === 'rules')
        <button type="submit" form="aimSettingsForm" class="aim-btn aim-btn--primary"><i data-lucide="save"></i> Save changes</button>
    @endif
@endsection

@section('aim_content')
    <nav class="aim-tabs" aria-label="Settings">
        <a href="{{ route('attendance.integrity.settings') }}" class="aim-tab {{ $tab === 'rules' ? 'is-active' : '' }}" @if($tab === 'rules') aria-current="page" @endif>
            <i data-lucide="sliders-horizontal"></i> Rules &amp; thresholds
        </a>
        <a href="{{ route('attendance.integrity.settings', ['tab' => 'scans']) }}" class="aim-tab {{ $tab === 'scans' ? 'is-active' : '' }}" @if($tab === 'scans') aria-current="page" @endif>
            <i data-lucide="scan-search"></i> Scans &amp; history
            @php $pending = $runs->whereIn('status', ['pending', 'running'])->count(); @endphp
            @if($pending)<span class="aim-tab__count">{{ $pending }}</span>@endif
        </a>
    </nav>

    @if($tab === 'rules')
        @php
            $old = $errors->any();
            $valueOf = function ($name, $type) use ($values, $old) {
                if (! $old) {
                    return $values[$name] ?? null;
                }
                // After a failed save, show what was typed (an unticked box sends nothing).
                return old($name, $type === 'bool' ? '0' : (in_array($type, ['statuses', 'weekdays'], true) ? [] : null));
            };
            $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
            $sections = [
                'Recording' => ['corrections', 'ownership', 'rates'],
                'Detection rules' => ['retrospective', 'amendments', 'bulk', 'high', 'patterns', 'hours', 'timetable', 'evidence', 'deletions'],
                'Scoring and schedule' => ['scoring', 'scans'],
            ];
            $groupErrors = [];
            foreach ($groups as $key => $group) {
                $groupErrors[$key] = collect(array_keys($group['fields']))->filter(fn ($n) => $errors->has($n))->count();
            }
        @endphp

        <div class="aim-settings">
            <nav class="aim-settings__nav" aria-label="Setting groups">
                @foreach($sections as $label => $keys)
                    <div class="aim-settings__nav-group">{{ $label }}</div>
                    @foreach($keys as $key)
                        @continue(! isset($groups[$key]))
                        <a href="#set-{{ $key }}" class="{{ $groupErrors[$key] ? 'has-error' : '' }}">
                            <i data-lucide="{{ $groups[$key]['icon'] }}"></i>
                            <span>{{ $groups[$key]['title'] }}</span>
                        </a>
                    @endforeach
                @endforeach
                <div class="aim-settings__nav-group">Reference</div>
                <a href="#set-statuses"><i data-lucide="list-checks"></i><span>Attendance statuses</span></a>
                @if($related)<a href="#set-related"><i data-lucide="link"></i><span>Related settings</span></a>@endif
            </nav>

            <form method="post" action="{{ route('attendance.integrity.settings.save') }}" id="aimSettingsForm" class="aim-settings__form" data-aim-settings novalidate>
                @csrf

                <div class="aim-callout aim-callout--neutral" style="margin: 0;">
                    <i data-lucide="history"></i>
                    <div>
                        @if($history && $history['count'] > 0)
                            Attendance history is recording every change (who, when, before and after) since <strong>{{ \Carbon\Carbon::parse($history['since'])->format('j M Y, H:i') }}</strong> UK time: {{ number_format($history['count']) }} changes so far.
                        @elseif($history)
                            Attendance history is switched on; no attendance has changed since.
                        @else
                            <strong>Attendance history is not recording:</strong> its table is missing. Run <code>php artisan migrate</code>.
                        @endif
                        Every save here is logged.
                    </div>
                </div>

                @foreach($sections as $label => $keys)
                    @foreach($keys as $key)
                        @continue(! isset($groups[$key]))
                        @php
                            $group = $groups[$key];
                            $fields = $group['fields'];
                            $switchName = collect(array_keys($fields))->first(fn ($n) => str_ends_with($n, '_enabled') && $fields[$n]['type'] === 'bool');
                        @endphp
                        <section class="aim-panel aim-setgroup" id="set-{{ $key }}">
                            <header class="aim-setgroup__head">
                                <span class="aim-setgroup__icon"><i data-lucide="{{ $group['icon'] }}"></i></span>
                                <div class="aim-setgroup__title">
                                    <h3>{{ $group['title'] }}</h3>
                                    <p>{{ $group['lead'] }}</p>
                                </div>
                                @if($switchName)
                                    @php $on = (string) $valueOf($switchName, 'bool') === '1'; @endphp
                                    <label class="aim-switch" title="{{ $fields[$switchName]['label'] }}">
                                        <input type="checkbox" name="{{ $switchName }}" value="1" @checked($on) aria-label="{{ $fields[$switchName]['label'] }}">
                                        <span class="aim-switch__track" aria-hidden="true"></span>
                                        <span class="aim-switch__text" aria-hidden="true"></span>
                                    </label>
                                @endif
                            </header>

                            <div class="aim-setgroup__rows">
                                @foreach($fields as $name => $field)
                                    @continue($name === $switchName)
                                    @php
                                        $type = $field['type'];
                                        $value = $valueOf($name, $type);
                                        $wide = in_array($type, ['statuses', 'weekdays', 'tiers'], true);
                                    @endphp
                                    <div class="aim-setrow {{ $wide ? 'aim-setrow--wide' : '' }} {{ $errors->has($name) ? 'has-error' : '' }}">
                                        <div class="aim-setrow__label">
                                            <label for="{{ $name }}">{{ $field['label'] }}</label>
                                            @isset($field['hint'])<p>{{ $field['hint'] }}</p>@endisset
                                        </div>
                                        <div class="aim-setrow__control">
                                            @if($type === 'bool')
                                                <label class="aim-switch">
                                                    <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" @checked((string) $value === '1')>
                                                    <span class="aim-switch__track" aria-hidden="true"></span>
                                                    <span class="aim-switch__text" aria-hidden="true"></span>
                                                </label>
                                            @elseif($type === 'statuses')
                                                <div class="aim-checks" id="{{ $name }}" role="group" aria-label="{{ $field['label'] }}">
                                                    @foreach($statuses as $status)
                                                        <label class="aim-check" title="{{ $status['name'] }}">
                                                            <input type="checkbox" name="{{ $name }}[]" value="{{ $status['id'] }}" @checked(in_array((int) $status['id'], array_map('intval', (array) $value), true))>
                                                            <b>{{ $status['code'] }}</b> {{ $status['name'] }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($type === 'weekdays')
                                                <div class="aim-checks" id="{{ $name }}" role="group" aria-label="{{ $field['label'] }}">
                                                    @foreach($days as $dayNo => $dayName)
                                                        <label class="aim-check">
                                                            <input type="checkbox" name="{{ $name }}[]" value="{{ $dayNo }}" @checked(in_array($dayNo, array_map('intval', (array) $value), true))>
                                                            {{ $dayName }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($type === 'tiers')
                                                @php $tierRows = array_values((array) $value); $tierRows = array_pad($tierRows, count($tierRows) + 2, ['', '']); @endphp
                                                <table class="aim-tiers" id="{{ $name }}">
                                                    <thead><tr><th>{{ $field['columns'][0] }}</th><th>{{ $field['columns'][1] }}</th></tr></thead>
                                                    <tbody>
                                                        @foreach($tierRows as $i => $tierRow)
                                                            <tr>
                                                                <td><input type="number" min="1" step="1" class="aim-input" name="{{ $name }}[{{ $i }}][0]" value="{{ $tierRow[0] ?? '' }}" aria-label="{{ $field['columns'][0] }}, row {{ $i + 1 }}"></td>
                                                                <td><input type="number" min="0" step="1" class="aim-input" name="{{ $name }}[{{ $i }}][1]" value="{{ $tierRow[1] ?? '' }}" aria-label="{{ $field['columns'][1] }}, row {{ $i + 1 }}"></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <p class="aim-small" style="margin: 6px 0 0;">Leave a row empty to remove it.</p>
                                            @else
                                                <div class="aim-affix">
                                                    <input id="{{ $name }}" type="{{ $type === 'int' ? 'number' : $type }}" class="aim-input" name="{{ $name }}" value="{{ is_array($value) ? '' : $value }}"
                                                        @if($type === 'int') step="1" inputmode="numeric" @isset($field['min']) min="{{ $field['min'] }}" @endisset @isset($field['max']) max="{{ $field['max'] }}" @endisset @endif>
                                                    @isset($field['suffix'])<span>{{ $field['suffix'] }}</span>@endisset
                                                </div>
                                            @endif
                                            @error($name)<div class="aim-setrow__error"><i data-lucide="alert-circle"></i> {{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                @endforeach

                <section class="aim-panel aim-setgroup" id="set-statuses">
                    <header class="aim-setgroup__head">
                        <span class="aim-setgroup__icon"><i data-lucide="list-checks"></i></span>
                        <div class="aim-setgroup__title">
                            <h3>Attendance statuses</h3>
                            <p>For reference. Every existing attendance report uses these, so they are not changed here.</p>
                        </div>
                    </header>
                    <div class="aim-table-wrap">
                        <table class="aim-table">
                            <thead><tr><th>Code</th><th>Status</th><th>Tutor can choose it</th><th>Counts as attended</th><th>Active</th></tr></thead>
                            <tbody>
                                @forelse($statuses as $status)
                                    <tr>
                                        <td><strong>{{ $status['code'] }}</strong></td>
                                        <td>{{ $status['name'] }}</td>
                                        @foreach(['tutor_availability', 'attendance_count', 'active'] as $col)
                                            <td>@if($status[$col])<span class="aim-yes"><i data-lucide="check"></i> Yes</span>@else<span class="aim-muted">No</span>@endif</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="aim-empty">No statuses found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                @if($related)
                    <section class="aim-panel aim-setgroup" id="set-related">
                        <header class="aim-setgroup__head">
                            <span class="aim-setgroup__icon"><i data-lucide="link"></i></span>
                            <div class="aim-setgroup__title">
                                <h3>Related settings</h3>
                                <p>Other settings that affect attendance. They open in Site Settings.</p>
                            </div>
                        </header>
                        <div class="aim-panel__body">
                            <div class="aim-list">
                                @foreach($related as $link)
                                    <a class="aim-item" href="{{ route($link['route']) }}">
                                        <span class="aim-item__icon"><i data-lucide="{{ $link['icon'] }}"></i></span>
                                        <div class="aim-item__text"><h4>{{ $link['label'] }}</h4><p>{{ $link['hint'] }}</p></div>
                                        <i data-lucide="chevron-right" class="aim-item__go"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                <div class="aim-savebar" data-aim-savebar>
                    <div class="aim-savebar__note">
                        <span class="aim-savebar__clean"><i data-lucide="check-circle-2"></i> All changes saved</span>
                        <span class="aim-savebar__dirty"><i data-lucide="circle-dot"></i> You have unsaved changes</span>
                    </div>
                    <div class="aim-savebar__actions">
                        <a href="{{ route('attendance.integrity.settings') }}" class="aim-btn">Discard</a>
                        <button type="submit" class="aim-btn aim-btn--primary"><i data-lucide="save"></i> Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class="aim-grid">
            <div class="aim-panel aim-col-6">
                <div class="aim-panel__head"><div><h3>Scan terms</h3><p>Runs every rule over the chosen terms. Queued scans start within five minutes and take a few seconds each. Running terms are also scanned every night{{ ($values['att_scan_daily'] ?? 1) ? ' at '.($values['att_scan_time'] ?? '02:30').' UK time' : ' (switched off)' }}.</p></div></div>
                <form method="post" action="{{ route('attendance.integrity.settings.scan') }}" class="aim-panel__body">
                    @csrf
                    <div class="aim-field">
                        <label for="aimScanTerms">Terms</label>
                        <select name="scan_terms[]" multiple data-aim-tom="multi" id="aimScanTerms">
                            @foreach($filterOptions['terms'] as $term)
                                <option value="{{ $term['id'] }}">{{ $term['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="aim-form__actions"><button type="submit" class="aim-btn aim-btn--primary"><i data-lucide="scan-search"></i> Queue scan</button></div>
                </form>
            </div>
            <div class="aim-panel aim-col-6">
                <div class="aim-panel__head"><div><h3>Attendance history</h3><p>Every attendance change, sealed nightly against tampering.</p></div></div>
                <div class="aim-panel__body">
                    <div class="aim-facts">
                        <div class="aim-fact"><div class="aim-fact__value">{{ $history ? number_format($history['count']) : '—' }}</div><div class="aim-fact__label">Changes recorded{{ $history && $history['since'] ? ' since '.\Carbon\Carbon::parse($history['since'])->format('j M Y') : '' }}</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ $lastSeal ? \Carbon\Carbon::parse($lastSeal->sealed_at)->format('j M, H:i') : 'Not yet' }}</div><div class="aim-fact__label">Last sealed (UK)</div></div>
                    </div>
                    <p class="aim-small" style="margin: 10px 0 0;">To check nothing was altered: <code>php artisan attendance-integrity:seal --verify</code></p>
                </div>
            </div>
            <div class="aim-panel aim-col-12">
                <div class="aim-panel__head"><div><h3>Recent scans</h3><p>The last 40, newest first. UK time.</p></div></div>
                <div class="aim-table-wrap">
                    <table class="aim-table">
                        <thead><tr><th>Term</th><th>Kind</th><th>Status</th><th>Requested</th><th>Finished</th><th class="num">New</th><th class="num">Updated</th><th class="num">Cleared</th></tr></thead>
                        <tbody>
                            @forelse($runs as $run)
                                <tr>
                                    <td>{{ $runTerms[$run->term_declaration_id] ?? '#'.$run->term_declaration_id }}</td>
                                    <td>{{ ucfirst($run->scope) }}</td>
                                    <td><span class="aim-badge {{ $run->status === 'completed' ? 'aim-badge--done' : ($run->status === 'failed' ? 'aim-badge--high' : 'aim-badge--open') }}">{{ ucfirst($run->status) }}</span>
                                        @if($run->error)<span class="aim-sub">{{ \Illuminate\Support\Str::limit($run->error, 120) }}</span>@endif</td>
                                    <td class="num">{{ \Carbon\Carbon::parse($run->requested_at)->format('d M, H:i') }}</td>
                                    <td class="num">{{ $run->finished_at ? \Carbon\Carbon::parse($run->finished_at)->format('d M, H:i') : '—' }}</td>
                                    <td class="num">{{ number_format($run->findings_new) }}</td>
                                    <td class="num">{{ number_format($run->findings_updated) }}</td>
                                    <td class="num">{{ number_format($run->findings_cleared) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="aim-empty">No scans yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
