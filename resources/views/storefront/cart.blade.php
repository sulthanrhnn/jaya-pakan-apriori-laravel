@extends('layouts.app')
@section('title', 'Keranjang Belanja')
@section('content')
<section class="page-hero slim"><div class="container"><span class="eyebrow">KERANJANG</span><h1>Keranjang belanja Anda</h1><p>Periksa jumlah produk sebelum checkout.</p></div></section>
<section class="section">
    <div class="container">
        @if($items->isEmpty())
            <div class="empty-state"><div class="empty-icon">🛒</div><h2>Keranjang masih kosong</h2><p>Tambahkan produk terlebih dahulu untuk mendapatkan rekomendasi pembelian.</p><a class="btn btn-primary" href="{{ route('home') }}#produk">Lihat Katalog</a></div>
        @else
            <div class="cart-layout">
                <div class="cart-list">
                    @foreach($items as $line)
                        @php($product = $line['product'])
                        <div class="cart-item">
                            <div class="product-visual mini">@if($product->image_path)<img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}">@else<span>{{ $product->code }}</span>@endif</div>
                            <div class="cart-info"><span class="product-code">{{ $product->code }}</span><h3>{{ $product->name }}</h3><p>Rp {{ number_format($product->price, 0, ',', '.') }} / unit • Stok {{ $product->stock }}</p></div>
                            <form method="POST" action="{{ route('cart.update', $product) }}" class="qty-form">@csrf @method('PATCH')<input type="number" name="quantity" min="1" max="{{ $product->stock }}" value="{{ $line['quantity'] }}"><button class="btn btn-secondary btn-sm">Update</button></form>
                            <div class="cart-line-total">Rp {{ number_format($line['lineTotal'], 0, ',', '.') }}</div>
                            <form method="POST" action="{{ route('cart.remove', $product) }}">@csrf @method('DELETE')<button class="icon-button danger" title="Hapus">×</button></form>
                        </div>
                    @endforeach
                    <div class="cart-actions"><a href="{{ route('home') }}#produk" class="text-link">← Lanjut belanja</a><form method="POST" action="{{ route('cart.clear') }}" data-confirm="Kosongkan seluruh keranjang?">@csrf @method('DELETE')<button class="btn btn-ghost btn-sm">Kosongkan keranjang</button></form></div>
                </div>
                <aside class="summary-card sticky-card"><h3>Ringkasan</h3><div class="summary-row"><span>Subtotal</span><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></div><div class="summary-row"><span>Biaya lain</span><span>—</span></div><hr><div class="summary-row total"><span>Total</span><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></div><p class="small-note">Pembayaran dilakukan melalui transfer bank dan bukti transfer diunggah saat checkout.</p><a href="{{ route('checkout.show') }}" class="btn btn-primary btn-block">Lanjut Checkout</a></aside>
            </div>

            @if($recommendations->isNotEmpty())
                <div class="recommendation-section"><div class="section-heading"><div><span class="eyebrow">REKOMENDASI PRODUK</span><h2>Sering dibeli bersama</h2><p>Mungkin Anda Juga Suka Produk Berikut Ini.</p></div></div>
                    <div class="product-grid recommendation-grid">
                        @foreach($recommendations as $recommendation)
                            @php($product = $recommendation['product'])
                            <article class="product-card recommendation-card"><div class="metric-pill">Confidence {{ number_format($recommendation['confidence'] * 100, 1) }}%</div><div class="product-visual compact"><span>{{ $product->code }}</span></div><div class="product-card-body"><h3>{{ $product->name }}</h3><div class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</div><div class="mini-metric">Lift {{ number_format($recommendation['lift'], 2) }}</div><form method="POST" action="{{ route('cart.add', $product) }}">@csrf<input type="hidden" name="quantity" value="1"><button class="btn btn-primary btn-block">+ Tambahkan</button></form></div></article>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
