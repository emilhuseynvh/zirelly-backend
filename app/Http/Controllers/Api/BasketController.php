<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBasketItemRequest;
use App\Http\Requests\UpdateBasketItemRequest;
use App\Http\Resources\BasketItemResource;
use App\Models\BasketItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class BasketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()
            ->basketItems()
            ->with(['product.translations', 'product.images'])
            ->latest('id')
            ->get();

        return $this->basketResponse($items);
    }

    public function storeItem(StoreBasketItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $quantity = $data['quantity'] ?? 1;

        $item = $request->user()
            ->basketItems()
            ->where('product_id', $data['product_id'])
            ->first();

        if ($item !== null) {
            $item->update(['quantity' => min($item->quantity + $quantity, 100)]);
        } else {
            $item = $request->user()->basketItems()->create([
                'product_id' => $data['product_id'],
                'quantity' => $quantity,
            ]);
        }

        $item->load(['product.translations', 'product.images']);

        return (new BasketItemResource($item))->response()->setStatusCode(201);
    }

    public function updateItem(UpdateBasketItemRequest $request, BasketItem $item): BasketItemResource
    {
        abort_unless($item->user_id === $request->user()->id, 403);

        $item->update($request->validated());

        return new BasketItemResource($item->load(['product.translations', 'product.images']));
    }

    public function destroyItem(Request $request, BasketItem $item): Response
    {
        abort_unless($item->user_id === $request->user()->id, 403);

        $item->delete();

        return response()->noContent();
    }

    public function clear(Request $request): Response
    {
        $request->user()->basketItems()->delete();

        return response()->noContent();
    }

    protected function basketResponse(Collection $items): JsonResponse
    {
        return response()->json([
            'data' => [
                'items' => BasketItemResource::collection($items),
                'summary' => [
                    'items_count' => $items->count(),
                    'quantity' => $items->sum('quantity'),
                    'subtotal' => round($items->sum(fn (BasketItem $item) => $item->lineTotal()), 2),
                ],
            ],
        ]);
    }
}