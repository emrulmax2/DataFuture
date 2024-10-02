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
            @php 
                $bgs = ['rgba(54, 162, 235, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 99, 132, 0.8)', 'rgba(255, 159, 64, 0.8)', 'rgba(59, 89, 152, 0.8)', 'rgba(74, 179, 244, 0.8)', 'rgba(81, 127, 164, 0.8)', 'rgba(0, 119, 181, 0.8)', 'rgba(13, 148, 136, 0.8)', 'rgba(6, 182, 212, 0.8)', 'rgba(22, 78, 99, 0.8)'];
            @endphp
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
                            <th class="countable" data-label="Overall" data-sl="0" data-color="rgba(255, 159, 64, 0.8)">
                                <div class="form-check m-0 items-center">
                                    <input Checked id="col_selection_0" class="form-check-input col_selection" name="col_selection[]" type="checkbox" value="0">
                                    <label class="form-check-label" for="col_selection_0">Overall</label>
                                </div>
                            </th>
                            @if(!empty($courses))
                                @php $i = 0; @endphp
                                @foreach($courses as $crs)
                                    <th class="countable" data-label="{{ $crs->name }}" data-sl="{{ $crs->id }}" data-color="{{ $bgs[$i] }}">
                                        <div class="form-check m-0 items-center">
                                            <input id="col_selection_{{ $crs->id }}" class="form-check-input col_selection" name="col_selection[]" type="checkbox" value="{{ $crs->id }}">
                                            <a href="{{ route('reports.term.performance.course.trend.view', [$term->id, $crs->id]) }}" class="font-medium text-primary ml-2">{{ $crs->name }}</a>
                                        </div>
                                    </th>
                                    @php $i++; @endphp
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result as $week => $res)
                            <tr>
                                <th class="labels" data-labels="W/S {{ date('d-m-Y', strtotime($res['start'])) }}">W/S {{ date('d-m-Y', strtotime($res['start'])) }}</th>
                                <th class="rowRates serial_0" data-rate="{{ $res['overall'] > 0 ? number_format($res['overall'], 2) : '0.00'}}">
                                    {{ $res['overall'] > 0 ? number_format($res['overall'], 2).'%' : '0.00%'}}
                                </th>
                                @foreach($res['rows'] as $course_id => $row)
                                    <th class="rowRates serial_{{ $course_id }}" data-rate="{{ ($row->percentage_withexcuse > 0 ? number_format(round($row->percentage_withexcuse, 2), 2) : '0.00') }}">{{ ($row->percentage_withexcuse > 0 ? number_format(round($row->percentage_withexcuse, 2), 2).'%' : '0.00%') }}</th>
                                @endforeach
                            </tr>
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
    @vite('resources/js/term-performance-trend-reports.js')
@endsection