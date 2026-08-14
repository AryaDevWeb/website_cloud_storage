<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 users table restructure:
 *  - Remove nisn and nip columns (moved to student_profiles / teacher_profiles)
 *  - Remove is_active boolean (replaced by status string with enum cast)
 *  - Add status column with default 'pending'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop unique indexes before dropping columns (required by PostgreSQL)
            $table->dropUnique(['nisn']);
            $table->dropUnique(['nip']);

            // Remove columns being moved to profile tables or replaced
            $table->dropColumn(['nisn', 'nip', 'is_active']);

            // Add the new status column
            // Using string for cross-DB compatibility (SQLite used in testing)
            $table->string('status', 20)->default('pending')->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');

            $table->string('nisn')->nullable()->unique()->after('email');
            $table->string('nip')->nullable()->unique()->after('nisn');
            $table->boolean('is_active')->default(true)->after('profile_photo');
        });
    }
};
