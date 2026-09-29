<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(): View
    {
        return view('storefront.track');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate(['invoice_number' => ['required', 'string', 'max:50']]);
        $invoice = strtoupper(trim($data['invoice_number']));

        $exists = Order::query()->where('source', 'web')->where('invoice_number', $invoice)->exists();
        if (! $exists) return back()->withInput()->with('error', 'Nomor invoice tidak ditemukan.');

        return redirect()->route('orders.track.detail', $invoice);
    }

    public function detail(string $invoice): View
    {
        $order = Order::query()
            ->where('source', 'web')
            ->where('invoice_number', strtoupper($invoice))
            ->with('items')
            ->firstOrFail();
        $logs = $order->statusLogs()->oldest()->get();

        return view('storefront.track-detail', compact('order', 'logs'));
    }
}
