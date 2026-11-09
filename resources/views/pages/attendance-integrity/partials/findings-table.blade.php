{{-- The findings table (filled by attendance-integrity.js). Selecting rows opens a case. --}}
@if($can['manage_investigations'])
    <div class="aim-bulkbar" id="aimBulkBar">
        <span><span id="aimBulkCount">0</span> selected</span>
        <button type="button" class="aim-btn aim-btn--sm aim-btn--primary" data-aim-case-from-selection><i data-lucide="folder-plus"></i> Open a case</button>
        <button type="button" class="aim-btn aim-btn--sm" data-aim-add-to-case>Add to an existing case</button>
        <button type="button" class="aim-btn aim-btn--sm aim-btn--ghost" data-aim-clear-selection>Clear</button>
    </div>
@endif
<div id="aimFindingsTable" data-url="{{ $dataUrl }}" data-manage="{{ $can['manage_investigations'] ? 1 : 0 }}"
    data-dismiss="{{ route('attendance.integrity.findings.dismiss', ['finding' => '__ID__']) }}"
    data-reopen="{{ route('attendance.integrity.findings.reopen', ['finding' => '__ID__']) }}"
    data-staff-url="{{ $can['view_staff'] ? route('attendance.integrity.staff.show', ['user' => '__ID__']) : '' }}"
    data-student-url="{{ $can['view_students'] ? route('attendance.integrity.students.show', ['student' => '__ID__']) : '' }}"
    data-case-url="{{ $can['view_investigations'] ? route('attendance.integrity.cases.show', ['case' => '__ID__']) : '' }}"></div>
