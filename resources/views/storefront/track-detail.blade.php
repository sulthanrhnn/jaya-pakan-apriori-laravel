@extends('layouts.app')
@section('title', 'Status '.$order->invoice_number)
@section('content')

<section class="page-hero slim">
    <div class="container">
        <span class="eyebrow">STATUS PESANAN</span>
        <h1>{{ $order->invoice_number }}</h1>
        <p>Dibuat {{ $order->ordered_at->format('d M Y, H:i') }} WIB • {{ $order->customer_name }}</p>
    </div>
</section>

<section class="section">
    <div class="container tracking-layout">
        <div class="tracking-main">
            <div class="status-highlight">
                <div>
                    <span>Status Saat Ini</span>
                    <h2>{{ $order->status_label }}</h2>
                    <p>Pembayaran: <strong>{{ $order->payment_status_label }}</strong></p>
                </div>
                <div class="status-orb status-{{ $order->status }}">
                    {{ $order->status === 'completed' ? '✓' : ($order->status === 'cancelled' ? '×' : '↻') }}
                </div>
            </div>
            
            <div class="timeline-card">
                <h3>Riwayat Status</h3>
                <div class="timeline">
                    @forelse($logs as $log)
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div>
                                <div class="timeline-head">
                                    <strong>{{ \App\Models\Order::STATUSES[$log->status] ?? $log->status }}</strong>
                                    <span>{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                
                                @if($log->note)
                                    <p>{{ $log->note }}</p>
                                @endif
                                
                                @if($log->payment_status)
                                    <small>Pembayaran: {{ \App\Models\Order::PAYMENT_STATUSES[$log->payment_status] ?? $log->payment_status }}</small>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="muted">Belum ada catatan status.</div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <aside class="tracking-side">
            <div class="summary-card">
                <h3>Ringkasan Pesanan</h3>
                @foreach($order->items as $item)
                    <div class="checkout-line">
                        <div>
                            <strong>{{ $item->product_name }}</strong>
                            <small>{{ $item->product_code }} × {{ $item->quantity }}</small>
                        </div>
                        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <hr>
                <div class="summary-row total">
                    <span>Total</span>
                    <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                </div>
                <a href="{{ route('orders.receipt', $order->invoice_number) }}" class="btn btn-secondary btn-block">Lihat Struk</a>
            </div>
            <div class="small-card">
                <strong>Butuh bantuan?</strong>
                <p>Sampaikan nomor invoice ini kepada staff CV. Jaya Pakan Utama agar pesanan mudah ditemukan.</p>
            </div>
        </aside>
    </div>
</section>

@endsection