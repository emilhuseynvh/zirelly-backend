<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\Crm\CrmUserResource;
use App\Models\AuditLog;
use App\Models\CrmUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 3;

    private const LOCKOUT_SECONDS = 300;

    private const TOKEN_HOURS = 12;

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'crm-login:'.strtolower((string) $request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

            return response()->json([
                'message' => "Çox sayda uğursuz cəhd. {$minutes} dəqiqə sonra yenidən yoxlayın.",
            ], 429);
        }

        $user = CrmUser::query()->where('email', $request->input('email'))->first();

        if ($user === null || ! Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);

            return response()->json(['message' => 'E-poçt və ya şifrə yanlışdır.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Hesabınız deaktiv edilib.'], 403);
        }

        RateLimiter::clear($key);

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken('crm', ['crm'], now()->addHours(self::TOKEN_HOURS));

        AuditLog::record($user, 'login');

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_in' => self::TOKEN_HOURS * 3600,
            'user' => new CrmUserResource($user),
        ]);
    }

    public function me(Request $request): CrmUserResource
    {
        return new CrmUserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        AuditLog::record($request->user(), 'logout');

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Çıxış edildi.']);
    }
}
