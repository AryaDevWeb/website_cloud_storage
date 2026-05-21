<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename galleries table to files
        if (Schema::hasTable('galleries') && !Schema::hasTable('files')) {
            Schema::rename('galleries', 'files');
        }

        // 2. Add columns to users table and rename quota/used columns
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('siswa'); // admin, guru_wali, guru_jurusan, tendik, siswa
            }
            if (!Schema::hasColumn('users', 'target_kelas')) {
                $table->string('target_kelas')->nullable(); // e.g. XII RPL
            }
            if (!Schema::hasColumn('users', 'target_jurusan')) {
                $table->string('target_jurusan')->nullable(); // e.g. RPL
            }
            if (Schema::hasColumn('users', 'storage_quota') && !Schema::hasColumn('users', 'storage_limit_bytes')) {
                $table->renameColumn('storage_quota', 'storage_limit_bytes');
            }
            if (Schema::hasColumn('users', 'storage_used') && !Schema::hasColumn('users', 'storage_used_bytes')) {
                $table->renameColumn('storage_used', 'storage_used_bytes');
            }
        });

        // 3. Create master_validations table
        if (!Schema::hasTable('master_validations')) {
            Schema::create('master_validations', function (Blueprint $table) {
                $table->id();
                $table->string('nama_lengkap');
                $table->string('nisn')->nullable()->index();
                $table->string('nik')->nullable()->index();
                $table->string('nip')->nullable()->index();
                $table->string('nuptk')->nullable()->index();
                $table->string('email')->nullable()->index();
                $table->string('role'); // siswa, guru, tendik
                $table->string('kelas')->nullable();
                $table->string('jurusan')->nullable();
                $table->string('jenis_ptk')->nullable();
                $table->string('tugas_tambahan')->nullable();
                $table->timestamps();
            });
        }

        // 4. Update folders table for scoping and shared drives
        Schema::table('folders', function (Blueprint $table) {
            if (!Schema::hasColumn('folders', 'is_shared_drive')) {
                $table->boolean('is_shared_drive')->default(false);
            }
            if (!Schema::hasColumn('folders', 'scope_kelas')) {
                $table->string('scope_kelas')->nullable();
            }
            if (!Schema::hasColumn('folders', 'scope_jurusan')) {
                $table->string('scope_jurusan')->nullable();
            }
            if (!Schema::hasColumn('folders', 'scope_tendik')) {
                $table->string('scope_tendik')->nullable();
            }
            if (!Schema::hasColumn('folders', 'is_assignment_folder')) {
                $table->boolean('is_assignment_folder')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert folders table changes
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn([
                'is_shared_drive',
                'scope_kelas',
                'scope_jurusan',
                'scope_tendik',
                'is_assignment_folder'
            ]);
        });

        // 2. Drop master_validations table
        Schema::dropIfExists('master_validations');

        // 3. Revert users table changes
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'storage_limit_bytes') && !Schema::hasColumn('users', 'storage_quota')) {
                $table->renameColumn('storage_limit_bytes', 'storage_quota');
            }
            if (Schema::hasColumn('users', 'storage_used_bytes') && !Schema::hasColumn('users', 'storage_used')) {
                $table->renameColumn('storage_used_bytes', 'storage_used');
            }
            $table->dropColumn(['role', 'target_kelas', 'target_jurusan']);
        });

        // 4. Rename files back to galleries
        if (Schema::hasTable('files') && !Schema::hasTable('galleries')) {
            Schema::rename('files', 'galleries');
        }
    }
};
