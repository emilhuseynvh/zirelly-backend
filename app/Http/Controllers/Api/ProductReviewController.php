<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ProductReviewResource;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends Controller
{
    public function index(Request $request, Product $product): AnonymousResourceCollection
    {
        $reviews = $product->reviews()
            ->with('user')
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return ProductReviewResource::collection($reviews);
    }

    public function store(StoreReviewRequest $request, Product $product): ProductReviewResource
    {
        $alreadyReviewed = $product->reviews()
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($alreadyReviewed) {
            throw ValidationException::withMessages([
                'product' => [__('messages.already_reviewed')],
            ]);
        }

        $review = $product->reviews()->create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return new ProductReviewResource($review->load('user'));
    }

    public function update(UpdateReviewRequest $request, ProductReview $review): ProductReviewResource
    {
        abort_unless($review->user_id === $request->user()->id, 403);

        $review->update($request->validated());

        return new ProductReviewResource($review->load('user'));
    }

    public function destroy(Request $request, ProductReview $review): Response
    {
        abort_unless(
            $review->user_id === $request->user()->id || $request->user()->isAdmin(),
            403,
        );

        $review->delete();

        return response()->noContent();
    }
}