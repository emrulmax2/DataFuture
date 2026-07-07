@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
 
@endsection

@section('subcontent')
    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->  

    @php
        $gradeMeta = [
            'P' => ['color' => '#0B6B66', 'bg' => '#E5F2F0'],
            'M' => ['color' => '#8A6D1F', 'bg' => '#F4EBD6'],
            'D' => ['color' => '#0F252D', 'bg' => '#E2E8E9'],
            'R' => ['color' => '#B3392E', 'bg' => '#FBEDEB'],
            'U' => ['color' => '#B3392E', 'bg' => '#FBEDEB'],
            'A' => ['color' => '#B3392E', 'bg' => '#FBEDEB'],
        ];
        $completedCount = 0; $outstandingCount = 0; $totalCount = 0;
        if(!empty($dataSet)):
            foreach($dataSet as $mName => $rSet):
                $totalCount++;
                $gcode = isset($rSet[0]->grade->code) ? trim($rSet[0]->grade->code) : '';
                if(in_array($gcode, ['P','M','D'])) { $completedCount++; } else { $outstandingCount++; }
            endforeach;
        endif;
    @endphp

    <!-- BEGIN: Results -->
    <div class="intro-y mt-5">
        <div class="sp-card sp-results" data-screen-label="Results Table">
            <div class="sp-card-head">
                <div class="sp-card-title">Results</div>
                <div class="sp-pills">
                    <span class="sp-pill sp-pill--done"><span class="sp-dot"></span>Completed {{ $completedCount }}</span>
                    <span class="sp-pill sp-pill--out"><span class="sp-dot"></span>Outstanding {{ $outstandingCount }}</span>
                    <span class="sp-pill sp-pill--total">Total {{ $totalCount }}</span>
                </div>
                <div class="sp-card-actions">
                    <a href="{{ route('student-results.print',$student->id) }}" id="tabulator-print-x" class="sp-btn sp-btn--ghost">
                        <i data-lucide="printer" class="w-4 h-4"></i> Print
                    </a>
                    @if(isset($student->status->eligible_for_award) && $student->status->eligible_for_award == 1 && empty($award) && !isset($award->id))
                        <button data-tw-toggle="modal" data-tw-target="#addStudentAwardInfoModal" type="button" class="sp-btn sp-btn--dark">Add Award</button>
                    @endif
                </div>
            </div>
            <div class="sp-rtable-wrap">
                <table id="sortable-table" class="sp-rtable">
                    <thead>
                        <tr>
                            <th data-sort="module" class="sp-th sp-th--sort">Module <i data-lucide="chevrons-up-down" class="sp-th-ic"></i></th>
                            <th data-sort="term" class="sp-th sp-th--sort">Term <i data-lucide="chevrons-up-down" class="sp-th-ic"></i></th>
                            <th data-sort="published" class="sp-th sp-th--sort">Published <i data-lucide="chevrons-up-down" class="sp-th-ic"></i></th>
                            <th data-sort="grade" class="sp-th sp-th--sort">Grade <i data-lucide="chevrons-up-down" class="sp-th-ic"></i></th>
                            <th data-sort="merit" class="sp-th sp-th--sort">Merit <i data-lucide="chevrons-up-down" class="sp-th-ic"></i></th>
                            <th data-sort="attempts" class="sp-th sp-th--sort sp-th--center">Attempts <i data-lucide="chevrons-up-down" class="sp-th-ic"></i></th>
                            <th data-sort="updated_by" class="sp-th sp-th--sort">Updated By <i data-lucide="chevrons-up-down" class="sp-th-ic"></i></th>
                            <th class="sp-th sp-th--right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($dataSet)
                            @foreach($dataSet as $moduleDetails => $resultSet)
                                @php
                                    $currentResult = $resultSet[0];
                                    $gcode = isset($currentResult->grade->code) ? trim($currentResult->grade->code) : '';
                                    $gm = $gradeMeta[$gcode] ?? ['color' => '#43585D', 'bg' => '#EEF2F3'];
                                    $attemptCount = count($resultSet);
                                @endphp
                                <tr class="sp-tr">
                                    <td class="sp-td">
                                        <span class="sp-mod-name">{{ $currentResult->plan->creations->module_name }} - {{ $currentResult->plan->creations->level->name }}</span>
                                        <span class="sp-mod-sub">
                                            <span class="sp-mono">{{ $currentResult->plan->creations->code }}</span>
                                            <span class="sp-sep">&middot;</span>
                                            <span>{{ $currentResult->plan->course->body->name }}</span>
                                            <span class="sp-sep">&middot;</span>
                                            <span class="sp-mono">#{{ $currentResult->id }}</span>
                                        </span>
                                    </td>
                                    <td class="sp-td sp-td--muted">
                                        @if($currentResult->term_declaration_id == Null)
                                            {{ $currentResult->plan->attenTerm->name }}
                                        @else
                                            {{ $currentResult->term->name }}
                                        @endif
                                    </td>
                                    <td class="sp-td sp-td--muted sp-td--num">
                                        {{ date('d M, Y',strtotime($currentResult->published_at)) }} &middot; {{ date('h:i a',strtotime($currentResult->published_at)) }}
                                    </td>
                                    <td class="sp-td">
                                        <span class="sp-grade" style="color:{{ $gm['color'] }};background:{{ $gm['bg'] }}">{{ $gcode }}</span>
                                    </td>
                                    <td class="sp-td sp-td--grade" style="color:{{ $gm['color'] }}">{{ $currentResult->grade->name }}</td>
                                    <td class="sp-td sp-td--center">
                                        <a href="javascript:;" data-theme="light" data-tw-toggle="modal" data-tw-target="#callLockModal{{ $resultSet[0]->id }}" data-trigger="click" class="sp-attempts {{ $attemptCount > 1 ? 'is-multi' : '' }}" title="attempt count">{{ $attemptCount }}</a>
                                    </td>
                                    <td class="sp-td sp-td--muted">
                                        {{ isset($currentResult->updatedBy->employee->full_name)  ? $currentResult->updatedBy->employee->full_name : (isset($currentResult->createdBy->employee->full_name) ? $currentResult->createdBy->employee->full_name : $currentResult->createdBy->name) }}
                                    </td>
                                    <td class="sp-td sp-td--right">
                                        @if(isset(auth()->user()->priv()['result_edit']) && auth()->user()->priv()['result_edit'] == 1)
                                            <button class="sp-edit-btn" type="button" data-tw-toggle="modal" data-tw-target="#editAttemptModal{{ $resultSet[0]->id  }}" data-module="{{ $currentResult->plan->creations->module_name }} - {{ $currentResult->plan->creations->level->name }}" data-code= "{{ $currentResult->plan->creations->code }}" data-termid="{{ ($currentResult->term_declaration_id) ? $currentResult->term_declaration_id : $currentResult->plan->attenTerm->id }}" data-term="{{ $currentResult->plan->attenTerm->name }}" data-publishTime={{ date('H:m',strtotime($currentResult->published_at))  }} data-publishDate={{ date('d-m-Y',strtotime($currentResult->published_at))  }} data-grade="{{ $currentResult->grade->id }}" data-id="{{ $currentResult->id  }}">
                                                Edit
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="sp-results-foot">
                <span>Showing <strong>{{ $totalCount }}</strong> of <strong>{{ $totalCount }}</strong> results</span>
            </div>
        </div>
    </div>
    <!-- END: Results -->

    <!-- BEGIN: Award Section -->
    @if(isset($student->status->eligible_for_award) && $student->status->eligible_for_award == 1 && (isset($award->id) && $award->id > 0))
    <div class="intro-y mt-5">
        <div class="sp-card sp-award" data-screen-label="Award Details">
            <div class="sp-card-head">
                <div class="sp-card-title">Award Details</div>
                @if(isset($award->qual->name) && !empty($award->qual->name))
                    <span class="sp-pill sp-pill--done"><span class="sp-dot"></span>{{ $award->qual->name }}</span>
                @endif
                <div class="sp-card-actions">
                    <button data-student="{{ $student->id }}" data-id="{{ $award->id }}" data-tw-toggle="modal" data-tw-target="#addStudentAwardInfoModal" type="button" class="editStudentAwardBtn sp-btn sp-btn--dark">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Update Award
                    </button>
                </div>
            </div>
            <div class="sp-award-grid">
                <div class="sp-field">
                    <div class="sp-field-label">Award Type</div>
                    <div class="sp-field-value">{{ (isset($award->qual_award_type) && !empty($award->qual_award_type) ? $award->qual_award_type : 'N/A') }}</div>
                </div>
                <div class="sp-field">
                    <div class="sp-field-label">Date of Award</div>
                    <div class="sp-field-value">{{ (isset($award->date_of_award) && !empty($award->date_of_award) ? date('jS M, Y', strtotime($award->date_of_award)) : '—') }}</div>
                </div>
                <div class="sp-field">
                    <div class="sp-field-label">Overall Result</div>
                    <div class="sp-field-value sp-field-value--accent">{{ (isset($award->qual->name) && !empty($award->qual->name) ? $award->qual->name : '—') }}</div>
                </div>
                <div class="sp-field">
                    <div class="sp-field-label">Requested By</div>
                    <div class="sp-field-value">{{ (isset($award->requested->employee->full_name) && !empty($award->requested->employee->full_name) ? $award->requested->employee->full_name : '—') }}</div>
                </div>
            </div>
            <div class="sp-milestones-wrap">
                <div class="sp-milestones">
                    <div class="sp-ms-head">
                        <div>Certificate Milestone</div>
                        <div>Status</div>
                        <div>Date</div>
                    </div>
                    <div class="sp-ms-row">
                        <div class="sp-ms-name">Requested from awarding body</div>
                        <div>
                            @php $reqYes = isset($award->certificate_requested) && $award->certificate_requested == 'Yes'; @endphp
                            <span class="sp-yn {{ $reqYes ? 'is-yes' : 'is-no' }}"><span class="sp-dot"></span>{{ $reqYes ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="sp-ms-date">{{ (isset($award->date_of_certificate_requested) && !empty($award->date_of_certificate_requested) ? date('j M Y', strtotime($award->date_of_certificate_requested)) : '—') }}</div>
                    </div>
                    <div class="sp-ms-row">
                        <div class="sp-ms-name">Certificate received</div>
                        <div>
                            @php $rcvYes = isset($award->certificate_received) && $award->certificate_received == 'Yes'; @endphp
                            <span class="sp-yn {{ $rcvYes ? 'is-yes' : 'is-no' }}"><span class="sp-dot"></span>{{ $rcvYes ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="sp-ms-date">{{ (isset($award->date_of_certificate_received) && !empty($award->date_of_certificate_received) ? date('j M Y', strtotime($award->date_of_certificate_received)) : '—') }}</div>
                    </div>
                    <div class="sp-ms-row">
                        <div class="sp-ms-name">Released to student</div>
                        <div>
                            @php $relYes = isset($award->certificate_released) && $award->certificate_released == 'Yes'; @endphp
                            <span class="sp-yn {{ $relYes ? 'is-yes' : 'is-no' }}"><span class="sp-dot"></span>{{ $relYes ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="sp-ms-date">{{ (isset($award->date_of_certificate_released) && !empty($award->date_of_certificate_released) ? date('j M Y', strtotime($award->date_of_certificate_released)) : '—') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- END: Award Section -->

<!-- BEGIN: Student Profile Lock Modal -->
   @if($dataSet)
        @foreach($dataSet as $key => $resultSet)
            <div id="callLockModal{{ $resultSet[0]->id  }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="font-medium text-base mr-auto">Attempt List</h2>
                                <a data-tw-dismiss="modal" href="javascript:;">
                                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                                </a>
                            </div>
                            <div class="modal-body  overflow-x-auto">
                                <div class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap mb-3">
                                    <div class="flex justify-between">
                                        <div class="flex">
                                            <div class="mr-2">
                                                <label class="form-label">Module</label>
                                                <div class="text-base font-medium">{{  $resultSet[0]->plan->creations->module_name }}</div>
                                                <div class="font-medium text-slate-600">Level: {{  $resultSet[0]->plan->creations->level->name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead data-tw-merge class="">
                                        <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                Term
                                            </th>
                                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                Code
                                            </th>
                                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                Created At
                                            </th>
                                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                Published At
                                            </th>
                                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                Grade
                                            </th>
                                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                Status
                                            </th>
                                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                Last Updated By
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resultSet as $result)
                                        @php
                                            if(isset($result->term_declaration_id) && !empty($result->term_declaration_id))
                                                    $termData = $result->term->name;
                                                else
                                                    $termData = $result->plan->attenTerm->name;
                                            @endphp
                                            <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">
                                                    {{ $termData }} 
                                                </td>
                                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">
                                                    {{ ($result->module_code)? $result->module_code :$result->plan->creations->code }}
                                                </td>
                                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    {{ date('d F,Y h:i a',strtotime($result->created_at))  }}
                                                </td>
                                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    {{ date('d F,Y h:i a',strtotime($result->published_at))  }}
                                                </td>
                                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    {{ $result->grade->code }} 
                                                </td>
                                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    {{ $result->grade->name }}
                                                </td>
                                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    {{ isset($result->updatedBy->employee->full_name)  ? $result->updatedBy->employee->full_name : (isset($result->createdBy->employee->full_name) ? $result->createdBy->employee->full_name: $result->createdBy->name)  }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                </div>
            </div>
        @endforeach
    @endif
<!-- END: Student Profile Lock Modal -->

@if($dataSet)
@foreach($dataSet as $key => $resultSet)
<!-- BEGIN: Edit Modal -->
<div id="editAttemptModal{{ $resultSet[0]->id  }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" id="editAttemptForm{{ $resultSet[0]->id  }}">
            <div class="modal-content ">
                <div class="modal-header">
                    <div class="sp-rmodal-titlewrap mr-auto">
                        <h2 class="font-medium text-base">Result Update</h2>
                        <div class="sp-rmodal-subtitle">Edit grade history for this module</div>
                    </div>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>

                </div>
                <div class="modal-body ">
                    <div role="alert" class="alert hidden relative border rounded-md px-5 py-4 bg-warning border-warning text-slate-900 dark:border-warning mb-2 flex items-center"><i data-tw-merge data-lucide="alert-circle" class="stroke-1.5 w-5 h-5 mr-2 h-6 w-6 mr-2 h-6 w-6"></i>
                        <span class="error-text">Awesome alert with icon</span>
                        <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close" type="button" aria-label="Close" class="text-slate-800 py-2 px-3 absolute right-0 my-auto mr-2 btn-close"><i data-tw-merge data-lucide="x" class="stroke-1.5 w-5 h-5 h-4 w-4 h-4 w-4"></i></button>
                    </div>
                    <div class="sp-rmodal-sub">
                        <span class="sp-rmodal-ic"><i data-lucide="graduation-cap" class="w-4 h-4"></i></span>
                        <div class="sp-rmodal-copy">
                            <div class="sp-rmodal-name">{{ $key }}</div>
                            <div class="sp-rmodal-meta"><span class="sp-mono">{{ $resultSet[0]->plan->creations->code }}</span> &middot; Level {{ $resultSet[0]->plan->creations->level->name }} &middot; {{ $resultSet[0]->plan->course->body->name }}</div>
                        </div>
                        @if(isset(auth()->user()->priv()['result_add']) && auth()->user()->priv()['result_add'] == 1)
                        <button type="button" data-id="{{ $resultSet[0]->id }}" class="sp-btn sp-btn--dark addNewRowBtn"><i data-lucide="plus-circle" class="w-4 h-4"></i> Add New Row</button>
                        @endif
                    </div>
                    <table id="result-bulk{{ $resultSet[0]->id }}" class="sp-rmodal-table min-w-full divide-y divide-gray-200">
                        <thead data-tw-merge class="">
                            <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Term
                                </th>
                                <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Code
                                </th>
                                <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Created At
                                </th>
                                <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Published At
                                </th>
                                <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Grade
                                </th>
                                <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Last Updated By
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bulk-update">
                            @php $index= 0; @endphp
                            @foreach($resultSet as $result)
                            @php
                                
                                if(isset($result->term_declaration_id) && !empty($result->term_declaration_id))
                                    $termData = $result->term_declaration_id;

                                else
                                    $termData = $result->plan->attenTerm->id;
                            @endphp
                                <tr data-tw-merge class="items-center justify-between [&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                    <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t relative w-36">
                                        <input type="hidden" name="id[]" value="{{ $result->id }}" />
                                        <input type="hidden" name="plan_id[]" value="{{ $result->plan_id }}" />
                                        <input type="hidden" name="student_id[]" value="{{ $result->student_id }}" />
                                        <input type="hidden" name="created_by[]" value="{{ $result->created_by }}" />
                                        <input type="hidden" name="updated_by[]" value="{{ auth()->user()->id }}" />
                                        
                                         <select id="term-data{{ $result->id }}" data-index="{{ $index }}" class="w-full lccTom lcc-tom-select" name="term_declaration_id[]">
                                            <option value="">Please Select</option>
                                            @if(isset($termSet[$result->plan->creations->module_name]))
                                                @foreach($termSet[$result->plan->creations->module_name] as $trm)
                                                    <option  {{ $termData==$trm->id ? 'selected' : '' }} value="{{ $trm->id }}">{{ $trm->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-term_declaration_id text-danger mt-2" data-index="{{ $index }}"></div>
                                        
                                    </td>
                                    <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t relative">
                                        <input type="text" class="form-control mt-2 sm:mt-0" value="{{ ($result->module_code)? $result->module_code :$result->plan->creations->code }}" placeholder="{{ ($result->module_code) ? $result->module_code :$result->plan->creations->code }}"  name="module_code[]" >
                                    </td>
                                    <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t w-40">
                                        <input id="created" placeholder="{{ date('d-m-Y H:i',strtotime($result->created_at))  }}" autocomplete="off"  class="datepicker_custom form-control w-full placeholder:text-slate-700" value=""  data-single-mode="true">
                                        <input name="created_at[]" data-index="{{ $index }}" type="hidden" value="{{ date('Y-m-d H:i:s',strtotime($result->created_at))  }}">
                                        <div class="acc__input-error error-created_at text-danger mt-2" data-index="{{ $index }}"></div>
                                    </td>
                                    <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t  w-40">
                                        <input id="published"   placeholder="{{ $result->published_at  }}" autocomplete="off" class="datepicker_custom form-control w-full  placeholder:text-slate-700" value=""  data-single-mode="true">
                                        <input name="published_at[]" data-index="{{ $index }}" type="hidden" value="{{ $result->published_at  }}">
                                        <div class="acc__input-error error-published_at text-danger mt-2" data-index="{{ $index }}"></div>
                                    </td>
                                    <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t">
                                        @php $ggm = $gradeMeta[trim($result->grade->code ?? '')] ?? null; @endphp
                                        <select id="grade_id" name="grade_id[]" data-index="{{ $index }}" class="form-control w-full sp-grade-select" style="{{ $ggm ? 'color:'.$ggm['color'].';font-weight:600;' : '' }}">
                                            <option value="">Please Select</option>
                                            @if(!empty($grades))
                                                @foreach($grades as $grade)
                                                    <option {{ $result->grade->id == $grade->id  ? 'selected' : "" }}  value="{{ $grade->id }}">{{ $grade->code }} - {{ $grade->name }} </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-grade_id text-danger mt-2" data-index="{{ $index }}"></div>
                                    </td>
                                    <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t">
                                        <div class="flex justify-between items-stretch ">
                                            <div class="item updated-name"> 
                                                {{ isset($result->updatedBy->employee->full_name)  ? $result->updatedBy->employee->full_name : (isset($result->createdBy->employee->full_name) ? $result->createdBy->employee->full_name: $result->createdBy->name) }}
                                            </div>
                                            @if(isset(auth()->user()->priv()['result_delete']) && auth()->user()->priv()['result_delete'] == 1)
                                            <div class="py-1 ml-2 item-center anchor-box">
                                                <a href="javascript:;" data-theme="light"  data-id="{{ $result->id }}" data-url="{{ route('result.destroy', $result->id); }}"  data-action="DELETE" class="delete_btn sp-del-btn cursor-pointer" title="delete result">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @php $index++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                        
                </div>
                <div class="modal-footer">
                    <div class="sp-rmodal-foot-note">{{ count($resultSet) }} grade {{ count($resultSet) == 1 ? 'entry' : 'entries' }} &middot; latest published {{ date('j M Y', strtotime($resultSet[0]->published_at)) }}</div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    @if(isset(auth()->user()->priv()['result_edit']) && auth()->user()->priv()['result_edit'] == 1)
                    <button type="submit" id="update" data-id="{{ $resultSet[0]->id }}" class="btn btn-primary w-auto update_btn">
                        Update <i class="w-4 h-4 ml-2 text-white hidden" data-loading-icon="oval" ></i>
                    </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
@endif

@if($prev_result_count > 0)
<div class="intro-y flex items-center p-5 mt-5 box">
    <h2 class="text-lg font-medium mr-auto">
        Previous Results
    </h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <h4 id="frequency-distribution-old" class="text-sm font-medium mr-auto ">[  ]</h4>
    </div>
</div>
<div class="intro-y box col-span-12 p-5 mt-5">
    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
        <form id="tabulatorFilterForm-AN" class="xl:flex sm:mr-auto" >
            {{-- <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Term</label>
                <select id="term-SN" name="term" class="mt-2 sm:mt-0 sm:w-40 2xl:w-48 tom-selects" >
                    <option selected value="">Please Select</option>
                    @if($terms->count() > 0)
                        @foreach($terms as $trm)
                            <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div> --}}
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                <input id="query-AN" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
            </div>
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                <select id="status-AN" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                    <option selected value="1">Active</option>
                    <option value="2">Archived</option>
                </select>
            </div>
            <div class="mt-2 xl:mt-0">
                <button id="tabulator-html-filter-go-AN" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                <button id="tabulator-html-filter-reset-AN" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
            </div>
        </form>
        <div class="flex mt-5 sm:mt-0">
            <button id="tabulator-print-AN" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
            </button>
            <div class="dropdown w-1/2 sm:w-auto">
                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                </button>
                <div class="dropdown-menu w-40">
                    <ul class="dropdown-content">
                        <li>
                            <a id="tabulator-export-csv-AN" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                            </a>
                        </li>
                        <li>
                            <a id="tabulator-export-xlsx-AN" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
   
    <div class="overflow-x-auto scrollbar-hidden">
        <div id="studentNotesListTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
    </div>
</div>
@endif

<!-- BEGIN: Edit Modal -->
<div id="editNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editNoteForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Previous Exam Result</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div role="alert" class="alert hidden relative border rounded-md px-5 py-4 bg-warning border-warning text-slate-900 dark:border-warning mb-2 flex items-center"><i data-tw-merge data-lucide="alert-circle" class="stroke-1.5 w-5 h-5 mr-2 h-6 w-6 mr-2 h-6 w-6"></i>
                        <span class="error-text">Awesome alert with icon</span>
                        <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close" type="button" aria-label="Close" class="text-slate-800 py-2 px-3 absolute right-0 my-auto mr-2 btn-close"><i data-tw-merge data-lucide="x" class="stroke-1.5 w-5 h-5 h-4 w-4 h-4 w-4"></i></button>
                    </div>
                    


                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="UpdateNote" class="btn btn-primary w-auto">     
                        Update                      
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
                    <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    <input type="hidden" name="id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Modal -->
<!-- BEGIN: Delete Confirmation Modal -->
<div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5">Are you sure?</div>
                    <div class="text-slate-500 mt-2">Do you really want to delete these records? <br>This process cannot be undone.</div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Cancel</button>
                    <button type="button" data-url="#" data-id="0" data-action="DELETE" class="agreeWith btn btn-danger w-24">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BEGIN: default Confirmation Modal -->
<div id="default-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="alert-circle" class="w-16 h-16 text-orange-500 mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                    <div class="text-slate-500 mt-2 confModDesc">Do you really want to delete these records? <br>This process cannot be undone.</div>
                    <input type="hidden" name="result_primary_set" value="">
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Cancel</button>
                    <button type="button" data-id="0" data-action="DEFAULT" class="agreeWith btn btn-elevated-warning w-24">Update</button>
                </div>
            </div>
        </div>
    </div>
</div>
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

<div id="previous-attempListmodal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
                <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Previous Attempt List</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>

                <div class="modal-body overflow-x-auto scrollbar-hidden">
                    <table id="studentAttemptPreviousListTable" data-student="{{ $student->id }}" class="min-w-full divide-y divide-gray-200">
                        <thead data-tw-merge class="">
                            <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">

                                
                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Term
                                </th>
                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Code
                                </th>
                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Paper ID
                                </th>
                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Exam Date
                                </th>
                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Grade
                                </th>
                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Status
                                </th>
                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                    Last Updated By
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
    </div>
</div>


<!-- BEGIN: Add/Edit Award Modal -->
<div id="addStudentAwardInfoModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addStudentAwardInfoForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Award Informations</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="date_of_award" class="form-label">Date of award <span class="text-danger">*</span></label>
                        <input id="date_of_award" name="date_of_award" type="text" class="form-control w-full datepicker" value="" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true" />
                        <div class="acc__input-error error-date_of_award text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="qual_award_type" class="form-label">Qualification Award Type</label>
                        <select id="qual_award_type" name="qual_award_type" class="form-control w-full">
                            <option value="">Please Select</option>
                            @if(isset($student->crel->course->dfQual) && $student->crel->course->dfQual->count() > 0)
                                @foreach($student->crel->course->dfQual as $dffileds)
                                    @if(isset($dffileds->field->name) && $dffileds->field->name == 'QUALAWARDID' && !empty($dffileds->field_value))
                                        <option value="{{ trim($dffileds->field_value) }}">{{ trim($dffileds->field_value) }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mt-3">
                        <label for="qual_award_result_id" class="form-label">Overall Result</label>
                        <select id="qual_award_result_id" class="tom-selects w-full" name="qual_award_result_id">
                            <option value="">Please Select</option>
                            @if($qualAwards->count() > 0)
                                @foreach($qualAwards as $qual)
                                    <option value="{{ $qual->id }}">{{ $qual->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-date_of_award text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="certificate_requested" class="form-label">Certificate Requested from Awarding body?</label>
                        <div class="form-check form-switch">
                            <input id="certificate_requested" class="form-check-input" name="certificate_requested" value="Yes" type="checkbox">
                            <label class="form-check-label checkLabel" for="certificate_requested">No</label>
                        </div>
                        <div class="acc__input-error error-certificate_requested text-danger mt-2"></div>
                    </div>
                    <div class="mt-3 cerReqWrap" style="display: none;">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-6">
                                <div>
                                    <label for="date_of_certificate_requested" class="form-label">Date of Request</label>
                                    <input id="date_of_certificate_requested" name="date_of_certificate_requested" type="text" class="form-control w-full datepicker" value="" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true" />
                                    <div class="acc__input-error error-date_of_certificate_requested text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div>
                                    <label for="certificate_requested_by" class="form-label">Requested by</label>
                                    <select id="certificate_requested_by" class="tom-selects w-full" name="certificate_requested_by">
                                        <option value="">Please Select</option>
                                        @if($users->count() > 0)
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-certificate_requested_by text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="certificate_received" class="form-label">Certificate Received</label>
                        <div class="form-check form-switch">
                            <input id="certificate_received" class="form-check-input" name="certificate_received" value="Yes" type="checkbox">
                            <label class="form-check-label checkLabel" for="certificate_received">No</label>
                        </div>
                        <div class="acc__input-error error-certificate_received text-danger mt-2"></div>
                    </div>
                    <div class="mt-3 cerRcvdWrap" style="display: none;">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12">
                                <div>
                                    <label for="date_of_certificate_received" class="form-label">Date of Received</label>
                                    <input id="date_of_certificate_received" name="date_of_certificate_received" type="text" class="form-control w-full datepicker" value="" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true" />
                                    <div class="acc__input-error error-date_of_certificate_received text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="certificate_released" class="form-label">Certificate Release to Student?</label>
                        <div class="form-check form-switch">
                            <input id="certificate_released" class="form-check-input" name="certificate_released" value="Yes" type="checkbox">
                            <label class="form-check-label checkLabel" for="certificate_released">No</label>
                        </div>
                        <div class="acc__input-error error-certificate_released text-danger mt-2"></div>
                    </div>
                    <div class="mt-3 cerRelsdWrap" style="display: none;">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-6">
                                <div>
                                    <label for="date_of_certificate_released" class="form-label">Date of Release</label>
                                    <input id="date_of_certificate_released" name="date_of_certificate_released" type="text" class="form-control w-full datepicker" value="" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true" />
                                    <div class="acc__input-error error-date_of_certificate_released text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div>
                                    <label for="certificate_released_by" class="form-label">Release by</label>
                                    <select id="certificate_released_by" class="tom-selects w-full" name="certificate_released_by">
                                        <option value="">Please Select</option>
                                        @if($users->count() > 0)
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-certificate_released_by text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="addAwardBtn" class="btn btn-primary w-auto">     
                        Save                      
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
                    <input type="hidden" value="{{ $student->id }}" name="student_id"/>
                    <input type="hidden" value="{{ $student->crel->id }}" name="student_course_relation_id"/>
                    <input type="hidden" value="{{ (isset($award->id) && $award->id > 0 ? $award->id : 0) }}" name="id"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add/Edit Award Modal -->
<div id="successAWModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 successAWModalTitle"></div>
                    <div class="text-slate-500 mt-2 successAWModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-action="NONE" class="successCloser btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

        @vite('resources/js/student-global.js')
        @vite('resources/js/student-results.js')
        @vite('resources/js/student-award.js')
@endsection
