@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Term Course Performance Trend</h2>
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
                        <canvas height="500" id="attendanceTrendLineChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto mt-5" id="attendanceTrendWrap">
                <table class="table table-bordered table-sm" id="attendanceTrendOvTable" data-title="{{ $term->name.' - '.$course->name }}">
                    <thead>
                        <tr>
                            <th style="width: 140px;">&nbsp;</th>
                            <th class="countable whitespace-nowrap" data-label="Overall" data-sl="0" data-color="rgba(255, 159, 64, 0.8)">
                                <div class="form-check m-0 items-center">
                                    <input Checked id="col_selection_0" class="form-check-input col_selection" name="col_selection[]" type="checkbox" value="0">
                                    <label class="form-check-label" for="col_selection_0">Overall</label>
                                </div>
                            </th>
                            @if(!empty($groups))
                                @foreach($groups as $gr)
                                    @php 
                                        $randKey = array_rand($bgs);
                                    @endphp
                                    <th class="countable whitespace-nowrap" data-label="{{ $gr->group_name }}" data-sl="{{ trim(str_replace(' ', '_', $gr->group_name)) }}" data-color="{{ $bgs[$randKey] }}">
                                        <div class="form-check m-0 items-center">
                                            <input id="col_selection_{{ $gr->groups_id }}" class="form-check-input col_selection" name="col_selection[]" type="checkbox" value="{{ $gr->groups_id }}">
                                            <a href="{{ route('reports.term.performance.group.trend.view', [$term->id, $course->id, $gr->groups_id]) }}" class="font-medium text-primary ml-2">{{ $gr->group_name }}</a>
                                        </div>
                                    </th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result as $week => $res)
                            <tr>
                                <th class="labels whitespace-nowrap" data-labels="W/S {{ date('d-m-Y', strtotime($res['start'])) }}">W/S {{ date('d-m-Y', strtotime($res['start'])) }}</th>
                                <th class="rowRates serial_0" data-rate="{{ $res['overall'] > 0 ? number_format($res['overall'], 2) : '0.00'}}">
                                    {{ $res['overall'] > 0 ? number_format($res['overall'], 2).'%' : '0.00%'}}
                                </th>
                                @foreach($res['rows'] as $group => $row)
                                    <th class="rowRates serial_{{ trim(str_replace(' ', '_', $group)) }}" data-rate="{{ ($row->percentage_withexcuse > 0 ? number_format(round($row->percentage_withexcuse, 2), 2) : '0.00') }}">{{ ($row->percentage_withexcuse > 0 ? number_format(round($row->percentage_withexcuse, 2), 2).'%' : '0.00%') }}</th>
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
    @vite('resources/js/term-performance-course-trend-reports.js')
@endsection