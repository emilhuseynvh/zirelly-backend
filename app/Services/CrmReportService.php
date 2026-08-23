<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CrmReportService
{
    public function summary(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $paidStatuses = array_map(fn ($s) => $s->value, OrderStatus::paidLike());

        $inRange = fn ($q) => $q
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()]);

        $paidBase = Order::query()->whereIn('status', $paidStatuses)->tap($inRange);

        $totals = [
            'revenue' => round((float) (clone $paidBase)->sum(DB::raw('total + delivery_fee')), 2),
            'goods_revenue' => round((float) (clone $paidBase)->sum('total'), 2),
            'delivery_total' => round((float) (clone $paidBase)->sum('delivery_fee'), 2),
            'discount_total' => round((float) (clone $paidBase)->sum('discount_amount'), 2),
            'paid_orders' => (clone $paidBase)->count(),
            'orders_count' => Order::query()->tap($inRange)->where('status', '!=', OrderStatus::Cancelled)->count(),
            'average_order' => round((float) (clone $paidBase)->avg(DB::raw('total + delivery_fee')), 2),
            'delivered_count' => Order::query()->tap($inRange)->where('status', OrderStatus::Delivered)->count(),
            'cancelled_count' => Order::query()->tap($inRange)->where('status', OrderStatus::Cancelled)->count(),
            'returned_count' => Order::query()->tap($inRange)->where('status', OrderStatus::Returned)->count(),
        ];

        $buyerContacts = Order::query()
            ->whereIn('status', $paidStatuses)
            ->tap($inRange)
            ->whereNotNull('contact_id')
            ->distinct()
            ->pluck('contact_id');

        $newCustomers = 0;

        if ($buyerContacts->isNotEmpty()) {
            $newCustomers = Order::query()
                ->whereIn('contact_id', $buyerContacts)
                ->whereIn('status', $paidStatuses)
                ->select('contact_id', DB::raw('MIN(created_at) as first_order_at'))
                ->groupBy('contact_id')
                ->havingRaw('MIN(created_at) >= ?', [$from->startOfDay()])
                ->get()
                ->count();
        }

        $totals['new_customers'] = $newCustomers;
        $totals['repeat_customers'] = max(0, $buyerContacts->count() - $newCustomers);

        $byProduct = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', $paidStatuses)
            ->tap($inRange)
            ->select('order_items.title', DB::raw('SUM(order_items.quantity) as quantity'), DB::raw('SUM(order_items.line_total) as revenue'))
            ->groupBy('order_items.title')
            ->orderByDesc(DB::raw('SUM(order_items.line_total)'))
            ->limit(20)
            ->get()
            ->map(fn ($row) => [
                'title' => $row->title,
                'quantity' => (int) $row->quantity,
                'revenue' => round((float) $row->revenue, 2),
            ]);

        $byChannel = Order::query()
            ->whereIn('status', $paidStatuses)
            ->tap($inRange)
            ->select('channel', DB::raw('COUNT(*) as orders'), DB::raw('SUM(total + delivery_fee) as revenue'))
            ->groupBy('channel')
            ->orderByDesc(DB::raw('SUM(total + delivery_fee)'))
            ->get()
            ->map(fn ($row) => [
                'channel' => $row->channel,
                'orders' => (int) $row->orders,
                'revenue' => round((float) $row->revenue, 2),
            ]);

        $byStatus = Order::query()
            ->tap($inRange)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status->value,
                'count' => (int) $row->count,
            ]);

        $byDay = Order::query()
            ->tap($inRange)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('SUM(CASE WHEN status IN ('.implode(',', array_map(fn ($s) => "'{$s}'", $paidStatuses)).') THEN total + delivery_fee ELSE 0 END) as revenue')
            ->where('status', '!=', OrderStatus::Cancelled)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => (string) $row->date,
                'orders' => (int) $row->orders,
                'revenue' => round((float) $row->revenue, 2),
            ]);

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'totals' => $totals,
            'by_product' => $byProduct,
            'by_channel' => $byChannel,
            'by_status' => $byStatus,
            'by_day' => $byDay,
        ];
    }
}
