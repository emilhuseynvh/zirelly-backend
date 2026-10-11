<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliverySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliverySettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['data' => DeliverySetting::current()]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'baku_fee' => ['required', 'numeric', 'min:0', 'max:1000'],
            'baku_free_threshold' => ['required', 'numeric', 'min:0', 'max:100000'],
            'region_fee' => ['required', 'numeric', 'min:0', 'max:1000'],
            'nakhchivan_fee' => ['required', 'numeric', 'min:0', 'max:1000'],
            'karabakh_fee' => ['required', 'numeric', 'min:0', 'max:1000'],
        ]);

        $settings = DeliverySetting::current();
        $settings->update($data);

        return response()->json(['data' => $settings]);
    }
}
