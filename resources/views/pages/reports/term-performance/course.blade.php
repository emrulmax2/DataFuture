@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Term Performance Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('reports') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Reports</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        @if($result->count() > 0)
            @php 
                $overAll = 0;
                $row = 1;

                $perticipents = $result->sum('TOTAL');
                $attendances = $result->sum('P') + $result->sum('O') + $result->sum('E') + $result->sum('M') + $result->sum('H') + $result->sum('L');
                $overAll = round($attendances * 100 / $perticipents, 2);

                $bgs = ['rgba(75, 192, 192, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(255, 159, 64, 0.2)', 'rgba(59, 89, 152, 0.2)', 'rgba(74, 179, 244, 0.2)', 'rgba(81, 127, 164, 0.2)', 'rgba(0, 119, 181, 0.2)', 'rgba(13, 148, 136, 0.2)', 'rgba(6, 182, 212, 0.2)', 'rgba(22, 78, 99, 0.2)'];
                $bds = ['rgb(75, 192, 192)', 'rgb(54, 162, 235)', 'rgb(153, 102, 255)', 'rgb(255, 99, 132)', 'rgb(255, 159, 64)', 'rgb(59, 89, 152)', 'rgb(74, 179, 244)', 'rgb(81, 127, 164)', 'rgb(0, 119, 181)', 'rgb(13, 148, 136)', 'rgb(6, 182, 212)', 'rgb(22, 78, 99)'];
            @endphp
            <div class="overflow-x-auto scrollbar-hidden mt-5" id="attendanceRateWrap">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-12">
                        <div class="chartWrap mb-7" style="max-width: 70%;">
                            <canvas height="{{ (55 * $result->count()) }}" id="attendanceRateBarChart"></canvas>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered table-sm" id="attendanceRateOvTable" data-title="{{ $term->name }}">
                    <tbody>
                        <tr class="rateRow" data-label="{{ $course->name }}" data-rate="{{ ($overAll > 0 ? $overAll : 0) }}" data-bg="{{ $bgs[0] }}" data-bd="{{ $bds[0] }}">
                            <td class="w-20">
                                <div class="form-check m-0 justify-center">
                                    <input checked id="rateRowCheck_0" class="form-check-input rateRowCheck" type="checkbox" name="rateRowCheck[]" value="1">
                                </div>
                            </td>
                            <th>{{ $course->name }}</th>
                            <th>
                                {{ $overAll > 0 ? $overAll.'%' : '0.00%'}}
                            </th>
                        </tr>
                        @foreach($result as $res)
                            @php 
                                $randKey = array_rand($bgs);
                            @endphp
                            <tr class="rateRow" data-label="{{ $res->group_name }}" data-rate="{{ ($res->percentage_withexcuse > 0 ? round($res->percentage_withexcuse, 2) : 0) }}" data-bg="{{ $bgs[$randKey] }}" data-bd="{{ $bds[$randKey] }}">
                                <td class="w-20">
                                    <div class="form-check m-0 justify-center">
                                        <input checked id="rateRowCheck_{{ $row }}" class="form-check-input rateRowCheck" type="checkbox" name="rateRowCheck[]" value="1">
                                    </div>
                                </td>    
                                <th><a href="{{ route('reports.term.performance.group.view', [$term->id, $course->id, $res->group_id]) }}">{{ $res->group_name }}</a></th>
                                <th>{{ ($res->percentage_withexcuse > 0 ? number_format(round($res->percentage_withexcuse, 2), 2).'%' : '0.00%') }}</th>
                            </tr>
                            @php $row++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
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
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Success Modal Content -->
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
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/term-performance-course-reports.js')
@endsection