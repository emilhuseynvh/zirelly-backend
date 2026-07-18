<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromocodeRequest;
use App\Http\Requests\UpdatePromocodeRequest;
use App\Http\Resources\PromocodeResource;
use App\Models\Promocode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PromocodeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $promocodes = Promocode::query()
            ->withCount(['orders' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->withSum(['orders' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'discount_amount')
            ->latest('id')
            ->paginate(20);

        return PromocodeResource::collection($promocodes);
    }

    public function store(StorePromocodeRequest $request): JsonResponse
    {
        $promocode = Promocode::create($request->validated());

        return (new PromocodeResource($promocode))->response()->setStatusCode(201);
    }

    public function show(Promocode $promocode): PromocodeResource
    {
        $promocode->loadCount(['orders' => fn ($q) => $q->where('status', '!=', 'cancelled')]);
        $promocode->loadSum(['orders' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'discount_amount');

        return new PromocodeResource($promocode);
    }

    public function update(UpdatePromocodeRequest $request, Promocode $promocode): PromocodeResource
    {
        $promocode->update($request->validated());

        return new PromocodeResource($promocode);
    }

    public function destroy(Promocode $promocode): Response
    {
        $promocode->delete();

        return response()->noContent();
    }

    /**
     * Validate a promocode against the current user's basket (used on checkout page).
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:50']]);

        $user = $request->user();
        $code = strtoupper(trim((string) $request->input('code')));

        $promocode = Promocode::where('code', $code)->first();

        if ($promocode === null) {
            return response()->json(['valid' => false, 'message' => 'Promocode not found.'], 422);
        }

        if (($error = $promocode->validateFor($user)) !== null) {
            return response()->json(['valid' => false, 'message' => Promocode::errorMessage($error)], 422);
        }

        $subtotal = round(
            $user->basketItems()
                ->with('product')
                ->get()
                ->filter(fn ($item) => $item->product !== null && $item->product->is_active)
                ->sum(fn ($item) => $item->lineTotal()),
            2,
        );

        $discount = $promocode->discountFor($subtotal);

        return response()->json([
            'valid' => true,
            'code' => $promocode->code,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => round($subtotal - $discount, 2),
        ]);
    }
}
