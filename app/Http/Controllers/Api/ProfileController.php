<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $data = $request->safe()->except('current_password');

        $emailChanged = array_key_exists('email', $data)
            && $data['email'] !== $user->email;

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
        $dataToSave = array_intersect_key($data, array_flip($columns));

        $user->forceFill($dataToSave)->save();

        // Müştərinin CRM kartı da yenilənsin (telefon, ünvan və s.)
        if ($user->hasVerifiedEmail()) {
            try {
                \App\Models\Contact::syncFromUser($user);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Contact sync on profile update failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return new UserResource($user->fresh());
    }
}