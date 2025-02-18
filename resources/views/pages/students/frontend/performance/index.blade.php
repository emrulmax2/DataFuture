@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.frontend.dashboard.show-info')
    <!-- END: Profile Info -->

    <!-- BEGIN: Term Sales -->
    @php $termstart=0 @endphp
    @if(isset($termSet))
        @foreach($termSet as $term)
                
            @php $termstart++; $planId=1; @endphp
            <div class="intro-y box col-span-12 p-5 mt-5  ">
                <div class="flex flex-col sm:flex-row sm:items-center px-5 py-5 sm:py-3  border-slate-200/60 bg-cyan-600 text-slate-100 rounded-tl rounded-tr">
                    @php

                        $avarageAttendance = isset($termAttendanceCount[$term->id]['avg']) ? round($termAttendanceCount[$term->id]['avg']) : 0;
                        
                        $attendanceCriteriaFound = \App\Models\AttendanceCriteria::where('range_from', '<=', $avarageAttendance)
                        ->where('range_to', '>=', $avarageAttendance)
                        ->first();
                    
                        $attendance_criteria = isset($attendanceCriteriaFound->id) ? round($attendanceCriteriaFound->point) : 0;
                        
                        if(isset($perTermModuleCriteria[$term->id])):
                            $achivedResult = $perTermModuleCriteria[$term->id];
                            $expectedResult = $perTermTopSet[$term->id];
                            $achivedPerformance = $attendance_criteria +  $perTermModuleCriteria[$term->id]; 
                            $expectedPerformance = $TopAttendanceCriteria +  $perTermTopSet[$term->id];
                        else:
                            $achivedPerformance = 0;
                            $expectedPerformance = 0;
                            $achivedResult = 0;
                            $expectedResult = 0;
                        endif;
                        if($expectedPerformance!=0)
                          $avgPerformance = number_format(($achivedPerformance/$expectedPerformance)*100,2);
                        else 
                           $avgPerformance = 0;
                        $performanceOutput = \App\Models\TermPerformanceCriteria::where('range_from', '<=', $avgPerformance)
                        ->where('range_to', '>=', $avgPerformance)
                        ->first();
                    

                    @endphp
                    <h2 class="font-medium text-base mr-auto ">{{ $term->name }} 
                        
                        <div class="font-medium dark:text-slate-500 {{ ($avarageAttendance>79)? "bg-green-700" : "bg-warning" }} {{ ($avarageAttendance>79)? "text-white" : "text-white" }} rounded px-2 mt-1.5  w-{{ $avarageAttendance/5 }} inline-flex item-center">{{ $avarageAttendance }}%</div>
                        
                        <div class="font-medium dark:text-slate-500 bg-cyan-900 text-white rounded px-2 mt-1.5  w-{{ ($avgPerformance>45) ? $avgPerformance/2 : 60 }} inline-flex item-center">{{ $performanceOutput->label}} {{ $avgPerformance }}%</div>
                        
                        <div class="text-slate-100 sm:mr-5 ml-auto text-sm mt-2">Attendnace Performacne {{ $attendance_criteria }}/ {{ $TopAttendanceCriteria }} </div>
                    </h2>
                    
                    <div class="font-medium text-base sm:mr-5 sm:ml-auto">
                        Term Performance: {{ $attendance_criteria +  $achivedResult }}/{{ $TopAttendanceCriteria +  $expectedResult }}
                        
                    </div>
                </div>
                <div class="w-full py-3 border-cyan-600 border-2 rounded-b-lg bg-transparent h-full">
                    <div class="w-full px-5 py-3  text-xl">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-4 font-medium sm:ml-2 sm:py-3 ">Expected Performance</div>
                            <div class="col-span-11 sm:col-span-7 sm:py-3">
                                <div class=" bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-info h-2.5 rounded-full " style="width: 100%"></div> <!-- Adjust width as needed -->
                                </div>   
                            </div>
                            <div class="col-span-1 ml-auto sm:py-3 -mt-2 font-medium mb-4 sm:mb-0 ">{{ $TopAttendanceCriteria +  $expectedResult }}</div>
                        </div>
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-4 font-medium sm:ml-2 sm:py-3 ">Achieved Performance</div>
                            <div class="col-span-11 sm:col-span-7 sm:py-3">
                                <div class=" bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-{{ $performanceOutput->color }} h-2.5 rounded-full " style="width: {{ $avgPerformance }}%"></div> <!-- Adjust width as needed -->
                                </div>   
                            </div>
                            <div class="col-span-1 ml-auto sm:py-3 -mt-2 font-medium">{{ $attendance_criteria +  $achivedResult }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 mx-5 my-3">
                        <div class="col-span-12 sm:col-span-6">
                            <div class="flex items-center px-5 py-5 sm:py-3 border-slate-200/60 bg-cyan-600 text-slate-100 rounded-tl rounded-tr w-full">
                                <h2 class="font-medium text-base mr-auto">Attendance Performance</h2>
                            </div>
                            <div class="w-full px-5 py-3 border-cyan-600 border-2 rounded-b-md bg-transparent text-sm">
                                <div class="grid grid-cols-12 gap-2 sm:gap-4">
                                    <div class="col-span-12 sm:col-span-4 font-medium sm:ml-2 sm:py-3">Attendance Expected</div>
                                    <div class="col-span-11 sm:col-span-7 sm:py-3">
                                        <div class=" bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-info h-2.5 rounded-full " style="width: 100%"></div> <!-- Adjust width as needed -->
                                        </div>  
                                    </div>
                                    <div class="col-span-1 ml-auto sm:py-3 -mt-1 font-medium">{{ $TopAttendanceCriteria }} </div>
                                </div>
                                <div class="grid grid-cols-12 gap-2 sm:gap-4">
                                    <div class="col-span-12 sm:col-span-4 font-medium sm:ml-2 sm:py-3">Attendance Achieved</div>
                                    <div class="col-span-11 sm:col-span-7 sm:py-3">
                                        <div class=" bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-success h-2.5 rounded-full " style="width: {{  (isset($TopAttendanceCriteria) && $TopAttendanceCriteria>0) ? number_format(($attendance_criteria/$TopAttendanceCriteria)*100,2) : 0 }}%"></div> <!-- Adjust width as needed -->
                                        </div>  
                                    </div>
                                        <div class="col-span-1 ml-auto sm:py-3 -mt-1 font-medium">{{ $attendance_criteria }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="flex items-center px-5 py-5 sm:py-3 border-slate-200/60 bg-cyan-600 text-slate-100 rounded-tl rounded-tr w-full">
                                <h2 class="font-medium text-base mr-auto">Academic Performance</h2>
                            </div>
                            <div class="w-full px-5 py-3 border-cyan-600 border-2 rounded-b-md bg-transparent">
                                <div class="grid grid-cols-12 gap-2 sm:gap-4">
                                    <div class="col-span-12 sm:col-span-4 font-medium sm:ml-2 sm:py-3">Result Expected</div>
                                    <div class="col-span-11 sm:col-span-7 sm:py-3">
                                        <div class="bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-info h-2.5 rounded-full " style="width: 100%"></div> <!-- Adjust width as needed -->
                                        </div>  
                                    </div>
                                    <div class="col-span-1 ml-auto sm:py-3 -mt-1 font-medium">{{ $expectedResult }}</div>
                                </div>
                                <div class="grid grid-cols-12 gap-2 sm:gap-4">
                                    <div class="col-span-12 sm:col-span-4 font-medium sm:ml-2 sm:py-3">Result Achieved</div>
                                    <div class="col-span-11 sm:col-span-7 sm:py-3">
                                        <div class=" bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-success h-2.5 rounded-full " style="width: {{ isset($expectedResult) && $expectedResult>0 ? number_format(($achivedResult/$expectedResult)*100,2) : 0 }}%"></div> <!-- Adjust width as needed -->
                                        </div> 
                                    </div>
                                    <div class="col-span-1 ml-auto sm:py-3 -mt-1 font-medium">{{ $achivedResult }}</div> 
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(isset($results[$term->id]))
                    <div class="grid grid-cols-12 gap-4 mx-5 my-3">
                        <div class="col-span-12">
                            <table class="w-full">
                                <thead>
                                    <tr class="flex flex-row justify-between items-center sm:table-row">
                                        <th colspan="3" class="flex flex-row justify-center sm:justify-between gap-4 items-center sm:table-cell text-right font-medium sm:px-5 py-3 border-0 dark:border-darkmode-300 sm:whitespace-nowrap w-full">
                                            <span>Academic Performance</span>
                                            <span class="sm:ml-4">{{ $achivedResult }} / {{ $expectedResult }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results[$term->id] as $moduleName => $result)
                                        <tr>
                                            <td class="sm:px-5 py-3 border-0 dark:border-darkmode-300 sm:whitespace-nowrap"><i data-lucide="check-circle" class="text-green-600 w-5 h-5 mr-2 sm:ml-2 inline-flex"></i> {{ $result['module'] }}</td>
                                            <td class="sm:px-5 py-3 border-0 dark:border-darkmode-300 sm:whitespace-nowrap text-right">{{ $result['grade'] }}</td>
                                            <td class="sm:px-5 py-3 border-0 dark:border-darkmode-300 sm:whitespace-nowrap text-right">{{ $result['academic_criteria'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
  <!-- END: Term Info -->
    

@endsection

@section('script')

@vite('resources/js/student-global.js')
@endsection
