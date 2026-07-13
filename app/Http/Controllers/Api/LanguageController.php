<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageRequest;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LanguageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LanguageResource::collection(Language::allCached());
    }

    public function store(StoreLanguageRequest $request): LanguageResource
    {
        $language = Language::create($request->validated());

        return new LanguageResource($language);
    }

    public function show(Language $language): LanguageResource
    {
        return new LanguageResource($language);
    }

    public function update(UpdateLanguageRequest $request, Language $language): LanguageResource
    {
        $language->update($request->validated());

        return new LanguageResource($language);
    }

    public function destroy(Language $language): Response
    {
        abort_if(
            $language->is_default,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Default dili silmək olmaz. Əvvəlcə başqa dili default edin.',
        );

        $language->delete();

        return response()->noContent();
    }
}