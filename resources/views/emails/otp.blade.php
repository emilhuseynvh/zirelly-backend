@php
    $intro = $type === 'reset_password'
        ? __('emails.reset_password_intro', [], $locale)
        : __('emails.register_intro', [], $locale);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zirelly</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f3f3;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f3f3;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:32px 32px 8px;">
                            <div style="font-size:22px;font-weight:700;letter-spacing:2px;color:#755C44;">ZIRELLY</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 0;">
                            <p style="margin:0 0 8px;font-size:16px;font-weight:600;">{{ __('emails.greeting', [], $locale) }}</p>
                            <p style="margin:0;font-size:14px;line-height:22px;color:#555;">{{ $intro }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;">
                            <div style="background-color:#f7f1e8;border-radius:12px;padding:20px;text-align:center;">
                                <span style="font-size:34px;font-weight:700;letter-spacing:10px;color:#755C44;">{{ $code }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 8px;">
                            <p style="margin:0;font-size:13px;color:#888;">{{ __('emails.expires', ['minutes' => 10], $locale) }}</p>
                            <p style="margin:8px 0 0;font-size:13px;color:#888;">{{ __('emails.ignore', [], $locale) }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px 32px;border-top:1px solid #eee;">
                            <p style="margin:16px 0 0;font-size:12px;color:#aaa;">{{ __('emails.footer', [], $locale) }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
