{{-- A conversion / Task Automation problem for one student (App\Mail\ConversionErrorReportMail). --}}
@php
    $student = $info['student'] ?? null;
    $applicant = $info['applicant'] ?? null;
    $rows = array_filter([
        'Step' => $step,
        'Student' => $student ? trim($student->first_name.' '.$student->last_name).' · '.($student->registration_no ?: 'no registration no').' (#'.$student->id.')' : null,
        'Applicant' => $applicant ? trim($applicant->first_name.' '.$applicant->last_name).' · '.($applicant->application_no ?: '#'.$applicant->id) : null,
        'Student task' => !empty($info['task_id']) ? '#'.$info['task_id'] : null,
        'Task Automation rule' => !empty($info['rule_id']) ? '#'.$info['rule_id'] : null,
        'When' => isset($info['time']) ? $info['time']->format('d-m-Y H:i:s') : null,
        'Where' => strtoupper((string) ($info['environment'] ?? '')).' · '.config('app.url'),
    ]);
@endphp
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>Conversion problem</title></head>
<body style="margin:0;padding:24px;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;color:#1f2a37;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e3e8ef;border-radius:12px;">
<tr><td style="padding:22px 26px 8px;">
    <p style="margin:0 0 6px;font-size:11px;font-weight:bold;letter-spacing:1.2px;color:#b42318;text-transform:uppercase;">Conversion / automation problem</p>
    <h1 style="margin:0 0 14px;font-size:20px;line-height:27px;color:#142b49;">{{ $problem }}</h1>
</td></tr>
<tr><td style="padding:0 26px 18px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:21px;">
        @foreach($rows as $label => $value)
            <tr><td valign="top" style="width:34%;padding:6px 10px 6px 0;color:#69788a;">{{ $label }}</td><td style="padding:6px 0;font-weight:bold;color:#183556;">{{ $value }}</td></tr>
        @endforeach
    </table>
    @if(!empty($info['detail']))
        <p style="margin:14px 0 6px;font-size:12px;font-weight:bold;color:#69788a;text-transform:uppercase;letter-spacing:.8px;">Details</p>
        <pre style="margin:0;padding:12px;background:#f6f8fb;border:1px solid #e3e8ef;border-radius:8px;font-size:12px;line-height:18px;white-space:pre-wrap;word-break:break-word;">{{ \Illuminate\Support\Str::limit((string) $info['detail'], 4000) }}</pre>
    @endif
    @if($student)
        <p style="margin:16px 0 0;font-size:13px;"><a href="{{ url('/student/process/'.$student->id) }}" style="color:#a41f4c;font-weight:bold;">Open the student's Process &amp; Tasks</a></p>
    @endif
</td></tr>
</table>
<p style="max-width:640px;margin:12px auto 0;text-align:center;font-size:11px;color:#8591a5;">Sent by DataFuture to {{ config('conversion_report.to') }} (CONVERSION_ERROR_REPORT_TO). The same problem for the same student is sent once every {{ config('conversion_report.repeat_minutes', 30) }} minutes.</p>
</body></html>
