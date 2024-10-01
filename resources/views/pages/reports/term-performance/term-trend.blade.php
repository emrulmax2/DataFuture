@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Term Performance Trend</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('reports') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Reports</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        @if(!empty($result))
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-12">
                    <div class="chartWrap mb-7" style="max-width: 70%;">
                        <canvas height="400" id="attendanceTrendLineChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden mt-5" id="attendanceTrendWrap">
                <table class="table table-bordered table-sm" id="attendanceTrendOvTable" data-title="{{ $term->name }}">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>Overall</th>
                            @if(!empty($courses))
                                @foreach($courses as $crs)
                                    <th>{{ $crs->name }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result as $week => $res)
                            @php 
                                $result = $res['result'];
                                $perticipents = $result->sum('TOTAL');
                                $attendances = $result->sum('P') + $result->sum('O') + $result->sum('E') + $result->sum('M') + $result->sum('H') + $result->sum('L');
                                $overAll = round($attendances * 100 / $perticipents, 2);
                            @endphp
                            <tr>
                                <th>{{ date('jS', strtotime($week.'-11-1986')) }} Week</th>
                                <th>
                                    {{ $overAll > 0 ? number_format($overAll, 2).'%' : '0.00%'}}
                                </th>
                                @foreach($result as $res)
                                    <th>{{ ($res->percentage_withexcuse > 0 ? number_format(round($res->percentage_withexcuse, 2), 2).'%' : '0.00%') }}</th>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif;
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
    @vite('resources/js/term-performance-trend-reports.js')
@endsection