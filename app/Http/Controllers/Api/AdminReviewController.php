<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductReviewResource;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AdminReviewController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $reviews = ProductReview::query()
            ->with(['user', 'product.translations'])
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where('status', $request->input('status')),
            )
            ->when(
                $request->filled('product_id'),
                fn ($q) => $q->where('product_id', $request->integer('product_id')),
            )
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ProductReviewResource::collection($reviews);
    }

    public function update(Request $request, ProductReview $review): ProductReviewResource
    {
        $data = $request->validate([
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'comment' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status' => ['sometimes', Rule::in(ProductReview::STATUSES)],
        ]);

        $review->update($data);

        return new ProductReviewResource($review->load(['user', 'product.translations']));
    }

    public function destroy(ProductReview $review): Response
    {
        $review->delete();

        return response()->noContent();
    }
}
