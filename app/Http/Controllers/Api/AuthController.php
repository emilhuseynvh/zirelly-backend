<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Təsdiqlənməmiş eyni e-poçtla təkrar qeydiyyat mövcud qeydi yeniləyir
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [...$data, 'email_verified_at' => null],
        );

        $this->sendOtp($user->email, OtpCode::TYPE_REGISTER);

        return response()->json([
            'message' => __('messages.otp_sent'),
            'otp_required' => true,
            'email' => $user->email,
        ], 201);
    }

    public function verifyRegistration(VerifyOtpRequest $request): JsonResponse
    {
        $email = $request->input('email');

        if (! OtpCode::verifyAndConsume($email, OtpCode::TYPE_REGISTER, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => [__('messages.otp_invalid')],
            ]);
        }

        $user = User::query()->where('email', $email)->firstOrFail();
        $user->markEmailAsVerified();

        try {
            \App\Models\Contact::syncFromUser($user);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Contact sync on registration failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            $this->sendOtp($user->email, OtpCode::TYPE_REGISTER);

            return response()->json([
                'message' => __('messages.email_unverified'),
                'email_unverified' => true,
                'email' => $user->email,
            ], 403);
        }

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $user = User::query()->where('email', $email)->whereNotNull('email_verified_at')->first();

        // Mövcudluğu sızdırmamaq üçün həmişə eyni cavab qaytarılır
        if ($user !== null) {
            $this->sendOtp($email, OtpCode::TYPE_RESET_PASSWORD);
        }

        return response()->json([
            'message' => __('messages.otp_sent'),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->input('email');

        if (! OtpCode::verifyAndConsume($email, OtpCode::TYPE_RESET_PASSWORD, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => [__('messages.otp_invalid')],
            ]);
        }

        $user = User::query()->where('email', $email)->firstOrFail();
        $user->forceFill(['password' => $request->input('password')])->save();
        $user->tokens()->delete();

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $type = $request->input('type');

        $query = User::query()->where('email', $email);

        if ($type === OtpCode::TYPE_RESET_PASSWORD) {
            $query->whereNotNull('email_verified_at');
        }

        if ($query->exists()) {
            $this->sendOtp($email, $type);
        }

        return response()->json([
            'message' => __('messages.otp_sent'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('messages.logged_out')]);
    }

    public function verifyToken(Request $request): JsonResponse
    {
        return response()->json([
            'valid' => true,
            'user' => new UserResource($request->user()),
        ]);
    }

    protected function sendOtp(string $email, string $type): void
    {
        $code = OtpCode::issue($email, $type);

        Mail::to($email)->send(new OtpMail($code, $type, app()->getLocale()));
    }
}
