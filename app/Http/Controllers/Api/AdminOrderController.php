<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->filteredQuery($request)
            ->with('user')
            ->withCount('items')
            ->paginate($request->integer('per_page', 20));

        return OrderResource::collection($orders);
    }

    public function export(Request $request): StreamedResponse
    {
        $orders = $this->filteredQuery($request)
            ->with('user')
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

        $filename = 'sifarisler-'.now()->format('Y-m-d-Hi').'.csv';

        return response()->streamDownload(function () use ($orders, $statusLabels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Sifariş №', 'Tarix', 'Müştəri', 'E-poçt', 'Telefon', 'Status',
                'Məhsul sayı', 'Ara cəm', 'Endirim', 'Promokod', 'Cəmi', 'Ödəniş tarixi',
            ]);

            foreach ($orders as $order) {
                fputcsv($out, [
                    $order->id,
                    $order->created_at?->format('d.m.Y H:i'),
                    trim(($order->user?->name ?? '').' '.($order->user?->surname ?? '')),
                    $order->user?->email,
                    $order->user?->phone,
                    $statusLabels[$order->status->value] ?? $order->status->value,
                    $order->items_count,
                    number_format((float) $order->subtotal, 2, '.', ''),
                    number_format((float) $order->discount_amount, 2, '.', ''),
                    $order->promocode_code,
                    number_format((float) $order->total, 2, '.', ''),
                    $order->paid_at?->format('d.m.Y H:i'),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function filteredQuery(Request $request): Builder
    {
        $request->validate([
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
            'search' => ['sometimes', 'string', 'max:100'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'min_total' => ['sometimes', 'numeric', 'min:0'],
            'max_total' => ['sometimes', 'numeric', 'min:0'],
            'promocode' => ['sometimes', 'string', 'max:50'],
            'sort' => ['sometimes', Rule::in(['id', 'total', 'created_at', 'paid_at'])],
            'dir' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ]);

        return Order::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->when($request->filled('min_total'), fn ($q) => $q->where('total', '>=', $request->input('min_total')))
            ->when($request->filled('max_total'), fn ($q) => $q->where('total', '<=', $request->input('max_total')))
            ->when($request->filled('promocode'), function ($q) use ($request) {
                $promocode = $request->input('promocode');

                match ($promocode) {
                    'any' => $q->whereNotNull('promocode_code'),
                    'none' => $q->whereNull('promocode_code'),
                    default => $q->where('promocode_code', 'like', "%{$promocode}%"),
                };
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');

                $q->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('promocode_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy(
                $request->input('sort', 'id'),
                $request->input('dir', 'desc'),
            );
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load(['user', 'items.product.images', 'transactions']));
    }

    public function updateStatus(Request $request, Order $order): OrderResource
    {
        $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $order->changeStatus(OrderStatus::from($request->input('status')), null, 'admin');

        return new OrderResource($order->load(['user', 'items', 'transactions']));
    }

    public function stats(Request $request): JsonResponse
    {
        $request->validate(['days' => ['sometimes', 'integer', 'min:7', 'max:365']]);

        $days = (int) $request->input('days', 30);
        $from = now()->subDays($days - 1)->startOfDay();

        $paidStatuses = OrderStatus::paidLike();
        $paid = Order::whereIn('status', $paidStatuses);
        $paidList = implode(',', array_map(fn ($s) => "'{$s->value}'", $paidStatuses));

        $totals = [
            'orders' => Order::where('status', '!=', OrderStatus::Cancelled)->count(),
            'paid_orders' => (clone $paid)->count(),
            'revenue' => round((float) (clone $paid)->sum('total'), 2),
            'discount_total' => round((float) (clone $paid)->sum('discount_amount'), 2),
            'average_order' => round((float) (clone $paid)->avg('total'), 2),
        ];

        $byDay = Order::query()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw("SUM(CASE WHEN status IN ({$paidList}) THEN total ELSE 0 END) as revenue")
            ->where('created_at', '>=', $from)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => (string) $row->date,
                'orders' => (int) $row->orders,
                'revenue' => round((float) $row->revenue, 2),
            ]);

        $byStatus = Order::query()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status->value,
                'count' => (int) $row->count,
            ]);

        $topProducts = OrderItem::query()
            ->select('title', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(line_total) as revenue'))
            ->whereHas('order', fn ($q) => $q->whereIn('status', OrderStatus::paidLike()))
            ->groupBy('title')
            ->orderByDesc(DB::raw('SUM(line_total)'))
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'title' => $row->title,
                'quantity' => (int) $row->quantity,
                'revenue' => round((float) $row->revenue, 2),
            ]);

        $promocodes = Order::query()
            ->whereNotNull('promocode_code')
            ->where('status', '!=', OrderStatus::Cancelled)
            ->select('promocode_code', DB::raw('COUNT(*) as uses'), DB::raw('SUM(discount_amount) as discount_total'))
            ->groupBy('promocode_code')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'code' => $row->promocode_code,
                'uses' => (int) $row->uses,
                'discount_total' => round((float) $row->discount_total, 2),
            ]);

        return response()->json([
            'data' => [
                'totals' => $totals,
                'by_day' => $byDay,
                'by_status' => $byStatus,
                'top_products' => $topProducts,
                'promocodes' => $promocodes,
            ],
        ]);
    }
}
