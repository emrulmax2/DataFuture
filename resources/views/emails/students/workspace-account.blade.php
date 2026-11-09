<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <title>Your new London Churchill College account is ready</title>
    {{--
        Laid out like Google's own "new account" sign-in email (white card, grey hairlines,
        blue Sign in button), but sent and branded by the college: college logo, no Google
        logo, nothing that claims to come from Google. Inlined and table-based for Gmail /
        Outlook. Built by App\Mail\StudentWorkspaceAccount.
    --}}
</head>
<body style="margin:0; padding:0; background:#ffffff; font-family:'Google Sans',Roboto,'Segoe UI',Helvetica,Arial,sans-serif; color:#202124; -webkit-font-smoothing:antialiased;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent; height:0; width:0;">Hi {{ $firstName ?: $name }}, your college account {{ $email }} is ready. Sign in to get started.</div>

    @php
        $logoSrc = (isset($message) && !empty($logoPath)) ? $message->embed($logoPath) : $logoUrl;
        $domain = \Illuminate\Support\Str::after($email, '@');
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;">
        <tr>
            <td align="center" style="padding:40px 16px 24px;">
                <table role="presentation" width="520" cellpadding="0" cellspacing="0" border="0" style="width:520px; max-width:100%;">

                    {{-- card --}}
                    <tr>
                        <td style="border:1px solid #dadce0; border-radius:8px; padding:40px 40px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                {{-- college mark (its logo is a white wordmark, so it sits on the college green) --}}
                                <tr>
                                    <td align="center" style="padding-bottom:24px;">
                                        @if(!empty($logoSrc))
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="background:#123c38; border-radius:8px; padding:10px 16px;">
                                                <img src="{{ $logoSrc }}" alt="{{ $companyName }}" width="112" style="display:block; width:112px; max-width:112px; height:auto; border:0; outline:none;">
                                            </td></tr></table>
                                        @else
                                            <div style="font-size:18px; font-weight:500; color:#123c38;">{{ $companyName }}</div>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="padding-bottom:8px; font-size:24px; line-height:32px; font-weight:400; color:#202124;">Welcome to your new account</td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-bottom:28px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                            <td style="border:1px solid #dadce0; border-radius:16px; padding:4px 12px 4px 8px; font-size:14px; line-height:20px; color:#3c4043;">
                                                <span style="display:inline-block; width:20px; height:20px; line-height:20px; border-radius:50%; background:#123c38; color:#ffffff; font-size:11px; font-weight:500; text-align:center; vertical-align:middle; margin-right:6px;">{{ mb_strtoupper(mb_substr($firstName ?: $name, 0, 1)) }}</span><span style="vertical-align:middle;">{{ $email }}</span>
                                            </td>
                                        </tr></table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="border-top:1px solid #dadce0; padding-top:24px; font-size:14px; line-height:22px; color:#3c4043;">
                                        Hi {{ $firstName ?: $name }},
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top:12px; font-size:14px; line-height:22px; color:#3c4043;">
                                        {{ $companyName }} has created a new Google Workspace account for you. Use it for Gmail, Google Drive, Classroom and everything else at the college.
                                    </td>
                                </tr>

                                {{-- sign-in details --}}
                                <tr>
                                    <td style="padding-top:20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8f9fa; border-radius:8px;">
                                            <tr>
                                                <td style="padding:16px 20px 6px; font-size:12px; line-height:16px; letter-spacing:0.3px; color:#5f6368;">Username</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 20px 14px; font-size:16px; line-height:24px; color:#202124; word-break:break-all;"><a href="mailto:{{ $email }}" style="color:#202124; text-decoration:none;">{{ $email }}</a></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 20px;"><div style="border-top:1px solid #e8eaed; font-size:0; line-height:0;">&nbsp;</div></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:14px 20px 6px; font-size:12px; line-height:16px; letter-spacing:0.3px; color:#5f6368;">Temporary password</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 20px 16px; font-size:16px; line-height:24px; color:#202124; font-family:'Roboto Mono',Menlo,Consolas,monospace; letter-spacing:1px;">{{ $password }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="padding-top:28px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                            <td style="background:#1a73e8; border-radius:4px;">
                                                <a href="{{ $signInUrl }}" style="display:inline-block; padding:10px 24px; font-size:14px; line-height:20px; font-weight:500; letter-spacing:0.25px; color:#ffffff; text-decoration:none;">Sign in</a>
                                            </td>
                                        </tr></table>
                                    </td>
                                </tr>
                                @if($mustChange)
                                    <tr>
                                        <td align="center" style="padding-top:14px; font-size:12px; line-height:18px; color:#5f6368;">You’ll be asked to choose a new password the first time you sign in.</td>
                                    </tr>
                                @endif

                                {{-- next steps --}}
                                <tr>
                                    <td style="padding-top:28px; font-size:16px; line-height:24px; color:#202124;">Next steps</td>
                                </tr>
                                @foreach([
                                    ['1', 'Sign in at mail.google.com with your new username and the temporary password above.'],
                                    ['2', 'Choose a new password that only you know. College staff will never ask for it.'],
                                    ['3', 'Use this address for all college emails from now on. Your personal email stays on your record for account recovery.'],
                                ] as [$n, $step])
                                    <tr>
                                        <td style="padding-top:12px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
                                                <td valign="top" width="32" style="width:32px;"><span style="display:inline-block; width:22px; height:22px; line-height:22px; border-radius:50%; background:#e8f0fe; color:#1967d2; font-size:12px; font-weight:500; text-align:center;">{{ $n }}</span></td>
                                                <td valign="top" style="font-size:14px; line-height:22px; color:#3c4043;">{{ $step }}</td>
                                            </tr></table>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- footer, outside the card, like Google's --}}
                    <tr>
                        <td align="center" style="padding:20px 8px 0; font-size:12px; line-height:18px; color:#5f6368;">
                            @if(!empty($sentTo))
                                This email was sent to {{ $sentTo }} because a {{ $companyName }} account was created for you.<br>
                            @endif
                            Keep your password private. If you did not expect this email, contact the college.
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:12px 8px 0; font-size:12px; line-height:18px; color:#5f6368;">
                            &copy; {{ date('Y') }} {{ $companyName }}, {{ $companyAddress }}<br>
                            <a href="tel:{{ $companyPhoneTel }}" style="color:#5f6368; text-decoration:none;">{{ $companyPhone }}</a> · <a href="https://lcc.ac.uk" style="color:#5f6368; text-decoration:none;">lcc.ac.uk</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
