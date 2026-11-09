{{-- The filter toolbar. A GET form to this page, so every filtered view has its own URL. --}}
@php
    $f = $filters;
    $moreCount = count(array_filter([$f->venue, $f->course, $f->group, $f->module, $f->status, $f->staff, $f->student, $f->caseStatus]));
    $staffName = $f->staff ? \App\Services\AttendanceIntegrity\Lookup::staffName($f->staff) : null;
    $studentName = $f->student ? \App\Services\AttendanceIntegrity\Lookup::studentLabel($f->student) : null;
@endphp
<form method="get" action="{{ url()->current() }}" class="aim-toolbar aim-no-print {{ $moreCount ? 'is-open' : '' }}" id="aimFilters" autocomplete="off">
    @foreach(['metric', 'rule'] as $keep)
        @if(request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
    @endforeach
    @if($f->plan)<input type="hidden" name="plan" value="{{ $f->plan }}">@endif
    @if($f->session)<input type="hidden" name="session" value="{{ $f->session }}">@endif

    <div class="aim-toolbar__row">
        <div class="aim-field">
            <label for="aimTerms">Academic term</label>
            <select id="aimTerms" name="terms[]" multiple data-aim-tom="multi">
                @foreach($filterOptions['terms'] as $term)
                    <option value="{{ $term['id'] }}" @selected(in_array($term['id'], $f->terms, true))>{{ $term['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="aim-field">
            <label for="aimYear">Academic year</label>
            <select id="aimYear" name="academic_year" class="aim-input">
                <option value="">Any year</option>
                @foreach($filterOptions['years'] as $year)
                    <option value="{{ $year['id'] }}" @selected($f->academicYear === $year['id'])>{{ $year['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="aim-field">
            <label for="aimDates">Date range</label>
            <input type="text" id="aimDates" class="aim-input" placeholder="Any date" readonly
                value="{{ $f->from ? date('d-m-Y', strtotime($f->from)).' - '.date('d-m-Y', strtotime($f->to ?? $f->from)) : '' }}">
            <input type="hidden" name="date_from" id="aimDateFrom" value="{{ $f->from }}">
            <input type="hidden" name="date_to" id="aimDateTo" value="{{ $f->to }}">
        </div>
        <div class="aim-field">
            <label for="aimRisk">Risk level</label>
            <select id="aimRisk" name="risk" class="aim-input">
                <option value="">Any level</option>
                @foreach(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $value => $label)
                    <option value="{{ $value }}" @selected($f->risk === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="aim-toolbar__actions">
            <button type="button" class="aim-btn" data-aim-more aria-expanded="{{ $moreCount ? 'true' : 'false' }}">
                <i data-lucide="sliders-horizontal"></i> More @if($moreCount)<span class="aim-count-dot">{{ $moreCount }}</span>@endif
            </button>
            <button type="submit" class="aim-btn aim-btn--primary"><i data-lucide="filter"></i> Apply</button>
        </div>
    </div>

    @hasSection('aim_extra_filters')
        <div class="aim-toolbar__extra">@yield('aim_extra_filters')</div>
    @endif

    <div class="aim-toolbar__more">
        <div class="aim-field">
            <label for="aimVenue">Campus</label>
            <select id="aimVenue" name="venue" class="aim-input">
                <option value="">Any campus</option>
                @foreach($filterOptions['venues'] as $venue)
                    <option value="{{ $venue['id'] }}" @selected($f->venue === $venue['id'])>{{ $venue['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="aim-field">
            <label for="aimCourse">Course</label>
            <select id="aimCourse" name="course" data-aim-tom="single">
                <option value="">Any course</option>
                @foreach($filterOptions['courses'] as $course)
                    <option value="{{ $course['id'] }}" @selected($f->course === $course['id'])>{{ $course['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="aim-field">
            <label for="aimGroup">Teaching group (cohort)</label>
            <select id="aimGroup" name="group" data-aim-tom="single" data-aim-term-options="groups">
                <option value="">Any group</option>
                @foreach($termOptions['groups'] ?? [] as $group)
                    <option value="{{ $group->id }}" @selected($f->group === (int) $group->id)>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="aim-field">
            <label for="aimModule">Module</label>
            <select id="aimModule" name="module" data-aim-tom="single" data-aim-term-options="modules">
                <option value="">Any module</option>
                @foreach($termOptions['modules'] ?? [] as $module)
                    <option value="{{ $module->id }}" @selected($f->module === (int) $module->id)>{{ $module->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="aim-field">
            <label for="aimStatus">Attendance status</label>
            <select id="aimStatus" name="status" class="aim-input">
                <option value="">Any status</option>
                @foreach($filterOptions['statuses'] as $status)
                    <option value="{{ $status['id'] }}" @selected($f->status === $status['id'])>{{ $status['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="aim-field">
            <label for="aimStaff">Staff member</label>
            <select id="aimStaff" name="staff" data-aim-remote="staff">
                @if($f->staff)<option value="{{ $f->staff }}" selected>{{ $staffName }}</option>@endif
            </select>
        </div>
        <div class="aim-field">
            <label for="aimStudent">Student</label>
            <select id="aimStudent" name="student" data-aim-remote="students">
                @if($f->student)<option value="{{ $f->student }}" selected>{{ $studentName }}</option>@endif
            </select>
        </div>
        <div class="aim-field">
            <label for="aimCaseStatus">Investigation status</label>
            <select id="aimCaseStatus" name="case_status" class="aim-input">
                <option value="">Any status</option>
                @foreach(\App\Models\AttendanceIntegrity\AimCase::STATUSES as $status)
                    <option value="{{ $status }}" @selected($f->caseStatus === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="aim-toolbar__foot">
        @foreach($filterText as $label => $value)
            <span class="aim-chip" title="{{ $label }}: {{ $value }}">{{ $label }}: <b>{{ $value }}</b></span>
        @endforeach
        @if($f->session)<span class="aim-chip">Class date: <b>#{{ $f->session }}</b></span>@endif
        <a href="{{ url()->current() }}{{ request()->filled('metric') ? '?metric='.request('metric') : '' }}" class="aim-btn aim-btn--sm aim-btn--ghost" style="margin-left: auto;"><i data-lucide="rotate-ccw"></i> Reset filters</a>
    </div>
</form>
