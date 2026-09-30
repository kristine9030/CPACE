{{--
    Shared shell for every CPAce email.

    Logos are embedded inline (CID) when the view is rendered by the mailer,
    so they show up even when APP_URL isn't publicly reachable. When the view
    is rendered outside a send (e.g. Mailable::render() in a preview) there's
    no $message, so fall back to the public asset URL.

    Sections: title, preheader, icon (optional emoji), content, note (optional
    muted footer line inside the card).
--}}
@php
    $crestPath = public_path('images/CPAce Logo (3).png');
    $wordmarkPath = public_path('images/wordmark-transparent.png');
    $canEmbed = isset($message) && method_exists($message, 'embed');
    $crestSrc = $canEmbed ? $message->embed($crestPath) : asset('images/' . rawurlencode('CPAce Logo (3).png'));
    $wordmarkSrc = $canEmbed ? $message->embed($wordmarkPath) : asset('images/wordmark-transparent.png');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <title>@yield('title', 'CPAce')</title>
</head>
<body style="margin:0; padding:0; background:#f4efee; font-family:'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased;">
    {{-- Inbox preview text --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f4efee;">
        @yield('preheader')&#8203;&nbsp;&#8203;&nbsp;&#8203;&nbsp;&#8203;&nbsp;&#8203;&nbsp;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4efee; padding:36px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                    {{-- Brand header --}}
                    <tr>
                        <td align="center" style="padding:0 0 22px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle; padding-right:12px;">
                                        <img src="{{ $crestSrc }}" width="46" height="48" alt="CPAce crest" style="display:block; border:0; width:46px; height:48px;">
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <img src="{{ $wordmarkSrc }}" width="140" height="34" alt="CPAce" style="display:block; border:0; width:140px; height:34px;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(74,16,16,0.08); border:1px solid #efe4e3;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="height:6px; line-height:6px; font-size:0; background:#7B1D1D; background-image:linear-gradient(90deg, #3a0f0f 0%, #7B1D1D 50%, #a33a3a 100%); border-radius:16px 16px 0 0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:36px 40px 8px;">
                                        @hasSection('icon')
                                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                                                <tr>
                                                    <td align="center" valign="middle" style="width:52px; height:52px; border-radius:14px; background:#f8eceb; font-size:24px; line-height:52px;">@yield('icon')</td>
                                                </tr>
                                            </table>
                                        @endif
                                        @yield('content')
                                    </td>
                                </tr>
                                @hasSection('note')
                                    <tr>
                                        <td style="padding:20px 40px; background:#faf6f5; border-top:1px solid #f0e6e5; border-radius:0 0 16px 16px;">
                                            <p style="margin:0; font-size:12px; color:#8a7c7b; line-height:1.65;">@yield('note')</p>
                                        </td>
                                    </tr>
                                @else
                                    <tr><td style="height:28px; line-height:28px; font-size:0;">&nbsp;</td></tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:26px 24px 0;">
                            <p style="margin:0 0 6px; font-size:12px; font-weight:600; color:#7B1D1D; letter-spacing:0.4px;">CPAce &middot; CPA Licensure Exam Reviewer</p>
                            <p style="margin:0; font-size:11px; color:#a89a99; line-height:1.6;">
                                This is an automated message from CPAce. Please don't share it with anyone.<br>
                                &copy; {{ date('Y') }} CPAce CPA Reviewer. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
