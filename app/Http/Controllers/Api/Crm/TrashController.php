<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\Crm\ContactResource;
use App\Http\Resources\Crm\CrmOrderResource;
use App\Models\AuditLog;
use App\Models\Contact;
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
