<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1 GB in bytes
        $quota = 1 * 1024 * 1024 * 1024;

        Schema::table('users', function (Blueprint $table) use ($quota) {
            $table->unsignedBigInteger('storage_quota')->default($quota)->change();
        });

        // Update all existing users to 1GB
        DB::table('users')->update(['storage_quota' => $quota]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 5 GB in bytes
        $quota = 5 * 1024 * 1024 * 1024;

        Schema::table('users', function (Blueprint $table) use ($quota) {
            $table->unsignedBigInteger('storage_quota')->default($quota)->change();
        });

        DB::table('users')->update(['storage_quota' => $quota]);
    }
};
