<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Mail\OrderReceiptMail;
use App\Models\Order;
use App\Models\Promocode;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function store(CheckoutRequest $request, PaymentService $payments): JsonResponse
    {
        $user = $request->user();

        $basketItems = $user->basketItems()
            ->with(['product.translations'])
            ->get()
            ->filter(fn ($item) => $item->product !== null && $item->product->is_active);

        if ($basketItems->isEmpty()) {
            return response()->json(['message' => __('messages.basket_empty')], 422);
        }

        $subtotal = round($basketItems->sum(fn ($item) => $item->lineTotal()), 2);

        $promocode = null;
        $discount = 0.0;

        if ($request->filled('promocode')) {
            $promocode = Promocode::where('code', $request->input('promocode'))->first();

            if ($promocode === null) {
                return response()->json(['message' => __('messages.promocode_not_found')], 422);
            }

            if (($error = $promocode->validateFor($user)) !== null) {
                return response()->json(['message' => Promocode::errorMessage($error)], 422);
            }

            $discount = $promocode->discountFor($subtotal);
        }

        $order = DB::transaction(function () use ($user, $basketItems, $subtotal, $discount, $promocode) {
            $order = $user->orders()->create([
                'status' => OrderStatus::Pending,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total' => round($subtotal - $discount, 2),
                'promocode_id' => $promocode?->id,
                'promocode_code' => $promocode?->code,
            ]);

            foreach ($basketItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'title' => $item->product->translate('title') ?? $item->product->slug,
                    'unit_price' => $item->product->finalPrice(),
                    'quantity' => $item->quantity,
                    'line_total' => $item->lineTotal(),
                ]);
            }

            return $order;
        });

        $transaction = $payments->charge($order);

        if ($transaction->status === TransactionStatus::Success) {
            $order->update([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
            ]);

            $user->basketItems()->delete();
        }

        $order->load(['user', 'items', 'transactions']);

        if ($order->status === OrderStatus::Paid) {
            try {
                Mail::to($user->email)->send(new OrderReceiptMail($order));
            } catch (\Throwable $e) {
                Log::warning('Order receipt email failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return (new OrderResource($order))->response()->setStatusCode(201);
    }
}
