@extends('layouts.staff')
@section('title','Edit Pengguna')
@section('page-title','Edit Pengguna Staff')
@section('page-subtitle',$user->name)
@section('content')<section class="panel form-panel"><form method="POST" action="{{ route('staff.users.update',$user) }}">@csrf @method('PUT') @include('staff.users._form')</form></section>@endsection
