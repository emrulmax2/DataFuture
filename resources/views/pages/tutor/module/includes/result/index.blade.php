<div class="rd-tablewrap">
    <table class="rd-table">
        <thead>
            <tr>
                <th class="rd-col-sn">#</th>
                <th class="rd-col-student">Student List</th>
                <th class="rd-col-status">Status</th>
                <th class="rd-col-grade">Grade <span class="rd-req">*</span></th>
                <th class="rd-col-attempt">Attempted</th>
                @if(isset($result) && count($result) > 0)
                    <th class="rd-col-action">Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php $serial = 1; @endphp
            @foreach($assignList as $assign)
                @php
                    $studentName = $assign->student->full_name;
                    $avatarSeed = $studentName . $assign->student->registration_no;
                    $hasRowResult = isset($result[$assign->student->id]["id"]);
                    $showActionColumn = isset($result) && count($result) > 0;
                @endphp
                <tr>
                    <td>
                        <span class="rd-sn">{{ $serial++ }}</span>
                    </td>

                    <td>
                        <div class="rd-student">
                            <span class="rd-student__avatar" style="background:{{ $avatarFor($avatarSeed) }};color:#fff;">{{ $initialsFor($studentName) }}</span>
                            <span class="min-w-0">
                                <a class="rd-student__reg" href="">{{ $assign->student->registration_no }}</a>
                                <span class="rd-student__name" title="{{ $studentName }}">{{ $studentName }}</span>
                            </span>
                        </div>
                    </td>

                    <td>
                        <span class="rd-status">
                            <span class="rd-status__dot"></span>{{ $assign->student->status->name }}
                        </span>
                    </td>

                    <td>
                        <div class="rd-select-wrap">
                            <select name="grade_id[]" aria-label="Grade" class="grade_id rd-select">
                                <option value="">Please Select</option>
                                @foreach($gradeList as $grade)
                                    {{-- compare against the same id the option carries: grade->grade->id, not the segment's own id --}}
                                    <option {{ (isset($result[$assign->student->id]["grade"]) && $result[$assign->student->id]["grade"] == $grade->grade->id) ? "selected" : ""; }} value="{{ $grade->grade->id }}">{{ $grade->grade->name }} - {{ $grade->grade->code }}</option>
                                @endforeach
                            </select>
                            <svg class="rd-select-wrap__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8a9b98" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                            {{-- must stay a sibling of the select: the validation handler uses .siblings('div.error-grade_id') --}}
                            <div class="acc__input-error error-grade_id rd-error"></div>
                        </div>
                        <input name="student_id[]" type="hidden" value="{{ $assign->student->id }}" />
                        <input name="assessment_plan_id[]" type="hidden" value="{{ $assessmentPlan->id }}" />
                        <input name="plan_id[]" type="hidden" value="{{ $assign->plan_id }}" />
                    </td>

                    <td>
                        @if(isset($result[$assign->student->id]["id"]))
                            <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#attemedModal" data-assessmentPlan="{{ $assessmentPlan->id }}" data-student_id="{{ $assign->student->id }}" class="cursor-pointer show-attempted attempt-count rd-attempt">{{ (isset($result[$assign->student->id]["count"])) ? $result[$assign->student->id]["count"] : 0 }}</a> <i data-loading-icon="oval" class="w-4 h-4 ml-1 inline-flex hidden"></i>
                        @else
                            <span class="rd-empty">—</span>
                        @endif
                    </td>

                    <input name="published_at[]" type="hidden" value="{{ $assessmentPlan->published_at }}" />
                    @if($hasRowResult)
                        <input name="id[]" type="hidden" value="{{ $result[$assign->student->id]["id"] }}" />
                    @endif

                    {{-- the cell always renders while the column exists, otherwise the row runs a column short --}}
                    @if($showActionColumn)
                        <td>
                            @if($hasRowResult)
                                <div class="rd-rowactions">
                                    <button type="button" data-id="{{ $result[$assign->student->id]["id"] }}" class="update-currentresult rd-btn is-green is-sm">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Update <i data-loading-icon="oval" class="w-3.5 h-3.5 ml-1 hidden text-white"></i>
                                    </button>
                                    <button type="button" data-assessmentPlan="{{ $assessmentPlan->id }}" class="readd-currentresult rd-btn is-ghost is-sm">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Re-Submission <i data-loading-icon="oval" class="w-3.5 h-3.5 ml-1 hidden"></i>
                                    </button>
                                </div>
                            @else
                                <span class="rd-empty">—</span>
                            @endif
                        </td>
                    @endif

                    <input name="created_by[]" type="hidden" value="{{ Auth::id(); }}" />
                    @if($hasRowResult)
                        <input name="updated_by[]" type="hidden" value="{{ $result[$assign->student->id]["created_by"] }}" />
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    @if(isset($result) && count($result) > 0)
        <input type="hidden" name="url" value="{{ route('result.update.bulk') }}" />
    @else
        <input type="hidden" name="url" value="{{ route('result.store') }}" />
    @endif
</div>
