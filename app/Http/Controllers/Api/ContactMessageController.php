<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        ContactMessage::create($request->validated());

        return response()->json([
            'message' => __('messages.message_sent'),
        ], 201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $messages = ContactMessage::query()
            ->when($request->boolean('unread'), fn ($q) => $q->where('is_read', false))
            ->when($request->input('status') === 'read', fn ($q) => $q->where('is_read', true))
            ->when($request->input('status') === 'unread', fn ($q) => $q->where('is_read', false))
            ->when($request->filled('subject'), fn ($q) => $q->where('subject', $request->input('subject')))
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return ContactMessageResource::collection($messages);
    }

    public function markRead(Request $request, ContactMessage $message): ContactMessageResource
    {
        $message->update(['is_read' => $request->has('is_read') ? $request->boolean('is_read') : true]);

        return new ContactMessageResource($message);
    }

    public function destroy(ContactMessage $message): Response
    {
        $message->delete();

        return response()->noContent();
    }
}