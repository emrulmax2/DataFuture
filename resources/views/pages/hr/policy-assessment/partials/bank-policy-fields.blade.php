{{-- Policy add/edit fields. Needs $prefix (add|edit) and $categories; $examMix (the question
     pattern per exam, from PolicyDocumentController::examPattern()) words the rules hint. --}}
<div class="ss-modal-grid">
    <div class="ss-modal-field">
        <label for="{{ $prefix }}_policy_category">Category <span>*</span></label>
        <label class="ss-modal-select" for="{{ $prefix }}_policy_category">
            <select id="{{ $prefix }}_policy_category" name="policy_category_id" class="policy_category_id">
                <option value="">Choose a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}{{ !$category->is_active ? ' (inactive)' : '' }}</option>
                @endforeach
            </select>
            <i data-lucide="chevron-down"></i>
        </label>
        <div class="acc__input-error error-policy_category_id"></div>
    </div>

    <div class="ss-modal-field">
        <label for="{{ $prefix }}_policy_version">Version</label>
        <input id="{{ $prefix }}_policy_version" type="text" name="version" maxlength="50" class="ss-modal-input version" placeholder="e.g. v3.1 or 2025">
        <div class="acc__input-error error-version"></div>
    </div>

    <div class="ss-modal-field ss-modal-field--full">
        <label for="{{ $prefix }}_policy_title">Title <span>*</span></label>
        <input id="{{ $prefix }}_policy_title" type="text" name="title" maxlength="191" class="ss-modal-input title" placeholder="e.g. Safeguarding Policy">
        <div class="acc__input-error error-title"></div>
    </div>

    <div class="ss-modal-field ss-modal-field--full">
        <label for="{{ $prefix }}_policy_pdf">PDF link</label>
        <input id="{{ $prefix }}_policy_pdf" type="url" name="pdf_url" maxlength="500" class="ss-modal-input pdf_url" placeholder="https://...">
        <small class="pa-field-hint">Staff open this when they click "Read policy".</small>
        <div class="acc__input-error error-pdf_url"></div>
    </div>

    <div class="ss-modal-field ss-modal-field--full">
        <label for="{{ $prefix }}_policy_page">Web page link</label>
        <input id="{{ $prefix }}_policy_page" type="url" name="page_url" maxlength="500" class="ss-modal-input page_url" placeholder="https://... (optional)">
        <div class="acc__input-error error-page_url"></div>
    </div>

    <div class="ss-modal-field ss-modal-field--full">
        <label for="{{ $prefix }}_policy_description">Description</label>
        <textarea id="{{ $prefix }}_policy_description" name="description" rows="3" maxlength="5000" class="ss-modal-input ss-modal-textarea description" placeholder="Optional. A short summary of the policy."></textarea>
        <div class="acc__input-error error-description"></div>
    </div>

    <p class="ss-modal-field--full pa-field-hint pa-rules-hint">
        The test rules below apply to every exam (Beginner, Intermediate and Expert). No exam draws from one level alone: each takes a set share of the questions per test from every level.
        @if(isset($examMix) && is_array($examMix) && !empty($examMix))
            <span class="pa-rules-hint__mix">
                Beginner / Intermediate / Expert questions:
                @foreach($examMix as $paMixRow)
                    <span class="pa-rules-hint__exam"><strong>{{ $paMixRow['name'] }}</strong> {{ implode(' / ', $paMixRow['mix']) }}%</span>@if(!$loop->last)<span class="pa-meta__dot" aria-hidden="true">·</span>@endif
                @endforeach
            </span>
        @endif
    </p>

    <div class="ss-modal-field">
        <label for="{{ $prefix }}_policy_pass">Pass mark (%) <span>*</span></label>
        <input id="{{ $prefix }}_policy_pass" type="number" min="1" max="100" step="1" name="pass_mark" class="ss-modal-input pass_mark" value="80">
        <div class="acc__input-error error-pass_mark"></div>
    </div>

    <div class="ss-modal-field">
        <label for="{{ $prefix }}_policy_per_test">Questions per test <span>*</span></label>
        <input id="{{ $prefix }}_policy_per_test" type="number" min="1" max="50" step="1" name="questions_per_attempt" class="ss-modal-input questions_per_attempt" value="10">
        <div class="acc__input-error error-questions_per_attempt"></div>
    </div>

    <div class="ss-modal-field">
        <label for="{{ $prefix }}_policy_attempts">Max attempts</label>
        <input id="{{ $prefix }}_policy_attempts" type="number" min="1" max="100" step="1" name="max_attempts" class="ss-modal-input max_attempts" placeholder="Blank = unlimited">
        <div class="acc__input-error error-max_attempts"></div>
    </div>

    <div class="ss-modal-field">
        <label for="{{ $prefix }}_policy_time_limit">Time limit (minutes)</label>
        <input id="{{ $prefix }}_policy_time_limit" type="number" min="1" max="600" step="1" inputmode="numeric" name="time_limit_minutes" class="ss-modal-input time_limit_minutes" placeholder="Blank = no time limit" aria-describedby="{{ $prefix }}_policy_time_limit_hint">
        <small id="{{ $prefix }}_policy_time_limit_hint" class="pa-field-hint">Leave blank for an untimed test. The clock starts when the member of staff starts the test and the test is submitted automatically when it runs out.</small>
        <div class="acc__input-error error-time_limit_minutes"></div>
    </div>

    <div class="ss-modal-field">
        <label for="{{ $prefix }}_policy_sort">Sort order</label>
        <input id="{{ $prefix }}_policy_sort" type="number" min="0" step="1" name="sort_order" class="ss-modal-input sort_order" placeholder="{{ $prefix === 'add' ? 'Next in category' : '0' }}">
        <div class="acc__input-error error-sort_order"></div>
    </div>

    <div class="ss-modal-field ss-modal-field--full">
        <label class="ss-status-toggle pa-active-toggle" for="{{ $prefix }}_policy_active">
            <input id="{{ $prefix }}_policy_active" name="is_active" value="1" type="checkbox" {{ $prefix === 'add' ? 'checked' : '' }} autocomplete="off">
            <span class="ss-status-toggle__control">
                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
            </span>
            <span class="ss-status-toggle__copy">
                <strong data-on="Active" data-off="Inactive">Active</strong>
                <small data-on="Can be assigned, and staff can take its test" data-off="Hidden from assigning; staff cannot start its test">Can be assigned, and staff can take its test</small>
            </span>
        </label>
        <div class="acc__input-error error-is_active"></div>
    </div>
</div>
