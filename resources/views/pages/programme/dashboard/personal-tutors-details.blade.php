@extends('../layout/' . $layout)

@section('subhead')
    <title>Programme Dashboard - Welcome to London churchill college</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9 pt-5 relative">
            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-12">  
                    <div id="term-dropdown" class="dropdown w-1/2 sm:w-auto mr-auto"  data-tw-placement="bottom-start">
                        <button id="selected-term" class="dropdown-toggle btn btn-outline-secondary bg-white w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> <i data-loading-icon="oval" class="w-4 h-4 mr-2 hidden"  data-color="white"></i> <span>{{ $termDeclaration->name }}</span> <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                        </button>
                        <div class="dropdown-menu w-80">
                            <ul class="dropdown-content max-h-96" style="overflow-x: hidden; overflow-y: auto;">
                                @if(!empty($termDeclarations) && $termDeclarations->count() > 0)
                                    @foreach($termDeclarations as $tds)
                                        <li>
                                            <a href="{{ route('programme.dashboard.personal.tutors.details', [$tds->id, $p_tutor_id]) }}" class="dropdown-item term-select {{ ($p_tutor_id == $tds->id ? ' dropdown-active ' : '') }}">
                                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>  {{ $tds->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>       
                </div>
                <div class="col-span-12 mt-5">
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                        <table class="table table-report sm:mt-2">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">Moudel Name</th>
                                    <th class="whitespace-nowrap">Group</th>
                                    <th class="whitespace-nowrap">Attendance Rate</th>
                                    <th class="whitespace-nowrap">Submission Rate</th>
                                    <th class="whitespace-nowrap">Achivement Rate</th>
                                    <th class="whitespace-nowrap">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="intro-x">
                                    <td class="font-medium">Overall</td>
                                    <td class="font-medium"></td>
                                    <td class="font-medium overAllAttendanceRate"></td>
                                    <td class="font-medium"></td>
                                    <td class="font-medium"></td>
                                    <td class="font-medium"></td>
                                </tr>
                                @php 
                                    $P = $O = $L = $E = $M = $H = $OVERALLTOTAL = 0;
                                @endphp
                                @if(!empty($plans))
                                    @foreach($plans as $pln)
                                        <tr class="intro-x">
                                            <td class="font-medium">
                                                {{ (isset($pln->creations->module->name) ? $pln->creations->module->name : '') }}
                                                {{ (isset($pln->tutor->employee->full_name) ? ' - '.$pln->tutor->employee->full_name : '') }}
                                                {{ (isset($pln->class_type) && !empty($pln->class_type) ? ' - '.$pln->class_type : '') }}
                                            </td>
                                            <td class="font-medium">
                                                @if(isset($pln->group->name) && !empty($pln->group->name))
                                                    @if(strlen($pln->group->name) > 2)
                                                    <div class="rounded text-lg bg-success text-white cursor-pointer font-medium w-auto h-auto inline-flex justify-center items-center px-3 py-1">
                                                    @else
                                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">
                                                    @endif
                                                        {{ $pln->group->name }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $attendances = $pln->attendances;

                                                    $attendance = 0;
                                                    $attendance += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
                                                    $P += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
                                                    $attendance += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
                                                    $O += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
                                                    $attendance += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
                                                    $L += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
                                                    $attendance += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
                                                    $E += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
                                                    $attendance += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
                                                    $M += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
                                                    $attendance += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);
                                                    $H += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);

                                                    $OVERALLTOTAL += (isset($attendances->TOTAL) && $attendances->TOTAL > 0) ? $attendances->TOTAL : 0;
                                                    $attendanceTotal = (isset($attendances->TOTAL) && $attendances->TOTAL > 0) ? $attendances->TOTAL : 0;
                                                    if($attendance > 0 && $attendanceTotal > 0):
                                                        echo number_format($attendance / $attendanceTotal * 100, 2).'%';
                                                    else:
                                                        echo '0.00%';
                                                    endif;
                                                @endphp
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td>
                                                <a href="{{ route('tutor-dashboard.plan.module.show', $pln->id) }}" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9"><i data-lucide="eye-off" class="w-4 h-4"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                @php 
                                    $overAllAttendance = $P + $O + $L + $E + $M + $H;
                                    if($overAllAttendance > 0 && $OVERALLTOTAL > 0):
                                        $overallRate = number_format($overAllAttendance / $OVERALLTOTAL * 100, 2).'%';
                                    else:
                                        $overallRate = '0.00%';
                                    endif;
                                @endphp
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-12 mt-3 2xl:mt-5">
                        <div class="intory-x box zoom-in p-5">
                            <div class="text-center pt-5 pb-3">
                                <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative ml-auto mr-auto">
                                    <img alt="{{ (isset($tutor->employee->full_name) ? $tutor->employee->full_name : '') }}" class="rounded-full" src="{{ (isset($tutor->employee->photo_url) ? $tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                </div>
                                <div class="mt-3 text-center">
                                    <div class="truncate sm:whitespace-normal font-medium text-lg">{{ (isset($tutor->employee->full_name) ? $tutor->employee->full_name : 'Unknown Employee') }}</div>
                                    <div class="text-slate-500">
                                        @if(isset($tutor->employee->address->address_line_1) && $tutor->employee->address->address_line_1 > 0)
                                            @if(isset($tutor->employee->address->address_line_1) && !empty($tutor->employee->address->address_line_1))
                                                {{ $tutor->employee->address->address_line_1 }}, 
                                            @endif
                                            @if(isset($tutor->employee->address->address_line_2) && !empty($tutor->employee->address->address_line_2))
                                                {{ $tutor->employee->address->address_line_2 }},
                                            @endif
                                            @if(isset($tutor->employee->address->city) && !empty($tutor->employee->address->city))
                                                {{ $tutor->employee->address->city }}, 
                                            @endif
                                            @if(isset($tutor->employee->address->state) && !empty($tutor->employee->address->state))
                                                {{ $tutor->employee->address->state }}, 
                                            @endif
                                            @if(isset($tutor->employee->address->post_code) && !empty($tutor->employee->address->post_code))
                                                {{ $tutor->employee->address->post_code }}, 
                                            @endif
                                            @if(isset($tutor->employee->address->country) && !empty($tutor->employee->address->country))
                                                {{ $tutor->employee->address->country }}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-3 mt-5">
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="truncate sm:whitespace-normal flex items-center">
                                            <i data-lucide="mail" class="w-4 h-4 mr-2"></i> {{ (isset($tutor->employee->email) ? $tutor->employee->email : '---') }}
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="truncate sm:whitespace-normal flex items-center">
                                            <i data-lucide="mail" class="w-4 h-4 mr-2"></i> {{ (isset($tutor->email) ? $tutor->email : '---') }}
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="truncate sm:whitespace-normal flex items-center">
                                            <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> {{ (isset($tutor->employee->mobile) ? $tutor->employee->mobile : '---') }}
                                        </div>
                                    </div>
                                    @if(isset($tutor->employee->employment->office_telephone) && !empty($tutor->employee->employment->office_telephone))
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="truncate sm:whitespace-normal flex items-center">
                                            <i data-lucide="tablet-smartphone" class="w-4 h-4 mr-2"></i> {{ (isset($tutor->employee->employment->office_telephone) ? $tutor->employee->employment->office_telephone : '---') }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="module">
        (function(){
            $('.overAllAttendanceRate').html('<?php echo $overallRate; ?>');
        })();
    </script>
@endsection