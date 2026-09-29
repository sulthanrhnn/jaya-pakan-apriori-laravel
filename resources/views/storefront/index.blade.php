@extends('layouts.app')
@section('title', 'Pemesanan Pakan - Jaya Pakan Utama')
@section('content')
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">SISTEM PEMESANAN PRODUK PAKAN</span>
            <h1>Belanja kebutuhan ternak lebih mudah, aman, dan terpercaya.</h1>
            <p>Pilih produk, masukkan ke keranjang, periksa pesanan, bayar dengan transfer, selesai.</p>
            <p>Solusi cepat dan praktis untuk kebutuhan ternak anda</p>
            <div class="hero-actions"><a href="#produk" class="btn btn-primary">Mulai Belanja</a><a href="{{ route('orders.track') }}" class="btn btn-secondary">Cek Pesanan Anda</a></div>
            <div class="trust-row"><span>✓ Tanpa akun pelanggan</span><span>✓ Transfer manual BCA</span><span>✓ Invoice otomatis</span></div>
        </div>
</section>

@if($recommendations->isNotEmpty())
<section class="section section-soft">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">UNTUK KERANJANG ANDA</span><h2>Rekomendasi produk pilihan</h2><p>Produk yang mungkin juga anda butuhkan.</p></div><a href="{{ route('cart.index') }}" class="text-link">Lihat keranjang →</a></div>
        <div class="product-grid recommendation-grid">
            @foreach($recommendations as $recommendation)
                @php($product = $recommendation['product'])
                <article class="product-card recommendation-card">
                    <div class="metric-pill">Confidence {{ number_format($recommendation['confidence'] * 100, 1) }}%</div>
                    <div class="product-visual compact">@if($product->image_path)<img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}">@else<span>{{ $product->code }}</span>@endif</div>
                    <div class="product-card-body"><div class="product-code">{{ $product->code }}</div><h3>{{ $product->name }}</h3><div class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</div><div class="mini-metric">Lift {{ number_format($recommendation['lift'], 2) }}</div>
                        <form method="POST" action="{{ route('cart.add', $product) }}">@csrf<input type="hidden" name="quantity" value="1"><button class="btn btn-primary btn-block">+ Tambahkan</button></form>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section" id="produk">
    <div class="container">
        <div class="section-heading product-heading"><div><span class="eyebrow">KATALOG PRODUK</span><h2>Pilih kebutuhan Anda</h2><p>Harga mengacu pada dataset produk CV Jaya Pakan Utama.</p></div>
            <form class="search-form" method="GET" action="{{ route('home') }}"><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari kode atau nama produk..."><button class="btn btn-dark">Cari</button>@if(request('q'))<a class="btn btn-ghost" href="{{ route('home') }}#produk">Reset</a>@endif</form>
        </div>
        <div class="product-grid">
            @forelse($products as $product)
                <article class="product-card">
                    <div class="product-visual">@if($product->image_path)<img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}">@else<div class="feed-bag"><span>{{ $product->code }}</span><small>JAYA PAKAN</small></div>@endif</div>
                    <div class="product-card-body">
                        <div class="product-topline"><span class="product-code">{{ $product->code }}</span><span class="stock {{ $product->stock > 10 ? 'in-stock' : ($product->stock > 0 ? 'low-stock' : 'out-stock') }}">{{ $product->stock > 0 ? 'Stok '.$product->stock : 'Habis' }}</span></div>
                        <h3>{{ $product->name }}</h3>
                        @if($product->description)<p class="product-desc">{{ \Illuminate\Support\Str::limit($product->description, 90) }}</p>@endif
                        <div class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                        <form method="POST" action="{{ route('cart.add', $product) }}" class="add-form">@csrf<input class="qty-input" type="number" name="quantity" min="1" max="{{ max(1, $product->stock) }}" value="1" {{ $product->stock < 1 ? 'disabled' : '' }}><button class="btn btn-primary" {{ $product->stock < 1 ? 'disabled' : '' }}>Tambah</button></form>
                    </div>
                </article>
            @empty
                <div class="empty-state wide"><div class="empty-icon">⌕</div><h3>Produk tidak ditemukan</h3><p>Coba gunakan kata kunci lain.</p></div>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $products->links() }}</div>
    </div>
</section>

@if($popularRules->isNotEmpty())
@endif
@endsection
