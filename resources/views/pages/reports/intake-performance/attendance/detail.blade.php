{{--
    Attendance Rate detail: sessions by code and term for one or more intakes:
    each intake (darker), its courses (shaded) and their terms (striped), then
    the note on terms dated before an intake. Shared by the screen (inside
    <details>) and the PDF (page 2); each styles `.crn-att-detail` /
    `.crn-att-early` itself.

    $report       App\Services\Reports\AttendanceRate::buildMany()
    $collapsible  screen only: intake rows open and close their courses, and
                  course rows their terms (resources/js/intake-rate-reports.js).
--}}
@php
    $num = function ($n) { return number_format((int) $n); };
    $pct = function ($rate) { return $rate === null ? '–' : number_format((float) $rate, 1).'%'; };
    $codes = ['P', 'O', 'L', 'LE', 'E', 'M', 'H'];
    $cells = function ($row) use ($codes, $num, $pct) {
        $out = '<td class="d-reg">'.$num($row['registered']).'</td>';
        foreach ($codes as $code) {
            $out .= '<td class="d-code">'.$num($row['codes'][$code]).'</td>';
        }
        return $out.'<td class="d-absent">'.$num($row['codes']['A']).'</td><td class="d-total">'.$num($row['total']).'</td><td class="d-rate">'.$pct($row['rate']).'</td>';
    };
@endphp
<table class="crn-att-detail is-grouped">
    <thead>
        <tr>
            <th class="d-name">Intake, course and term</th>
            <th class="d-reg">Registered</th>
            @foreach($codes as $code)
                <th class="d-code">{{ $code }}</th>
            @endforeach
            <th class="d-absent">A (Absent)</th>
            <th class="d-total">Total</th>
            <th class="d-rate">Rate</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report['intakes'] as $intake)
            @php $intakeGroup = 'atn-d-intake-'.$loop->index; $i = $loop->index; @endphp
            <tr class="d-intake">
                <td class="d-name">
                    @if(!empty($collapsible) && !empty($intake['courses']))
                        @include('pages.reports.intake-performance.rate.group-toggle', ['key' => $intakeGroup, 'label' => $intake['intake']])
                    @else
                        {{ $intake['intake'] }}
                    @endif
                </td>
                {!! $cells($intake['totals']) !!}
            </tr>
            @foreach($intake['courses'] as $course)
                @php $group = 'atn-course-'.$i.'-'.$loop->index; @endphp
                <tr class="d-course" data-crn-group="{{ $intakeGroup }}">
                    <td class="d-name">
                        @if(!empty($collapsible) && !empty($course['terms']))
                            <button type="button" class="crn-att-toggle" aria-expanded="false" data-terms="{{ $group }}">{{ $course['name'] }}</button>
                        @else
                            {{ $course['name'] }}
                        @endif
                    </td>
                    {!! $cells($course) !!}
                </tr>
                @foreach($course['terms'] as $term)
                    <tr class="d-term{{ $loop->even ? ' d-even' : '' }}" data-crn-group="{{ $intakeGroup }}" data-terms-of="{{ $group }}" @if(!empty($collapsible)) hidden @endif>
                        <td class="d-name">{{ $term['name'] }}</td>
                        {!! $cells($term) !!}
                    </tr>
                @endforeach
            @endforeach
        @endforeach
        @if($report['many'])
            <tr class="d-all">
                <td class="d-name">All intakes</td>
                {!! $cells($report['totals']) !!}
            </tr>
        @endif
    </tbody>
</table>
<div class="crn-att-early">
    @if(!empty($withCodesNote))Every code except A counts as attended. @endif
    @if($report['early']['marks'] > 0)
        Terms dated before {{ $report['many'] ? 'an intake' : 'the intake' }} started hold {{ $num($report['early']['marks']) }} sessions; they are included in the totals.
    @endif
</div>
