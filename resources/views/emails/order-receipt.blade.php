<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sifariş qəbzi</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#18181b;padding:24px 32px;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;">Zirelly</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 4px;font-size:18px;color:#18181b;">Sifarişiniz üçün təşəkkürlər!</h2>
                            <p style="margin:0 0 24px;color:#71717a;font-size:14px;">
                                Hörmətli {{ $order->user->name }}, sifarişiniz uğurla qəbul edildi.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td style="padding:12px 16px;background-color:#fafafa;border-radius:8px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="color:#71717a;font-size:13px;">Sifariş nömrəsi</td>
                                                <td align="right" style="color:#18181b;font-size:13px;font-weight:600;">#{{ $order->id }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#71717a;font-size:13px;padding-top:6px;">Tarix</td>
                                                <td align="right" style="color:#18181b;font-size:13px;padding-top:6px;">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                            </tr>
                                            @if ($order->paid_at)
                                            <tr>
                                                <td style="color:#71717a;font-size:13px;padding-top:6px;">Ödəniş</td>
                                                <td align="right" style="color:#16a34a;font-size:13px;font-weight:600;padding-top:6px;">Ödənilib</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
                                <tr>
                                    <th align="left" style="padding:8px 0;border-bottom:2px solid #e4e4e7;color:#71717a;font-size:12px;text-transform:uppercase;">Məhsul</th>
                                    <th align="center" style="padding:8px 0;border-bottom:2px solid #e4e4e7;color:#71717a;font-size:12px;text-transform:uppercase;">Say</th>
                                    <th align="right" style="padding:8px 0;border-bottom:2px solid #e4e4e7;color:#71717a;font-size:12px;text-transform:uppercase;">Qiymət</th>
                                    <th align="right" style="padding:8px 0;border-bottom:2px solid #e4e4e7;color:#71717a;font-size:12px;text-transform:uppercase;">Cəmi</th>
                                </tr>
                                @foreach ($order->items as $item)
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #f4f4f5;color:#18181b;font-size:14px;">{{ $item->title }}</td>
                                    <td align="center" style="padding:12px 0;border-bottom:1px solid #f4f4f5;color:#71717a;font-size:14px;">{{ $item->quantity }}</td>
                                    <td align="right" style="padding:12px 0;border-bottom:1px solid #f4f4f5;color:#71717a;font-size:14px;">{{ number_format((float) $item->unit_price, 2) }} ₼</td>
                                    <td align="right" style="padding:12px 0;border-bottom:1px solid #f4f4f5;color:#18181b;font-size:14px;font-weight:600;">{{ number_format((float) $item->line_total, 2) }} ₼</td>
                                </tr>
                                @endforeach
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#71717a;font-size:14px;padding:4px 0;">Ara cəm</td>
                                    <td align="right" style="color:#18181b;font-size:14px;padding:4px 0;">{{ number_format((float) $order->subtotal, 2) }} ₼</td>
                                </tr>
                                @if ((float) $order->discount_amount > 0)
                                <tr>
                                    <td style="color:#71717a;font-size:14px;padding:4px 0;">
                                        Endirim @if ($order->promocode_code) ({{ $order->promocode_code }}) @endif
                                    </td>
                                    <td align="right" style="color:#16a34a;font-size:14px;padding:4px 0;">−{{ number_format((float) $order->discount_amount, 2) }} ₼</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="color:#18181b;font-size:16px;font-weight:700;padding:12px 0 0;border-top:2px solid #e4e4e7;">Yekun məbləğ</td>
                                    <td align="right" style="color:#18181b;font-size:16px;font-weight:700;padding:12px 0 0;border-top:2px solid #e4e4e7;">{{ number_format((float) $order->total, 2) }} ₼</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;background-color:#fafafa;border-top:1px solid #f4f4f5;">
                            <p style="margin:0;color:#a1a1aa;font-size:12px;text-align:center;">
                                Bu email avtomatik göndərilib. Suallarınız üçün bizimlə əlaqə saxlayın.<br>
                                © {{ date('Y') }} Zirelly. Bütün hüquqlar qorunur.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
