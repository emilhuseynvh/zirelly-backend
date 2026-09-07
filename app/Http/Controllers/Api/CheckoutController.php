<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Promocode;
use App\Services\PaymentService;
use App\Support\DeliveryLocations;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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

            // Öz endirimi olan məhsulun üstünə promokod gəlmir
            if ($basketItems->contains(fn ($item) => $item->product->hasDiscount())) {
                return response()->json(['message' => __('messages.promocode_discounted_products')], 422);
            }

            $discount = $promocode->discountFor($subtotal);
        }

        // Struktur ünvan gələndə vahid sətir server tərəfdə qurulur
        $address = $request->filled('address_city')
            ? DeliveryLocations::compose(
                $request->input('address_city'),
                $request->input('address_district'),
                $request->input('address_street'),
                $request->input('address_building'),
                $request->input('address_apartment'),
            )
            : $request->input('address');

        $order = DB::transaction(function () use ($request, $user, $basketItems, $subtotal, $discount, $promocode, $address) {
            $order = $user->orders()->create([
                'status' => OrderStatus::Pending,
                'address' => $address,
                'address_city' => $request->input('address_city'),
                'address_district' => $request->input('address_district'),
                'address_street' => $request->input('address_street'),
                'address_building' => $request->input('address_building'),
                'address_apartment' => $request->input('address_apartment'),
                'address_note' => $request->input('address_note'),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total' => round($subtotal - $discount, 2),
                'promocode_id' => $promocode?->id,
                'promocode_code' => $promocode?->code,
            ]);

            foreach ($basketItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'title' => $item->product->translate('title', 'az') ?? $item->product->translate('title') ?? $item->product->slug,
                    'unit_price' => $item->product->finalPrice(),
                    'quantity' => $item->quantity,
                    'line_total' => $item->lineTotal(),
                ]);
            }

            return $order;
        });

        try {
            [, $paymentUrl] = $payments->initiate($order);
        } catch (\Throwable $e) {
            return response()->json(['message' => __('messages.payment_init_failed')], 502);
        }

        $order->load(['user', 'items', 'transactions']);

        return (new OrderResource($order))
            ->additional(['payment_url' => $paymentUrl])
            ->response()
            ->setStatusCode(201);
    }
}
