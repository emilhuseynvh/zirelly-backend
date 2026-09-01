<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sifariş №{{ $order->id }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f3f3;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f3f3;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td align="center" style="padding:40px 32px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="width:96px;height:96px;background-color:#f7f1e8;border-radius:48px;font-size:44px;line-height:96px;">
                                        &#128276;
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:16px 32px 8px;">
                            <h1 style="margin:0;font-size:30px;line-height:40px;font-weight:700;color:#B08A5C;">
                                Sifariş &#8470;{{ $order->id }} yeniləndi
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 40px 0;">
                            <p style="margin:0 0 20px;font-size:16px;line-height:24px;color:#1a1a1a;">
                                <strong>Hörmətli {{ trim(($order->user?->name ?? '').' '.($order->user?->surname ?? '')) ?: 'müştərimiz' }},</strong>
                            </p>

                            <p style="margin:0 0 20px;font-size:16px;line-height:26px;color:#333333;">
                                Sizin &#8470;{{ $order->id }} saylı sifarişinizin statusu dəyişdi.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:0 40px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f1e8;border-radius:12px;">
                                <tr>
                                    <td align="center" style="padding:20px 24px;">
                                        <p style="margin:0 0 4px;font-size:13px;line-height:18px;color:#8a7861;text-transform:uppercase;letter-spacing:1px;">
                                            Yeni status
                                        </p>
                                        <p style="margin:0;font-size:22px;line-height:30px;font-weight:700;color:#755C44;">
                                            {{ $order->status->labelAz() }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 40px 0;">
                            <p style="margin:0;font-size:15px;line-height:24px;color:#555555;">
                                Sifarişinizin gedişatını saytımızda öz şəxsi kabinetinizdən izləyə bilərsiniz.
                                Hər hansı sualınız yaranarsa, komandamız sizə kömək etməyə hazırdır!
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:32px 40px 8px;">
                            <a href="https://zirelly.az/profil?tab=orders"
                               style="display:inline-block;background-color:#755C44;color:#ffffff;text-decoration:none;font-size:17px;font-weight:600;line-height:24px;padding:14px 48px;border-radius:28px;">
                                Şəxsi kabinetə giriş
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 40px 32px;">
                            <p style="margin:0;padding-top:16px;border-top:1px solid #eeeeee;font-size:12px;line-height:18px;color:#aaaaaa;text-align:center;">
                                Zirelly.az — gözəllik və qulluq məhsulları
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
