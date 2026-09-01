<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProductsPageRequest;
use App\Http\Resources\ProductsPageResource;
use App\Models\ProductsPage;
use Illuminate\Support\Facades\DB;

class ProductsPageController extends Controller
{
    public function show(): ProductsPageResource
    {
        return new ProductsPageResource($this->loadRelations(ProductsPage::current()));
    }

    public function update(UpdateProductsPageRequest $request): ProductsPageResource
    {
        $data = $request->validated();
        $page = ProductsPage::current();

        DB::transaction(function () use ($data, $page) {
            $page->update(
                collect($data)->only(['side_image_id', 'og_image_id'])->all(),
            );

            if (! empty($data['translations'])) {
                $page->syncTranslations($data['translations']);
            }

            if (array_key_exists('slides', $data)) {
                $page->slides()->get()->each->delete();

                foreach ($data['slides'] ?? [] as $index => $row) {
                    $slide = $page->slides()->create([
                        'image_id' => $row['image_id'] ?? null,
                        'link' => $row['link'] ?? null,
                        'position' => $index,
                    ]);

                    $slide->syncTranslations($row['translations'] ?? []);
                }
            }
        });

        return new ProductsPageResource($this->loadRelations($page));
    }

    protected function loadRelations(ProductsPage $page): ProductsPage
    {
        return $page->load([
            'translations',
            'slides.translations',
            'slides.image',
            'sideImage',
            'ogImage',
        ]);
    }
}