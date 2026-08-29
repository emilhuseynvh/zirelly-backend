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
                                        &#128230;
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:16px 32px 8px;">
                            <h1 style="margin:0;font-size:34px;line-height:42px;font-weight:700;color:#B08A5C;">
                                Sifariş &#8470;{{ $order->id }}
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 40px 0;">
                            <p style="margin:0 0 20px;font-size:16px;line-height:24px;color:#1a1a1a;">
                                <strong>Hörmətli {{ trim(($order->user?->name ?? '').' '.($order->user?->surname ?? '')) ?: 'müştərimiz' }},</strong>
                            </p>

                            <p style="margin:0 0 20px;font-size:16px;line-height:26px;color:#333333;">
                                Təşəkkür edirik! Sizin {{ $order->created_at?->timezone('Asia/Baku')->format('d.m.Y H:i') }} tarixli
                                &#8470;{{ $order->id }} saylı sifarişiniz üçün ödəniş uğurla qəbul olundu.
                            </p>

                            <p style="margin:0 0 8px;font-size:16px;line-height:26px;color:#333333;">
                                Sifarişin statusu: <strong style="color:#1a1a1a;">Ödənilib</strong>
                            </p>

                            <p style="margin:0 0 20px;font-size:16px;line-height:26px;color:#333333;">
                                Sifarişin məbləği: <strong style="color:#1a1a1a;">{{ number_format((float) $order->total + (float) $order->delivery_fee, 2) }} AZN</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 40px;">
                            <p style="margin:0 0 10px;font-size:16px;line-height:24px;color:#333333;">Sifarişin tərkibi:</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td style="padding:8px 0;border-bottom:1px solid #f0e9df;font-size:15px;line-height:22px;color:#1a1a1a;">
                                            {{ $item->product?->translate('title', 'az') ?? $item->title }} — {{ $item->quantity }} ədəd
                                        </td>
                                        <td align="right" style="padding:8px 0;border-bottom:1px solid #f0e9df;font-size:15px;line-height:22px;color:#1a1a1a;white-space:nowrap;">
                                            {{ number_format((float) $item->line_total, 2) }} AZN
                                        </td>
                                    </tr>
                                @endforeach

                                @if ((float) $order->discount_amount > 0)
                                    <tr>
                                        <td style="padding:8px 0;font-size:14px;line-height:20px;color:#777777;">
                                            Endirim{{ $order->promocode_code ? ' ('.$order->promocode_code.')' : '' }}
                                        </td>
                                        <td align="right" style="padding:8px 0;font-size:14px;line-height:20px;color:#777777;white-space:nowrap;">
                                            &minus;{{ number_format((float) $order->discount_amount, 2) }} AZN
                                        </td>
                                    </tr>
                                @endif

                                @if ((float) $order->delivery_fee > 0)
                                    <tr>
                                        <td style="padding:8px 0;font-size:14px;line-height:20px;color:#777777;">Çatdırılma</td>
                                        <td align="right" style="padding:8px 0;font-size:14px;line-height:20px;color:#777777;white-space:nowrap;">
                                            {{ number_format((float) $order->delivery_fee, 2) }} AZN
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    @if ($order->address)
                        <tr>
                            <td style="padding:20px 40px 0;">
                                <p style="margin:0;font-size:15px;line-height:24px;color:#333333;">
                                    Çatdırılma ünvanı: <span style="color:#1a1a1a;">{{ $order->address }}</span>
                                </p>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:28px 40px 0;">
                            <p style="margin:0 0 6px;font-size:15px;line-height:24px;color:#555555;">
                                Sifarişiniz qısa zamanda hazırlanacaq və statusu dəyişdikcə onu izləyə biləcəksiniz.
                            </p>
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
