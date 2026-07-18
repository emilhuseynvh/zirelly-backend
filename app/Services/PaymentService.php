<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Order;
use App\Models\Transaction;

class PaymentService
{
    /**
     * Charge the given order.
     *
     * NOTE: real payment gateway integration goes here later.
     * For now this is a stub that always succeeds.
     */
    public function charge(Order $order): Transaction
    {
        return $order->transactions()->create([
            'status' => TransactionStatus::Success,
            'method' => 'stub',
            'amount' => $order->total,
            'reference' => 'stub-'.uniqid(),
        ]);
    }
}
