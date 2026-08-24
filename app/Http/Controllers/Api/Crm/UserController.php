<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\Crm\CrmUserResource;
use App\Models\AuditLog;
use App\Models\CrmUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    private const PASSWORD_MESSAGE = 'Şifrə minimum 8 simvol olmalı, ən azı bir hərf və bir rəqəm ehtiva etməlidir.';

    public function index(): AnonymousResourceCollection
    {
        return CrmUserResource::collection(
            CrmUser::query()->orderBy('id')->get(),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('crm_users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'permissions' => ['required', 'array'],
            'permissions.*' => [Rule::in(CrmUser::SECTIONS)],
        ], [
            'password.*' => self::PASSWORD_MESSAGE,
            'password' => self::PASSWORD_MESSAGE,
            'email.unique' => 'Bu e-poçt ilə istifadəçi artıq mövcuddur.',
        ]);

        $user = CrmUser::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => CrmUser::ROLE_ADMIN,
            'permissions' => array_values($data['permissions']),
            'is_active' => true,
        ]);

        AuditLog::record($request->user(), 'crm_user_created', $user, [
            'email' => $user->email,
            'permissions' => $user->permissions,
        ]);

        return (new CrmUserResource($user))->response()->setStatusCode(201);
    }

    public function update(Request $request, CrmUser $user): CrmUserResource
    {
        if ($user->isSuperadmin() && $user->id !== $request->user()->id) {
            abort(403, 'Superadmin hesabını dəyişmək olmaz.');
        }

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('crm_users', 'email')->ignore($user->id)->whereNull('deleted_at')],
            'password' => ['sometimes', 'required', 'string', Password::min(8)->letters()->numbers()],
            'permissions' => ['sometimes', 'required', 'array'],
            'permissions.*' => [Rule::in(CrmUser::SECTIONS)],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'password.*' => self::PASSWORD_MESSAGE,
            'password' => self::PASSWORD_MESSAGE,
            'email.unique' => 'Bu e-poçt ilə istifadəçi artıq mövcuddur.',
        ]);

        if ($user->id === $request->user()->id && ($data['is_active'] ?? true) === false) {
            abort(422, 'Öz hesabınızı deaktiv edə bilməzsiniz.');
        }

        if ($user->isSuperadmin()) {
            unset($data['permissions'], $data['is_active']);
        }

        $user->update($data);

        AuditLog::record($request->user(), 'crm_user_updated', $user, [
            'fields' => array_keys($data),
        ]);

        return new CrmUserResource($user);
    }

    public function destroy(Request $request, CrmUser $user): JsonResponse
    {
        if ($user->isSuperadmin()) {
            abort(403, 'Superadmin hesabını silmək olmaz.');
        }

        if ($user->id === $request->user()->id) {
            abort(422, 'Öz hesabınızı silə bilməzsiniz.');
        }

        $user->tokens()->delete();
        $user->delete();

        AuditLog::record($request->user(), 'crm_user_deleted', $user, ['email' => $user->email]);

        return response()->json(['message' => 'İstifadəçi silindi.']);
    }
}
