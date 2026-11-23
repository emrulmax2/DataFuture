{{--
    Submission & Pass Rate detail for one or more intakes: each intake
    (darker), its courses (shaded) and their terms (striped). Shared by the
    screen (inside <details>) and the PDF (from a new page). Uses the
    attendance detail's look: `.crn-att-detail`, `.crn-att-toggle`, `.crn-att-early`.

    $report       App\Services\Reports\SubmissionPassRate::buildMany()
    $collapsible  screen only: intake rows open and close their courses, and
                  course rows their terms.
--}}
@php
    $num = function ($n) { return number_format((int) $n); };
    $pct = function ($rate) { return $rate === null ? '–' : number_format((float) $rate, 1).'%'; };
    $cells = function ($row, $students = null) use ($num, $pct) {
        return '<td class="d-students">'.($students === null ? '' : $num($students)).'</td>'
            .'<td class="d-num">'.$num($row['expected']).'</td>'
            .'<td class="d-num">'.$num($row['submitted']).'</td>'
            .'<td class="d-num">'.$num($row['passed']).'</td>'
            .'<td class="d-wide">'.$num($row['not_submitted']).'</td>'
            .'<td class="d-wide">'.$pct($row['submission_rate']).'</td>'
            .'<td class="d-rate">'.$pct($row['pass_rate']).'</td>';
    };
@endphp
<table class="crn-att-detail crn-sub-detail is-grouped">
    <thead>
        <tr>
            <th class="d-name">Intake, course and term</th>
            <th class="d-students">Students</th>
            <th class="d-num">Expected</th>
            <th class="d-num">Submitted</th>
            <th class="d-num">Passed</th>
            <th class="d-wide">Not submitted</th>
            <th class="d-wide">Submission rate</th>
            <th class="d-rate">Pass rate</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report['intakes'] as $intake)
            @php $intakeGroup = 'sub-d-intake-'.$loop->index; $i = $loop->index; @endphp
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
                @php $group = 'sub-course-'.$i.'-'.$loop->index; @endphp
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
                        {!! $cells($term, $term['students']) !!}
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
<div class="crn-att-early">Students are counted once per term, so a student appears in each term they were due to submit. Not submitted = Expected − Submitted.</div>
