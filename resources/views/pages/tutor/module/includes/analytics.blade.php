<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12 sm:col-span-6">
        <div class="intro-y box mt-5">
            <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                <h2 class="font-medium text-base mr-auto">Attendance Rates</h2>
            </div>
            <div class="p-5">
                @if(!empty($attendance_rate))
                    @php 
                        $bgs = ['rgba(75, 192, 192, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(255, 159, 64, 0.2)'];
                        $bds = ['rgb(75, 192, 192)', 'rgb(54, 162, 235)', 'rgb(153, 102, 255)', 'rgb(255, 99, 132)', 'rgb(255, 159, 64)'];
                    @endphp
                    <div class="overflow-x-auto scrollbar-hidden" id="attendanceRateWrap">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-12">
                                <div class="chartWrap mb-7" style="max-width: 100%;">
                                    <canvas height="300" id="attendanceRateBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <table class="table table-bordered table-sm" id="attendanceRateOvTable">
                            <tbody>
                                <!--<tr class="rateRow" data-label="Overall" data-rate="{{ ($attendance_rate->percentage_withexcuse > 0 ? $attendance_rate->percentage_withexcuse : 0) }}" data-bg="{{ $bgs[0] }}" data-bd="{{ $bds[0] }}">
                                    <td class="w-20">
                                        <div class="form-check m-0 justify-center">
                                            <input checked id="rateRowCheck_0" class="form-check-input rateRowCheck" type="checkbox" name="rateRowCheck[]" value="1">
                                        </div>
                                    </td>
                                    <th>Overall</th>
                                    <th>
                                        {{ $attendance_rate->percentage_withexcuse > 0 ? number_format($attendance_rate->percentage_withexcuse, 2).'%' : '0.00%'}}
                                    </th>
                                </tr>-->
                                <tr class="rateRow" data-label="{{ $attendance_rate->module_name }}" data-rate="{{ ($attendance_rate->percentage_withexcuse > 0 ? round($attendance_rate->percentage_withexcuse, 2) : 0) }}" data-bg="{{ $bgs[1] }}" data-bd="{{ $bds[1] }}">
                                    <td class="w-20">
                                        <div class="form-check m-0 justify-center">
                                            <input checked id="rateRowCheck_{{ $attendance_rate->module_creations_id }}" class="form-check-input rateRowCheck" type="checkbox" name="rateRowCheck[]" value="{{ $attendance_rate->module_creations_id }}">
                                        </div>
                                    </td>    
                                    <th>{{ $attendance_rate->module_name }}</th>
                                    <th>{{ ($attendance_rate->percentage_withexcuse > 0 ? number_format(round($attendance_rate->percentage_withexcuse, 2), 2).'%' : '0.00%') }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-danger-soft show flex items-center" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Data not found
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-span-12 sm:col-span-6">
        <div class="intro-y box mt-5">
            <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                <h2 class="font-medium text-base mr-auto">Attendance Trends</h2>
            </div>
            <div class="p-5">
                @if(!empty($attendance_trend))
                    @php 
                        $bgs = ['rgba(54, 162, 235, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 99, 132, 0.8)', 'rgba(255, 159, 64, 0.8)', 'rgba(59, 89, 152, 0.8)', 'rgba(74, 179, 244, 0.8)', 'rgba(81, 127, 164, 0.8)', 'rgba(0, 119, 181, 0.8)', 'rgba(13, 148, 136, 0.8)', 'rgba(6, 182, 212, 0.8)', 'rgba(22, 78, 99, 0.8)'];
                    @endphp
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12">
                            <div class="chartWrap mb-7" style="max-width: 100%;">
                                <canvas height="300" id="attendanceTrendLineChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto mt-5" id="attendanceTrendWrap">
                        <table class="table table-bordered table-sm" id="attendanceTrendOvTable">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">&nbsp;</th>
                                    <!--<th class="countable whitespace-nowrap" data-label="Overall" data-sl="0" data-color="rgba(255, 159, 64, 0.8)">
                                        <div class="form-check m-0 items-center">
                                            <input Checked id="col_selection_0" class="form-check-input col_selection" name="col_selection[]" type="checkbox" value="0">
                                            <label class="form-check-label" for="col_selection_0">Overall</label>
                                        </div>
                                    </th>-->
                                    <th class="countable whitespace-nowrap" data-label="{{ $plan->creations->module_name }}" data-sl="{{ $plan->module_creation_id }}" data-color="{{ $bgs[0] }}">
                                        <div class="form-check m-0 items-center">
                                            <input Checked id="col_selection_{{ $plan->module_creation_id }}" class="form-check-input col_selection" name="col_selection[]" type="checkbox" value="{{ $plan->module_creation_id }}">
                                            <a href="javascript:void(0);" class="font-medium text-primary ml-2">{{ $plan->creations->module_name }}</a>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendance_trend as $week => $res)
                                    <tr>
                                        <th class="labels whitespace-nowrap" data-labels="W/S {{ date('d-m-Y', strtotime($res['start'])) }}">W/S {{ date('d-m-Y', strtotime($res['start'])) }}</th>
                                        <!--<th class="rowRates serial_0" data-count="{{ $res['overall_count'] > 0 ? $res['overall_count'] : 0 }}" data-attendance="{{ $res['overall_attendance'] > 0 ? $res['overall_attendance'] : 0 }}" data-rate="{{ $res['overall'] > 0 ? number_format($res['overall'], 2) : '0.00'}}">
                                            {{ $res['overall'] > 0 ? number_format($res['overall'], 2).'%' : '0.00%'}}
                                        </th>-->
                                        @foreach($res['rows'] as $mod => $row)
                                            <th class="rowRates serial_{{ $mod }}" data-count="{{ $row->TOTAL > 0 ? $row->TOTAL : 0 }}" data-attendance="{{ $row->TOTALATTENDANCE > 0 ? $row->TOTALATTENDANCE : 0 }}" data-rate="{{ ($row->percentage_withexcuse > 0 ? number_format(round($row->percentage_withexcuse, 2), 2) : '0.00') }}">{{ ($row->percentage_withexcuse > 0 ? number_format(round($row->percentage_withexcuse, 2), 2).'%' : '0.00%') }}</th>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else 
                    <div class="alert alert-danger-soft show flex items-center" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Trends not available
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>