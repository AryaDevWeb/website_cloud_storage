<?php

namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Models\MasterValidation;
use Illuminate\Support\Facades\Auth;

class Register extends Controller
{
    public function tampil()
    {
        $pesan = 'Selamat Datang di SMK Cloud Storage';
        return view('register', compact('pesan'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'role_type' => 'required|in:siswa,guru_tendik',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            // Siswa validation inputs
            'nama_lengkap' => 'required_if:role_type,siswa|nullable|string',
            'nisn' => 'required_if:role_type,siswa|nullable|string',
            'nik' => 'required_if:role_type,siswa|nullable|string',
            // Guru/Tendik validation inputs
            'nip' => 'required_if:role_type,guru_tendik|nullable|string',
            'nuptk' => 'required_if:role_type,guru_tendik|nullable|string',
        ]);

        if ($request->role_type === 'siswa') {
            // Find record in master data
            $master = MasterValidation::where('role', 'siswa')
                ->where(function ($q) use ($request) {
                    $q->where('nama_lengkap', 'ILIKE', $request->nama_lengkap)
                      ->orWhere('nama_lengkap', 'LIKE', $request->nama_lengkap);
                })
                ->where(function ($q) use ($request) {
                    if ($request->nisn) $q->orWhere('nisn', $request->nisn);
                    if ($request->nik) $q->orWhere('nik', $request->nik);
                })
                ->first();

            if (!$master) {
                return back()->withInput()->with('error', 'Data Siswa tidak ditemukan di Master Dapodik. Pastikan Nama Lengkap dan NISN/NIK sesuai.');
            }

            // Quota limit default for Siswa: 2 GB
            $quota = 2 * 1024 * 1024 * 1024;

            $user = User::create([
                'name' => $master->nama_lengkap,
                'email' => $request->email,
                'role' => 'siswa',
                'target_kelas' => $master->kelas, // Automatically locked from master data
                'target_jurusan' => $master->jurusan,
                'storage_limit_bytes' => $quota,
                'storage_used_bytes' => 0,
                'password' => Hash::make($request->password),
            ]);

        } else {
            // Find record for Guru / Tendik
            $master = MasterValidation::whereIn('role', ['guru', 'tendik', 'admin'])
                ->where('email', $request->email)
                ->where(function ($q) use ($request) {
                    if ($request->nip) $q->orWhere('nip', $request->nip);
                    if ($request->nuptk) $q->orWhere('nuptk', $request->nuptk);
                })
                ->first();

            if (!$master) {
                return back()->withInput()->with('error', 'Data Guru/Tendik tidak ditemukan di Master Dapodik. Pastikan Email dan NIP/NUPTK sesuai.');
            }

            // Determine specific sub-role and default storage quota
            $role = 'tendik';
            $quota = 100 * 1024 * 1024 * 1024; // Tendik default: 100 GB
            $targetKelas = null;
            $targetJurusan = $master->jurusan;

            if ($master->role === 'admin') {
                $role = 'admin';
                $quota = 100 * 1024 * 1024 * 1024; // Admin: 100 GB+
            } elseif ($master->role === 'guru') {
                // If tugas_tambahan starts with or contains "Wali Kelas"
                if ($master->tugas_tambahan && stripos($master->tugas_tambahan, 'Wali Kelas') !== false) {
                    $role = 'guru_wali';
                    $quota = 20 * 1024 * 1024 * 1024; // Wali Kelas: 20 GB
                    
                    // Extract kelas from "Wali Kelas XII RPL" or similar
                    $targetKelas = $master->kelas;
                    if (!$targetKelas) {
                        preg_match('/Wali Kelas\s+([A-Za-z0-9\s]+)/i', $master->tugas_tambahan, $matches);
                        $targetKelas = isset($matches[1]) ? trim($matches[1]) : null;
                    }
                } else {
                    $role = 'guru_jurusan';
                    $quota = 50 * 1024 * 1024 * 1024; // Guru Jurusan: 50 GB
                }
            }

            $user = User::create([
                'name' => $master->nama_lengkap,
                'email' => $request->email,
                'role' => $role,
                'target_kelas' => $targetKelas,
                'target_jurusan' => $targetJurusan,
                'storage_limit_bytes' => $quota,
                'storage_used_bytes' => 0,
                'password' => Hash::make($request->password),
            ]);
        }

        // Log in the newly registered user
        Auth::login($user);

        return redirect('/dashboard/' . $user->id)->with('status', 'Registrasi berhasil divalidasi dengan Dapodik!');
    }
}
