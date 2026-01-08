<div class="term-block w-full  {{ (isset($attendanceIndicator[$termId]) && $attendanceIndicator[$termId]===0 ? "border-red-600" : "border-teal-600" ) }} border-2 rounded-b-lg bg-transparent h-full rounded-tl rounded-tr">
            <div class="term-summary border-slate-200/60 {{ (isset($attendanceIndicator[$termId]) && $attendanceIndicator[$termId]===0 ? "bg-red-600" : "bg-teal-600 " ) }} text-slate-100">
                
                <h2 class="font-medium text-base mr-auto ">{{ $term[$termId]["name"] }} 
                    @if(isset($attendanceIndicator[$termId]) && $attendanceIndicator[$termId]===0)
                    <div class="font-medium dark:text-slate-500 text-white rounded px-2 mt-1.5  w-{{ $avarageTotalPercentage[$termId]/5 }} inline-flex ml-2">{{ $avarageTotalPercentage[$termId] }}%</div>
                    
                    @else
                    <div class="font-medium dark:text-slate-500 {{ ($avarageTotalPercentage[$termId]>79)? "bg-teal-900" : "bg-warning" }} {{ ($avarageTotalPercentage[$termId]>79)? "text-white" : "text-white" }} rounded px-2 mt-1.5  w-{{ $avarageTotalPercentage[$termId]/5 }} inline-flex ml-2">{{ $avarageTotalPercentage[$termId] }}%</div>
                    @endif
                    <div class="text-slate-100 sm:mr-5 ml-auto text-sm mt-2">{{ strlen($totalFullSetFeedList[$termId]) > 0 ? "[".$totalFullSetFeedList[$termId]."]" : ""  }} {{ (isset($totalClassFullSet[$termId]) && $totalClassFullSet[$termId]!=0) ? "Total: ".$totalClassFullSet[$termId]. " days class" : "No class found" }} </div>
                </h2>
                <div class="term-summary-body">
                    <div class="meta-inline text-sm text-white">
                        <div>Date From {{ date("d-m-Y",strtotime($term[$termId]["start_date"])) }} To {{ date("d-m-Y",strtotime($term[$termId]["end_date"])) }}</div>
                        <div>Last Attendance: {{ isset($lastAttendanceDate[$termId]) && !empty($lastAttendanceDate[$termId] && $lastAttendanceDate[$termId]!="N/A") ?  date("jS F, Y",strtotime($lastAttendanceDate[$termId])) : '---' }}</div>
                        
                    </div>
                </div>
            </div>
            @foreach($dataStartPoint as $planId => $data)
                @if(isset($planDetails[$termId][$planId]) && !empty($planDetails[$termId][$planId]))
                    @php
                        if(isset($planDetails[$termId][$planId]->start_time) && isset($planDetails[$termId][$planId]->end_time)){
                            $start_time = date("Y-m-d ".$planDetails[$termId][$planId]->start_time);
                            $start_time = date('h:i A', strtotime($start_time));
                            $end_time = date("Y-m-d ".$planDetails[$termId][$planId]->end_time);
                            $end_time = date('h:i A', strtotime($end_time));  
                        } else {
                            $start_time = "N/A";
                            $end_time = "N/A";
                        }
                    @endphp

                    <div class="mt-3 grid grid-cols-12 gap-2 attendance-block px-3 py-2">
                        <div class="col-span-8">
                            <div id="tablepoint-{{ $termId }}" class ="flex tablepoint-toggle">
                                <div  class=" image-fit table-collapsed cursor-pointer ">
                                    <i data-lucide="minus" class="plusminus w-6 h-6 mr-2 hidden"></i>
                                    <i data-lucide="plus" class="plusminus w-6 h-6 mr-2 "></i>
                                </div>
                                <div class="col-span-9">
                                    <div class="text-sm font-semibold">{{ $moduleNameList[$planId] }} [{{ $planId }}]</div>
                                    <div class="text-sm text-gray-600">Group: {{ $planDetails[$termId][$planId]->group->name ?? 'N/A' }} | Room: {{ $planDetails[$termId][$planId]->room->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-600">Time: {{ $start_time }} - {{ $end_time }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-4">
                            <div class="text-sm text-gray-600">Tutor: @if($ClassType[$planId] != 'Tutorial') {{ !empty($planDetails[$termId][$planId]->tutor->employee) ? $planDetails[$termId][$planId]->tutor->employee->full_name : 'N/A' }} @else {{ !empty($planDetails[$termId][$planId]->personalTutor->employee) ? $planDetails[$termId][$planId]->personalTutor->employee->full_name : 'N/A' }} @endif</div>
                            <div class="text-sm text-gray-600 ">Average: <span class="badge">{{ $avarageDetails[$termId][$planId] ?? 'N/A' }}% </span></div>
                        </div>

                        <div id="tabledata{{ $planDetails[$termId][$planId]->id }}" class="tabledataset overflow-x-auto py-5 pt-0 col-span-12" style="display: none;">
                        <table class="min-w-full text-sm border-collapse table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-1 text-left font-medium text-gray-700 border">S/N</th>
                                    <th class="px-3 py-1 text-left font-medium text-gray-700 border">Date</th>
                                    <th class="px-3 py-1 text-left font-medium text-gray-700 border">Time</th>
                                    <th class="px-3 py-1 text-left font-medium text-gray-700 border">Taken By</th>
                                    <th class="px-3 py-1 text-left font-medium text-gray-700 border">Code</th>
                                    <th class="px-3 py-1 text-left font-medium text-gray-700 border">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @if(isset($data) && count($data)>0)
                                    @php $serial=0; @endphp
                                    @foreach($data as $planDateList)
                                        @if(isset($planDateList["attendance"]) && $planDateList["attendance"]!=null)
                                            @php $serial++; @endphp
                                            <tr class="even:bg-gray-50">
                                                <td class="px-3 py-1 border">{{ $serial }}</td>
                                                <td class="px-3 py-1 border">
                                                    @if(!empty($planDateList["attendance"]->note))
                                                        {{ date('d F, Y',strtotime($planDateList["attendance"]->attendance_date))  }} {{ $planDateList["attendance"]->note ? " [ ".$planDateList["attendance"]->note." ]" : "" }}
                                                    @else
                                                        {{ date('d F, Y',strtotime($planDateList["date"]))  }}
                                                    @endif
                                                </td>
                                                <td class="px-3 py-1 border">{{ $start_time }} - {{ $end_time }}</td>
                                                <td class="px-3 py-1 border">{{ !empty($planDateList["attendance_information"]->tutor->employee) ? $planDateList["attendance_information"]->tutor->employee->full_name : (!empty($planDateList["attendance"]->note) ? "N/A" : "Tutor Not Found") }}</td>
                                                <td class="px-3 py-1 border">{{ $planDateList["attendance"]->feed->code }}</td>
                                                <td class="px-3 py-1 border">{{ $planDateList["attendance"]->feed->name }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <tr><td colspan="6" class="px-3 py-1 border">No attendance records</td></tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="px-3 py-1 text-left font-medium border">Total</th>
                                    <th colspan="3" class="px-3 py-1 text-left font-medium border">{{ $totalFeedList[$termId][$planId] ?? '0' }}</th>
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>