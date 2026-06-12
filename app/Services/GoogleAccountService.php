<?php

namespace App\Services;

use App\Models\MasterValidation;
use App\Models\User;

class GoogleAccountService
{
    public function findOrCreateFromGoogleUser(object $googleUser): ?User
    {
        $email = strtolower((string) $googleUser->email);

        $user = User::where('google_id', $googleUser->id)->first()
            ?: User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'role' => $this->roleForGoogleEmail($email, $user->role),
                'storage_limit_bytes' => $user->storage_limit_bytes ?: 1 * 1024 * 1024 * 1024,
            ]);

            return $user->fresh();
        }

        if ($this->isLocalAdminEmail($email)) {
            return User::create([
                'name' => $googleUser->name,
                'email' => $email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'role' => 'admin',
                'storage_limit_bytes' => 100 * 1024 * 1024 * 1024,
                'storage_used_bytes' => 0,
            ]);
        }

        $master = MasterValidation::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$master) {
            return null;
        }

        $profile = $this->profileFromMaster($master);

        return User::create([
            'name' => $master->nama_lengkap ?: $googleUser->name,
            'email' => $email,
            'google_id' => $googleUser->id,
            'avatar' => $googleUser->avatar,
            'role' => $profile['role'],
            'target_kelas' => $profile['target_kelas'],
            'target_jurusan' => $profile['target_jurusan'],
            'storage_limit_bytes' => $profile['quota'],
            'storage_used_bytes' => 0,
        ]);
    }

    public function roleForGoogleEmail(string $email, ?string $currentRole = null): string
    {
        if ($this->isLocalAdminEmail($email)) {
            return 'admin';
        }

        return $currentRole ?: 'siswa';
    }

    private function profileFromMaster(MasterValidation $master): array
    {
        if ($master->role === 'siswa') {
            return [
                'role' => 'siswa',
                'target_kelas' => $master->kelas,
                'target_jurusan' => $master->jurusan,
                'quota' => 2 * 1024 * 1024 * 1024,
            ];
        }

        if ($master->role === 'admin') {
            return [
                'role' => 'admin',
                'target_kelas' => null,
                'target_jurusan' => $master->jurusan,
                'quota' => 100 * 1024 * 1024 * 1024,
            ];
        }

        if ($master->role === 'guru') {
            $isWali = $master->tugas_tambahan && stripos($master->tugas_tambahan, 'Wali Kelas') !== false;

            return [
                'role' => $isWali ? 'guru_wali' : 'guru_jurusan',
                'target_kelas' => $isWali ? $this->resolveWaliKelas($master) : null,
                'target_jurusan' => $master->jurusan,
                'quota' => $isWali ? 20 * 1024 * 1024 * 1024 : 50 * 1024 * 1024 * 1024,
            ];
        }

        return [
            'role' => 'tendik',
            'target_kelas' => null,
            'target_jurusan' => $master->jurusan,
            'quota' => 100 * 1024 * 1024 * 1024,
        ];
    }

    private function resolveWaliKelas(MasterValidation $master): ?string
    {
        if ($master->kelas) {
            return $master->kelas;
        }

        preg_match('/Wali Kelas\s+([A-Za-z0-9\s]+)/i', (string) $master->tugas_tambahan, $matches);

        return isset($matches[1]) ? trim($matches[1]) : null;
    }

    private function isLocalAdminEmail(string $email): bool
    {
        $adminEmails = collect(explode(',', (string) env('LOCAL_ADMIN_EMAILS', '')))
            ->map(fn ($item) => strtolower(trim($item)))
            ->filter()
            ->all();

        return in_array(strtolower($email), $adminEmails, true);
    }
}
