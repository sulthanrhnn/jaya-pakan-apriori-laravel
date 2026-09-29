@extends('layouts.staff')
@section('title','Tambah Pengguna')
@section('page-title','Tambah Pengguna Staff')
@section('page-subtitle','Buat akun admin atau kasir')
@section('content')<section class="panel form-panel"><form method="POST" action="{{ route('staff.users.store') }}">@csrf @include('staff.users._form')</form></section>@endsection
