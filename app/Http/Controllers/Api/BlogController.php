<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $blogs = Blog::query()
            ->with('translations')
            ->when($request->boolean('published'), fn ($q) => $q->published())
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return BlogResource::collection($blogs);
    }

    public function store(StoreBlogRequest $request): BlogResource
    {
        $data = $request->validated();
        $translations = $data['translations'];
        $defaultCode = Language::defaultLanguage()->code;

        $blog = DB::transaction(function () use ($request, $data, $translations, $defaultCode) {
            $blog = Blog::create([
                'slug' => $data['slug'] ?? Blog::generateUniqueSlug($translations[$defaultCode]['title']),
                'image' => $request->hasFile('image')
                    ? $request->file('image')->store('blogs', 'public')
                    : null,
                'is_published' => $data['is_published'] ?? false,
                'published_at' => $data['published_at']
                    ?? (($data['is_published'] ?? false) ? now() : null),
            ]);

            return $blog->syncTranslations($translations);
        });

        return new BlogResource($blog->load('translations'));
    }

    public function show(Request $request, Blog $blog): BlogResource
    {
        return new BlogResource($blog->load('translations'));
    }

    public function showBySlug(string $slug): BlogResource
    {
        $blog = Blog::query()
            ->published()
            ->where('slug', $slug)
            ->with('translations')
            ->firstOrFail();

        return new BlogResource($blog);
    }

    public function update(UpdateBlogRequest $request, Blog $blog): BlogResource
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $blog) {
            if ($request->hasFile('image')) {
                if ($blog->image) {
                    Storage::disk('public')->delete($blog->image);
                }

                $data['image'] = $request->file('image')->store('blogs', 'public');
            }

            if (($data['is_published'] ?? false) && ! $blog->published_at && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $blog->update(collect($data)->except('translations')->all());

            if (! empty($data['translations'])) {
                $blog->syncTranslations($data['translations']);
            }
        });

        return new BlogResource($blog->load('translations'));
    }

    public function destroy(Blog $blog): Response
    {
        if ($blog->image) {
            Storage::disk('public')->delete($blog->image);
        }

        $blog->delete();

        return response()->noContent();
    }
}