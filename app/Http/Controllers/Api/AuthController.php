<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MasterValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/register
     * Register a new user with master data validation.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'role_type' => 'required|in:siswa,guru_tendik',
            'username' => 'required|string|min:3|max:255|unique:users,username|regex:/^[a-zA-Z0-9_.]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            // Siswa validation inputs
            'nama_lengkap' => 'required_if:role_type,siswa|nullable|string',
            'nisn' => 'exclude_unless:role_type,siswa|required_without:nik|nullable|string',
            'nik' => 'exclude_unless:role_type,siswa|required_without:nisn|nullable|string',
            // Guru/Tendik validation inputs
            'nip' => 'exclude_unless:role_type,guru_tendik|required_without:nuptk|nullable|string',
            'nuptk' => 'exclude_unless:role_type,guru_tendik|required_without:nip|nullable|string',
        ]);

        if ($request->role_type === 'siswa') {
            // Find record in master data
            $master = MasterValidation::where('role', 'siswa')
                ->whereRaw('LOWER(nama_lengkap) = ?', [mb_strtolower(trim($request->nama_lengkap))])
                ->where(function ($q) use ($request) {
                    if ($request->nisn) $q->orWhere('nisn', $request->nisn);
                    if ($request->nik) $q->orWhere('nik', $request->nik);
                })
                ->first();

            if (!$master) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Siswa tidak ditemukan di Master Dapodik. Pastikan Nama Lengkap dan NISN/NIK sesuai.'
                ], 422);
            }

            // Quota limit default for Siswa: 2 GB
            $quota = 2 * 1024 * 1024 * 1024;

            $user = User::create([
                'name' => $master->nama_lengkap,
                'email' => $request->email,
                'username' => $request->username,
                'role' => 'siswa',
                'target_kelas' => $master->kelas,
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
                return response()->json([
                    'success' => false,
                    'message' => 'Data Guru/Tendik tidak ditemukan di Master Dapodik. Pastikan Email dan NIP/NUPTK sesuai.'
                ], 422);
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
                if ($master->tugas_tambahan && stripos($master->tugas_tambahan, 'Wali Kelas') !== false) {
                    $role = 'guru_wali';
                    $quota = 20 * 1024 * 1024 * 1024; // Wali Kelas: 20 GB
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
                'username' => $request->username,
                'role' => $role,
                'target_kelas' => $targetKelas,
                'target_jurusan' => $targetJurusan,
                'storage_limit_bytes' => $quota,
                'storage_used_bytes' => 0,
                'password' => Hash::make($request->password),
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully and validated with Dapodik.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data' => $this->formatUser($user),
        ], 201);
    }

    /**
     * POST /api/v1/auth/login
     * Authenticate user and return a token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data' => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Revokes the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/v1/auth/me
     * Returns the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'User retrieved.',
            'data'    => $this->formatUser($request->user()),
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'role'           => $user->role,
            'target_kelas'   => $user->target_kelas,
            'target_jurusan' => $user->target_jurusan,
            'storage_used'   => (int) $user->storage_used,
            'storage_quota'  => (int) $user->storage_quota,
            'storage_used_mb'  => round($user->storage_used / 1024 / 1024, 2),
            'storage_quota_mb' => round($user->storage_quota / 1024 / 1024, 2),
            'created_at'     => $user->created_at?->toIso8601String(),
        ];
    }
}
