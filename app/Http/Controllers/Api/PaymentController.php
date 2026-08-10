<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function handleReturn(Request $request, Transaction $transaction, PaymentService $payments): RedirectResponse
    {
        $frontendUrl = rtrim((string) config('services.frontend.url'), '/');

        $providedToken = Str::before(trim((string) $request->query('token')), '?');
        $expectedHash = (string) data_get($transaction->payload, 'return_token_hash');
        $providedHash = hash('sha256', $providedToken);

        if ($expectedHash === '' || ! hash_equals($expectedHash, $providedHash)) {
            return redirect()->away($frontendUrl.'/odenis-neticesi?status=pending');
        }

        $transaction = $payments->settle($transaction);

        $status = match ($transaction->status) {
            TransactionStatus::Success => 'success',
            TransactionStatus::Failed => 'failed',
            TransactionStatus::Pending => 'pending',
        };

        return redirect()->away(
            $frontendUrl.'/odenis-neticesi?'.http_build_query([
                'order' => $transaction->order_id,
                'status' => $status,
            ]),
        );
    }

    public function webhook(Request $request, PaymentService $payments): JsonResponse
    {
        $clientOrderId = (string) (
            $request->input('clientOrderId')
            ?? $request->input('ClientOrderId')
            ?? $request->input('orderId')
            ?? ''
        );

        if ($clientOrderId !== '' && str_starts_with($clientOrderId, 'ZRL-')) {
            $transaction = Transaction::query()
                ->where('method', 'unitedpayment')
                ->where('reference', $clientOrderId)
                ->first();

            if ($transaction !== null) {
                $payments->settle($transaction);
            }
        }

        return response()->json(['ok' => true]);
    }
}
