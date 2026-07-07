@php
    $cocTone = 'is-slate';
    switch($coc->coc_type) {
        case 'Resumption': $cocTone = 'is-teal'; break;
        case 'Repetition': $cocTone = 'is-gold'; break;
        case 'Suspension':
        case 'Withdrawal': $cocTone = 'is-red'; break;
        default: $cocTone = 'is-slate';
    }
    $showMove = $showMove ?? false;
@endphp
<tr>
    <td class="slc-td-mono">{{ $coc->id.(isset($coc->slc_attendance_id) && $coc->slc_attendance_id > 0 ? ' - '.$coc->slc_attendance_id : '') }}</td>
    <td class="slc-td-strong">{{ (!empty($coc->confirmation_date) ? date('jS F, Y', strtotime($coc->confirmation_date)) : '') }}</td>
    <td>@if(!empty($coc->coc_type))<span class="slc-pill {{ $cocTone }}"><span class="slc-pill-dot"></span>{{ $coc->coc_type }}</span>@endif</td>
    <td>@if(!empty($coc->reason)){{ $coc->reason }}@else<span class="slc-dash">No reason recorded</span>@endif</td>
    <td>@if(strtolower($coc->actioned) === 'yes')<span class="slc-yes">Yes</span>@elseif(!empty($coc->actioned)){{ ucfirst($coc->actioned) }}@else<span class="slc-dash">—</span>@endif</td>
    <td>{{ (isset($coc->user->employee->full_name) ? $coc->user->employee->full_name : '') }}</td>
    <td>
        @if($coc->documents->count() > 0)
            <div class="dropdown">
                <button class="dropdown-toggle slc-doclink" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-lucide="file-text"></i> Documents <i data-lucide="chevron-down" class="w-3 h-3"></i>
                </button>
                <div class="dropdown-menu w-80">
                    <ul class="dropdown-content">
                        @foreach($coc->documents as $doc)
                            @php
                                $cocDocUrl = null;
                                if(isset($doc->current_file_name) && !empty($doc->current_file_name)):
                                    try {
                                        if(Storage::disk('s3')->exists('public/students/'.$student->id.'/'.$doc->current_file_name)):
                                            $cocDocUrl = Storage::disk('s3')->temporaryUrl('public/students/'.$doc->student_id.'/'.$doc->current_file_name, now()->addMinutes(60));
                                        endif;
                                    } catch (\Throwable $e) {
                                        $cocDocUrl = null;
                                    }
                                endif;
                            @endphp
                            <li>
                                <span class="dropdown-item">
                                    <i data-lucide="check-check" class="w-4 h-4 mr-2"></i> {{ $doc->display_file_name }}
                                    <span class="ml-auto inline-flex justify-end items-center">
                                        @if($cocDocUrl)
                                            <a href="{{ $cocDocUrl }}" target="_blank" class="text-success mr-2"><i data-lucide="download-cloud" class="w-4 h-4"></i></a>
                                        @endif
                                        @if($can_delete) <a data-cocid="{{ $coc->id }}" data-docid="{{ $doc->id }}" href="javascript:void(0);" target="_blank" class="deleteCOCDoc text-danger"><i data-lucide="trash-2" class="w-4 h-4"></i></a> @endif
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @else
            <span class="slc-dash">—</span>
        @endif
    </td>
    <td class="slc-td-actions">
        <div class="slc-iconbtn-group">
            @if($can_edit) <button data-id="{{ $coc->id }}" data-tw-toggle="modal" data-tw-target="#editCOCModal" type="button" class="edit_coc_btn slc-iconbtn" title="Edit"><i data-lucide="pencil"></i></button> @endif
            @if($can_delete) <button data-id="{{ $coc->id }}" type="button" class="delete_coc_btn slc-iconbtn is-danger" title="Delete"><i data-lucide="trash-2"></i></button> @endif
            @if($showMove && !empty($studentAttendanceIds) && $can_add)
                <div class="dropdown inline-block" data-tw-placement="bottom-end">
                    <button class="dropdown-toggle slc-iconbtn" aria-expanded="false" data-tw-toggle="dropdown" title="Move to attendance"><i data-lucide="arrow-right-left"></i></button>
                    <div class="dropdown-menu w-64">
                        <ul class="dropdown-content">
                            @foreach($studentAttendanceIds as $atnId)
                                <li><a href="javascript:void(0);" data-atn="{{ $atnId }}" data-coc="{{ $coc->id }}" class="dropdown-item assignCocToAttendance text-success"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Move to Attendance &nbsp;<strong>#{{ $atnId }}</strong></a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </td>
</tr>
