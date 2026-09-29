<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Jaya Pakan Utama</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
</head>
<body class="staff-body">
<div class="staff-shell">
    <aside class="staff-sidebar" id="staffSidebar">
        <a href="{{ route('staff.dashboard') }}" class="staff-brand"><span class="brand-mark">JPU</span><span><strong>Jaya Pakan</strong><small>Panel Staff</small></span></a>
        <nav class="staff-nav">
            <a class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" href="{{ route('staff.dashboard') }}"><span>▦</span> Dashboard</a>
            <a class="{{ request()->routeIs('staff.orders.*') ? 'active' : '' }}" href="{{ route('staff.orders.index') }}"><span>▤</span> Pesanan & Transaksi</a>
            <a class="{{ request()->routeIs('staff.products.*') ? 'active' : '' }}" href="{{ route('staff.products.index') }}"><span>◫</span> Produk & Stok</a>
            @if(auth()->user()->isAdmin())
                <div class="nav-label">ADMINISTRASI</div>
                <a class="{{ request()->routeIs('staff.apriori.*') ? 'active' : '' }}" href="{{ route('staff.apriori.index') }}"><span>⌘</span> Algoritma Apriori</a>
                <a class="{{ request()->routeIs('staff.reports.*') ? 'active' : '' }}" href="{{ route('staff.reports.index') }}"><span>↗</span> Laporan</a>
                <a class="{{ request()->routeIs('staff.users.*') ? 'active' : '' }}" href="{{ route('staff.users.index') }}"><span>◎</span> Pengguna Staff</a>
                <a class="{{ request()->routeIs('staff.settings.*') ? 'active' : '' }}" href="{{ route('staff.settings.edit') }}"><span>⚙</span> Pengaturan</a>
            @endif
            <div class="nav-label">LAINNYA</div>
            <a href="{{ route('home') }}" target="_blank"><span>↗</span> Lihat Beranda</a>
        </nav>
        <div class="staff-user-card">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="staff-user-meta"><strong>{{ auth()->user()->name }}</strong><small>{{ strtoupper(auth()->user()->role) }}</small></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="icon-button" title="Logout">↪</button></form>
        </div>
    </aside>
    <section class="staff-main">
        <header class="staff-topbar">
            <button class="mobile-menu" type="button" data-sidebar-toggle>☰</button>
            <div><h1>@yield('page-title', 'Dashboard')</h1><p>@yield('page-subtitle', 'Kelola operasional CV. Jaya Pakan Utama')</p></div>
            <div class="topbar-chip">{{ now()->format('d M Y') }}</div>
        </header>
        <div class="staff-content">
            @include('partials.flash')
            @yield('content')
        </div>
    </section>
</div>
<script src="{{ asset('assets/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
