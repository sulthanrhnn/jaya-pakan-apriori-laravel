@extends('layouts.staff')
@section('title','Edit Produk')
@section('page-title','Edit Produk')
@section('page-subtitle',$product->code.' • '.$product->name)
@section('content')<section class="panel form-panel"><form method="POST" action="{{ route('staff.products.update',$product) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('staff.products._form')</form></section>@endsection
