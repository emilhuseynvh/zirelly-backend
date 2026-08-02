<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\TransactionStatus;
use App\Mail\OrderReceiptMail;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly UnitedPaymentService $gateway,
        private readonly TelegramService $telegram,
    ) {}

    public function initiate(Order $order): array
    {
        $order->loadMissing('user');

        $clientOrderId = 'ZRL-'.$order->id.'-'.Str::upper(Str::random(8));
        $returnToken = Str::random(48);

        $transaction = $order->transactions()->create([
            'status' => TransactionStatus::Pending,
            'method' => 'unitedpayment',
            'amount' => $order->total,
            'reference' => $clientOrderId,
            'payload' => ['return_token_hash' => hash('sha256', $returnToken)],
        ]);

        $returnUrl = route('payments.united.return', [
            'transaction' => $transaction->id,
            'token' => $returnToken,
        ]);

        try {
            $checkout = $this->gateway->createCheckout($this->checkoutPayload($order, $clientOrderId, $returnUrl));
        } catch (\Throwable $e) {
            Log::error('United Payment initiation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            $transaction->update(['status' => TransactionStatus::Failed]);
            $order->update(['status' => OrderStatus::Cancelled]);

            $this->telegram->notifyPayment($order, TransactionStatus::Failed, 'Ödəniş sisteminə qoşulmaq mümkün olmadı');

            throw $e;
        }

        $transaction->update([
            'payload' => array_merge($transaction->payload ?? [], [
                'checkout' => [
                    'transactionId' => $checkout['transactionId'] ?? null,
                    'transactionType' => $checkout['transactionType'] ?? null,
                    'status' => $checkout['status'] ?? null,
                ],
            ]),
        ]);

        return [$transaction, (string) $checkout['url']];
    }

    public function settle(Transaction $transaction): Transaction
    {
        if ($transaction->method !== 'unitedpayment' || blank($transaction->reference)) {
            return $transaction;
        }

        try {
            $statusPayload = $this->gateway->transactionStatus($transaction->reference);
        } catch (\Throwable $e) {
            Log::error('United Payment status verification failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return $transaction->refresh();
        }

        $status = $this->gateway->resolveStatus($statusPayload);

        if ($status === TransactionStatus::Pending) {
            return $transaction->refresh();
        }

        [$settled, $justSettled] = DB::transaction(function () use ($transaction, $status, $statusPayload): array {
            $locked = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== TransactionStatus::Pending) {
                return [$locked, false];
            }

            $locked->update([
                'status' => $status,
                'payload' => array_merge($locked->payload ?? [], ['result' => $statusPayload]),
            ]);

            $order = $locked->order()->lockForUpdate()->first();

            if ($status === TransactionStatus::Success) {
                $order->update([
                    'status' => OrderStatus::Paid,
                    'paid_at' => now(),
                ]);
            } elseif ($order->status === OrderStatus::Pending) {
                $order->update(['status' => OrderStatus::Cancelled]);
            }

            return [$locked, true];
        });

        if ($justSettled) {
            $this->afterSettlement($settled->fresh('order'));
        }

        return $settled->refresh();
    }

    private function afterSettlement(Transaction $transaction): void
    {
        $order = $transaction->order->load(['user', 'items']);

        if ($transaction->status === TransactionStatus::Success) {
            $order->user?->basketItems()->delete();

            $this->telegram->notifyPayment($order, TransactionStatus::Success);

            if (filled($order->user?->email)) {
                try {
                    Mail::to($order->user->email)->send(new OrderReceiptMail($order));
                } catch (\Throwable $e) {
                    Log::warning('Order receipt email failed', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return;
        }

        $this->telegram->notifyPayment($order, TransactionStatus::Failed, 'Ödəniş bank tərəfindən təsdiqlənmədi');
    }

    private function checkoutPayload(Order $order, string $clientOrderId, string $returnUrl): array
    {
        $user = $order->user;

        $payload = [
            'amount' => round((float) $order->total, 2),
            'language' => $this->gateway->checkoutLanguage(),
            'successUrl' => $returnUrl,
            'cancelUrl' => $returnUrl,
            'declineUrl' => $returnUrl,
            'clientOrderId' => $clientOrderId,
            'currency' => 'AZN',
            'description' => 'Zirelly sifariş #'.$order->id,
        ];

        if (filled($user?->email)) {
            $payload['email'] = $user->email;
        }

        if (filled($user?->phone)) {
            $payload['phoneNumber'] = $user->phone;
        }

        $clientName = trim(($user?->name ?? '').' '.($user?->surname ?? ''));

        if ($clientName !== '') {
            $payload['clientName'] = $clientName;
        }

        if (filled(config('services.unitedpayment.partner_id'))) {
            $payload['partnerId'] = (string) config('services.unitedpayment.partner_id');
        }

        if (str_starts_with((string) config('app.url'), 'https://')) {
            $payload['webhookUrl'] = route('payments.united.webhook');
        }

        return $payload;
    }
}
