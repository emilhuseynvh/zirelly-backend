<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Language;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with(['translations', 'images'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->boolean('active'), fn ($q) => $q->active())
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        $data = $request->validated();
        $defaultCode = Language::defaultLanguage()->code;

        $product = DB::transaction(function () use ($data, $defaultCode) {
            $product = Product::create([
                'slug' => $data['slug'] ?? Product::generateUniqueSlug($data['translations'][$defaultCode]['title']),
                'price' => $data['price'],
                'discount' => $data['discount'] ?? null,
                'discount_type' => $data['discount_type'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $product->syncTranslations($data['translations']);
            $this->syncImages($product, $data['image_ids'] ?? []);
            $this->syncFeatures($product, $data['features'] ?? null);

            return $product;
        });

        return new ProductResource($this->loadRelations($product));
    }

    public function show(Request $request, Product $product): ProductResource
    {
        $request->user('sanctum')?->recordProductView($product);

        return new ProductResource($this->loadRelations($product));
    }

    public function showBySlug(Request $request, string $slug): ProductResource
    {
        $product = Product::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $request->user('sanctum')?->recordProductView($product);

        return new ProductResource($this->loadRelations($product));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $product) {
            $product->update(
                collect($data)->only(['slug', 'price', 'discount', 'discount_type', 'is_active'])->all(),
            );

            if (! empty($data['translations'])) {
                $product->syncTranslations($data['translations']);
            }

            if (array_key_exists('image_ids', $data)) {
                $this->syncImages($product, $data['image_ids'] ?? []);
            }

            if (array_key_exists('features', $data)) {
                $this->syncFeatures($product, $data['features'] ?? []);
            }
        });

        return new ProductResource($this->loadRelations($product));
    }

    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }

    protected function syncImages(Product $product, array $imageIds): void
    {
        $product->images()->sync(
            collect($imageIds)
                ->mapWithKeys(fn (int $id, int $index) => [$id => ['position' => $index]])
                ->all(),
        );
    }

    protected function syncFeatures(Product $product, ?array $features): void
    {
        if ($features === null) {
            return;
        }

        $product->features()->get()->each->delete();

        foreach ($features as $index => $feature) {
            $product->features()
                ->create(['position' => $index])
                ->syncTranslations($feature['translations']);
        }
    }

    protected function loadRelations(Product $product): Product
    {
        return $product
            ->load(['translations', 'images', 'features.translations'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');
    }
}