<div class="agm-referral-card" data-agm-referral-panel data-filter="all">
    <div class="agm-referral-toolbar">
        <div class="agm-referral-tabs" role="group" aria-label="Referral filters">
            <button type="button" class="agm-referral-tab is-active" data-agm-referral-filter="all" aria-pressed="true">
                <span>All</span>
                <b>{{ $counts['all'] }}</b>
            </button>
            <button type="button" class="agm-referral-tab" data-agm-referral-filter="matched" aria-pressed="false">
                <span>Matched</span>
                <b>{{ $counts['matched'] }}</b>
            </button>
            <button type="button" class="agm-referral-tab" data-agm-referral-filter="mismatched" aria-pressed="false">
                <span>Mismatched</span>
                <b>{{ $counts['mismatched'] }}</b>
            </button>
        </div>

        <label class="agm-referral-search">
            <i data-lucide="search"></i>
            <input type="search" placeholder="Filter referral name or code..." autocomplete="off" data-agm-referral-search>
        </label>
    </div>

    <div class="agm-referral-table-wrap">
        <table class="agm-referral-table" id="referralCountTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Referral Name</th>
                    <th>Referral Code</th>
                    <th>Type</th>
                    <th>No of Student</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    @if($row['matched'])
                        <tr class="code_row agm-referral-row"
                            data-referral-row
                            data-referral-state="matched"
                            data-referral-search="{{ $row['search'] }}"
                            data-code="{{ $row['code'] }}"
                            data-semester="{{ $row['semester_id'] }}">
                            <td>
                                <span class="agm-ref-index">{{ $row['display_index'] }}</span>
                            </td>
                            <td>
                                <div class="agm-ref-person">
                                    <span class="agm-ref-avatar agm-ref-avatar--tone-{{ $row['tone'] }}">
                                        @if(!empty($row['photo_url']))
                                            <img src="{{ $row['photo_url'] }}" alt="{{ $row['name'] }}">
                                        @else
                                            {{ $row['initials'] }}
                                        @endif
                                    </span>
                                    <span class="agm-ref-person__copy">
                                        <strong>{{ $row['name'] }}</strong>
                                        <small>{{ $row['email'] }}</small>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="agm-ref-code">{{ $row['code'] }}</span>
                            </td>
                            <td>
                                <span class="agm-ref-type"><i></i>{{ $row['type'] }}</span>
                            </td>
                            <td>
                                <div class="agm-ref-students">
                                    <strong>{{ $row['student_count'] }}</strong>
                                    <span class="agm-ref-meter agm-ref-meter--tone-{{ $row['tone'] }}">
                                        <i style="--agm-meter-width: {{ $row['meter_width'] }}%;"></i>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="agm-ref-actions">
                                    <a href="{{ route('agent.management.comission', [$row['semester_id'], $row['agent_user_id']]) }}"
                                       id="comission_view_{{ $row['semester_id'] }}_{{ $row['agent_user_id'] }}"
                                       class="agm-ref-action agm-ref-action--view {{ $row['has_rule'] ? '' : 'hidden' }}"
                                       title="View commission">
                                        <i data-lucide="eye"></i>
                                    </a>
                                    <button data-isdefault="{{ $row['is_default'] }}"
                                            data-code="{{ $row['code'] }}"
                                            data-agent="{{ $row['agent_user_id'] }}"
                                            data-semester="{{ $row['semester_id'] }}"
                                            type="button"
                                            class="theRuleBtn agm-ref-action agm-ref-action--rule"
                                            title="Commission rule">
                                        <i data-lucide="settings"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr class="agm-referral-row agm-referral-row--mismatch"
                            data-referral-row
                            data-referral-state="mismatched"
                            data-referral-search="{{ $row['search'] }}">
                            <td colspan="6">
                                <div class="agm-ref-warning">
                                    <span class="agm-ref-warning__icon">
                                        <i data-lucide="alert-triangle"></i>
                                    </span>
                                    <span>
                                        <strong>{{ $row['code'] }}</strong>
                                        <small>This code does not match the referral code.</small>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                <tr class="agm-referral-row agm-referral-row--empty" data-agm-referral-empty hidden>
                    <td colspan="6">
                        <div class="agm-ref-empty">No referral results match this filter.</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
