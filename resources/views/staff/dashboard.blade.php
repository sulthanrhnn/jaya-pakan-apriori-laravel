@extends('layouts.staff')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan operasional sistem pemesanan dan rekomendasi Apriori')
@section('content')
<div class="stats-grid">
    <div class="stat-card"><span class="stat-icon">◫</span><div><small>Total Produk</small><strong>{{ $stats['products'] }}</strong><p>{{ $stats['active_products'] }} aktif di katalog</p></div></div>
    <div class="stat-card"><span class="stat-icon">▤</span><div><small>Pesanan Web</small><strong>{{ $stats['web_orders'] }}</strong><p>{{ $stats['pending_orders'] }} menunggu verifikasi</p></div></div>
    <div class="stat-card"><span class="stat-icon">✓</span><div><small>Transaksi Selesai</small><strong>{{ $stats['historical_orders'] + $stats['completed_web_orders'] }}</strong><p>{{ $stats['historical_orders'] }} data historis penelitian</p></div></div>
    <div class="stat-card"><span class="stat-icon">⌘</span><div><small>Aturan Apriori</small><strong>{{ $latestRun?->eligible_rule_count ?? 0 }}</strong><p>{{ $latestRun ? $latestRun->transaction_count.' transaksi dianalisis' : 'Belum diproses' }}</p></div></div>
</div>

<div class="staff-grid-2">
    <section class="panel"><div class="panel-head"><div><h2>Pesanan Web Terbaru</h2><p>Pemesanan yang masuk melalui halaman publik.</p></div><a class="text-link" href="{{ route('staff.orders.index', ['source' => 'web']) }}">Lihat semua →</a></div>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>Invoice</th><th>Pemesan</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>@forelse($recentOrders as $order)<tr><td><strong>{{ $order->invoice_number }}</strong><small>{{ $order->ordered_at->format('d/m/Y H:i') }}</small></td><td>{{ $order->customer_name }}</td><td>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td><td><span class="badge badge-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ $order->status_label }}</span></td><td><a class="table-link" href="{{ route('staff.orders.show', $order) }}">Detail</a></td></tr>@empty<tr><td colspan="5" class="empty-cell">Belum ada pesanan web.</td></tr>@endforelse</tbody></table></div>
    </section>

    <section class="panel"><div class="panel-head"><div><h2>Stok Perlu Perhatian</h2><p>Produk aktif dengan stok ≤ 10 unit.</p></div><a class="text-link" href="{{ route('staff.products.index') }}">Kelola stok →</a></div>
        <div class="stock-list">@forelse($lowStockProducts as $product)<div class="stock-row"><div class="product-avatar">{{ $product->code }}</div><div><strong>{{ $product->name }}</strong><small>Rp {{ number_format($product->price, 0, ',', '.') }}</small></div><span class="stock-count {{ $product->stock === 0 ? 'critical' : '' }}">{{ $product->stock }} unit</span></div>@empty<div class="empty-mini">Semua stok produk aktif masih aman.</div>@endforelse</div>
    </section>
</div>

@if(auth()->user()->isAdmin())
<section class="panel apriori-summary"><div class="panel-head"><div><h2>Status Algoritma Apriori</h2><p>Parameter sesuai proposal: support, confidence, lift, dan maksimum itemset.</p></div><a class="btn btn-secondary btn-sm" href="{{ route('staff.apriori.index') }}">Buka Analisis</a></div>
@if($latestRun)<div class="apriori-strip"><div><small>Min Support</small><strong>{{ number_format($latestRun->minimum_support*100, 0) }}%</strong></div><div><small>Min Confidence</small><strong>{{ number_format($latestRun->minimum_confidence*100, 0) }}%</strong></div><div><small>Syarat Lift</small><strong>&gt; {{ number_format($latestRun->minimum_lift, 1) }}</strong></div><div><small>Frequent 1/2/3</small><strong>{{ $latestRun->frequent_1_count }}/{{ $latestRun->frequent_2_count }}/{{ $latestRun->frequent_3_count }}</strong></div><div><small>Terakhir Proses</small><strong>{{ $latestRun->created_at->format('d/m H:i') }}</strong></div></div>@else<p class="muted">Belum ada proses Apriori.</p>@endif
</section>
@endif
@endsection
