@extends('../layout/' . $layout)

@section('subhead')
    <title>Programme Dashboard - Welcome to London churchill college</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 pt-5 relative">
            <div class="intro-y block sm:flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">
                    Personal Tutors: {{ $termDeclaration->name }}
                </h2>

                <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                    <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 ml-3">
                        <i data-lucide="sliders-horizontal" class="hidden sm:block w-4 h-4 mr-2"></i>
                        <select class="form-control w-full border-0" name="course_id" id="personalTutorCourseFilter" style="max-width: 230px;">
                            <option value="{{ route('programme.dashboard.personal.tutors', $termDeclaration->id) }}">All Course</option>
                            @if(!empty($courses))
                                @foreach($courses as $cr)
                                    <option {{ $selected_course == $cr->id ? 'Selected' : '' }} value="{{ route('programme.dashboard.personal.tutors', [$termDeclaration->id, $cr->id]) }}">{{ $cr->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0 relative">
                <table class="table table-report sm:mt-2" id="dailyClassInfoTable">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap uppercase">Personal Tutor</th>
                            <th class="text-center whitespace-nowrap uppercase">Contracted Hour</th>
                            <th class="text-center whitespace-nowrap uppercase">Assigned Students</th>
                            <th class="text-center whitespace-nowrap uppercase">Load</th>
                            <th class="text-center whitespace-nowrap uppercase">No of Modules / Groups</th>
                            <th class="text-left whitespace-nowrap uppercase">Attendance Rate</th>
                            <th class="text-left whitespace-nowrap uppercase">Outstanding Call</th>
                            <th class="text-left whitespace-nowrap uppercase">Outstanding Uploads</th>
                            <th class="text-left whitespace-nowrap uppercase">Submission Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($tutors))
                            @foreach($tutors as $tut)
                                @php 
                                    $contracted_hour = (isset($tut->contracted_hour) && !empty($tut->contracted_hour) ? $tut->contracted_hour : '');
                                    $hours = (!empty($contracted_hour) ? explode(':', $contracted_hour) : []);
                                    $hour = (isset($hours[0]) ? (int) $hours[0] : 0);
                                    $hour += (isset($hours[1]) ? (int) $hours[1] / 60 : 0);
                                    $no_of_assigned = (isset($tut->no_of_assigned) && $tut->no_of_assigned > 0 ? $tut->no_of_assigned : 0);
                                    $load = ($hour > 0 && $no_of_assigned > 0 ? $no_of_assigned / $hour : 0)
                                @endphp
                                <tr class="intro-x">
                                    <td>
                                        <div class="flex items-center justify-start">
                                            <div class="w-10 h-10 image-fit mr-4">
                                                <img alt="{{ (isset($tut->employee->full_name) ? $tut->employee->full_name : '') }}" class="rounded-full" src="{{ (isset($tut->employee->photo_url) ? $tut->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                            </div>
                                            <div>
                                                <a href="{{ route('programme.dashboard.personal.tutors.details', [$termDeclaration->id, $tut->id]) }}" class="font-medium whitespace-nowrap uppercase">{{ (isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee') }}</a>
                                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-medium">
                                            {{ (!empty($contracted_hour) ? $contracted_hour : '00:00') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-medium">
                                            {{ $no_of_assigned }}
                                        </span>
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
                                        <span class="rounded-full text-lg bg-info text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">
                                            {{ (isset($tut->no_of_group) && $tut->no_of_group > 0 ? $tut->no_of_group : '0') }}
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
                                    <td class="text-left"></td>
                                    <td class="text-center"><span class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">
                                        {{ $tut->undecidedUploads }}</span></td>
                                    <td class="text-left"></td>
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