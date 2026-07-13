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

        $user->forceFill($data)->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return new UserResource($user->fresh());
    }
}