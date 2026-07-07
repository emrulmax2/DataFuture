<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Interim Transcript of Academic Achievement</title>
    <style>
        /*
         * NOTE: dompdf ignores longhand (and even shorthand) `@page` margins in
         * this build, which made the content sit flush against the paper edges.
         * The reliable approach is to reserve the page margin on `body` and pull
         * the fixed running header/footer bands up into that margin zone.
         */
        @page {
            margin: 0;
        }
        * { -webkit-print-color-adjust: exact; }
        html { margin: 0; padding: 0; }
        body {
            /* Public Sans matches the prototype and reads tighter/smaller than
               DejaVu Sans; keep DejaVu as a fallback if the font isn't installed. */
            font-family: "Public Sans", "DejaVu Sans", sans-serif;
            color: #152528;
            font-size: 10pt;
            line-height: 1.4;
            margin: 74px 60px 78px 60px;
            padding: 0;
        }
        table { border-collapse: collapse; }
        thead { display: table-header-group; }
        tfoot { display: table-row-group; }
        tr, td, th { page-break-inside: avoid; }
        .page-header {
            position: fixed;
            top: 40px;
            left: 60px;
            right: 60px;
        }
        .page-footer {
            position: fixed;
            bottom: 34px;
            left: 60px;
            right: 60px;
        }
        .content { padding-top: 0; }
        .card {
            border: 1px solid #e5ebec;
            border-radius: 8px;
            overflow: hidden;
        }
        .lbl {
            font-size: 7pt;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #7c8d91;
            font-weight: bold;
        }
        .val { font-weight: bold; }
        .muted { color: #9aa7aa; }
        .mono { font-family: "DejaVu Sans Mono", monospace; }
    </style>
</head>
<body>

    @php
        $gradeMeta = [
            'P' => ['#0b6b66', '#e5f2f0'],
            'M' => ['#8a6d1f', '#f4ebd6'],
            'D' => ['#0f252d', '#e2e8e9'],
            'R' => ['#b3392e', '#fbedeb'],
            'U' => ['#b3392e', '#fbedeb'],
            'A' => ['#b3392e', '#fbedeb'],
        ];
        $statusMap = ['C' => 'Core', 'S' => 'Specialist', 'O' => 'Optional'];
        $studentName = trim($student->full_name);
        $studentId = $student->registration_no ?: '—';
        $issuedAt = date('jS F, Y');
        $dateOfBirth = (isset($student->date_of_birth) && !empty($student->date_of_birth)) ? date('j F Y', strtotime($student->date_of_birth)) : '—';
        $programmeName = isset($student->crel->course->name) && !empty($student->crel->course->name) ? $student->crel->course->name : '—';
        $studentAddress = isset($student->contact->termaddress->full_address_pdf) && !empty($student->contact->termaddress->full_address_pdf) ? $student->contact->termaddress->full_address_pdf : '—';
        $awardingBody = trim((isset($student->crel->course->body->name) ? $student->crel->course->body->name : '').(isset($student->crel->abody->reference) && !empty($student->crel->abody->reference) ? ' · '.$student->crel->abody->reference : ''));
        $startDate = (isset($student->crel->course_start_date) && !empty($student->crel->course_start_date)) ? $student->crel->course_start_date : ($courseCreationStart ?: '—');
        $dateOfAward = (isset($student->awarded->date_of_award) && !empty($student->awarded->date_of_award)) ? date('j F Y', strtotime($student->awarded->date_of_award)) : 'Pending';
    @endphp

    <!-- ===== Running header band ===== -->
    <div class="page-header">
        <table width="100%">
            <tr>
                <td style="font-size: 8pt; color: #7c8d91; padding-bottom: 6px; border-bottom: 1px solid #e5ebec;">
                    <span style="font-weight: bold; color: #0f252d;">London Churchill College</span>
                    &middot; Interim Transcript of Academic Achievement
                </td>
                <td style="font-size: 8pt; color: #7c8d91; padding-bottom: 6px; text-align: right; border-bottom: 1px solid #e5ebec;">
                    {{ $studentName }} &middot; {{ $studentId }}
                </td>
            </tr>
        </table>
    </div>

    <div class="page-footer">
        <table width="100%">
            <tr>
                <td style="padding-top: 10px; font-size: 8pt; color: #9aa7aa; border-top: 1px solid #e5ebec;">Interim transcript &ndash; not valid as a final award certificate</td>
                <td style="padding-top: 10px; font-size: 8pt; color: #9aa7aa; text-align: right; border-top: 1px solid #e5ebec;">London Churchill College &middot; lcc.ac.uk</td>
            </tr>
        </table>
    </div>

    <div class="content">
        <!-- ===== Title block ===== -->
        <table width="100%" style="margin-top: 2px;">
            <tr>
                <td style="width: 165px; vertical-align: top;">
                    @if(!empty($logo) && file_exists($logo))
                        <img src="{{ $logo }}" alt="London Churchill College" style="width: 150px; height: auto;">
                    @endif
                </td>
                <td style="vertical-align: top; text-align: right;">
                    <div style="font-size: 15pt; font-weight: bold; color: #0f252d; line-height: 1.25;">Interim Transcript of<br>Academic Achievement</div>
                    <div style="font-size: 9pt; color: #7c8d91; padding-top: 4px;">Date of issue: {{ $issuedAt }}</div>
                </td>
            </tr>
        </table>

        <!-- ===== Gradient rule ===== -->
        <div style="margin: 10px 0 8px;">
            @if(!empty($gradient))
                <img src="{{ $gradient }}" alt="" width="100%" height="4" style="display: block; width: 100%; height: 4px;">
            @else
                <div style="height: 4px; width: 100%; background-image: linear-gradient(to right, #c9992e, #a31621, #0b6b66); background-color: #a31621;"></div>
            @endif
        </div>

        <!-- ===== Student meta ===== -->
        <div class="card" style="padding: 12px 16px;">
            <table width="100%">
                <tr>
                    <td style="width: 34%; vertical-align: top; padding: 4px 8px;">
                        <div class="lbl">Student</div><div class="val">{{ $studentName }}</div>
                    </td>
                    <td style="width: 33%; vertical-align: top; padding: 4px 8px;">
                        <div class="lbl">Student ID</div><div class="val">{{ $studentId }}</div>
                    </td>
                    <td style="width: 33%; vertical-align: top; padding: 4px 8px;">
                        <div class="lbl">Date of Birth</div>
                        <div class="val">{{ $dateOfBirth }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="vertical-align: top; padding: 4px 8px;">
                        <div class="lbl">Programme</div>
                        <div class="val">{{ $programmeName }}</div>
                    </td>
                    <td style="vertical-align: top; padding: 4px 8px;">
                        <div class="lbl">Address</div>
                        <div class="val">{{ $studentAddress }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 4px 8px;">
                        <div class="lbl">Awarding Body</div>
                        <div class="val">{{ $awardingBody ?: '—' }}</div>
                    </td>
                    <td style="vertical-align: top; padding: 4px 8px;">
                        <div class="lbl">Start Date</div>
                        <div class="val">{{ $startDate }}</div>
                    </td>
                    <td style="vertical-align: top; padding: 4px 8px;">
                        <div class="lbl">Date of Award</div>
                        <div class="val muted">{{ $dateOfAward }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ===== Units table ===== -->
        @php $total_cr = 0; @endphp
        <div class="card" style="margin-top: 16px;">
            <table width="100%">
                <thead>
                    <tr>
                        <th style="width: 14%; text-align: left; padding: 7px 10px; font-size: 7pt; letter-spacing: 0.06em; text-transform: uppercase; color: #b7c8cc; background-color: #0f252d;">Unit Number</th>
                        <th style="width: 45%; text-align: left; padding: 7px 10px; font-size: 7pt; letter-spacing: 0.06em; text-transform: uppercase; color: #b7c8cc; background-color: #0f252d;">Unit Name</th>
                        <th style="width: 12%; text-align: center; padding: 7px 6px; font-size: 7pt; letter-spacing: 0.06em; text-transform: uppercase; color: #b7c8cc; background-color: #0f252d;">Credit Value</th>
                        <th style="width: 9%; text-align: center; padding: 7px 6px; font-size: 7pt; letter-spacing: 0.06em; text-transform: uppercase; color: #b7c8cc; background-color: #0f252d;">Level</th>
                        <th style="width: 11%; text-align: center; padding: 7px 6px; font-size: 7pt; letter-spacing: 0.06em; text-transform: uppercase; color: #b7c8cc; background-color: #0f252d;">Status</th>
                        <th style="width: 9%; text-align: center; padding: 7px 6px; font-size: 7pt; letter-spacing: 0.06em; text-transform: uppercase; color: #b7c8cc; background-color: #0f252d;">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowIndex = 0; @endphp
                    @foreach($data as $moduleName => $results)
                        @php
                            $result = $results[0];
                            $credit_value = $result->plan->creations->credit_value;
                            $unit_value = (isset($result->plan->creations->unit_value) && !empty($result->plan->creations->unit_value)) ? $result->plan->creations->unit_value : $result->plan->creations->module->unit_value;
                            $unit_mode = $result->plan->creations->unit_mode;
                            $gcode = isset($result->grade->code) ? trim($result->grade->code) : '';
                            $gm = $gradeMeta[$gcode] ?? ['#43585d', '#eef2f3'];
                            $statusLabel = $statusMap[$unit_mode] ?? $unit_mode;
                            if(in_array($gcode, ['D', 'M', 'P'])) { $total_cr += $credit_value; }
                            $rowBg = ($rowIndex % 2) ? '#fafbfb' : '#ffffff';
                            $rowIndex++;
                        @endphp
                        <tr>
                            <td class="mono" style="padding: 6px 10px; font-size: 8pt; color: #5b6e72; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $result->module_code ? $result->module_code : $result->plan->creations->code }}</td>
                            <td style="padding: 6px 10px; font-size: 8.5pt; font-weight: bold; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $moduleName }}</td>
                            <td style="padding: 6px 6px; font-size: 8.5pt; text-align: center; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $credit_value }}</td>
                            <td style="padding: 6px 6px; font-size: 8.5pt; text-align: center; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $unit_value }}</td>
                            <td style="padding: 6px 6px; font-size: 8.5pt; text-align: center; color: #5b6e72; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $statusLabel }}</td>
                            <td style="padding: 6px 6px; text-align: center; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">
                                <span style="display: inline-block; width: 18px; padding: 3px 0; text-align: center; border-radius: 5px; font-size: 8pt; font-weight: bold; color: {{ $gm[0] }}; background-color: {{ $gm[1] }};">{{ $gcode }}</span>
                            </td>
                        </tr>
                    @endforeach

                    @foreach($dataPrevious as $moduleName => $results)
                        @php
                            $result = $results[0];
                            $credit_value = $result->courseModule->credit_value;
                            $unit_value = $result->courseModule->unit_value;
                            $unit_mode = $result->courseModule->unit_mode;
                            $gcode = isset($result->grade->code) ? trim($result->grade->code) : '';
                            $gm = $gradeMeta[$gcode] ?? ['#43585d', '#eef2f3'];
                            $statusLabel = $statusMap[$unit_mode] ?? $unit_mode;
                            if(in_array($gcode, ['D', 'M', 'P'])) { $total_cr += $credit_value; }
                            $rowBg = ($rowIndex % 2) ? '#fafbfb' : '#ffffff';
                            $rowIndex++;
                        @endphp
                        <tr>
                            <td class="mono" style="padding: 6px 10px; font-size: 8pt; color: #5b6e72; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $result->module_code ? $result->module_code : $result->courseModule->code }}</td>
                            <td style="padding: 6px 10px; font-size: 8.5pt; font-weight: bold; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $moduleName }}</td>
                            <td style="padding: 6px 6px; font-size: 8.5pt; text-align: center; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $credit_value }}</td>
                            <td style="padding: 6px 6px; font-size: 8.5pt; text-align: center; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $unit_value }}</td>
                            <td style="padding: 6px 6px; font-size: 8.5pt; text-align: center; color: #5b6e72; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">{{ $statusLabel }}</td>
                            <td style="padding: 6px 6px; text-align: center; border-top: 1px solid #f2f5f5; background-color: {{ $rowBg }};">
                                <span style="display: inline-block; width: 18px; padding: 3px 0; text-align: center; border-radius: 5px; font-size: 8pt; font-weight: bold; color: {{ $gm[0] }}; background-color: {{ $gm[1] }};">{{ $gcode }}</span>
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <td colspan="4" style="padding: 8px 10px; font-size: 7pt; letter-spacing: 0.06em; text-transform: uppercase; color: #7c8d91; font-weight: bold; border-top: 1px solid #e5ebec; background-color: #fafbfb;">Total Credits Achieved</td>
                        <td colspan="2" style="padding: 8px 10px; font-size: 10pt; font-weight: bold; color: #0f252d; text-align: right; border-top: 1px solid #e5ebec; background-color: #fafbfb;">{{ $total_cr }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ===== Legends ===== -->
        <table width="100%" style="margin-top: 16px;">
            <tr>
                <td style="width: 58%; vertical-align: top; padding-right: 8px;">
                    <div class="card" style="padding: 10px 14px;">
                    <div class="lbl" style="margin-bottom: 6px;">Grade Key</div>
                    <table width="100%" style="font-size: 8pt; color: #43585d;">
                        <tr>
                            <td style="padding: 2px 0;"><span style="font-weight: bold; color: #0f252d;">D</span>&nbsp;&nbsp;Distinction</td>
                            <td style="padding: 2px 0;"><span style="font-weight: bold; color: #b3392e;">R</span>&nbsp;&nbsp;Referred</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0;"><span style="font-weight: bold; color: #8a6d1f;">M</span>&nbsp;&nbsp;Merit</td>
                            <td style="padding: 2px 0;"><span style="font-weight: bold; color: #b3392e;">A</span>&nbsp;&nbsp;Absent or non-submission</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0;"><span style="font-weight: bold; color: #0b6b66;">P</span>&nbsp;&nbsp;Pass</td>
                            <td style="padding: 2px 0;"><span style="font-weight: bold; color: #b3392e;">U</span>&nbsp;&nbsp;Unclassified / Compensated</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0;"><span style="font-weight: bold; color: #5b6e72;">C</span>&nbsp;&nbsp;Malpractice / Unfair practice</td>
                            <td style="padding: 2px 0;"><span style="font-weight: bold; color: #5b6e72;">W</span>&nbsp;&nbsp;Withhold</td>
                        </tr>
                    </table>
                </div>
                </td>
                <td style="width: 42%; vertical-align: top; padding-left: 8px;">
                    <div class="card" style="padding: 10px 14px;">
                        <div class="lbl" style="margin-bottom: 6px;">Unit Status</div>
                        <table width="100%" style="font-size: 8pt; color: #43585d;">
                            <tr><td style="padding: 2px 0;"><span style="font-weight: bold; color: #0f252d;">C</span>&nbsp;&nbsp;Core</td></tr>
                            <tr><td style="padding: 2px 0;"><span style="font-weight: bold; color: #0f252d;">S</span>&nbsp;&nbsp;Specialist</td></tr>
                            <tr><td style="padding: 2px 0;"><span style="font-weight: bold; color: #0f252d;">O</span>&nbsp;&nbsp;Optional</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ===== Signature ===== -->
        <table width="100%" style="margin-top: 30px;">
            <tr>
                <td style="width: 240px; vertical-align: bottom;">
                    <div style="border-bottom: 1px dotted #7c8d91; height: 32px;"></div>
                    <div style="font-size: 8pt; font-weight: bold; color: #0f252d; padding-top: 6px;">Authorised Signature</div>
                    <div style="font-size: 7.5pt; color: #7c8d91;">Registrar, London Churchill College</div>
                </td>
                <td style="vertical-align: bottom; text-align: right;">
                    <div class="lbl">Date of Issue</div>
                    <div style="font-size: 9pt; font-weight: bold; color: #0f252d;">{{ $issuedAt }}</div>
                </td>
            </tr>
        </table>

        <!-- ===== Amendment note ===== -->
        <div class="card" style="margin-top: 16px; padding: 10px 14px; background-color: #faf3e1; border-color: #ead9a8; font-size: 8pt; color: #8a6d1f;">
            Please note that the grades may be subject to amendment(s) based on the recommendation(s) by external examiner(s).
        </div>
    </div>

</body>
</html>
