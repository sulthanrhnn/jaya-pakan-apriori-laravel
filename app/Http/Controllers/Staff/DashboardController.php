<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AprioriRun;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'products' => Product::query()->count(),
            'active_products' => Product::query()->where('is_active', true)->count(),
            'low_stock' => Product::query()->where('is_active', true)->where('stock', '<=', 10)->count(),
            'web_orders' => Order::query()->where('source', 'web')->count(),
            'pending_orders' => Order::query()->where('source', 'web')->where('status', 'pending_verification')->count(),
            'completed_web_orders' => Order::query()->where('source', 'web')->where('status', 'completed')->count(),
            'historical_orders' => Order::query()->where('source', 'historical')->count(),
        ];

        $latestRun = AprioriRun::query()->where('status', 'success')->latest('id')->first();
        $recentOrders = Order::query()->where('source', 'web')->latest('ordered_at')->limit(8)->get();
        $lowStockProducts = Product::query()->where('is_active', true)->where('stock', '<=', 10)->orderBy('stock')->limit(8)->get();

        return view('staff.dashboard', compact('stats', 'latestRun', 'recentOrders', 'lowStockProducts'));
    }
}
