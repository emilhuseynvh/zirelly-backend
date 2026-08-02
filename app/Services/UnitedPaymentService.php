<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class UnitedPaymentService
{
    private const TOKEN_CACHE_KEY = 'unitedpayment.auth_token';

    private const TOKEN_TTL_MINUTES = 50;

    public function createCheckout(array $payload): array
    {
        $response = $this->request()->post('/api/transactions/checkout', $payload);

        if ($response->status() === 401) {
            $response = $this->request(freshToken: true)->post('/api/transactions/checkout', $payload);
        }

        if ($response->failed() || blank($response->json('url'))) {
            throw new RuntimeException('United Payment checkout failed: '.$this->safeErrorSummary($response));
        }

        return $response->json();
    }

    public function transactionStatus(string $clientOrderId): array
    {
        $response = $this->request()->post('/api/transactions/transaction-status-by-order-id', [
            'clientOrderId' => $clientOrderId,
        ]);

        if ($response->status() === 401) {
            $response = $this->request(freshToken: true)->post('/api/transactions/transaction-status-by-order-id', [
                'clientOrderId' => $clientOrderId,
            ]);
        }

        if ($response->failed()) {
            throw new RuntimeException('United Payment status check failed: '.$this->safeErrorSummary($response));
        }

        return $response->json() ?? [];
    }

    public function resolveStatus(array $statusPayload): TransactionStatus
    {
        $raw = strtolower((string) (
            $statusPayload['orderStatus']
            ?? $statusPayload['transactionStatus']
            ?? $statusPayload['status']
            ?? ''
        ));

        return match (true) {
            in_array($raw, ['approved', 'success', 'successful', 'completed', 'paid', 'fullypaid'], true) => TransactionStatus::Success,
            in_array($raw, ['declined', 'decline', 'failed', 'error', 'cancelled', 'canceled', 'rejected', 'reversed', 'refunded', 'expired', 'timeout'], true) => TransactionStatus::Failed,
            default => TransactionStatus::Pending,
        };
    }

    public function checkoutLanguage(): string
    {
        return match (app()->getLocale()) {
            'az' => 'AZ',
            'ru' => 'RU',
            default => 'EN',
        };
    }

    private function request(bool $freshToken = false): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->withHeaders(['x-auth-token' => $this->token($freshToken)]);
    }

    private function token(bool $fresh = false): string
    {
        if ($fresh) {
            Cache::forget(self::TOKEN_CACHE_KEY);
        }

        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if (is_string($cached)) {
            try {
                return Crypt::decryptString($cached);
            } catch (DecryptException) {
                Cache::forget(self::TOKEN_CACHE_KEY);
            }
        }

        $response = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->post('/api/auth/', [
                'email' => config('services.unitedpayment.email'),
                'password' => config('services.unitedpayment.password'),
            ]);

        $token = $response->json('token');

        if ($response->failed() || blank($token)) {
            throw new RuntimeException('United Payment authentication failed.');
        }

        Cache::put(self::TOKEN_CACHE_KEY, Crypt::encryptString($token), now()->addMinutes(self::TOKEN_TTL_MINUTES));

        return $token;
    }

    private function baseUrl(): string
    {
        $url = rtrim((string) config('services.unitedpayment.base_url'), '/');

        if (! str_starts_with($url, 'https://')) {
            throw new RuntimeException('United Payment base URL must use HTTPS.');
        }

        return $url;
    }

    private function safeErrorSummary(Response $response): string
    {
        $message = $response->json('message') ?? $response->json('title') ?? 'Unknown error';

        return 'HTTP '.$response->status().' - '.$message;
    }
}
