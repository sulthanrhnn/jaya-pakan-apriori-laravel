<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $completed = Order::query()->where('status', 'completed')->orderBy('ordered_at')->get();
        $monthly = $completed->groupBy(fn (Order $order) => $order->ordered_at->format('Y-m'))->map(function ($orders, $month) {
            return [
                'month' => $month,
                'transactions' => $orders->count(),
                'revenue' => $orders->sum('grand_total'),
            ];
        })->values();

        $topProducts = Product::query()
            ->withSum(['orderItems as sold_quantity' => fn ($q) => $q->whereHas('order', fn ($o) => $o->where('status', 'completed'))], 'quantity')
            ->orderByDesc('sold_quantity')
            ->limit(10)
            ->get();

        $summary = [
            'completed_transactions' => $completed->count(),
            'revenue' => $completed->sum('grand_total'),
            'historical' => $completed->where('source', 'historical')->count(),
            'web' => $completed->where('source', 'web')->count(),
        ];

        return view('staff.reports.index', compact('monthly', 'topProducts', 'summary'));
    }
}
