<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('name')->paginate(20);
        return view('staff.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('staff.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'role' => ['required', 'in:admin,kasir'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::query()->create([
            ...$data,
            'password' => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('staff.users.index')->with('success', 'Pengguna staff berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('staff.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,kasir'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($user->id === auth()->id() && (! $request->boolean('is_active') || $data['role'] !== 'admin')) {
            return back()->with('error', 'Akun admin yang sedang digunakan tidak boleh dinonaktifkan atau diturunkan menjadi kasir.');
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ];
        if (! empty($data['password'])) $payload['password'] = Hash::make($data['password']);

        $user->update($payload);
        return redirect()->route('staff.users.index')->with('success', 'Pengguna staff berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        if ($user->role === 'admin' && User::query()->where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Minimal harus ada satu admin aktif.');
        }

        $user->delete();
        return back()->with('success', 'Pengguna staff dihapus.');
    }
}
