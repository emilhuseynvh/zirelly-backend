<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class RedirectController extends Controller
{
    private const CACHE_KEY = 'redirects.active';

    public function index(): JsonResponse
    {
        $redirects = Cache::remember(self::CACHE_KEY, 300, function () {
            return Redirect::query()
                ->where('is_active', true)
                ->get(['from_path', 'to_path', 'code']);
        });

        return response()->json(['data' => $redirects]);
    }

    public function adminIndex(): JsonResponse
    {
        return response()->json([
            'data' => Redirect::query()->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        if (Redirect::query()->where('from_path', $data['from_path'])->exists()) {
            abort(422, 'Bu ünvan üçün yönləndirmə artıq mövcuddur.');
        }

        $redirect = Redirect::query()->create($data);

        Cache::forget(self::CACHE_KEY);

        return response()->json(['data' => $redirect], 201);
    }

    public function update(Request $request, Redirect $redirect): JsonResponse
    {
        $data = $this->validated($request);

        $duplicate = Redirect::query()
            ->where('from_path', $data['from_path'])
            ->where('id', '!=', $redirect->id)
            ->exists();

        if ($duplicate) {
            abort(422, 'Bu ünvan üçün yönləndirmə artıq mövcuddur.');
        }

        $redirect->update($data);

        Cache::forget(self::CACHE_KEY);

        return response()->json(['data' => $redirect]);
    }

    public function destroy(Redirect $redirect): JsonResponse
    {
        $redirect->delete();

        Cache::forget(self::CACHE_KEY);

        return response()->json(['message' => 'Yönləndirmə silindi.']);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:500'],
            'to_path' => ['required', 'string', 'max:500'],
            'code' => ['required', Rule::in([301, 302])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['from_path'] = Redirect::normalizePath($data['from_path']);
        $data['to_path'] = Redirect::normalizePath($data['to_path']);

        if ($data['from_path'] === $data['to_path']) {
            abort(422, 'Mənbə və hədəf ünvan eyni ola bilməz.');
        }

        return $data;
    }
}
