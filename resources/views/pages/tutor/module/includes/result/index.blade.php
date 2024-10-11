<div class="overflow-x-auto">
    <table data-tw-merge class="w-full text-left">
        <thead data-tw-merge class="">
            <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                    #
                </th>
                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                    Student List
                </th>
                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                    Status
                </th>
                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                    Grade <span class="text-danger">*</span>
                </th>
                
                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                    Attemped
                </th>
                
                @if(isset($result) && count($result)>0)
                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                    Action
                </th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php $serial=1; @endphp
            @foreach($assignList as $assign)
            <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                    {{ $serial++ }}
                </td>
                
                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                    <a class="whitespace-nowrap font-medium" href="">
                        {{ $assign->student->registration_no }}
                    </a>
                    <div class="mt-0.5 whitespace-nowrap text-xs text-slate-500">
                        {{ $assign->student->full_name }}
                    </div>
                </td>
                
                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                    {{ $assign->student->status->name }}
                </td>

                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                    <select name="grade_id[]" data-tw-merge aria-label="Default select example" class="grade_id disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 group-[.form-inline]:flex-1 mt-2 sm:mr-2 ">
                        <option value="">Please Select</option>
                        @foreach($gradeList as $grade)
                                    <option {{ (isset($result[$assign->student->id]["grade"]) && $result[$assign->student->id]["grade"]==$grade->id) ? "selected" : ""; }} value="{{ $grade->grade->id }}">{{ $grade->grade->name }} - {{ $grade->grade->code }}</option>
                                
                        @endforeach
                    </select>
                    <div class="acc__input-error error-grade_id text-danger mt-2"></div>
                    <input name="student_id[]" type="hidden" value="{{ $assign->student->id }}" />
                    <input name="assessment_plan_id[]" type="hidden" value="{{ $assessmentPlan->id }}"/>
                    <input name="plan_id[]" type="hidden" value="{{ $assign->plan_id }}"/>
                </td>
                
                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                    @if(isset($result[$assign->student->id]["id"]))
                    <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#attemedModal" data-assessmentPlan="{{ $assessmentPlan->id }}" data-student_id="{{ $assign->student->id }}" class="cursor-pointer show-attempted attempt-count ">
                        {{ (isset($result[$assign->student->id]["count"])) ? $result[$assign->student->id]["count"] : 0 }}</a> <i data-loading-icon="oval" class="w-4 h-4 ml-1 inline-flex hidden"></i>
                    @endif
                </td>
                @if(isset($result[$assign->student->id]["id"]))
                    <input name="published_at[]" type="hidden" value="{{ $assessmentPlan->published_at }}" />
                    <input name="id[]" type="hidden" value="{{ $result[$assign->student->id]["id"] }}" />
                    <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                        
                        <button type="button" data-id="{{ $result[$assign->student->id]["id"] }}" class="update-currentresult btn btn-success text-white shadow-md mr-2"><i data-lucide="pencil" class="w-4 h-4 mr-1 text-white"></i> Update <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white" ></i></button>
                        <button type="button" data-assessmentPlan={{ $assessmentPlan->id }} class="readd-currentresult btn btn-primary text-white shadow-md"><i data-lucide="plus" class="w-4 h-4 mr-1 text-white"></i> Re-Submission <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white" ></i></button>
                        
                    </td>
                    <input name="created_by[]" type="hidden" value="{{ Auth::id(); }}" />
                    <input name="updated_by[]" type="hidden" value="{{ $result[$assign->student->id]["created_by"] }}" />
                @else
                    <input name="published_at[]" type="hidden" value="{{ $assessmentPlan->published_at }}" />
                    <input name="created_by[]" type="hidden" value="{{ Auth::id(); }}" />
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @if(isset($result) && count($result)>0)
        <input type="hidden" name="url" value="{{ route('result.update.bulk') }}" />
    @else
        <input type="hidden" name="url" value="{{ route('result.store') }}" />
    @endif
</div>
