<?php

namespace App\Http\Controllers\Api\Crm;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Crm\CrmOrderResource;
use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->filteredQuery($request)
            ->with(['contact', 'user'])
            ->withCount('items')
            ->paginate($request->integer('per_page', 20));

        return CrmOrderResource::collection($orders);
    }

    public function show(Order $order): CrmOrderResource
    {
        return new CrmOrderResource(
            $order->load(['contact', 'user', 'items.product.images', 'statusHistories.changedBy']),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact_id' => ['required', 'integer', Rule::exists('contacts', 'id')->whereNull('deleted_at')],
            'channel' => ['required', Rule::in(Contact::CHANNELS)],
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'items.*.title' => ['required_without:items.*.product_id', 'nullable', 'string', 'max:255'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'discount_amount' => ['sometimes', 'numeric', 'min:0'],
            'delivery_fee' => ['sometimes', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $contact = Contact::query()->findOrFail($data['contact_id']);

        $order = DB::transaction(function () use ($data, $contact, $request) {
            $subtotal = 0;
            $items = [];

            foreach ($data['items'] as $item) {
                $product = filled($item['product_id'] ?? null)
                    ? Product::query()->find($item['product_id'])
                    : null;

                $lineTotal = round($item['unit_price'] * $item['quantity'], 2);
                $subtotal += $lineTotal;

                $items[] = [
                    'product_id' => $product?->id,
                    'title' => $item['title'] ?? $product?->translate('title', 'az') ?? $product?->translate('title') ?? $product?->slug ?? '—',
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                ];
            }

            $discount = min(round((float) ($data['discount_amount'] ?? 0), 2), $subtotal);
            $status = OrderStatus::from($data['status']);

            $order = Order::query()->create([
                'user_id' => $contact->user_id,
                'contact_id' => $contact->id,
                'status' => $status,
                'channel' => $data['channel'],
                'subtotal' => round($subtotal, 2),
                'discount_amount' => $discount,
                'total' => round($subtotal - $discount, 2),
                'delivery_fee' => round((float) ($data['delivery_fee'] ?? 0), 2),
                'address' => $data['address'] ?? null,
                'note' => $data['note'] ?? null,
                'paid_at' => in_array($status, OrderStatus::paidLike(), true) ? now() : null,
            ]);

            $order->items()->createMany($items);

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => $status->value,
                'crm_user_id' => $request->user()->id,
                'source' => 'crm',
            ]);

            return $order;
        });

        AuditLog::record($request->user(), 'order_created', $order, [
            'channel' => $order->channel,
            'total' => (string) $order->total,
        ]);

        return (new CrmOrderResource($order->load(['contact', 'user', 'items'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Order $order): CrmOrderResource
    {
        $data = $request->validate([
            'channel' => ['sometimes', Rule::in(Contact::CHANNELS)],
            'delivery_fee' => ['sometimes', 'numeric', 'min:0'],
            'discount_amount' => ['sometimes', 'numeric', 'min:0'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'note' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        if (array_key_exists('discount_amount', $data)) {
            $data['discount_amount'] = min(round((float) $data['discount_amount'], 2), (float) $order->subtotal);
            $data['total'] = round((float) $order->subtotal - $data['discount_amount'], 2);
        }

        $before = $order->only(array_keys($data));

        $order->update($data);

        $changes = [];
        foreach ($data as $key => $value) {
            if ((string) ($before[$key] ?? '') !== (string) $value) {
                $changes[$key] = ['from' => $before[$key] ?? null, 'to' => $value];
            }
        }

        AuditLog::record($request->user(), 'order_updated', $order, $changes ?: null);

        return new CrmOrderResource($order->load(['contact', 'user', 'items', 'statusHistories.changedBy']));
    }

    public function updateStatus(Request $request, Order $order): CrmOrderResource
    {
        $request->validate(['status' => ['required', Rule::enum(OrderStatus::class)]]);

        $from = $order->status->value;
        $order->changeStatus(OrderStatus::from($request->input('status')), $request->user(), 'crm');

        AuditLog::record($request->user(), 'order_status_changed', $order, [
            'from' => $from,
            'to' => $order->status->value,
        ]);

        return new CrmOrderResource($order->load(['contact', 'user', 'statusHistories.changedBy']));
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $order->delete();

        AuditLog::record($request->user(), 'order_deleted', $order);

        return response()->json(['message' => 'Sifariş arxivləşdirildi.']);
    }

    public function export(Request $request): StreamedResponse
    {
        $orders = $this->filteredQuery($request)
            ->with(['contact', 'user', 'items'])
            ->withCount('items')
            ->get();

        $statusLabels = [
            'pending' => 'Gözləyir',
            'paid' => 'Yeni sifariş',
            'preparing' => 'Çatdırılmaya hazırlanır',
            'shipped' => 'Çatdırılmaya verildi',
            'delivered' => 'Çatdırıldı',
            'returned' => 'Qaytarıldı',
            'cancelled' => 'Ləğv edilib',
        ];

        $filename = 'crm-sifarisler-'.now()->format('Y-m-d-Hi').'.csv';

        return response()->streamDownload(function () use ($orders, $statusLabels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Sifariş №', 'Tarix', 'Müştəri', 'Telefon', 'Kanal', 'Status', 'Məhsullar',
                'Say', 'Ara cəm', 'Endirim', 'Promokod', 'Çatdırılma', 'Yekun', 'Ünvan', 'Qeyd',
            ]);

            foreach ($orders as $order) {
                $customer = $order->contact
                    ? trim($order->contact->name.' '.($order->contact->surname ?? ''))
                    : trim(($order->user?->name ?? '').' '.($order->user?->surname ?? ''));

                fputcsv($out, [
                    $order->id,
                    $order->created_at?->format('d.m.Y H:i'),
                    $customer,
                    $order->contact?->phone ?? $order->user?->phone,
                    $order->channel,
                    $statusLabels[$order->status->value] ?? $order->status->value,
                    $order->items->map(fn ($i) => $i->title.' ×'.$i->quantity)->implode('; '),
                    $order->items->sum('quantity'),
                    number_format((float) $order->subtotal, 2, '.', ''),
                    number_format((float) $order->discount_amount, 2, '.', ''),
                    $order->promocode_code,
                    number_format((float) $order->delivery_fee, 2, '.', ''),
                    number_format((float) $order->total + (float) $order->delivery_fee, 2, '.', ''),
                    $order->address,
                    $order->note,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function filteredQuery(Request $request): Builder
    {
        $request->validate([
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
            'channel' => ['sometimes', Rule::in(Contact::CHANNELS)],
            'search' => ['sometimes', 'string', 'max:100'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'sort' => ['sometimes', Rule::in(['id', 'total', 'created_at'])],
            'dir' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ]);

        return Order::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('channel'), fn ($q) => $q->where('channel', $request->input('channel')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');

                $q->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('promocode_code', 'like', "%{$search}%")
                        ->orWhereHas('contact', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy(
                $request->input('sort', 'id'),
                $request->input('dir', 'desc'),
            );
    }
}
