@extends('layouts.app')
@section('title', 'Struk '.$order->invoice_number)
@section('content')
<section class="section receipt-page"><div class="container receipt-wrap">
    <div class="success-banner no-print"><div class="success-check">✓</div><div><h1>Pesanan berhasil dibuat</h1><p>Simpan nomor invoice berikut untuk mengecek status pesanan Anda.</p></div></div>
    <article class="receipt-card" id="receipt">
        <div class="receipt-head"><div class="brand"><span class="brand-mark">JPU</span><span><strong>CV. JAYA PAKAN UTAMA</strong><small>Struk Pemesanan</small></span></div><div class="receipt-status"><span class="badge badge-warning">{{ $order->status_label }}</span><small>{{ $order->ordered_at->format('d/m/Y H:i') }} WIB</small></div></div>
        <div class="invoice-block"><span>NOMOR INVOICE</span><strong>{{ $order->invoice_number }}</strong><button type="button" class="copy-link no-print" data-copy="{{ $order->invoice_number }}">Salin invoice</button></div>
        <div class="receipt-info-grid"><div><span>Nama Pemesan</span><strong>{{ $order->customer_name }}</strong></div><div><span>Telepon</span><strong>{{ $order->customer_phone }}</strong></div><div class="full"><span>Alamat</span><strong>{{ $order->customer_address }}</strong></div></div>
        <table class="receipt-table"><thead><tr><th>Produk</th><th class="text-center">Qty</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td><strong>{{ $item->product_name }}</strong><small>{{ $item->product_code }}</small></td><td class="text-center">{{ $item->quantity }}</td><td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td><td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td></tr>@endforeach</tbody><tfoot><tr><td colspan="3">TOTAL</td><td class="text-right">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td></tr></tfoot></table>
        <div class="payment-receipt"><div><span>Pembayaran</span><strong>Transfer {{ $bank['bank'] }} • {{ $bank['number'] }}</strong><small>a.n. {{ $bank['name'] }}</small></div><div><span>Status Bukti</span><strong>{{ $order->payment_status_label }}</strong></div></div>
        <div class="receipt-footer"><p>Kasir akan memverifikasi pembayaran dan memperbarui status pesanan. Gunakan nomor invoice untuk pelacakan.</p></div>
    </article>
    <div class="receipt-actions no-print"><a href="{{ route('orders.track.detail', $order->invoice_number) }}" class="btn btn-primary">Cek Status Pesanan</a><button type="button" class="btn btn-secondary" onclick="window.print()">Cetak / Simpan PDF</button><a href="{{ route('home') }}" class="btn btn-ghost">Kembali ke Beranda</a></div>
</div></section>
@endsection
