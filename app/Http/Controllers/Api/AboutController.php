<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAboutRequest;
use App\Http\Resources\AboutResource;
use App\Models\AboutPage;
use Illuminate\Support\Facades\DB;

class AboutController extends Controller
{
    public function show(): AboutResource
    {
        return new AboutResource($this->loadRelations(AboutPage::current()));
    }

    public function update(UpdateAboutRequest $request): AboutResource
    {
        $data = $request->validated();
        $page = AboutPage::current();

        DB::transaction(function () use ($data, $page) {
            $page->update(
                collect($data)->only(['hero_image_id', 'section_image_id'])->all(),
            );

            if (! empty($data['translations'])) {
                $page->syncTranslations($data['translations']);
            }

            if (array_key_exists('items', $data)) {
                $page->items()->get()->each->delete();

                foreach ($data['items'] ?? [] as $index => $item) {
                    $page->items()
                        ->create(['position' => $index])
                        ->syncTranslations($item['translations']);
                }
            }
        });

        return new AboutResource($this->loadRelations($page));
    }

    protected function loadRelations(AboutPage $page): AboutPage
    {
        return $page->load(['translations', 'heroImage', 'sectionImage', 'items.translations']);
    }
}