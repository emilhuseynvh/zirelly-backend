<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLegalPageRequest;
use App\Http\Resources\LegalPageResource;
use App\Models\LegalPage;

class LegalPageController extends Controller
{
    public function show(string $slug): LegalPageResource
    {
        abort_unless(in_array($slug, LegalPage::SLUGS, true), 404);

        return new LegalPageResource(LegalPage::forSlug($slug)->load(['translations', 'ogImage']));
    }

    public function update(UpdateLegalPageRequest $request, string $slug): LegalPageResource
    {
        abort_unless(in_array($slug, LegalPage::SLUGS, true), 404);

        $page = LegalPage::forSlug($slug);

        if ($request->exists('og_image_id')) {
            $page->update(['og_image_id' => $request->validated('og_image_id')]);
        }

        $page->syncTranslations($request->validated('translations'));
        $page->touch();

        return new LegalPageResource($page->load(['translations', 'ogImage']));
    }
}
