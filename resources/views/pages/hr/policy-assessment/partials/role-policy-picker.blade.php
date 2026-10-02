{{-- The policy tick list of a role. Needs $prefix (add|edit), $policyGroups
     ([[id, name, archived, policies => [[id, title, version, is_active]]]]) and $policyTotal.
     Rendered once with the page; resources/js/policy-assessment-roles.js drives it
     (search, per-category select all, running total). Only policy_ids[] is submitted. --}}
@php
    $paPickerId = $prefix.'_role_policies';
    /* The note about inactive policies is only shown when there is one to tick. */
    $paHasUnassignable = false;
    foreach($policyGroups as $paGroup):
        foreach($paGroup['policies'] as $paPolicy):
            if(!$paPolicy['is_active'] || $paGroup['archived']):
                $paHasUnassignable = true;
            endif;
        endforeach;
    endforeach;
@endphp
<div class="pa-role-picker" data-role-picker data-total="{{ (int) $policyTotal }}">
    <div class="pa-role-picker__intro">
        <span id="{{ $paPickerId }}_label" class="pa-role-picker__label">Policies for this role <span aria-hidden="true">*</span></span>
        <small class="pa-field-hint">Tick every policy that staff in this role must pass.</small>
    </div>

    @if(empty($policyGroups))
        <p class="pa-role-picker__empty">
            There are no policies in the Question Bank yet.
            <a href="{{ route('policy.assessment.policy') }}" class="pa-inline-link">Add policies first</a>, then come back to tick them for this role.
        </p>
        <div class="acc__input-error error-policy_ids" role="alert"></div>
    @else
        <div class="pa-role-picker__bar">
            <div class="acc__input-error error-policy_ids" role="alert"></div>
            <div class="pa-role-picker__row">
                <label class="pa-role-picker__search" for="{{ $paPickerId }}_search">
                    <i data-lucide="search"></i>
                    <span class="pa-visually-hidden">Search the policies</span>
                    <input id="{{ $paPickerId }}_search" type="search" placeholder="Search policies..." autocomplete="off" spellcheck="false" data-picker-search>
                </label>
                <span class="pa-role-picker__total" aria-live="polite">
                    <strong data-picker-total>No policies ticked</strong>
                </span>
            </div>
            <div class="pa-role-picker__actions">
                <button type="button" class="pa-role-picker__action" data-picker-all>Tick all</button>
                <button type="button" class="pa-role-picker__action" data-picker-none>Clear all</button>
                <button type="button" class="pa-role-picker__action pa-role-picker__action--toggle" data-picker-only aria-pressed="false">
                    <span class="pa-role-picker__action-dot" aria-hidden="true"></span>
                    Ticked only
                </button>
                <small class="pa-role-picker__note" data-picker-total-note hidden><i data-lucide="alert-triangle"></i><span></span></small>
            </div>
        </div>

        <div class="pa-role-picker__list policy_ids" role="group" aria-labelledby="{{ $paPickerId }}_label" data-picker-list>
            @foreach($policyGroups as $paGroup)
                @php
                    $paGroupId = $paPickerId.'_cat_'.$paGroup['id'];
                    $paGroupSize = count($paGroup['policies']);
                @endphp
                <section class="pa-role-group {{ $paGroup['archived'] ? 'is-archived' : '' }}" data-picker-group="{{ $paGroup['id'] }}">
                    <div class="pa-role-group__head">
                        <label class="pa-role-check pa-role-check--group" for="{{ $paGroupId }}">
                            <input id="{{ $paGroupId }}" type="checkbox" autocomplete="off" aria-describedby="{{ $paGroupId }}_count" data-picker-group-toggle>
                            <span class="pa-role-check__box" aria-hidden="true"></span>
                            <span class="pa-role-check__copy">
                                <span class="pa-visually-hidden">Tick every policy in </span><span class="pa-role-check__title">{{ $paGroup['name'] }}</span>
                                @if($paGroup['archived'])
                                    <span class="pa-pill pa-pill--muted pa-role-check__tag">Archived category</span>
                                @endif
                            </span>
                        </label>
                        <span id="{{ $paGroupId }}_count" class="pa-role-group__count" data-picker-group-count>0 of {{ $paGroupSize }}</span>
                        <button type="button" class="pa-role-group__fold" aria-expanded="true" aria-controls="{{ $paGroupId }}_items" title="Hide or show this category" data-picker-fold>
                            <span class="pa-visually-hidden">Hide or show {{ $paGroup['name'] }}</span>
                            <i data-lucide="chevron-down"></i>
                        </button>
                    </div>
                    <ul id="{{ $paGroupId }}_items" class="pa-role-group__items">
                        @foreach($paGroup['policies'] as $paPolicy)
                            @php
                                $paVersion = trim((string) $paPolicy['version']);
                                /* "5.1" reads better as "v5.1"; a year ("2025") or free text is shown as entered. */
                                $paVersionLabel = ($paVersion !== '' && preg_match('/^\d{1,3}(\.\d+)*$/', $paVersion) ? 'v'.$paVersion : $paVersion);
                            @endphp
                            <li class="pa-role-item" data-picker-item data-search="{{ mb_strtolower($paPolicy['title'].' '.$paVersionLabel) }}">
                                <label class="pa-role-check" for="{{ $paPickerId }}_p{{ $paPolicy['id'] }}">
                                    <input id="{{ $paPickerId }}_p{{ $paPolicy['id'] }}" type="checkbox" name="policy_ids[]" value="{{ $paPolicy['id'] }}" autocomplete="off" data-picker-policy data-active="{{ $paPolicy['is_active'] && !$paGroup['archived'] ? 1 : 0 }}">
                                    <span class="pa-role-check__box" aria-hidden="true"></span>
                                    <span class="pa-role-check__copy">
                                        <span class="pa-role-check__title">{{ $paPolicy['title'] }}</span>
                                        @if($paVersionLabel !== '')
                                            <span class="pa-role-check__version">{{ $paVersionLabel }}</span>
                                        @endif
                                        @if(!$paPolicy['is_active'])
                                            <span class="pa-pill pa-pill--muted pa-role-check__tag" title="Switched off in the Question Bank. It stays ticked, and is assigned once it is switched on.">Inactive</span>
                                        @endif
                                    </span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
            <p class="pa-role-picker__nomatch" data-picker-nomatch hidden></p>
        </div>

        @if($paHasUnassignable)
            <small class="pa-field-hint pa-role-picker__foot">A policy marked Inactive, or in an archived category, can be ticked. It is only assigned once it is switched on again in the Question Bank.</small>
        @endif
    @endif
</div>
