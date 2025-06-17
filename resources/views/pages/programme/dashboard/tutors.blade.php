@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 pt-5 relative">
            <div class="intro-y block sm:flex items-center h-10">
                <div class="inline-flex items-center">
                    <!-- <span class="text-lg font-medium truncate mr-2">Tutors: </span> -->
                    <div id="term-dropdown" class="dropdown w-1/2 sm:w-auto mr-auto"  data-tw-placement="bottom-start">
                        <button id="selected-term" class="dropdown-toggle btn btn-outline-secondary bg-white w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> <i data-loading-icon="oval" class="w-4 h-4 mr-2 hidden"  data-color="white"></i> <span>{{ $termDeclaration->name }}</span> <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                        </button>
                        <div class="dropdown-menu w-80">
                            <ul class="dropdown-content max-h-96" style="overflow-x: hidden; overflow-y: auto;">
                                @if(!empty($termDeclarations) && $termDeclarations->count() > 0)
                                    @foreach($termDeclarations as $tds)
                                        <li>
                                            <a href="{{ route('programme.dashboard.tutors', $tds->id) }}" class="dropdown-item term-select {{ ($termDeclaration->id == $tds->id ? ' dropdown-active ' : '') }}">
                                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>  {{ $tds->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div> 
                </div>

                <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                    <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 ml-3">
                        <i data-lucide="sliders-horizontal" class="hidden sm:block w-4 h-4 mr-2"></i>
                        <select class="form-control w-full border-0" name="course_id" id="personalTutorCourseFilter" style="max-width: 230px;">
                            <option value="{{ route('programme.dashboard.personal.tutors', $termDeclaration->id) }}">All Course</option>
                            @if(!empty($courses))
                                @foreach($courses as $cr)
                                    <option {{ $selected_course == $cr->id ? 'Selected' : '' }} value="{{ route('programme.dashboard.tutors', [$termDeclaration->id, $cr->id]) }}">{{ $cr->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <a href="{{ route('programme.dashboard.tutors.export', [$termDeclaration->id, $selected_course]) }}" class="btn btn-success text-white ml-1 py-0 h-[36px]"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export XL</a>
                </div>
            </div>

            <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0 relative">
                <table class="table table-report sm:mt-2" id="dailyClassInfoTable">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap uppercase">Tutor</th>
                            <th class="text-center whitespace-nowrap uppercase">Contracted Hour</th>
                            <th class="text-center whitespace-nowrap uppercase">Class Hour</th>
                            <th class="text-center whitespace-nowrap uppercase">Load</th>
                            <th class="text-center whitespace-nowrap uppercase">No of Module</th>
                            <th class="text-left whitespace-nowrap uppercase">Attendance Rate</th>
                            <th class="text-left whitespace-nowrap uppercase">Exp. Submission</th>
                            <th class="text-left whitespace-nowrap uppercase">Submission Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($tutors))
                            @foreach($tutors as $tut)
                                @php 
                                    $contracted_hour = (isset($tut->contracted_hour) && !empty($tut->contracted_hour) ? $tut->contracted_hour : '00:00');
                                    $chours = (!empty($contracted_hour) ? explode(':', $contracted_hour) : []);
                                    $cHour = (isset($chours[0]) ? (int) $chours[0] : 0);
                                    $cHour += (isset($chours[1]) ? (int) $chours[1] / 60 : 0);

                                    $class_hour = (isset($tut->class_hours) && !empty($tut->class_hours) ? $tut->class_hours : '00:00');
                                    $clhours = (!empty($class_hour) ? explode(':', $class_hour) : []);
                                    $clHour = (isset($clhours[0]) ? (int) $clhours[0] : 0);
                                    $clHour += (isset($clhours[1]) ? (int) $clhours[1] / 60 : 0);

                                    $load = ($cHour > 0 && $clHour > 0 ? $clHour / $cHour : 0)
                                @endphp
                                <tr class="intro-x">
                                    <td>
                                        <div class="flex items-center justify-start">
                                            <div class="w-10 h-10 image-fit mr-4">
                                                <img alt="{{ (isset($tut->employee->full_name) ? $tut->employee->full_name : '') }}" class="rounded-full" src="{{ (isset($tut->employee->photo_url) ? $tut->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                            </div>
                                            <div>
                                                <a href="{{ route('programme.dashboard.tutors.details', [$termDeclaration->id, $tut->id]) }}" class="font-medium whitespace-nowrap uppercase">{{ (isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee') }}</a>
                                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">
                                                    {{ isset($tut->employee->employment->employeeWorkType->name) && !empty($tut->employee->employment->employeeWorkType->name) ? $tut->employee->employment->employeeWorkType->name : '' }}
                                                    {{ isset($tut->employee->employment->employeeJobTitle->name) && !empty($tut->employee->employment->employeeJobTitle->name) ? ' - '.$tut->employee->employment->employeeJobTitle->name : '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-medium">
                                        {{ (isset($tut->contracted_hour) && !empty($tut->contracted_hour) ? $tut->contracted_hour : '00:00') }}
                                    </td>
                                    <td class="text-center font-medium">
                                        {{ (isset($tut->class_hours) && !empty($tut->class_hours) ? $tut->class_hours : '00:00') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="font-medium">
                                            {{ number_format($load, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">
                                            {{ (isset($tut->no_of_module) && $tut->no_of_module > 0 ? $tut->no_of_module : '0') }}
                                        </span>
                                    </td>
                                    <td class="text-left">
                                        @php
                                            $attendances = $tut->attendances;

                                            $attendance = 0;
                                            $attendance += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
                                            $attendance += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
                                            $attendance += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
                                            $attendance += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
                                            $attendance += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
                                            $attendance += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);

                                            $attendanceTotal = (isset($attendances->TOTAL) && $attendances->TOTAL > 0) ? $attendances->TOTAL : 0;
                                            if($attendance > 0 && $attendanceTotal > 0):
                                                echo number_format($attendance / $attendanceTotal * 100, 2).'%';
                                            else:
                                                echo '0.00%';
                                            endif;
                                        @endphp
                                    </td>
                                    <td>{{ (isset($tut->expected_submission) && $tut->expected_submission > 0 ? $tut->expected_submission : '0') }}</td>
                                    <td class="text-left">
                                        0.0%
                                    </td>
                                </tr>
                            @endforeach
                        @else 
                            <tr class="intro-x">
                                <td colspan="5">
                                    <div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> No Tutors found for the selected Term.</div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="module">
        (function () {
            $('#personalTutorCourseFilter').on('change', function(e){
                window.location.href = $(this).val();
            })
        })()
    </script>
@endsection