@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <!-- BEGIN: Daily Sales -->
        @php $termstart=0 @endphp
        @foreach($termSet as $term)
            
        @php $termstart++; $planId=1; @endphp
        <div class="intro-y box col-span-12 p-5 mt-5  ">
            <div class="flex items-center px-5 py-5 sm:py-3  border-slate-200/60 bg-cyan-600 text-slate-100 rounded-tl rounded-tr">
                @php
                        $attendanceCriteriaFound = \App\Models\AttendanceCriteria::where('range_from', '<=', round($termAttendanceCount[$term->id]['avg']))
                    ->where('range_to', '>=', round($termAttendanceCount[$term->id]['avg']))
                    ->first();
                
                    $attendance_criteria = isset($attendanceCriteriaFound->id) ? round($attendanceCriteriaFound->point) : 0;

                    $achivedPerformance = $attendance_criteria +  $perTermModuleCriteria[$term->id]; 
                    $expectedPerformance = $TopAttendanceCriteria +  $perTermTopSet[$term->id];

                    $avgPerformance = number_format(($achivedPerformance/$expectedPerformance)*100,2);

                    $performanceOutput = \App\Models\TermPerformanceCriteria::where('range_from', '<=', $avgPerformance)
                    ->where('range_to', '>=', $avgPerformance)
                    ->first();
                

                @endphp
                <h2 class="font-medium text-base mr-auto ">{{ $term->name }} 
                    
                    <div class="font-medium dark:text-slate-500 {{ ($termAttendanceCount[$term->id]['avg']>79)? "bg-green-700" : "bg-warning" }} {{ ($termAttendanceCount[$term->id]['avg']>79)? "text-white" : "text-white" }} rounded px-2 mt-1.5  w-{{ $termAttendanceCount[$term->id]['avg']/5 }} inline-flex item-center">{{ $termAttendanceCount[$term->id]['avg'] }}%</div>
                    
                    <div class="font-medium dark:text-slate-500 bg-cyan-900 text-white rounded px-2 mt-1.5  w-{{ $avgPerformance/2 }} inline-flex item-center">{{ $performanceOutput->label}} {{ $avgPerformance }}%</div>
                    
                    <div class="text-slate-100 sm:mr-5 ml-auto text-sm mt-2">Attendnace Performacne {{ $attendance_criteria }}/ {{ $TopAttendanceCriteria }} </div>
                </h2>
                
                <div class="font-medium text-base sm:mr-5 ml-auto">
                    Term Performance: {{ $attendance_criteria +  $perTermModuleCriteria[$term->id] }}/{{ $TopAttendanceCriteria +  $perTermTopSet[$term->id] }}
                    
                </div>
            </div>
            <div class="w-full py-3 border-cyan-600 border-2 rounded-b-lg bg-transparent h-full">
                <div class="w-full px-5 py-3  text-xl">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-4 font-medium ml-2 py-3">Expected Performance</div>
                        <div class="col-span-7 ml-auto py-3">
                            <div class="w-96 bg-gray-200 rounded-full h-2.5">
                                <div class="bg-info h-2.5 rounded-full " style="width: 100%"></div> <!-- Adjust width as needed -->
                            </div>   
                        </div>
                        <div class="col-span-1 ml-auto py-3 font-medium ">{{ $TopAttendanceCriteria +  $perTermTopSet[$term->id] }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-4 font-medium ml-2 py-3">Achieved Performance</div>
                        <div class="col-span-7 ml-auto py-3">
                            <div class="w-96 bg-gray-200 rounded-full h-2.5">
                                <div class="bg-{{ $performanceOutput->color }} h-2.5 rounded-full " style="width: {{ $avgPerformance }}%"></div> <!-- Adjust width as needed -->
                            </div>   
                        </div>
                        <div class="col-span-1 ml-auto py-3 font-medium ">{{ $attendance_criteria +  $perTermModuleCriteria[$term->id] }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-4 mx-5 my-3">
                    <div class="col-span-6">
                        <div class="flex items-center px-5 py-5 sm:py-3 border-slate-200/60 bg-cyan-600 text-slate-100 rounded-tl rounded-tr w-full">
                            <h2 class="font-medium text-base mr-auto">Attendance Performance</h2>
                        </div>
                        <div class="w-full px-5 py-3 border-cyan-600 border-2 rounded-b-md bg-transparent text-sm">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-4 font-medium ml-2 py-3">Attendance Expected</div>
                                <div class="col-span-7 ml-auto py-3">
                                    <div class="w-96 bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-info h-2.5 rounded-full " style="width: 100%"></div> <!-- Adjust width as needed -->
                                    </div>  
                                </div>
                                <div class="col-span-1 ml-auto py-3 font-medium ">{{ $TopAttendanceCriteria }} </div>
                            </div>
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-4 font-medium ml-2 py-3">Attendance Achieved</div>
                                <div class="col-span-7 ml-auto py-3">
                                    <div class="w-96 bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-success h-2.5 rounded-full " style="width: {{ number_format(($attendance_criteria/$TopAttendanceCriteria)*100,2) }}%"></div> <!-- Adjust width as needed -->
                                    </div>  
                                </div>
                                    <div class="col-span-1 ml-auto py-3 font-medium ">{{ $attendance_criteria }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-6">
                        <div class="flex items-center px-5 py-5 sm:py-3 border-slate-200/60 bg-cyan-600 text-slate-100 rounded-tl rounded-tr w-full">
                            <h2 class="font-medium text-base mr-auto">Academic Performance</h2>
                        </div>
                        <div class="w-full px-5 py-3 border-cyan-600 border-2 rounded-b-md bg-transparent">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-4 font-medium ml-2 py-3">Result Expected</div>
                                <div class="col-span-7 ml-auto py-3">
                                    <div class="w-96 bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-info h-2.5 rounded-full " style="width: 100%"></div> <!-- Adjust width as needed -->
                                    </div>  
                                </div>
                                <div class="col-span-1 font-medium ml-2 py-3">{{ $perTermTopSet[$term->id] }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-4 font-medium ml-2 py-3">Result Achieved</div>
                                <div class="col-span-7 ml-auto py-3">
                                    <div class="w-96 bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-success h-2.5 rounded-full " style="width: {{ number_format(($perTermModuleCriteria[$term->id]/$perTermTopSet[$term->id])*100,2) }}%"></div> <!-- Adjust width as needed -->
                                    </div> 
                                </div>
                                <div class="col-span-1 font-medium ml-2 py-3">{{ $perTermModuleCriteria[$term->id] }}</div> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-4 mx-5 my-3">
                    <div class="col-span-12">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th colspan="3" class="text-right font-medium px-5 py-3 border-0 dark:border-darkmode-300 whitespace-nowrap">Academic Performance</th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right font-medium px-5 py-3 border-0 dark:border-darkmode-300 whitespace-nowrap">{{ $perTermModuleCriteria[$term->id] }} / {{ $perTermTopSet[$term->id] }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results[$term->id] as $moduleName => $result)
                                    <tr>
                                        <td class="px-5 py-3 border-0 dark:border-darkmode-300 whitespace-nowrap"><i data-lucide="check-circle" class="text-green-600 w-5 h-5 mr-2 ml-2 inline-flex"></i> {{ $result['module'] }}</td>
                                        <td class="px-5 py-3 border-0 dark:border-darkmode-300 whitespace-nowrap text-right">{{ $result['grade'] }}</td>
                                        <td class="px-5 py-3 border-0 dark:border-darkmode-300 whitespace-nowrap text-right">{{ $result['academic_criteria'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    
    

@endsection

@section('script')

@vite('resources/js/student-global.js')
@endsection
