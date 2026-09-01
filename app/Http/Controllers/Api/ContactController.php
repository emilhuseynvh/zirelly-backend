<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\ContactPage;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function show(): ContactResource
    {
        return new ContactResource(ContactPage::current()->load(['translations', 'ogImage']));
    }

    public function update(UpdateContactRequest $request): ContactResource
    {
        $data = $request->validated();
        $page = ContactPage::current();

        DB::transaction(function () use ($data, $page) {
            $page->update(
                collect($data)->only(['email', 'phone', 'whatsapp_number', 'map_embed_url', 'facebook_url', 'instagram_url', 'tiktok_url', 'linkedin_url', 'og_image_id'])->all(),
            );

            if (! empty($data['translations'])) {
                $page->syncTranslations($data['translations']);
            }
        });

        return new ContactResource($page->load(['translations', 'ogImage']));
    }
}