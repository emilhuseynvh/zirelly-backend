<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\Crm\ContactNoteResource;
use App\Http\Resources\Crm\ContactResource;
use App\Http\Resources\Crm\CrmOrderResource;
use App\Http\Resources\Crm\CrmUserResource;
use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\ContactNote;
use App\Models\CrmUser;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TrashController extends Controller
{
    public function contacts(Request $request): AnonymousResourceCollection
    {
        $contacts = Contact::onlyTrashed()
            ->withCount('orders')
            ->latest('deleted_at')
            ->paginate($request->integer('per_page', 20));

        return ContactResource::collection($contacts);
    }

    public function orders(Request $request): AnonymousResourceCollection
    {
        $orders = Order::onlyTrashed()
            ->with(['contact' => fn ($q) => $q->withTrashed(), 'user'])
            ->withCount('items')
            ->latest('deleted_at')
            ->paginate($request->integer('per_page', 20));

        return CrmOrderResource::collection($orders);
    }

    public function restoreContact(Request $request, int $id): ContactResource
    {
        $contact = Contact::onlyTrashed()->findOrFail($id);
        $contact->restore();

        AuditLog::record($request->user(), 'contact_restored', $contact);

        return new ContactResource($contact);
    }

    public function restoreOrder(Request $request, int $id): CrmOrderResource
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->restore();

        AuditLog::record($request->user(), 'order_restored', $order);

        return new CrmOrderResource($order->load(['contact', 'user']));
    }

    public function users(Request $request): AnonymousResourceCollection
    {
        $users = CrmUser::onlyTrashed()
            ->latest('deleted_at')
            ->paginate($request->integer('per_page', 20));

        return CrmUserResource::collection($users);
    }

    public function restoreUser(Request $request, int $id): CrmUserResource
    {
        $user = CrmUser::onlyTrashed()->findOrFail($id);

        if (CrmUser::query()->where('email', $user->email)->exists()) {
            abort(422, 'Bu e-poçt ilə aktiv istifadəçi mövcuddur — bərpa mümkün deyil.');
        }

        $user->restore();

        AuditLog::record($request->user(), 'crm_user_restored', $user, ['email' => $user->email]);

        return new CrmUserResource($user);
    }

    public function forceDeleteUser(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $user = CrmUser::onlyTrashed()->findOrFail($id);

        AuditLog::record($request->user(), 'crm_user_force_deleted', $user, ['email' => $user->email]);

        $user->forceDelete();

        return response()->json(['message' => 'İstifadəçi tam silindi.']);
    }

    public function notes(Request $request): AnonymousResourceCollection
    {
        $notes = ContactNote::onlyTrashed()
            ->with(['author', 'contact' => fn ($q) => $q->withTrashed()])
            ->latest('deleted_at')
            ->paginate($request->integer('per_page', 20));

        return ContactNoteResource::collection($notes);
    }

    public function restoreNote(Request $request, int $id): ContactNoteResource
    {
        $note = ContactNote::onlyTrashed()->findOrFail($id);
        $note->restore();

        AuditLog::record($request->user(), 'contact_note_restored', $note->contact()->withTrashed()->first());

        return new ContactNoteResource($note->load('author'));
    }

    public function forceDeleteNote(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $note = ContactNote::onlyTrashed()->findOrFail($id);

        AuditLog::record($request->user(), 'contact_note_force_deleted', $note->contact()->withTrashed()->first());

        $note->forceDelete();

        return response()->json(['message' => 'Qeyd tam silindi.']);
    }

    public function forceDeleteContact(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $contact = Contact::onlyTrashed()->findOrFail($id);

        AuditLog::record($request->user(), 'contact_force_deleted', $contact, [
            'name' => trim($contact->name.' '.($contact->surname ?? '')),
            'phone' => $contact->phone,
        ]);

        $contact->forceDelete();

        return response()->json(['message' => 'Müştəri tam silindi.']);
    }

    public function forceDeleteOrder(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $order = Order::onlyTrashed()->findOrFail($id);

        AuditLog::record($request->user(), 'order_force_deleted', $order, [
            'total' => (string) $order->total,
        ]);

        $order->forceDelete();

        return response()->json(['message' => 'Sifariş tam silindi.']);
    }
}
