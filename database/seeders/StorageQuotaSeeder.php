<?php

namespace Database\Seeders;

use App\Models\StorageQuota;
use App\Models\User;
use Illuminate\Database\Seeder;

class StorageQuotaSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->each(function (User $user): void {
            StorageQuota::firstOrCreate(
                ['user_id' => $user->id],
                ['max_bytes' => StorageQuota::defaultMaxBytesFor($user), 'used_bytes' => 0],
            );
        });
    }
}
