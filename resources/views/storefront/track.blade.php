@extends('layouts.app')
@section('title', 'Cek Pesanan Anda')
@section('content')
<section class="track-hero"><div class="container track-container"><div class="track-card"><span class="eyebrow">PELACAKAN PESANAN</span><h1>Cek Pesanan Anda</h1><p>Masukkan nomor invoice yang diterima setelah checkout untuk melihat status terbaru pesanan.</p><form method="POST" action="{{ route('orders.track.lookup') }}" class="track-form">@csrf<label>Nomor Invoice</label><div class="track-input-row"><input name="invoice_number" value="{{ old('invoice_number') }}" placeholder="Contoh: JPU-20260922-ABC123" required autocomplete="off"><button class="btn btn-primary">Cek Status</button></div></form><div class="track-help">Nomor invoice tercetak pada struk setelah pesanan berhasil dibuat.</div></div></div></section>
@endsection
