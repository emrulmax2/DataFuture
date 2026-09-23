<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Library charge {{ optional($loan)->reference }}</title>
    {{-- Inlined and table-based, like every other mail here: Gmail strips
         <style> in some clients and Outlook ignores most of it. --}}
</head>
<body style="margin:0; padding:0; background:#f2f0ec; font-family:'Instrument Sans',-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#232528; -webkit-font-smoothing:antialiased;">

    {{-- Inbox preview line, then hidden in the body. --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent; height:0; width:0;">
        £{{ number_format($deposit->amount, 2) }} to pay by midnight, or the book stays on your record as out on loan.
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
                        <td style="padding:32px 32px 20px;">
                            <div style="font-size:11.5px; font-weight:600; color:#6b6b66; letter-spacing:0.12em; text-transform:uppercase; margin-bottom:10px;">
                                Overdue charge
                            </div>
                            <div style="font-family:Georgia,'Times New Roman',serif; font-size:24px; line-height:1.25; color:#232528;">
                                £{{ number_format($deposit->amount, 2) }} to pay
                            </div>

                            <div style="margin-top:16px; font-size:14px; line-height:1.6; color:#3c3f44;">
                                Hello{{ $studentName ? ' '.$studentName : '' }}, thank you for bringing
                                @if($loan)<strong>{{ $loan->title }}</strong>@else your book @endif back.
                                It came back after its due date, so there is a charge to settle before the
                                return can be completed.
                            </div>
                        </td>
                    </tr>

                    {{-- The deadline is the whole message, so it gets its own
                         band rather than a sentence in the paragraph above. --}}
                    <tr>
                        <td style="padding:0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background:#fdf6e8; border:1px solid #f0dcbc; border-radius:10px;">
                                <tr>
                                    <td style="padding:14px 16px; font-size:13.5px; line-height:1.6; color:#8a6218;">
                                        <strong>Please pay by {{ optional($deadline)->format('g:ia \o\n j M Y') }}.</strong>
                                        Until then the book is still on your record as out on loan. If the charge
                                        is not paid today, another day is added and you will need to see the
                                        library desk again.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:24px 32px 6px;">
                            <a href="{{ $payUrl }}"
                               style="display:inline-block; padding:13px 30px; background:#c9a24a; color:#ffffff; font-size:15px; font-weight:600; text-decoration:none; border-radius:9px;">
                                Pay £{{ number_format($deposit->amount, 2) }} now
                            </a>
                        </td>
                    </tr>

                    {{-- The same link in full, for a client that strips the
                         button or a phone that will not open it. --}}
                    <tr>
                        <td align="center" style="padding:6px 32px 26px; font-size:11.5px; line-height:1.6; color:#8a8a84; word-break:break-all;">
                            Or paste this into your browser:<br>
                            <span style="color:#5c6472;">{{ $payUrl }}</span>
                        </td>
                    </tr>

                    @if($loan)
                        <tr>
                            <td style="padding:0 32px 30px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                       style="border-top:1px solid #eeece6;">
                                    <tr>
                                        <td style="padding:14px 0 0; font-size:12.5px; color:#6b6b66;">Reference</td>
                                        <td align="right" style="padding:14px 0 0; font-size:12.5px; font-weight:600; color:#232528;">{{ $loan->reference }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0 0; font-size:12.5px; color:#6b6b66;">Was due</td>
                                        <td align="right" style="padding:6px 0 0; font-size:12.5px; font-weight:600; color:#232528;">{{ optional($loan->due_at)->format('j M Y') }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="background:#faf9f7; border-top:1px solid #eeece6; padding:18px 32px; font-size:11.5px; line-height:1.6; color:#8a8a84;">
                            Paid at the desk already? Nothing more to do — this link stops working once the
                            charge is settled. Any questions, please speak to the library.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
