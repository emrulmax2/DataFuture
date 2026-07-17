@php
    // Demo palette: red under 40, amber under 50, green otherwise.
    $trendColor = function ($rate) {
        $r = (float) $rate;
        if ($r < 40) return '#c0392b';
        if ($r < 50) return '#a1802f';
        return '#0d7c73';
    };
@endphp

<div class="tm-analytics-grid">
    {{-- ------------------------------------------------------------ Rates --}}
    <div class="tm-panel tm-arate">
        <div class="tm-arate__head">Attendance Rates</div>
        <div class="tm-arate__body">
            @if(!empty($attendance_rate))
                @php
                    $bgs = ['rgba(75, 192, 192, 0.2)', 'rgba(13, 124, 115, 0.20)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(255, 159, 64, 0.2)'];
                    $bds = ['rgb(75, 192, 192)', 'rgb(13, 124, 115)', 'rgb(153, 102, 255)', 'rgb(255, 99, 132)', 'rgb(255, 159, 64)'];
                    $rateVal = $attendance_rate->percentage_withexcuse > 0 ? round($attendance_rate->percentage_withexcuse, 2) : 0;
                    $rateId = $attendance_rate->module_creations_id;
                @endphp

                {{-- kept for the analytics JS: existence hook + data attributes drive the bar toggle --}}
                <table id="attendanceRateOvTable" class="tm-visually-hidden">
                    <tbody>
                        <tr class="rateRow" data-label="{{ $attendance_rate->module_name }}" data-rate="{{ $rateVal }}" data-bg="{{ $bgs[1] }}" data-bd="{{ $bds[1] }}">
                            <td>
                                <input checked id="rateRowCheck_{{ $rateId }}" class="rateRowCheck" type="checkbox" name="rateRowCheck[]" value="{{ $rateId }}">
                            </td>
                            <th>{{ $attendance_rate->module_name }}</th>
                            <th>{{ number_format($rateVal, 2) }}%</th>
                        </tr>
                    </tbody>
                </table>

                <div id="attendanceRateWrap">
                    <div class="tm-arate__line" data-rate-id="{{ $rateId }}">
                        <span class="tm-arate__label">{{ $attendance_rate->module_name }}</span>
                        <div class="tm-arate__track">
                            <div class="tm-arate__fill" style="width: {{ min($rateVal, 100) }}%;">
                                <span class="tm-arate__fillval">{{ number_format($rateVal, 2) }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="tm-arate__axis">
                        <span>0</span><span>25</span><span>50</span><span>75</span><span>100</span>
                    </div>

                    <label class="tm-arate__legend" for="rateRowCheck_{{ $rateId }}">
                        <span class="tm-arate__legend-name">
                            <span class="tm-arate__swatch"></span>{{ $attendance_rate->module_name }}
                        </span>
                        <span class="tm-arate__legend-val">{{ number_format($rateVal, 2) }}%</span>
                    </label>
                </div>
            @else
                <div class="tm-analytics-empty">
                    <i data-lucide="alert-octagon" class="w-5 h-5"></i> Data not found
                </div>
            @endif
        </div>
    </div>

    {{-- ----------------------------------------------------------- Trends --}}
    <div class="tm-panel tm-atrend">
        <div class="tm-atrend__head">Attendance Trends</div>
        <div class="tm-atrend__body">
            @if(!empty($attendance_trend))
                @php
                    $bgs = ['rgba(47, 149, 208, 0.85)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 99, 132, 0.8)', 'rgba(255, 159, 64, 0.8)', 'rgba(59, 89, 152, 0.8)', 'rgba(74, 179, 244, 0.8)', 'rgba(81, 127, 164, 0.8)', 'rgba(0, 119, 181, 0.8)', 'rgba(13, 148, 136, 0.8)', 'rgba(6, 182, 212, 0.8)', 'rgba(22, 78, 99, 0.8)'];
                @endphp
                <div class="tm-atrend__chart">
                    <canvas height="300" id="attendanceTrendLineChart"></canvas>
                </div>
                <div class="tm-atrend__tablewrap tdscroll" id="attendanceTrendWrap">
                    <table class="tm-atrend__table" id="attendanceTrendOvTable">
                        <thead>
                            <tr>
                                <th class="tm-atrend__th-week">Week Starting</th>
                                <th class="countable tm-atrend__th-col" data-label="{{ $plan->creations->module_name }}" data-sl="{{ $plan->module_creation_id }}" data-color="{{ $bgs[0] }}">
                                    <label class="tm-atrend__collabel" for="col_selection_{{ $plan->module_creation_id }}">
                                        <input checked id="col_selection_{{ $plan->module_creation_id }}" class="col_selection" name="col_selection[]" type="checkbox" value="{{ $plan->module_creation_id }}">
                                        <span>{{ $plan->creations->module_name }}</span>
                                    </label>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendance_trend as $week => $res)
                                <tr>
                                    <th class="labels tm-atrend__week" data-labels="W/S {{ date('d-m-Y', strtotime($res['start'])) }}">W/S {{ date('d-m-Y', strtotime($res['start'])) }}</th>
                                    @foreach($res['rows'] as $mod => $row)
                                        @php $cellRate = $row->percentage_withexcuse > 0 ? number_format(round($row->percentage_withexcuse, 2), 2) : '0.00'; @endphp
                                        <th class="rowRates serial_{{ $mod }} tm-atrend__val" style="color: {{ $trendColor($cellRate) }};" data-count="{{ $row->TOTAL > 0 ? $row->TOTAL : 0 }}" data-attendance="{{ $row->TOTALATTENDANCE > 0 ? $row->TOTALATTENDANCE : 0 }}" data-rate="{{ $cellRate }}">{{ $cellRate }}%</th>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="tm-analytics-empty">
                    <i data-lucide="alert-octagon" class="w-5 h-5"></i> Trends not available
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    #tutorModuleDetails .tm-analytics-grid {
        align-items: start;
        display: grid;
        gap: 20px;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
    }

    #tutorModuleDetails .tm-visually-hidden {
        position: absolute !important;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* ---------------------------------------------------------- panels */
    #tutorModuleDetails .tm-arate,
    #tutorModuleDetails .tm-atrend {
        background: #fff;
        border: 1px solid #e6e1d3;
        border-radius: 18px;
        box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
        overflow: hidden;
    }

    #tutorModuleDetails .tm-arate__head,
    #tutorModuleDetails .tm-atrend__head {
        border-bottom: 1px solid #f0ede3;
        color: #0f2d2a;
        font-family: "IBM Plex Serif", Georgia, serif;
        font-size: 17px;
        font-weight: 600;
        padding: 18px 22px;
    }

    /* ---------------------------------------------------------- rates */
    #tutorModuleDetails .tm-arate__body {
        padding: 26px 22px;
    }

    #tutorModuleDetails .tm-arate__line {
        align-items: center;
        display: flex;
        gap: 14px;
    }

    #tutorModuleDetails .tm-arate__line + .tm-arate__line {
        margin-top: 14px;
    }

    #tutorModuleDetails .tm-arate__label {
        color: #0d7c73;
        flex: none;
        font-size: 12.5px;
        font-weight: 600;
        overflow: hidden;
        text-align: right;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 110px;
    }

    #tutorModuleDetails .tm-arate__track {
        background: #f4f6f5;
        border-radius: 8px;
        flex: 1;
        height: 44px;
        overflow: hidden;
        position: relative;
    }

    #tutorModuleDetails .tm-arate__fill {
        align-items: center;
        background: linear-gradient(90deg, #0d7c73, #12a08f);
        border-radius: 8px;
        display: flex;
        height: 100%;
        justify-content: flex-end;
        left: 0;
        min-width: 44px;
        padding-right: 12px;
        position: absolute;
        top: 0;
        transition: width .5s cubic-bezier(.4, 0, .2, 1);
    }

    #tutorModuleDetails .tm-arate__fillval {
        color: #fff;
        font-family: "IBM Plex Mono", monospace;
        font-size: 12.5px;
        font-weight: 700;
    }

    #tutorModuleDetails .tm-arate__line.is-off .tm-arate__fill {
        width: 0 !important;
        min-width: 0;
    }

    #tutorModuleDetails .tm-arate__line.is-off .tm-arate__fillval {
        display: none;
    }

    #tutorModuleDetails .tm-arate__axis {
        color: #adbbb9;
        display: flex;
        font-family: "IBM Plex Mono", monospace;
        font-size: 10.5px;
        justify-content: space-between;
        margin-top: 8px;
        padding-left: 124px;
    }

    #tutorModuleDetails .tm-arate__legend {
        align-items: center;
        background: #f4f8f6;
        border: 1px solid #dcebe5;
        border-radius: 11px;
        cursor: pointer;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        margin-top: 22px;
        padding: 13px 16px;
        user-select: none;
    }

    #tutorModuleDetails .tm-arate__legend-name {
        align-items: center;
        color: #0f2d2a;
        display: inline-flex;
        font-size: 13px;
        font-weight: 600;
        gap: 9px;
    }

    #tutorModuleDetails .tm-arate__swatch {
        align-items: center;
        background: #0d7c73;
        border-radius: 4px;
        display: flex;
        flex: none;
        height: 16px;
        justify-content: center;
        position: relative;
        width: 16px;
    }

    #tutorModuleDetails .tm-arate__swatch::after {
        border: solid #fff;
        border-width: 0 3px 3px 0;
        content: "";
        height: 8px;
        margin-top: -2px;
        transform: rotate(45deg);
        width: 4px;
    }

    #tutorModuleDetails .tm-arate__legend.is-off .tm-arate__swatch {
        background: #cbd5d1;
    }

    #tutorModuleDetails .tm-arate__legend.is-off .tm-arate__swatch::after {
        display: none;
    }

    #tutorModuleDetails .tm-arate__legend-val {
        color: #0d7c73;
        font-family: "IBM Plex Mono", monospace;
        font-size: 15px;
        font-weight: 700;
    }

    /* ---------------------------------------------------------- trends */
    #tutorModuleDetails .tm-atrend__body {
        padding: 20px 22px;
    }

    #tutorModuleDetails .tm-atrend__chart {
        height: 300px;
        position: relative;
    }

    #tutorModuleDetails .tm-atrend__tablewrap {
        border: 1px solid #ecebe2;
        border-radius: 12px;
        margin-top: 16px;
        max-height: 300px;
        overflow-y: auto;
    }

    #tutorModuleDetails .tm-atrend__table {
        border-collapse: collapse;
        width: 100%;
    }

    #tutorModuleDetails .tm-atrend__table thead th {
        background: #fafaf7;
        border-bottom: 1px solid #eef0ea;
        color: #9aa8a5;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        padding: 11px 16px;
        position: sticky;
        text-align: left;
        text-transform: uppercase;
        top: 0;
        z-index: 1;
    }

    #tutorModuleDetails .tm-atrend__collabel {
        align-items: center;
        color: #0d7c73;
        cursor: pointer;
        display: inline-flex;
        gap: 8px;
        letter-spacing: .05em;
    }

    #tutorModuleDetails .tm-atrend__collabel input {
        accent-color: #0d7c73;
        cursor: pointer;
    }

    #tutorModuleDetails .tm-atrend__table tbody tr {
        background: #fff;
    }

    #tutorModuleDetails .tm-atrend__table tbody tr:nth-child(even) {
        background: #fbfbf9;
    }

    #tutorModuleDetails .tm-atrend__table tbody th {
        border-bottom: 1px solid #f3f4f0;
        font-family: "IBM Plex Mono", monospace;
        font-weight: 600;
        padding: 10px 16px;
        text-align: left;
    }

    #tutorModuleDetails .tm-atrend__week {
        color: #5a6f6c;
        font-size: 12px;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-atrend__val {
        font-size: 12.5px;
    }

    /* ---------------------------------------------------------- empty */
    #tutorModuleDetails .tm-analytics-empty {
        align-items: center;
        background: #fdeeed;
        border: 1px solid #f3dcd8;
        border-radius: 12px;
        color: #b3261e;
        display: flex;
        font-size: 13.5px;
        font-weight: 500;
        gap: 8px;
        padding: 14px 16px;
    }

    @media (max-width: 1100px) {
        #tutorModuleDetails .tm-analytics-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
