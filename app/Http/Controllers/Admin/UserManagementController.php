<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $users = User::orderBy('name')->get();

        return view('admin.users', [
            'users' => $users,
            'roles' => ['admin', 'guru_wali', 'guru_jurusan', 'tendik', 'siswa'],
            'classes' => ['X RPL', 'XI RPL', 'XII RPL'],
            'majors' => ['RPL'],
        ]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'guru_wali', 'guru_jurusan', 'tendik', 'siswa'])],
            'target_kelas' => ['nullable', Rule::in(['X RPL', 'XI RPL', 'XII RPL'])],
            'target_jurusan' => ['nullable', Rule::in(['RPL'])],
            'storage_limit_gb' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $user->update([
            'role' => $validated['role'],
            'target_kelas' => $validated['target_kelas'] ?? null,
            'target_jurusan' => $validated['target_jurusan'] ?? null,
            'storage_limit_bytes' => (int) $validated['storage_limit_gb'] * 1024 * 1024 * 1024,
        ]);

        return back()->with('status', 'User access updated.');
    }
}
