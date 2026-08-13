<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'search' => ['sometimes', 'string', 'max:100'],
            'role' => ['sometimes', Rule::enum(UserRole::class)],
            'verified' => ['sometimes', Rule::in(['yes', 'no'])],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'sort' => ['sometimes', Rule::in(['id', 'name', 'created_at', 'orders_count', 'orders_total'])],
            'dir' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ]);

        $paidStatuses = array_map(fn ($s) => $s->value, OrderStatus::paidLike());

        $users = User::query()
            ->withCount('orders')
            ->withSum(['orders as orders_total' => fn ($q) => $q->whereIn('status', $paidStatuses)], 'total')
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')))
            ->when($request->input('verified') === 'yes', fn ($q) => $q->whereNotNull('email_verified_at'))
            ->when($request->input('verified') === 'no', fn ($q) => $q->whereNull('email_verified_at'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');

                $q->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy(
                $request->input('sort', 'id'),
                $request->input('dir', 'desc'),
            )
            ->paginate($request->integer('per_page', 20));

        return UserResource::collection($users);
    }
}
