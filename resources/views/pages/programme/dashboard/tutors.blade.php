@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 pt-5 relative">
            <div class="intro-y block sm:flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">
                    Tutors: {{ $termDeclaration->name }}
                </h2>
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
                                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ isset($tut->employee->employment->employeeWorkType->name) && !empty($tut->employee->employment->employeeWorkType->name) ? $tut->employee->employment->employeeWorkType->name : '' }}</div>
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