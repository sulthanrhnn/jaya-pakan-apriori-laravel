<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Jaya Pakan Utama')</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
</head>
<body class="storefront-body">
<header class="site-header">
    <div class="container nav-bar">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-mark">JPU</span>
            <span><strong>Jaya Pakan Utama</strong><small>Pakan ternak & produk pendukung</small></span>
        </a>
        <nav class="main-nav" aria-label="Navigasi utama">
            <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a>
            <a class="{{ request()->routeIs('orders.track*') ? 'active' : '' }}" href="{{ route('orders.track') }}">Cek Pesanan Anda</a>
            <a class="cart-link {{ request()->routeIs('cart.*') ? 'active' : '' }}" href="{{ route('cart.index') }}">Keranjang <span class="cart-badge">{{ array_sum(session('cart', [])) }}</span></a>
            @auth
                <a href="{{ route('staff.dashboard') }}">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Login Staff</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    <div class="container flash-wrap">@include('partials.flash')</div>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div><strong>CV. JAYA PAKAN UTAMA</strong><p>Sistem pemesanan berbasis web dengan rekomendasi Produk.</p></div>
        <div class="footer-note">Pemesanan tanpa akun pelanggan • Pembayaran transfer manual • Status dapat dicek dengan nomor invoice</div>
    </div>
</footer>
<script src="{{ asset('assets/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
