@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('plans.tree') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Tree</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6 mt-5 z-30 relative">
        <div class="col-span-12 z-30">
            <div class="intro-y box">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Unsignned Students</h2>
                    <div id="unsignedResultCount" class="font-bold text-base text-right" data-total="0"></div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3">
                            <label for="unsigned_term" class="mb-2 block">Term Declaration<span class="text-danger">*</span></label>
                            <select id="unsigned_term" name="unsigned_term" class="form-control w-full lcc-tom-select">
                                <option value="">Please Select</option>
                                @if($termDeclarations->count())
                                    @foreach($termDeclarations as $term)
                                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                                    @endforeach
                                @endif
                                {{--@if($semesters->count())
                                    @foreach($semesters as $sem)
                                        <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                    @endforeach
                                @endif--}}
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="unsigned_statuses" class="mb-2 block">Student Statuses<span class="text-danger">*</span></label>
                            <select id="unsigned_statuses" class="form-control w-full lcc-tom-select" name="unsigned_statuses[]" multiple>
                                @if($statuses->count() > 0)
                                    @foreach($statuses as $sts)
                                        <option {{ (in_array($sts->id, [18, 23, 24, 28, 29]) ? 'selected' : '') }} value="{{ $sts->id }}">{{ $sts->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3 text-right sm:pt-7">
                            <input type="hidden" name="unsigned_course_id" value="{{ $theCourse->id }}" id="unsigned_course_id"/>
                            <button id="unsignnedStudentList-go" type="button" class="btn btn-primary w-auto sm:w-16" >Go</button>
                            <button id="unsignnedStudentList-reset" type="button" class="btn btn-secondary w-auto sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            <button id="moveToProtentialList" style="display: none;" type="button" class="btn btn-success text-white w-auto sm:ml-1">Move to Potential</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto scrollbar-hidden unsignedStudentListWrap hidden">
                        <div id="unsignedStudentList" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5 z-20">
        <div class="col-span-12 z-20">
            <div class="intro-y box">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Assigned To</h2>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-2">
                        <div class="col-span-6">
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-5 text-slate-500 font-medium">Academic Year</div>
                                <div class="col-span-7 font-medium">
                                    {{ $theAcademicYear->name }}
                                    <input type="hidden" id="assignToAcademicYearId" value="{{ $theAcademicYear->id }}" name="assignToAcademicYearId"/>
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-5 text-slate-500 font-medium">Attendance Term</div>
                                <div class="col-span-7 font-medium">
                                    {{ $theTermDeclaration->name }}
                                    <input type="hidden" id="assignToTermDeclarationId" value="{{ $theTermDeclaration->id }}" name="assignToTermDeclarationId"/>
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-5 text-slate-500 font-medium">Course</div>
                                <div class="col-span-7 font-medium">
                                    {{ $theCourse->name }}
                                    <input type="hidden" id="assignToCourseId" value="{{ $theCourse->id }}" name="assignToCourseId"/>
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-5 text-slate-500 font-medium">Group</div>
                                <div class="col-span-7 font-medium">
                                    {{ $theGroup->name }}
                                    <input type="hidden" id="assignToGroupId" value="{{ $theGroup->id }}" name="assignToGroupId"/>
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-5 text-slate-500 font-medium">Evening and Weekend</div>
                                <div class="col-span-7 font-medium">
                                    {!! ($theGroup->evening_and_weekend == 1 ? '<span class="font-medium text-primary inline-flex justify-start items-center tooltip" title="Evening & Weekend">Yes<i data-lucide="sunset" class="w-8 h-8 ml-2"></i></span>' : '<span title="Weekdays" class="font-medium text-amber-600 inline-flex justify-start items-center tooltip">No<i data-lucide="sun" class="w-8 h-8 ml-2"></i></span>' ) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6">
                            <div class="text-slate-500 font-medium mb-2">Modules</div>
                            <div class="relative">
                                @if($selectedModules->count() > 0)
                                    @foreach($selectedModules as $smd)
                                        <div class="form-check mb-2">
                                            <input checked id="assignToModuleIds_{{ $smd->id }}" class="form-check-input assignToModuleIds" name="assignToModuleIds[]" type="checkbox" value="{{ $smd->id }}">
                                            <label class="form-check-label" for="assignToModuleIds_{{ $smd->id }}">
                                                {!! $smd->creations->module_name. (isset($smd->class_type) && !empty($smd->class_type) ? ' - '.$smd->class_type.' ' : '') . (isset($smd->assign) ? ' <strong>('.$smd->assign->count().')</strong>' : ' <strong>(0)</strong>') !!}
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 resultWrap hidden"></div>
        <div class="col-span-8" id="studentListArea">
            <div class="intro-y box">
                <div class="grid grid-cols-12 gap-0 relative">
                    <div class="col-span-6 assignExistingCol">
                        <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium text-base mr-auto">Existing Students <span class="existingCount">({{ (isset($existingStudents['count']) && $existingStudents['count'] > 0 ? $existingStudents['count'] : 0) }})</span></h2>
                        </div>
                        <div class="p-5 pr-0">
                            <ul class="assignStudentsList existingStudentList">
                                {!! (isset($existingStudents['htm']) && !empty($existingStudents['htm']) ? $existingStudents['htm'] : '') !!}
                            </ul>
                            <div class="mt-3">
                                <input id="filterExistingStudents" name="filterExistingStudents" type="text" class="form-control w-full rounded-0"  placeholder="Filter...">
                            </div>
                        </div>
                    </div>
                    <div class="addRemoveBtns">
                        <button type="button" disabled class="btn btn-success btn-sm text-white mb-1 w-auto addStudents">
                            <i data-lucide="chevrons-left" class="w-4 h-4 mr-1"></i> Add
                            <i data-loading-icon="three-dots" class="w-8 h-8 theLoader hidden"></i>
                        </button>
                        <button type="button" disabled class="btn btn-danger btn-sm text-white w-auto removeStudents">
                            Remove <i data-lucide="chevrons-right" class="w-4 h-4 ml-1"></i>
                            <i data-loading-icon="three-dots" class="w-8 h-8 theLoader hidden"></i>
                        </button>
                        <button type="button" disabled class="btn btn-pending btn-sm mt-1 text-white w-auto reAssignStudent">
                            Re-Assign <i data-lucide="chevrons-right" class="w-4 h-4 ml-1"></i>
                            <i data-loading-icon="three-dots" class="w-8 h-8 theLoader hidden"></i>
                        </button>
                    </div>
                    <div class="col-span-6 assignPotentialCol">
                        <div class="flex items-center p-5 pl-0 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium text-base mr-auto">Potential Students  <span class="potentialCount"></span></h2>
                        </div>
                        <div class="p-5 pl-0">
                            <ul class="assignStudentsList potentialStudentList"></ul>
                            <div class="mt-3 flex jsutify-start">
                                <button type="button" class="btn btn-secondary w-auto selectDeselectAllPotential"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> <span>Select/Deselect All</span></button>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="col-span-4">
            <div class="intro-y box">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Search Form</h2>
                </div>
                <div class="p-5" id="theSearchForm">
                    <div class="grid grid-cols-12 gap-4 items-center mb-4">
                        <div class="col-span-12 sm:col-span-5 form-label mb-0">Student Search</div>
                        <div class="col-span-12 sm:col-span-7">
                            <input type="text" name="potentialStudentSearch" value="" id="potentialStudentSearch" class="form-control w-full"/>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 items-center mb-4">
                        <div class="col-span-12 sm:col-span-5 form-label mb-0 flex items-center">Term Declaration <i data-loading-icon="three-dots" class="w-6 h-6 ml-4 theLoading hidden"></i></div>
                        <div class="col-span-12 sm:col-span-7">
                            <select id="potentialTermDeclaration" class="w-full tom-selects" name="potentialTermDeclaration">
                                <option value="">Please Select</option>
                                @if(!empty($termDeclarations))
                                    @foreach($termDeclarations as $trm)
                                        <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class=" mb-4 hidden potentialGroupArea">
                        <div class="grid grid-cols-12 gap-4 items-center">
                            <div class="col-span-12 sm:col-span-5 form-label mb-0 flex items-center">Groups <i data-loading-icon="three-dots" class="w-6 h-6 ml-4 theLoading hidden"></i></div>
                            <div class="col-span-12 sm:col-span-7">
                                <select id="potentialGroups" class="w-full tom-selects" name="potentialGroups">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="hidden potentialModuleArea">
                        <div class="grid grid-cols-12 gap-4 items-center">
                            <div class="col-span-12 sm:col-span-5 form-label mb-0 flex items-center">Modules <i data-loading-icon="three-dots" class="w-6 h-6 ml-4 theLoading hidden"></i></div>
                            <div class="col-span-12 sm:col-span-7">
                                <select id="potentialModules" class="w-full tom-selects" name="potentialModules">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="intro-y box mt-5 termModuleBox hidden">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Modules</h2>
                </div>
                <div class="p-5" id="termModuleBoxBody">
                    
                </div>
            </div>
        </div>
    </div>

    <div id="showAllModulesModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body p-5 pb-3">
                    
                </div>
            </div>
        </div>
    </div>
     
    <!-- Student re-assign Modal Start -->
    <div id="studentReAssignModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="studentReAssignForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Re - Assign Student to New Group</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body p-5">
                        <div>
                            <label for="new_group_id" class="form-label">Assigned To <span class="text-danger">*</span></label>
                            <select name="new_group_id" class="w-full tom-selects" id="new_group_id">
                                <option value="">Please Select</option>
                                @if(!empty($otherGroup) && count($otherGroup) > 0)
                                    @foreach($otherGroup as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mt-3 moduleArea" style="display: none;">
                            <table class="table table-bordered table-sm" id="moduleLilstTables">
                                <thead>
                                    <tr>
                                        <th><span class="oldGroupName"></span> Group Modules (Tick to Remove)</th>
                                        <th><span class="newGroupName"></span> Group Modules (Tick to Add)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="assignedModulesCol"></td>
                                        <td class="newModulesCol"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button disabled type="submit" id="reAssignStdBtn" class="btn btn-primary w-auto">
                            Change Group
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="student_id" value="0"/>
                        <input type="hidden" name="academic_year_id" value="{{ $theAcademicYear->id }}"/>
                        <input type="hidden" name="term_declaration_id" value="{{ $theTermDeclaration->id }}"/>
                        <input type="hidden" name="course_id" value="{{ $theCourse->id }}"/>
                        <input type="hidden" name="group_id" value="{{ $theGroup->id }}"/>
                        <input type="hidden" name="assigned_module_ids" value="{{ (!empty($selectedModuleIds) ? implode(',', $selectedModuleIds) : '') }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Student re-assign Modal End -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->
@endsection

@section('script')
    @vite('resources/js/course-management.js')
    @vite('resources/js/unsigned.js')
    @vite('resources/js/assign.js')
@endsection