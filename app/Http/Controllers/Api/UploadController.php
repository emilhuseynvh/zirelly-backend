<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UploadResource;
use App\Models\Upload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
            'alt' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('image');

        $upload = Upload::create([
            'path' => $file->store('uploads', 'public'),
            'original_name' => $file->getClientOriginalName(),
            'alt' => $request->input('alt'),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return (new UploadResource($upload))->response()->setStatusCode(201);
    }

    public function update(Request $request, Upload $upload): UploadResource
    {
        $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
        ]);

        $upload->update(['alt' => $request->input('alt')]);

        return new UploadResource($upload);
    }

    public function destroy(Upload $upload): Response
    {
        $upload->delete();

        return response()->noContent();
    }
}