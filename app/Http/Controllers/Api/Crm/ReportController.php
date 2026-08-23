<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Services\CrmReportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly CrmReportService $reports) {}

    public function summary(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        return response()->json(['data' => $this->reports->summary($from, $to)]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $data = $this->reports->summary($from, $to);

        $filename = 'hesabat-'.$from->toDateString().'-'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Hesabat', $data['from'].' — '.$data['to']]);
            fputcsv($out, []);

            $labels = [
                'revenue' => 'Ümumi dövriyyə (çatdırılma daxil)',
                'goods_revenue' => 'Məhsul satışı',
                'delivery_total' => 'Çatdırılmadan yığılan',
                'discount_total' => 'Endirim məbləği',
                'paid_orders' => 'Ödənilmiş sifariş sayı',
                'orders_count' => 'Ümumi sifariş sayı',
                'average_order' => 'Orta sifariş məbləği',
                'delivered_count' => 'Çatdırılmış sifarişlər',
                'cancelled_count' => 'Ləğv edilmiş sifarişlər',
                'returned_count' => 'Qaytarılmış sifarişlər',
                'new_customers' => 'Yeni müştərilər',
                'repeat_customers' => 'Təkrar müştərilər',
            ];

            foreach ($labels as $key => $label) {
                fputcsv($out, [$label, $data['totals'][$key]]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Məhsul üzrə satış']);
            fputcsv($out, ['Məhsul', 'Say', 'Məbləğ']);

            foreach ($data['by_product'] as $row) {
                fputcsv($out, [$row['title'], $row['quantity'], $row['revenue']]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Satış kanalı üzrə']);
            fputcsv($out, ['Kanal', 'Sifariş', 'Məbləğ']);

            foreach ($data['by_channel'] as $row) {
                fputcsv($out, [$row['channel'], $row['orders'], $row['revenue']]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Status üzrə']);
            fputcsv($out, ['Status', 'Say']);

            foreach ($data['by_status'] as $row) {
                fputcsv($out, [$row['status'], $row['count']]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function range(Request $request): array
    {
        $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        $to = $request->filled('to')
            ? CarbonImmutable::parse($request->input('to'))
            : CarbonImmutable::today();

        $from = $request->filled('from')
            ? CarbonImmutable::parse($request->input('from'))
            : $to->subDays(29);

        return [$from, $to];
    }
}
