<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
            'search' => ['sometimes', 'string', 'max:100'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
        ]);

        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');

                $q->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('promocode_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(20);

        return OrderResource::collection($orders);
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

        $status = OrderStatus::from($request->input('status'));

        $order->update([
            'status' => $status,
            'paid_at' => $status === OrderStatus::Paid ? ($order->paid_at ?? now()) : $order->paid_at,
        ]);

        return new OrderResource($order->load(['user', 'items', 'transactions']));
    }

    public function stats(Request $request): JsonResponse
    {
        $request->validate(['days' => ['sometimes', 'integer', 'min:7', 'max:365']]);

        $days = (int) $request->input('days', 30);
        $from = now()->subDays($days - 1)->startOfDay();

        $paid = Order::where('status', OrderStatus::Paid);

        $totals = [
            'orders' => Order::count(),
            'paid_orders' => (clone $paid)->count(),
            'revenue' => round((float) (clone $paid)->sum('total'), 2),
            'discount_total' => round((float) (clone $paid)->sum('discount_amount'), 2),
            'average_order' => round((float) (clone $paid)->avg('total'), 2),
        ];

        $byDay = Order::query()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END) as revenue")
            ->where('created_at', '>=', $from)
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
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Paid))
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
