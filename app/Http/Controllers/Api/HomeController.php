<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateHomeRequest;
use App\Http\Resources\HomeResource;
use App\Models\HomePage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function show(): HomeResource
    {
        return new HomeResource($this->loadRelations(HomePage::current()));
    }

    public function update(UpdateHomeRequest $request): HomeResource
    {
        $data = $request->validated();
        $page = HomePage::current();

        DB::transaction(function () use ($data, $page) {
            $page->update(
                collect($data)->only(['banner_image_id', 'banner_link', 'og_image_id'])->all(),
            );

            if (! empty($data['translations'])) {
                $page->syncTranslations($data['translations']);
            }

            $this->syncChildren($page->slides(), $data, 'slides', ['image_id', 'link']);
            $this->syncChildren($page->stats(), $data, 'stats', ['value']);
            $this->syncChildren($page->testimonials(), $data, 'testimonials', ['image_id', 'name', 'rating']);
            $this->syncChildren($page->faqs(), $data, 'faqs', []);
        });

        return new HomeResource($this->loadRelations($page));
    }

    protected function syncChildren(HasMany $relation, array $data, string $key, array $columns): void
    {
        if (! array_key_exists($key, $data)) {
            return;
        }

        $relation->get()->each->delete();

        foreach ($data[$key] ?? [] as $index => $row) {
            $child = $relation->create([
                ...collect($row)->only($columns)->all(),
                'position' => $index,
            ]);

            $child->syncTranslations($row['translations'] ?? []);
        }
    }

    protected function loadRelations(HomePage $page): HomePage
    {
        return $page->load([
            'translations',
            'bannerImage',
            'ogImage',
            'slides.translations',
            'slides.image',
            'stats.translations',
            'testimonials.translations',
            'testimonials.image',
            'faqs.translations',
        ]);
    }
}