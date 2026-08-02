<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function notifyPayment(Order $order, TransactionStatus $status, ?string $reason = null): void
    {
        $this->send($this->buildPaymentMessage($order, $status, $reason));
    }

    private function buildPaymentMessage(Order $order, TransactionStatus $status, ?string $reason): string
    {
        $user = $order->user;

        $customer = trim(($user?->name ?? '').' '.($user?->surname ?? ''));

        $header = match ($status) {
            TransactionStatus::Success => '✅ <b>Ödəniş uğurla tamamlandı</b>',
            TransactionStatus::Failed => '❌ <b>Ödəniş uğursuz oldu</b>',
            TransactionStatus::Pending => '⏳ <b>Ödəniş gözləmədədir</b>',
        };

        $lines = [
            $header,
            '',
            '🧾 Sifariş: <b>#'.$order->id.'</b>',
            '💰 Məbləğ: <b>'.number_format((float) $order->total, 2).' AZN</b>',
        ];

        if ($customer !== '') {
            $lines[] = '👤 Müştəri: '.$this->escape($customer);
        }

        if (filled($user?->phone)) {
            $lines[] = '📞 Telefon: '.$this->escape($user->phone);
        }

        if (filled($user?->email)) {
            $lines[] = '📧 E-poçt: '.$this->escape($user->email);
        }

        if ($order->promocode_code) {
            $lines[] = '🎟 Promokod: '.$this->escape($order->promocode_code);
        }

        $lines[] = '🕒 Tarix: '.now()->timezone('Asia/Baku')->format('d.m.Y H:i');

        if ($status === TransactionStatus::Failed) {
            $lines[] = '';
            $lines[] = 'ℹ️ Səbəb: '.$this->escape($reason ?: 'Ödəniş bank tərəfindən təsdiqlənmədi');
        }

        return implode("\n", $lines);
    }

    private function send(string $text): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (blank($token) || blank($chatId)) {
            Log::warning('Telegram notification skipped: bot is not configured.');

            return;
        }

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->asJson()
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            if ($response->failed()) {
                Log::warning('Telegram notification failed', [
                    'status' => $response->status(),
                    'description' => $response->json('description'),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Telegram notification error', ['error' => $e->getMessage()]);
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
