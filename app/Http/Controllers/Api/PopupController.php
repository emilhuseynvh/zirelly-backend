<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePopupRequest;
use App\Http\Resources\PopupResource;
use App\Models\Popup;
use Illuminate\Support\Facades\DB;

class PopupController extends Controller
{
    public function show(): PopupResource
    {
        return new PopupResource($this->loadRelations(Popup::current()));
    }

    public function update(UpdatePopupRequest $request): PopupResource
    {
        $data = $request->validated();
        $popup = Popup::current();

        DB::transaction(function () use ($data, $popup) {
            $popup->update(
                collect($data)
                    ->only(['image_id', 'button_link', 'delay_seconds', 'is_active', 'show_once'])
                    ->all(),
            );

            if (! empty($data['translations'])) {
                $popup->syncTranslations($data['translations']);
            }
        });

        return new PopupResource($this->loadRelations($popup));
    }

    protected function loadRelations(Popup $popup): Popup
    {
        return $popup->load(['translations', 'image']);
    }
}
