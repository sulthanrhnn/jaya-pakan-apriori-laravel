@extends('layouts.staff')
@section('title','Pengguna Staff')
@section('page-title','Pengguna Staff')
@section('page-subtitle','Kelola akun admin dan kasir. Pelanggan tidak memerlukan akun.')
@section('content')
<div class="toolbar"><div class="muted">Hanya admin yang dapat mengelola akun staff.</div><a class="btn btn-primary" href="{{ route('staff.users.create') }}">+ Tambah Pengguna</a></div>
<section class="panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th class="text-right">Aksi</th></tr></thead><tbody>@foreach($users as $user)<tr><td><strong>{{ $user->name }}</strong>@if($user->id===auth()->id())<small>Anda</small>@endif</td><td>{{ $user->email }}</td><td><span class="badge badge-{{ $user->role==='admin'?'info':'neutral' }}">{{ strtoupper($user->role) }}</span></td><td><span class="badge badge-{{ $user->is_active?'success':'danger' }}">{{ $user->is_active?'Aktif':'Nonaktif' }}</span></td><td><div class="table-actions"><a class="table-link" href="{{ route('staff.users.edit',$user) }}">Edit</a>@if($user->id!==auth()->id())<form method="POST" action="{{ route('staff.users.destroy',$user) }}" data-confirm="Hapus akun {{ $user->name }}?">@csrf @method('DELETE')<button class="table-link danger-link">Hapus</button></form>@endif</div></td></tr>@endforeach</tbody></table></div><div class="panel-footer">{{ $users->links() }}</div></section>
@endsection
