@extends('layouts.staff')
@section('title','Tambah Produk')
@section('page-title','Tambah Produk')
@section('page-subtitle','Tambahkan item baru ke katalog dan stok')
@section('content')<section class="panel form-panel"><form method="POST" action="{{ route('staff.products.store') }}" enctype="multipart/form-data">@csrf @include('staff.products._form')</form></section>@endsection
