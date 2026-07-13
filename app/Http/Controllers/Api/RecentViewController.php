<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecentViewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecentViewController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $views = $request->user()
            ->recentViews()
            ->with(['product.translations', 'product.images'])
            ->orderByDesc('viewed_at')
            ->get();

        return RecentViewResource::collection($views);
    }
}