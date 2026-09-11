<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Library reservation {{ $loan->reference }}</title>
    {{-- Inlined and table-based on purpose: Gmail strips <style> in some
         clients and Outlook ignores most of it, so every rule that matters is
         on the element it applies to. --}}
</head>
<body style="margin:0; padding:0; background:#f2f0ec; font-family:'Instrument Sans',-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#232528; -webkit-font-smoothing:antialiased;">

    {{-- Shown in the inbox list under the subject, then hidden in the body. --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent; height:0; width:0;">
        {{ $studentName }} has reserved {{ $loan->title }}. Hold it until {{ optional($loan->expires_at)->format('j M Y') }}.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f2f0ec;">
        <tr>
            <td align="center" style="padding:28px 14px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                       style="width:600px; max-width:100%; background:#ffffff; border-radius:14px; overflow:hidden; border:1px solid #e8e6e1;">

                    <tr>
                        <td style="background:#26292e; padding:26px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size:15px; font-weight:600; color:#ffffff; letter-spacing:-0.01em;">
                                        London Churchill College
                                    </td>
                                    <td align="right" style="font-size:11px; font-weight:600; color:#cda468; letter-spacing:0.14em; text-transform:uppercase;">
                                        Library
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 32px 22px;">
                            <div style="font-size:11.5px; font-weight:600; color:#6b6b66; letter-spacing:0.12em; text-transform:uppercase; margin-bottom:10px;">
                                New reservation
                            </div>
                            <div style="font-family:Georgia,'Times New Roman',serif; font-size:24px; line-height:1.25; color:#232528;">
                                {{ $loan->title }}
                            </div>
                            @if($loan->author)
                                <div style="font-size:14px; color:#53565b; margin-top:5px;">{{ $loan->author }}</div>
                            @endif

                            <div style="margin-top:18px; font-size:14px; line-height:1.6; color:#3c3f44;">
                                A copy is held in Operations and off the shelf. Please put it aside for collection.
                            </div>
                        </td>
                    </tr>

                    {{-- The two things the desk acts on, given their own band so
                         they survive being skimmed on a phone. --}}
                    <tr>
                        <td style="padding:0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background:#f9f3e7; border:1px solid #efe7da; border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 20px;" width="50%">
                                        <div style="font-size:10.5px; font-weight:600; color:#825d1c; letter-spacing:0.12em; text-transform:uppercase;">Reference</div>
                                        <div style="font-size:17px; font-weight:600; color:#232528; margin-top:4px; letter-spacing:0.02em;">{{ $loan->reference }}</div>
                                    </td>
                                    <td style="padding:16px 20px; border-left:1px solid #efe7da;" width="50%">
                                        <div style="font-size:10.5px; font-weight:600; color:#825d1c; letter-spacing:0.12em; text-transform:uppercase;">Hold until</div>
                                        <div style="font-size:17px; font-weight:600; color:#232528; margin-top:4px;">
                                            {{ optional($loan->expires_at)->format('j M Y') ?: '—' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;">
                                @php
                                    $rows = [
                                        'Student' => trim($studentName).($registrationNo ? "\n".$registrationNo : ''),
                                        'Collect from' => ($loan->campus ?: 'Not specified').($loan->location ? "\n".$loan->location : ''),
                                        'Copy barcode' => $loan->barcode,
                                        'ISBN' => $loan->isbn13,
                                    ];
                                @endphp
                                @foreach($rows as $label => $value)
                                    @continue(empty($value))
                                    @php $parts = explode("\n", (string) $value); @endphp
                                    <tr>
                                        <td style="padding:11px 0; border-bottom:1px solid #f0eeea; color:#6b6b66; vertical-align:top; width:132px;">{{ $label }}</td>
                                        <td style="padding:11px 0; border-bottom:1px solid #f0eeea; color:#232528;">
                                            {{ $parts[0] }}
                                            @if(isset($parts[1]))
                                                <div style="color:#6b6b66; font-size:12.5px; margin-top:2px;">{{ $parts[1] }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px 30px;">
                            {{-- The loan clock starts at the desk, not here. --}}
                            <div style="font-size:12.5px; line-height:1.65; color:#6b6b66;">
                                The loan period starts when you hand the book over. If it is not collected by the date
                                above, the hold is released automatically and the copy returns to the shelf.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px; background:#faf9f7; border-top:1px solid #f0eeea;">
                            <div style="font-size:11px; line-height:1.6; color:#8a8a84;">
                                This e-mail and its attachments are intended for the named recipient only and may be
                                confidential. If it has reached you in error, please reply to highlight the mistake and
                                take no action based on its contents.
                            </div>
                            <div style="font-size:11px; color:#8a8a84; margin-top:12px;">
                                Barclay Hall, 156B Green Street, London, E7 8JQ &nbsp;&middot;&nbsp; +44 (0) 207 377 1077
                            </div>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
